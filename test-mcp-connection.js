/**
 * سكريبت لاختبار اتصال MCP مع MySQL
 * Test script for MCP MySQL connection
 */

const { spawn } = require('child_process');

console.log('🔄 جاري اختبار اتصال MCP مع MySQL...\n');

// إعدادات الاتصال
const env = {
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
    env: { ...process.env, ...env },
    stdio: ['pipe', 'pipe', 'pipe']
});

let output = '';
let errorOutput = '';

mcpServer.stdout.on('data', (data) => {
    output += data.toString();
    console.log('📤 MCP Output:', data.toString());
});

mcpServer.stderr.on('data', (data) => {
    errorOutput += data.toString();
    console.log('⚠️  MCP Error:', data.toString());
});

mcpServer.on('close', (code) => {
    console.log(`\n🔚 خادم MCP انتهى بالكود: ${code}`);
    
    if (code === 0) {
        console.log('✅ تم تشغيل خادم MCP بنجاح!');
    } else {
        console.log('❌ فشل في تشغيل خادم MCP');
        console.log('Error details:', errorOutput);
    }
});

mcpServer.on('error', (error) => {
    console.log('❌ خطأ في تشغيل خادم MCP:', error.message);
});

// إرسال رسالة اختبار بعد 3 ثوان
setTimeout(() => {
    console.log('\n🧪 إرسال رسالة اختبار...');
    
    // إرسال طلب MCP بسيط
    const testRequest = {
        jsonrpc: '2.0',
        id: 1,
        method: 'initialize',
        params: {
            protocolVersion: '2024-11-05',
            capabilities: {},
            clientInfo: {
                name: 'test-client',
                version: '1.0.0'
            }
        }
    };
    
    mcpServer.stdin.write(JSON.stringify(testRequest) + '\n');
    
    // إنهاء الاختبار بعد 5 ثوان
    setTimeout(() => {
        console.log('\n⏰ إنهاء الاختبار...');
        mcpServer.kill();
    }, 5000);
    
}, 3000);

// معالجة إيقاف البرنامج
process.on('SIGINT', () => {
    console.log('\n🛑 إيقاف الاختبار...');
    mcpServer.kill();
    process.exit(0);
});
