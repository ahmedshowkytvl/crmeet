<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>خطأ في قاعدة البيانات</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
        }
        .error-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 40px;
            text-align: center;
            max-width: 500px;
            width: 100%;
        }
        .error-icon {
            font-size: 60px;
            color: #dc3545;
            margin-bottom: 20px;
        }
        .error-title {
            color: #dc3545;
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 15px;
        }
        .error-message {
            color: #6c757d;
            font-size: 16px;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .solution-steps {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 30px;
            text-align: right;
        }
        .step {
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
        }
        .step-number {
            background: #28a745;
            color: white;
            width: 25px;
            height: 25px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-left: 10px;
            flex-shrink: 0;
        }
        .step-content {
            flex: 1;
        }
        .step-title {
            font-weight: bold;
            color: #333;
            margin-bottom: 5px;
        }
        .step-description {
            color: #666;
            font-size: 14px;
        }
        .btn {
            background: #007bff;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            margin: 0 10px;
            text-decoration: none;
            display: inline-block;
        }
        .btn:hover {
            background: #0056b3;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-success:hover {
            background: #1e7e34;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">🗄️</div>
        <h1 class="error-title">خطأ في قاعدة البيانات</h1>
        <p class="error-message">
            عذراً، لا يمكن الاتصال بقاعدة البيانات حالياً. يرجى المحاولة مرة أخرى.
        </p>

        <div class="solution-steps">
            <h3 style="margin-top: 0; color: #333;">خطوات الحل:</h3>
            
            <div class="step">
                <div class="step-number">1</div>
                <div class="step-content">
                    <div class="step-title">تحقق من XAMPP</div>
                    <div class="step-description">تأكد من تشغيل Apache و MySQL في XAMPP Control Panel</div>
                </div>
            </div>

            <div class="step">
                <div class="step-number">2</div>
                <div class="step-content">
                    <div class="step-title">تحقق من الاتصال</div>
                    <div class="step-description">تأكد من أن MySQL يعمل على المنفذ 3306</div>
                </div>
            </div>

            <div class="step">
                <div class="step-number">3</div>
                <div class="step-content">
                    <div class="step-title">تحقق من الإعدادات</div>
                    <div class="step-description">راجع ملف .env للتأكد من صحة إعدادات قاعدة البيانات</div>
                </div>
            </div>
        </div>

        <div>
            <button onclick="window.location.reload()" class="btn btn-success">
                🔄 إعادة المحاولة
            </button>
            <a href="/login" class="btn">
                🏠 العودة للصفحة الرئيسية
            </a>
        </div>

        <div style="margin-top: 20px; color: #666; font-size: 14px;">
            إذا استمرت المشكلة، يرجى الاتصال بالدعم الفني
        </div>
    </div>

    <script>
        // Auto refresh every 30 seconds
        setTimeout(function() {
            window.location.reload();
        }, 30000);
    </script>
</body>
</html>