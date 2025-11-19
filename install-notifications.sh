#!/bin/bash

# =====================================================
# سكريبت التثبيت التلقائي لنظام الإشعارات
# Auto-Installation Script for Notifications System
# =====================================================

set -e  # إيقاف عند أي خطأ

# ألوان للطباعة
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# رموز
CHECK="${GREEN}✓${NC}"
CROSS="${RED}✗${NC}"
ARROW="${BLUE}→${NC}"

echo -e "${BLUE}"
echo "════════════════════════════════════════════════════════"
echo "  🔔 تثبيت نظام الإشعارات - Laravel Notifications"
echo "════════════════════════════════════════════════════════"
echo -e "${NC}"

# =====================================================
# التحقق من البيئة
# =====================================================

echo -e "${YELLOW}⚙ التحقق من البيئة...${NC}"

# التحقق من وجود مجلد Laravel
if [ ! -f "artisan" ]; then
    echo -e "${CROSS} خطأ: هذا ليس مجلد Laravel!"
    echo -e "   يرجى تشغيل السكريبت من داخل مجلد Laravel الرئيسي"
    exit 1
fi

echo -e "${CHECK} تم العثور على مشروع Laravel"

# التحقق من Composer
if ! command -v composer &> /dev/null; then
    echo -e "${CROSS} Composer غير مثبت!"
    exit 1
fi
echo -e "${CHECK} Composer متوفر"

# التحقق من npm
if ! command -v npm &> /dev/null; then
    echo -e "${CROSS} npm غير مثبت!"
    exit 1
fi
echo -e "${CHECK} npm متوفر"

# التحقق من مجلد المصدر
SOURCE_DIR="../laravel-notifications"
if [ ! -d "$SOURCE_DIR" ]; then
    echo -e "${CROSS} مجلد laravel-notifications غير موجود!"
    echo -e "   المتوقع: $SOURCE_DIR"
    exit 1
fi
echo -e "${CHECK} مجلد المصدر موجود"

echo ""

# =====================================================
# نسخ الملفات
# =====================================================

echo -e "${BLUE}📁 نسخ الملفات...${NC}"

# إنشاء المجلدات إذا لم تكن موجودة
mkdir -p app/Models
mkdir -p app/Http/Controllers
mkdir -p app/Events
mkdir -p app/Services
mkdir -p app/Providers
mkdir -p app/Console/Commands
mkdir -p database/migrations
mkdir -p database/factories
mkdir -p database/seeders
mkdir -p resources/views/components
mkdir -p resources/js
mkdir -p routes
mkdir -p config
mkdir -p tests/Feature

# نسخ Models
echo -e "${ARROW} نسخ Models..."
cp "$SOURCE_DIR/app/Models/Notification.php" app/Models/
cp "$SOURCE_DIR/app/Models/NotificationPreference.php" app/Models/
echo -e "${CHECK} Models"

# نسخ Controllers
echo -e "${ARROW} نسخ Controllers..."
cp "$SOURCE_DIR/app/Http/Controllers/NotificationController.php" app/Http/Controllers/
echo -e "${CHECK} Controllers"

# نسخ Events
echo -e "${ARROW} نسخ Events..."
cp "$SOURCE_DIR/app/Events/NotificationCreated.php" app/Events/
cp "$SOURCE_DIR/app/Events/NotificationCountUpdated.php" app/Events/
echo -e "${CHECK} Events"

# نسخ Services
echo -e "${ARROW} نسخ Services..."
cp "$SOURCE_DIR/app/Services/NotificationService.php" app/Services/
echo -e "${CHECK} Services"

# نسخ Providers
echo -e "${ARROW} نسخ Providers..."
cp "$SOURCE_DIR/app/Providers/BroadcastServiceProvider.php" app/Providers/
echo -e "${CHECK} Providers"

# نسخ Commands
echo -e "${ARROW} نسخ Console Commands..."
cp "$SOURCE_DIR/app/Console/Commands/CleanupOldNotifications.php" app/Console/Commands/
echo -e "${CHECK} Console Commands"

