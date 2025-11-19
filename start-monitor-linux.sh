#!/bin/bash

# Linux script لبدء نظام مراقبة النظام
# System Monitor Startup Script for Linux

# ألوان للطباعة
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${CYAN}========================================${NC}"
echo -e "${YELLOW}   بدء نظام مراقبة النظام${NC}"
echo -e "${CYAN}========================================${NC}"
echo

# دالة للتحقق من وجود أمر
check_command() {
    if command -v $1 &> /dev/null; then
        return 0
    else
        return 1
    fi
}

# دالة لطباعة رسالة نجاح
print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

# دالة لطباعة رسالة خطأ
print_error() {
    echo -e "${RED}✗ $1${NC}"
}

# دالة لطباعة رسالة تحذير
print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

# دالة لطباعة رسالة معلومات
print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

# التحقق من PHP
echo -e "${GREEN}[1/4] التحقق من PHP...${NC}"
if check_command php; then
    PHP_VERSION=$(php --version | head -n1)
    print_success "PHP موجود: $PHP_VERSION"
else
    print_error "PHP غير مثبت أو غير موجود في PATH"
    print_info "يرجى تثبيت PHP:"
    print_info "Ubuntu/Debian: sudo apt install php-cli php-mysql"
    print_info "CentOS/RHEL: sudo yum install php-cli php-mysql"
    print_info "Arch Linux: sudo pacman -S php"
    exit 1
fi

# التحقق من Node.js
echo -e "${GREEN}[2/4] التحقق من Node.js...${NC}"
if check_command node; then
    NODE_VERSION=$(node --version)
    print_success "Node.js موجود: $NODE_VERSION"
else
    print_error "Node.js غير مثبت أو غير موجود في PATH"
    print_info "يرجى تثبيت Node.js من https://nodejs.org"
    print_info "أو استخدم مدير الحزم:"
    print_info "Ubuntu/Debian: curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash - && sudo apt-get install -y nodejs"
    print_info "CentOS/RHEL: curl -fsSL https://rpm.nodesource.com/setup_18.x | sudo bash - && sudo yum install -y nodejs"
    exit 1
fi

# تثبيت تبعيات WebSocket
echo -e "${GREEN}[3/4] تثبيت تبعيات WebSocket...${NC}"
if [ -d "node_modules" ]; then
    print_success "تبعيات Node.js موجودة بالفعل"
else
    print_info "تثبيت تبعيات WebSocket..."
    if npm install ws; then
        print_success "تم تثبيت التبعيات بنجاح"
    else
        print_error "فشل في تثبيت التبعيات"
        exit 1
    fi
fi

# التحقق من Laravel
echo -e "${GREEN}[4/4] التحقق من Laravel...${NC}"
if [ -f "artisan" ]; then
    print_success "Laravel موجود"
else
    print_error "Laravel غير موجود في هذا المجلد"
    print_info "يرجى التأكد من أنك في مجلد مشروع Laravel"
    exit 1
fi

# بدء الخوادم
echo -e "${GREEN}بدء الخوادم...${NC}"

# دالة لإيقاف العمليات عند إنهاء السكريبت
cleanup() {
    echo
    print_warning "إيقاف النظام..."
    if [ ! -z "$LARAVEL_PID" ]; then
        kill $LARAVEL_PID 2>/dev/null
        print_info "تم إيقاف خادم Laravel"
    fi
    if [ ! -z "$WEBSOCKET_PID" ]; then
        kill $WEBSOCKET_PID 2>/dev/null
        print_info "تم إيقاف خادم WebSocket"
    fi
    exit 0
}

# ربط دالة التنظيف بإشارة الإنهاء
trap cleanup SIGINT SIGTERM

# بدء خادم Laravel
print_info "بدء خادم Laravel..."
php artisan serve --host=0.0.0.0 --port=8000 &
LARAVEL_PID=$!

# انتظار قليل
sleep 3

# بدء خادم WebSocket
print_info "بدء خادم WebSocket..."
node websocket-server.js &
WEBSOCKET_PID=$!

# انتظار قليل
sleep 2

echo
echo -e "${CYAN}========================================${NC}"
echo -e "${GREEN}   تم تشغيل النظام بنجاح!${NC}"
echo -e "${CYAN}========================================${NC}"
echo
echo -e "${YELLOW}روابط الوصول:${NC}"
echo -e "${NC}- واجهة المراقب: http://localhost:8000/system-monitor${NC}"
echo -e "${NC}- خادم WebSocket: ws://localhost:8080/ws/system-monitor${NC}"
echo
echo -e "${YELLOW}للوصول من الشبكة:${NC}"
echo -e "${NC}1. اعرف عنوان IP الخاص بك: ip addr show${NC}"
echo -e "${NC}2. استخدم: http://YOUR_IP:8000/system-monitor${NC}"
echo
echo -e "${RED}لإيقاف النظام، اضغط Ctrl+C${NC}"
echo

# عرض حالة الخوادم
while true; do
    LARAVEL_STATUS=""
    WEBSOCKET_STATUS=""
    
    if kill -0 $LARAVEL_PID 2>/dev/null; then
        LARAVEL_STATUS="✓ يعمل"
    else
        LARAVEL_STATUS="✗ متوقف"
    fi
    
    if kill -0 $WEBSOCKET_PID 2>/dev/null; then
        WEBSOCKET_STATUS="✓ يعمل"
    else
        WEBSOCKET_STATUS="✗ متوقف"
    fi
    
    echo -e "${CYAN}حالة الخوادم: Laravel $LARAVEL_STATUS | WebSocket $WEBSOCKET_STATUS${NC}"
    
    sleep 10
done






