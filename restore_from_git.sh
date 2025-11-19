#!/bin/bash

# سكريبت عكسي لاستعادة المشروع من Git وتحديث قاعدة البيانات من النسخة الاحتياطية
# Script to restore project from Git and update database from backup

set -e  # إيقاف التنفيذ عند حدوث خطأ

# الألوان للرسائل
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}=== بدء عملية الاستعادة من Git وتحديث قاعدة البيانات ===${NC}\n"

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
DB_PORT=${DB_PORT:-3306}

# التحقق من وجود مجلد النسخ الاحتياطي
BACKUP_DIR="database_backups"
if [ ! -d "$BACKUP_DIR" ]; then
    echo -e "${RED}❌ خطأ: مجلد النسخ الاحتياطي غير موجود: $BACKUP_DIR${NC}"
    exit 1
fi

# البحث عن أحدث ملف backup
echo -e "${BLUE}🔍 البحث عن أحدث نسخة احتياطية...${NC}"

# البحث عن ملفات .sql.gz أو .sql
LATEST_BACKUP=$(find "$BACKUP_DIR" -name "*.sql.gz" -type f | sort -r | head -n 1)

if [ -z "$LATEST_BACKUP" ]; then
    # البحث عن ملفات .sql غير المضغوطة
    LATEST_BACKUP=$(find "$BACKUP_DIR" -name "*.sql" -type f | sort -r | head -n 1)
fi

if [ -z "$LATEST_BACKUP" ]; then
    echo -e "${RED}❌ خطأ: لم يتم العثور على أي نسخة احتياطية${NC}"
    exit 1
fi

echo -e "${GREEN}✓ تم العثور على النسخة الاحتياطية: ${LATEST_BACKUP}${NC}\n"

# استعادة المشروع من Git
echo -e "${BLUE}🔄 جاري استعادة المشروع من Git...${NC}"
git pull origin $(git branch --show-current)

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✓ تم استعادة المشروع بنجاح${NC}\n"
else
    echo -e "${YELLOW}⚠ تحذير: فشل pull من Git، سيتم المتابعة مع استعادة قاعدة البيانات${NC}\n"
fi

# فك الضغط إذا كان الملف مضغوطاً
RESTORE_FILE="$LATEST_BACKUP"
if [[ "$LATEST_BACKUP" == *.gz ]]; then
    echo -e "${BLUE}🔄 جاري فك الضغط...${NC}"
    RESTORE_FILE="${LATEST_BACKUP%.gz}"
    gunzip -c "$LATEST_BACKUP" > "$RESTORE_FILE"
    echo -e "${GREEN}✓ تم فك الضغط: ${RESTORE_FILE}${NC}\n"
fi

# استعادة قاعدة البيانات حسب نوعها
echo -e "${YELLOW}⚠ تحذير: سيتم استبدال قاعدة البيانات الحالية بالنسخة الاحتياطية${NC}"
read -p "هل تريد المتابعة؟ (y/n): " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${YELLOW}تم الإلغاء${NC}"
    exit 0
fi

echo -e "${BLUE}🔄 جاري استعادة قاعدة البيانات...${NC}"

if [ "$DB_CONNECTION" = "pgsql" ] || [ "$DB_CONNECTION" = "postgres" ]; then
    # PostgreSQL
    if [ -z "$DB_PASSWORD" ]; then
        PGPASSWORD="" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE" -f "$RESTORE_FILE"
    else
        PGPASSWORD="$DB_PASSWORD" psql -h "$DB_HOST" -p "$DB_PORT" -U "$DB_USERNAME" -d "$DB_DATABASE" -f "$RESTORE_FILE"
    fi
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ تم استعادة قاعدة البيانات بنجاح${NC}\n"
    else
        echo -e "${RED}❌ فشل استعادة قاعدة البيانات${NC}"
        exit 1
    fi
    
elif [ "$DB_CONNECTION" = "mysql" ] || [ "$DB_CONNECTION" = "mariadb" ]; then
    # MySQL/MariaDB
    if [ -z "$DB_PASSWORD" ]; then
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" "$DB_DATABASE" < "$RESTORE_FILE"
    else
        mysql -h "$DB_HOST" -P "$DB_PORT" -u "$DB_USERNAME" -p"$DB_PASSWORD" "$DB_DATABASE" < "$RESTORE_FILE"
    fi
    
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}✓ تم استعادة قاعدة البيانات بنجاح${NC}\n"
    else
        echo -e "${RED}❌ فشل استعادة قاعدة البيانات${NC}"
        exit 1
    fi
    
else
    echo -e "${RED}❌ نوع قاعدة البيانات غير مدعوم: ${DB_CONNECTION}${NC}"
    exit 1
fi

# تنظيف الملف المؤقت إذا كان مضغوطاً
if [[ "$LATEST_BACKUP" == *.gz ]]; then
    rm -f "$RESTORE_FILE"
fi

echo -e "${GREEN}=== تم الانتهاء بنجاح ===${NC}"
echo -e "${GREEN}✅ تم استعادة المشروع من Git${NC}"
echo -e "${GREEN}✅ تم تحديث قاعدة البيانات من النسخة الاحتياطية${NC}\n"