# نسخ Migration
echo -e "${ARROW} نسخ Migration..."
cp "$SOURCE_DIR/database/migrations/2025_10_01_000001_create_notifications_system_tables.php" database/migrations/
echo -e "${CHECK} Migration"

# نسخ Factory & Seeder
echo -e "${ARROW} نسخ Factory & Seeder..."
cp "$SOURCE_DIR/database/factories/NotificationFactory.php" database/factories/
cp "$SOURCE_DIR/database/seeders/NotificationSeeder.php" database/seeders/
echo -e "${CHECK} Factory & Seeder"

# نسخ Blade Component
echo -e "${ARROW} نسخ Blade Component..."
cp "$SOURCE_DIR/resources/views/components/notification-bell.blade.php" resources/views/components/
echo -e "${CHECK} Blade Component"

# نسخ JavaScript
echo -e "${ARROW} نسخ JavaScript files..."
cp "$SOURCE_DIR/resources/js/notifications.js" resources/js/
cp "$SOURCE_DIR/resources/js/bootstrap.js" resources/js/
cp "$SOURCE_DIR/resources/js/app.js" resources/js/
echo -e "${CHECK} JavaScript files"

# نسخ Routes
echo -e "${ARROW} نسخ Routes..."
if [ -f "routes/api.php" ]; then
    echo -e "${YELLOW}   تحذير: routes/api.php موجود - سيتم النسخ الاحتياطي${NC}"
    cp routes/api.php routes/api.php.backup
fi
cp "$SOURCE_DIR/routes/api.php" routes/
cp "$SOURCE_DIR/routes/channels.php" routes/
echo -e "${CHECK} Routes"

# نسخ Config
echo -e "${ARROW} نسخ Config..."
if [ -f "config/broadcasting.php" ]; then
    cp config/broadcasting.php config/broadcasting.php.backup
fi
cp "$SOURCE_DIR/config/broadcasting.php" config/
echo -e "${CHECK} Config"

# نسخ Tests
echo -e "${ARROW} نسخ Tests..."
cp "$SOURCE_DIR/tests/Feature/NotificationTest.php" tests/Feature/
echo -e "${CHECK} Tests"

# نسخ package.json & vite.config.js
echo -e "${ARROW} نسخ package.json & vite.config.js..."
if [ -f "package.json" ]; then
    cp package.json package.json.backup
fi
if [ -f "vite.config.js" ]; then
    cp vite.config.js vite.config.js.backup
fi
cp "$SOURCE_DIR/package.json" .
cp "$SOURCE_DIR/vite.config.js" .
echo -e "${CHECK} Package files"

echo -e "\n${GREEN}✓ تم نسخ جميع الملفات بنجاح!${NC}\n"

# =====================================================
# تثبيت التبعيات
# =====================================================

echo -e "${BLUE}📦 تثبيت التبعيات...${NC}"

# Composer
echo -e "${ARROW} تثبيت Pusher PHP Server..."
composer require pusher/pusher-php-server --no-interaction
echo -e "${CHECK} Pusher PHP Server"

# npm packages
echo -e "${ARROW} تثبيت JavaScript packages..."
npm install --silent
npm install alpinejs laravel-echo pusher-js --save-dev --silent
echo -e "${CHECK} JavaScript packages"

echo -e "\n${GREEN}✓ تم تثبيت جميع التبعيات!${NC}\n"

# =====================================================
# تشغيل Migration
# =====================================================

echo -e "${BLUE}🗄 تشغيل Database Migration...${NC}"

php artisan migrate --force

echo -e "${CHECK} تم إنشاء جداول قاعدة البيانات\n"

# =====================================================
# Clear Cache
# =====================================================

echo -e "${BLUE}🧹 تنظيف الـ Cache...${NC}"

php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

echo -e "${CHECK} تم تنظيف الـ Cache\n"

