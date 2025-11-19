import argparse
import json
import os
from datetime import datetime, timedelta
from pathlib import Path

import psycopg2
import requests

# محاولة تحميل python-dotenv إذا كان متوفرًا
try:
    from dotenv import load_dotenv
    DOTENV_AVAILABLE = True
except ImportError:
    DOTENV_AVAILABLE = False


class Logger:
    def __init__(self, log_file="zoho_cli.log"):
        self.log_file = log_file

    def log(self, message, level="INFO"):
        timestamp = datetime.now().strftime("%Y-%m-%d %H:%M:%S")
        entry = f"[{timestamp}] [{level}] {message}\n"
        try:
            with open(self.log_file, "a", encoding="utf-8") as handler:
                handler.write(entry)
        except OSError:
            print(entry, end="")

    def info(self, message):
        self.log(message, "INFO")

    def warning(self, message):
        self.log(message, "WARNING")

    def error(self, message):
        self.log(message, "ERROR")

    def success(self, message):
        self.log(message, "SUCCESS")


class ZohoTicketsSync:
    def __init__(self, db_config, zoho_config, logger=None):
        self.db_config = db_config
        self.zoho_config = zoho_config
        self.logger = logger or Logger()
        self.session = requests.Session()

    # ------------------------------------------------------------------
    # إعدادات الاتصال
    # ------------------------------------------------------------------
    def connect_db(self):
        return psycopg2.connect(**self.db_config)

    def get_access_token(self):
        payload = {
            "refresh_token": self.zoho_config["refresh_token"],
            "client_id": self.zoho_config["client_id"],
            "client_secret": self.zoho_config["client_secret"],
            "grant_type": "refresh_token",
        }
        response = self.session.post(self.zoho_config["token_url"], data=payload, timeout=30)
        if response.status_code != 200:
            raise RuntimeError(f"فشل في جلب رمز الوصول: {response.status_code} - {response.text[:200]}")
        data = response.json()
        token = data.get("access_token")
        if not token:
            raise RuntimeError("الرد لم يحتوي على access_token")
        return token

    # ------------------------------------------------------------------
    # قراءة آخر وقت تم حفظه
    # ------------------------------------------------------------------
    def get_last_ticket_timestamp(self):
        query = """
            SELECT GREATEST(
                MAX(created_at_zoho),
                MAX(COALESCE(closed_at_zoho, '1970-01-01'::timestamp))
            ) AS last_time
            FROM zoho_tickets_cache
        """
        with self.connect_db() as conn:
            with conn.cursor() as cursor:
                cursor.execute(query)
                result = cursor.fetchone()
                return result[0] if result and result[0] else None

    # ------------------------------------------------------------------
    # أدوات مساعدة
    # ------------------------------------------------------------------
    def _format_range(self, start_dt, end_dt):
        return (
            start_dt.strftime("%Y-%m-%dT%H:%M:%S.000Z"),
            end_dt.strftime("%Y-%m-%dT%H:%M:%S.000Z"),
        )

    def _extract_closed_by(self, ticket):
        closed_by = None
        cf_section = ticket.get("cf")
        if isinstance(cf_section, dict):
            closed_by = cf_section.get("cf_closed_by")
        if not closed_by:
            custom_fields = ticket.get("customFields")
            if isinstance(custom_fields, dict):
                closed_by = custom_fields.get("Closed By") or custom_fields.get("cf_closed_by")
            elif isinstance(custom_fields, list):
                for field in custom_fields:
                    if isinstance(field, dict) and field.get("apiName") in {"cf_closed_by", "Closed_By"}:
                        closed_by = field.get("value")
                        if closed_by:
                            break
        if not closed_by:
            closed_by = ticket.get("cf_closed_by")
        return closed_by

    def _parse_iso_datetime(self, value):
        if not value:
            return None
        cleaned = value.replace("Z", "")
        try:
            return datetime.fromisoformat(cleaned)
        except ValueError:
            return None

    # ------------------------------------------------------------------
    # جلب التذاكر من Zoho
    # ------------------------------------------------------------------
    def fetch_tickets(self, start_dt, end_dt, page_limit=50):
        token = self.get_access_token()
        headers = {
            "Authorization": f"Zoho-oauthtoken {token}",
            "Content-Type": "application/json",
        }
        start_str, end_str = self._format_range(start_dt, end_dt)

        self.logger.info(f"بدء جلب التذاكر في الفترة: {start_str} → {end_str}")
        all_tickets = []
        page = 1
        while page <= page_limit:
            offset = (page - 1) * 100
            params = {
                "orgId": self.zoho_config["org_id"],
                "limit": 100,
                "from": offset,
                "sortBy": "-modifiedTime",
                "modifiedTimeRange": f"{start_str},{end_str}",
            }
            response = self.session.get(
                f"{self.zoho_config['base_url']}/tickets/search",
                headers=headers,
                params=params,
                timeout=60,
            )
            if response.status_code != 200:
                if response.status_code in (400, 422):
                    self.logger.warning("انتهت النتائج أو الطلب غير صالح، التوقف.")
                    break
                raise RuntimeError(f"خطأ API: {response.status_code} - {response.text[:200]}")
            data = response.json()
            tickets = data.get("data", [])
            if not tickets:
                self.logger.info("لا توجد نتائج إضافية، التوقف.")
                break
            self.logger.info(f"صفحة {page}: تم استرجاع {len(tickets)} تذكرة")
            all_tickets.extend(tickets)
            if len(tickets) < 100:
                break
            page += 1
        self.logger.success(f"اكتمل الجلب: عدد التذاكر {len(all_tickets)}")
        return all_tickets

    # ------------------------------------------------------------------
    # حفظ التذاكر في قاعدة البيانات
    # ------------------------------------------------------------------
    def save_tickets(self, tickets):
        insert_query = """
            INSERT INTO zoho_tickets_cache (
                zoho_ticket_id, ticket_number, subject, status, department_id,
                created_at_zoho, closed_at_zoho, thread_count, raw_data, closed_by_name
            ) VALUES (%s, %s, %s, %s, %s, %s, %s, %s, %s, %s)
        """
        update_query = """
            UPDATE zoho_tickets_cache
            SET ticket_number = %s, subject = %s, status = %s, department_id = %s,
                created_at_zoho = %s, closed_at_zoho = %s, thread_count = %s,
                raw_data = %s, closed_by_name = %s, updated_at = NOW()
            WHERE zoho_ticket_id = %s
        """
        saved = 0
        updated = 0
        with self.connect_db() as conn:
            with conn.cursor() as cursor:
                for ticket in tickets:
                    ticket_id = ticket.get("id")
                    cursor.execute(
                        "SELECT id FROM zoho_tickets_cache WHERE zoho_ticket_id = %s",
                        (ticket_id,),
                    )
                    exists = cursor.fetchone()
                    ticket_number = ticket.get("ticketNumber", "")
                    subject = ticket.get("subject", "")
                    status_val = ticket.get("status", "Open")
                    department_id = ticket.get("departmentId", "")
                    thread_count = ticket.get("threadCount", 0)
                    created_at = self._parse_iso_datetime(ticket.get("createdTime"))
                    closed_at = self._parse_iso_datetime(ticket.get("closedTime"))
                    raw_json = json.dumps(ticket)
                    closed_by = self._extract_closed_by(ticket)
                    if exists:
                        cursor.execute(
                            update_query,
                            (
                                ticket_number,
                                subject,
                                status_val,
                                department_id,
                                created_at,
                                closed_at,
                                thread_count,
                                raw_json,
                                closed_by,
                                ticket_id,
                            ),
                        )
                        updated += 1
                    else:
                        cursor.execute(
                            insert_query,
                            (
                                ticket_id,
                                ticket_number,
                                subject,
                                status_val,
                                department_id,
                                created_at,
                                closed_at,
                                thread_count,
                                raw_json,
                                closed_by,
                            ),
                        )
                        saved += 1
        self.logger.success(f"حفظ التذاكر اكتمل: جديد {saved}, محدث {updated}")
        return saved, updated

    # ------------------------------------------------------------------
    # عمليات CLI
    # ------------------------------------------------------------------
    def sync_between(self, start_dt, end_dt):
        tickets = self.fetch_tickets(start_dt, end_dt)
        if not tickets:
            self.logger.info("لا توجد تذاكر ضمن الفترة المحددة.")
            return {"saved": 0, "updated": 0, "count": 0}
        saved, updated = self.save_tickets(tickets)
        return {"saved": saved, "updated": updated, "count": len(tickets)}

    def sync_incremental(self, fallback_days=None):
        last_time = self.get_last_ticket_timestamp()
        if not last_time:
            if fallback_days is None:
                raise RuntimeError("لا توجد تذاكر سابقة. استخدم --start أو --days لبدء المزامنة الأولى.")
            self.logger.warning("لا توجد بيانات سابقة، سيتم البدء من الفترة الاحتياطية.")
            start_dt = datetime.now() - timedelta(days=fallback_days)
        else:
            start_dt = last_time - timedelta(minutes=1)
        end_dt = datetime.now()
        self.logger.info(f"تشغيل مزامنة من {start_dt} إلى {end_dt}")
        return self.sync_between(start_dt, end_dt)


