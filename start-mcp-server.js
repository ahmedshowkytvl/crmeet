#!/usr/bin/env node

/**
 * سكريبت تشغيل خادم MCP مع MySQL
 * MCP Server startup script with MySQL
 */

const { spawn } = require('child_process');
const path = require('path');

console.log('🚀 بدء تشغيل خادم MCP مع MySQL...\n');

// إعدادات قاعدة البيانات
const env = {
    ...process.env,
    MYSQL_HOST: '127.0.0.1',
    MYSQL_PORT: '3306',
    MYSQL_USER: 'root',
    MYSQL_PASSWORD: '',
    MYSQL_DATABASE: 'crm',
    MYSQL_SSL: 'false',
    MYSQL_CHARSET: 'utf8mb4',
    MYSQL_COLLATION: 'utf8mb4_unicode_ci'
};

console.log('📋 إعدادات الاتصال:');
console.log(`Host: ${env.MYSQL_HOST}`);
console.log(`Port: ${env.MYSQL_PORT}`);
console.log(`Database: ${env.MYSQL_DATABASE}`);
console.log(`User: ${env.MYSQL_USER}\n`);

// تشغيل خادم MCP
const mcpServer = spawn('npx', ['-y', '@benborla29/mcp-server-mysql'], {
    env: env,
    stdio: ['inherit', 'inherit', 'inherit'],
    shell: true
});

mcpServer.on('error', (error) => {
    console.error('❌ خطأ في تشغيل خادم MCP:', error.message);
    process.exit(1);
});

mcpServer.on('close', (code) => {
    console.log(`\n🔚 خادم MCP انتهى بالكود: ${code}`);
    if (code !== 0) {
        console.log('❌ فشل في تشغيل خادم MCP');
    }
});

// معالجة إيقاف البرنامج
process.on('SIGINT', () => {
    console.log('\n🛑 إيقاف خادم MCP...');
    mcpServer.kill();
    process.exit(0);
});

process.on('SIGTERM', () => {
    console.log('\n🛑 إيقاف خادم MCP...');
    mcpServer.kill();
    process.exit(0);
});

console.log('✅ خادم MCP يعمل الآن!');
console.log('💡 يمكنك الآن استخدام MCP في Cursor');
console.log('🛑 اضغط Ctrl+C لإيقاف الخادم\n');