# =====================================================
# Autoload
# =====================================================

echo -e "${BLUE}🔄 تحديث Autoload...${NC}"

composer dump-autoload

echo -e "${CHECK} تم تحديث Autoload\n"

# =====================================================
# بناء Assets
# =====================================================

echo -e "${BLUE}🎨 بناء Frontend Assets...${NC}"

npm run build

echo -e "${CHECK} تم بناء Assets\n"

# =====================================================
# (اختياري) إنشاء بيانات تجريبية
# =====================================================

read -p "هل تريد إنشاء بيانات تجريبية للإشعارات؟ (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${ARROW} إنشاء بيانات تجريبية..."
    php artisan db:seed --class=NotificationSeeder
    echo -e "${CHECK} تم إنشاء البيانات التجريبية"
fi

echo ""

# =====================================================
# النتيجة النهائية
# =====================================================

echo -e "${GREEN}"
echo "════════════════════════════════════════════════════════"
echo "  ✓ تم التثبيت بنجاح!"
echo "════════════════════════════════════════════════════════"
echo -e "${NC}"

echo -e "${YELLOW}📋 الخطوات التالية:${NC}"
echo ""
echo -e "1. ${ARROW} حدّث ملف .env بـ Pusher credentials:"
echo -e "   ${BLUE}BROADCAST_CONNECTION=pusher${NC}"
echo -e "   ${BLUE}PUSHER_APP_ID=your-app-id${NC}"
echo -e "   ${BLUE}PUSHER_APP_KEY=your-key${NC}"
echo -e "   ${BLUE}PUSHER_APP_SECRET=your-secret${NC}"
echo -e "   ${BLUE}PUSHER_APP_CLUSTER=mt1${NC}"
echo -e "   ${BLUE}VITE_PUSHER_APP_KEY=\"\${PUSHER_APP_KEY}\"${NC}"
echo -e "   ${BLUE}VITE_PUSHER_APP_CLUSTER=\"\${PUSHER_APP_CLUSTER}\"${NC}"
echo ""
echo -e "2. ${ARROW} نظف الـ config:"
echo -e "   ${BLUE}php artisan config:clear${NC}"
echo ""
echo -e "3. ${ARROW} أضف مكون الإشعارات في Blade:"
echo -e "   ${BLUE}<x-notification-bell :userId=\"auth()->id()\" />${NC}"
echo ""
echo -e "4. ${ARROW} شغّل dev server:"
echo -e "   ${BLUE}npm run dev${NC}"
echo -e "   ${BLUE}php artisan serve${NC}"
echo ""
echo -e "5. ${ARROW} اختبر من Tinker:"
echo -e "   ${BLUE}php artisan tinker${NC}"
echo ""
echo -e "   ${GREEN}\$n = \\App\\Models\\Notification::create([${NC}"
echo -e "   ${GREEN}    'user_id' => 1,${NC}"
echo -e "   ${GREEN}    'type' => 'message',${NC}"
echo -e "   ${GREEN}    'title' => 'اختبار',${NC}"
echo -e "   ${GREEN}    'body' => 'يعمل!',${NC}"
echo -e "   ${GREEN}    'actor_id' => 1,${NC}"
echo -e "   ${GREEN}]);${NC}"
echo ""
echo -e "   ${GREEN}event(new \\App\\Events\\NotificationCreated(\$n));${NC}"
echo ""

echo -e "${BLUE}📚 للمزيد من التفاصيل:${NC}"
echo -e "   ${ARROW} HOW_TO_USE.md"
echo -e "   ${ARROW} USAGE_EXAMPLES.md"
echo -e "   ${ARROW} INTEGRATION_STEPS_FOR_YOUR_CRM.md"
echo ""

echo -e "${GREEN}════════════════════════════════════════════════════════${NC}"
echo -e "${GREEN}  🎉 تم! النظام جاهز للاستخدام!${NC}"
echo -e "${GREEN}════════════════════════════════════════════════════════${NC}"