def load_env_file(env_path=None):
    """تحميل ملف .env إذا كان متوفرًا"""
    if DOTENV_AVAILABLE:
        if env_path:
            env_file = Path(env_path)
            if env_file.exists():
                load_dotenv(env_file)
            else:
                print(f"تحذير: ملف .env غير موجود في المسار المحدد: {env_path}")
        else:
            # البحث عن ملف .env في المجلد الحالي أو المجلد الرئيسي
            current_dir = Path.cwd()
            home_dir = Path.home()
            for path in [current_dir / ".env", home_dir / ".env", Path("/root/.env")]:
                if path.exists():
                    load_dotenv(path)
                    break
            else:
                # محاولة تحميل من أي مكان
                load_dotenv()


def build_config(args=None):
    """بناء إعدادات قاعدة البيانات و Zoho من المتغيرات البيئية أو الخيارات"""
    # تحميل ملف .env تلقائيًا
    env_file_path = args.env_file if args and hasattr(args, "env_file") and args.env_file else None
    load_env_file(env_file_path)
    
    # إعدادات قاعدة البيانات - الأولوية للخيارات ثم المتغيرات البيئية ثم القيم الافتراضية
    db_config = {
        "host": (
            args.db_host if args and hasattr(args, "db_host") and args.db_host
            else os.getenv("ZOHO_DB_HOST", "127.0.0.1")
        ),
        "port": int(
            args.db_port if args and hasattr(args, "db_port") and args.db_port
            else os.getenv("ZOHO_DB_PORT", "5432")
        ),
        "database": (
            args.db_name if args and hasattr(args, "db_name") and args.db_name
            else os.getenv("ZOHO_DB_NAME", "CRM_ALL")
        ),
        "user": (
            args.db_user if args and hasattr(args, "db_user") and args.db_user
            else os.getenv("ZOHO_DB_USER", "postgres")
        ),
        "password": (
            args.db_password if args and hasattr(args, "db_password") and args.db_password
            else os.getenv("ZOHO_DB_PASSWORD", os.getenv("DB_PASSWORD", ""))
        ),
    }
    
    # إعدادات Zoho
    zoho_config = {
        "client_id": (
            args.zoho_client_id if args and hasattr(args, "zoho_client_id") and args.zoho_client_id
            else os.getenv("ZOHO_CLIENT_ID", "")
        ),
        "client_secret": (
            args.zoho_client_secret if args and hasattr(args, "zoho_client_secret") and args.zoho_client_secret
            else os.getenv("ZOHO_CLIENT_SECRET", "")
        ),
        "refresh_token": (
            args.zoho_refresh_token if args and hasattr(args, "zoho_refresh_token") and args.zoho_refresh_token
            else os.getenv("ZOHO_REFRESH_TOKEN", "")
        ),
        "org_id": (
            args.zoho_org_id if args and hasattr(args, "zoho_org_id") and args.zoho_org_id
            else os.getenv("ZOHO_ORG_ID", "")
        ),
        "token_url": os.getenv("ZOHO_TOKEN_URL", "https://accounts.zoho.com/oauth/v2/token"),
        "base_url": os.getenv("ZOHO_BASE_URL", "https://desk.zoho.com/api/v1"),
    }
    
    # التحقق من وجود القيم المطلوبة لـ Zoho
    missing = [key for key, value in zoho_config.items() if not value and key in ["client_id", "client_secret", "refresh_token", "org_id"]]
    if missing:
        raise RuntimeError(f"قيم Zoho مفقودة في المتغيرات البيئية أو الخيارات: {', '.join(missing)}")
    
    return db_config, zoho_config


