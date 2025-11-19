#!/bin/bash

# سكريبت لأخذ نسخة احتياطية من قاعدة البيانات ورفع المشروع على Git
# Script to backup database and push project to Git

set -e  # إيقاف التنفيذ عند حدوث خطأ

# الألوان للرسائل
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== بدء عملية النسخ الاحتياطي والرفع على Git ===${NC}\n"

# قراءة إعدادات قاعدة البيانات من .env
if [ ! -f .env ]; then
    echo -e "${RED}❌ خطأ: ملف .env غير موجود${NC}"
    exit 1
fi

# تحميل متغيرات البيئة
export $(grep -v '^#' .env | xargs)

# تحديد نوع قاعدة البيانات
DB_CONNECTION=${DB_CONNECTION:-mysql}
DB_HOST=${DB_HOST:-127.0.0.1}
DB_DATABASE=${DB_DATABASE:-laravel}
DB_USERNAME=${DB_USERNAME:-root}
DB_PASSWORD=${DB_PASSWORD:-}

# إنشاء مجلد النسخ الاحتياطي إذا لم يكن موجوداً
BACKUP_DIR="database_backups"
mkdir -p "$BACKUP_DIR"

# إنشاء اسم الملف مع التاريخ والوقت
TIMESTAMP=$(date +"%Y%m%d_%H%M%S")
BACKUP_FILE="$BACKUP_DIR/${DB_DATABASE}_backup_${TIMESTAMP}.sql"

echo -e "${YELLOW}📦 نوع قاعدة البيانات: ${DB_CONNECTION}${NC}"
echo -e "${YELLOW}📦 قاعدة البيانات: ${DB_DATABASE}${NC}"
echo -e "${YELLOW}📦 الملف الاحتياطي: ${BACKUP_FILE}${NC}\n"

# أخذ نسخة احتياطية حسب نوع قاعدة البيانات
if [ "$DB_CONNECTION" = "pgsql" ] || [ "$DB_CONNECTION" = "postgres" ]; then
    # PostgreSQL
    echo -e "${BLUE}🔄 جاري أخذ نسخة احتياطية من PostgreSQL...${NC}"
    
    if [ -z "$DB_PASSWORD" ]; then
        PGPASSWORD="" pg_dump -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -F p > "$BACKUP_FILE"
    else
        PGPASSWORD="$DB_PASSWORD" pg_dump -h "$DB_HOST" -U "$DB_USERNAME" -d "$DB_DATABASE" -F p > "$BACKUP_FILE"
    fi
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ تم أخذ النسخة الاحتياطية بنجاح${NC}\n"
    else
        echo -e "${RED}❌ فشل أخذ النسخة الاحتياطية${NC}"
        exit 1
    fi
    
elif [ "$DB_CONNECTION" = "mysql" ] || [ "$DB_CONNECTION" = "mariadb" ]; then
    # MySQL/MariaDB
    echo -e "${BLUE}🔄 جاري أخذ نسخة احتياطية من MySQL...${NC}"
    
    DB_PORT=${DB_PORT:-3306}
    
    if [ -z "$DB_PASSWORD" ]; then
        mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "$DB_DATABASE" > "$BACKUP_FILE"
    else
        mysqldump -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" > "$BACKUP_FILE"
    fi
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ تم أخذ النسخة الاحتياطية بنجاح${NC}\n"
    else
        echo -e "${RED}❌ فشل أخذ النسخة الاحتياطية${NC}"
        exit 1
    fi
    
else
    echo -e "${RED}❌ نوع قاعدة البيانات غير مدعوم: ${DB_CONNECTION}${NC}"
    echo -e "${YELLOW}المدعومة: mysql, mariadb, pgsql, postgres${NC}"
    exit 1
fi

# ضغط الملف الاحتياطي (اختياري)
echo -e "${BLUE}🔄 جاري ضغط الملف الاحتياطي...${NC}"
gzip -f "$BACKUP_FILE"
BACKUP_FILE="${BACKUP_FILE}.gz"
echo -e "${GREEN}✓ تم ضغط الملف: ${BACKUP_FILE}${NC}\n"

# إنشاء ملف README في مجلد النسخ الاحتياطي
cat > "$BACKUP_DIR/README.md" << EOF
# نسخ احتياطية قاعدة البيانات

هذا المجلد يحتوي على نسخ احتياطية من قاعدة البيانات.

## استعادة النسخة الاحتياطية

### MySQL/MariaDB:
\`\`\`bash
gunzip database_backups/filename.sql.gz
mysql -u username -p database_name < database_backups/filename.sql
\`\`\`

### PostgreSQL:
\`\`\`bash
gunzip database_backups/filename.sql.gz
psql -U username -d database_name -f database_backups/filename.sql
\`\`\`

**ملاحظة:** تأكد من قراءة ملف .env لمعرفة إعدادات قاعدة البيانات.
EOF

# التأكد من أن مجلد النسخ الاحتياطي غير موجود في .gitignore
if grep -q "^database_backups" .gitignore 2>/dev/null; then
    echo -e "${YELLOW}⚠ تم العثور على database_backups في .gitignore، سيتم إزالته${NC}"
    sed -i '/^database_backups/d' .gitignore
fi

# إضافة جميع الملفات إلى Git
echo -e "${BLUE}🔄 جاري إضافة الملفات إلى Git...${NC}"
git add .

# التحقق من وجود تغييرات
if git diff --staged --quiet; then
    echo -e "${YELLOW}⚠ لا توجد تغييرات لإضافتها${NC}"
else
    # إنشاء رسالة commit
    COMMIT_MESSAGE="Backup and push: Database backup $(date +"%Y-%m-%d %H:%M:%S")"
    
    echo -e "${BLUE}🔄 جاري عمل commit...${NC}"
    git commit -m "$COMMIT_MESSAGE"
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ تم عمل commit بنجاح${NC}\n"
    else
        echo -e "${RED}❌ فشل عمل commit${NC}"
        exit 1
    fi
    
    # رفع التغييرات إلى Git
    echo -e "${BLUE}🔄 جاري رفع التغييرات إلى Git...${NC}"
    git push origin $(git branch --show-current)
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ تم رفع التغييرات بنجاح${NC}\n"
    else
        echo -e "${RED}❌ فشل رفع التغييرات${NC}"
        exit 1
    fi
fi

# عرض معلومات الملف الاحتياطي
FILE_SIZE=$(du -h "$BACKUP_FILE" | cut -f1)
echo -e "${GREEN}=== تم الانتهاء بنجاح ===${NC}"
echo -e "${GREEN}📁 الملف الاحتياطي: ${BACKUP_FILE}${NC}"
echo -e "${GREEN}📊 حجم الملف: ${FILE_SIZE}${NC}"
echo -e "${GREEN}✅ تم رفع المشروع على Git${NC}\n"