def parse_args():
    parser = argparse.ArgumentParser(
        description="أداة مزامنة تذاكر Zoho عبر سطر الأوامر - تعمل محليًا أو عبر الشبكة."
    )
    
    # خيارات عامة لجميع الأوامر - إعدادات قاعدة البيانات
    parser.add_argument(
        "--db-host",
        type=str,
        default=None,
        help="عنوان IP أو اسم المضيف لقاعدة البيانات (افتراضي: 127.0.0.1 أو من ZOHO_DB_HOST)",
    )
    parser.add_argument(
        "--db-port",
        type=int,
        default=None,
        help="منفذ قاعدة البيانات (افتراضي: 5432 أو من ZOHO_DB_PORT)",
    )
    parser.add_argument(
        "--db-name",
        type=str,
        default=None,
        help="اسم قاعدة البيانات (افتراضي: CRM_ALL أو من ZOHO_DB_NAME)",
    )
    parser.add_argument(
        "--db-user",
        type=str,
        default=None,
        help="اسم مستخدم قاعدة البيانات (افتراضي: postgres أو من ZOHO_DB_USER)",
    )
    parser.add_argument(
        "--db-password",
        type=str,
        default=None,
        help="كلمة مرور قاعدة البيانات (افتراضي: من ZOHO_DB_PASSWORD أو DB_PASSWORD)",
    )
    
    # خيارات Zoho (اختيارية - يمكن استخدام المتغيرات البيئية)
    parser.add_argument(
        "--zoho-client-id",
        type=str,
        default=None,
        help="Zoho Client ID (أو من ZOHO_CLIENT_ID)",
    )
    parser.add_argument(
        "--zoho-client-secret",
        type=str,
        default=None,
        help="Zoho Client Secret (أو من ZOHO_CLIENT_SECRET)",
    )
    parser.add_argument(
        "--zoho-refresh-token",
        type=str,
        default=None,
        help="Zoho Refresh Token (أو من ZOHO_REFRESH_TOKEN)",
    )
    parser.add_argument(
        "--zoho-org-id",
        type=str,
        default=None,
        help="Zoho Organization ID (أو من ZOHO_ORG_ID)",
    )
    
    # خيارات إضافية
    parser.add_argument(
        "--env-file",
        type=str,
        default=None,
        help="مسار ملف .env (افتراضي: البحث تلقائيًا)",
    )
    
    subparsers = parser.add_subparsers(dest="command", required=True)

    sync_parser = subparsers.add_parser(
        "sync",
        help="مزامنة تزايدية بدايةً من آخر تاريخ محفوظ حتى الآن.",
    )
    sync_parser.add_argument(
        "--fallback-days",
        type=int,
        default=None,
        help="عدد الأيام الرجعية عند عدم توفر بيانات سابقة (لأول تشغيل).",
    )

    range_parser = subparsers.add_parser(
        "range",
        help="مزامنة ضمن نطاق زمني محدد.",
    )
    range_parser.add_argument("--start", required=True, help="تاريخ البداية بصيغة ISO مثل 2024-01-01T00:00:00")
    range_parser.add_argument("--end", required=True, help="تاريخ النهاية بصيغة ISO مثل 2024-01-31T23:59:59")

    days_parser = subparsers.add_parser(
        "days",
        help="مزامنة آخر عدد محدد من الأيام حتى الآن.",
    )
    days_parser.add_argument("--days", type=int, required=True, help="عدد الأيام الرجعية لجلب التذاكر.")

    return parser.parse_args()


def main():
    args = parse_args()
    db_config, zoho_config = build_config(args)
    logger = Logger()
    syncer = ZohoTicketsSync(db_config, zoho_config, logger=logger)
    
    # طباعة معلومات الاتصال (بدون كلمة المرور)
    logger.info(f"الاتصال بقاعدة البيانات: {db_config['host']}:{db_config['port']}/{db_config['database']} (مستخدم: {db_config['user']})")

    if args.command == "sync":
        result = syncer.sync_incremental(fallback_days=args.fallback_days)
    elif args.command == "range":
        start_dt = datetime.fromisoformat(args.start)
        end_dt = datetime.fromisoformat(args.end)
        result = syncer.sync_between(start_dt, end_dt)
    else:  # days
        end_dt = datetime.now()
        start_dt = end_dt - timedelta(days=args.days)
        result = syncer.sync_between(start_dt, end_dt)

    logger.success(
        f"انتهت العملية: إجمالي {result['count']}، جديد {result['saved']}, محدث {result['updated']}"
    )


if __name__ == "__main__":
    main()

