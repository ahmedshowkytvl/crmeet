<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ __('Database Connection Error') }} - {{ config('app.name') }}</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            color: #333;
        }

        .error-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            max-width: 800px;
            width: 100%;
            overflow: hidden;
            animation: slideUp 0.6s ease-out;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .error-header {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 30px;
            text-align: center;
            position: relative;
        }

        .error-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="2" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.3;
        }

        .error-icon {
            font-size: 4rem;
            margin-bottom: 15px;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .error-title {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .error-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        .error-content {
            padding: 40px;
        }

        .error-message {
            background: #f8f9fa;
            border-left: 4px solid #ff6b6b;
            padding: 20px;
            margin-bottom: 30px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            line-height: 1.6;
        }

        .error-details {
            background: #2c3e50;
            color: #ecf0f1;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            font-family: 'Courier New', monospace;
            font-size: 0.85rem;
            line-height: 1.5;
            overflow-x: auto;
        }

        .solutions {
            margin-bottom: 30px;
        }

        .solutions h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            font-size: 1.3rem;
        }

        .solution-item {
            background: #e8f5e8;
            border: 1px solid #4caf50;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
            display: flex;
            align-items: flex-start;
            gap: 15px;
        }

        .solution-item i {
            color: #4caf50;
            font-size: 1.2rem;
            margin-top: 2px;
        }

        .solution-item div {
            flex: 1;
        }

        .solution-item strong {
            color: #2e7d32;
            display: block;
            margin-bottom: 5px;
        }

        .actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .language-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
            background: rgba(255, 255, 255, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.3s ease;
        }

        .language-toggle:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        .rtl {
            direction: rtl;
            text-align: right;
        }

        .rtl .error-message {
            border-left: none;
            border-right: 4px solid #ff6b6b;
        }

        .rtl .actions {
            flex-direction: row-reverse;
        }

        .rtl .solution-item {
            flex-direction: row-reverse;
        }

        .rtl .language-toggle {
            right: auto;
            left: 20px;
        }

        @media (max-width: 768px) {
            .error-container {
                margin: 10px;
                border-radius: 15px;
            }

            .error-header {
                padding: 20px;
            }

            .error-content {
                padding: 20px;
            }

            .error-title {
                font-size: 1.5rem;
            }

            .actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="error-container {{ app()->getLocale() == 'ar' ? 'rtl' : '' }}">
        <div class="error-header">
            <button class="language-toggle" onclick="toggleLanguage()">
                <i class="fas fa-language"></i>
                {{ app()->getLocale() == 'ar' ? 'English' : 'العربية' }}
            </button>
            <div class="error-icon">
                <i class="fas fa-database"></i>
            </div>
            <h1 class="error-title">{{ __('Database Connection Error') }}</h1>
            <p class="error-subtitle">{{ __('Oops! We can\'t connect to the database right now') }}</p>
        </div>

        <div class="error-content">
            <div class="error-message">
                <strong>{{ __('Error Details') }}:</strong><br>
                {{ $error['error_message'] ?? 'SQLSTATE[HY000] [2002] No connection could be made because the target machine actively refused it' }}
            </div>

            <div class="error-details">
<strong>{{ __('ERROR LOG FOR DEVELOPERS') }}:</strong>
{{ __('Connection') }}: {{ $error['connection'] ?? 'mysql' }}
{{ __('SQL') }}: {{ $error['sql'] ?? 'select * from `users` where `email` = admin@stafftobia.com limit 1' }}
{{ __('POST') }}: {{ $error['request_url'] ?? '127.0.0.1:8000' }}
{{ __('PHP') }}: {{ $error['php_version'] ?? '8.2.12' }} — Laravel {{ $error['laravel_version'] ?? '12.28.1' }}
{{ __('File') }}: {{ $error['file'] ?? 'D:\\xampp\\htdocs\\crm\\stafftobia\\vendor\\laravel\\framework\\src\\Illuminate\\Database\\Connection.php' }}:{{ $error['line'] ?? '824' }}
{{ __('Timestamp') }}: {{ $error['timestamp'] ?? now()->format('Y-m-d H:i:s') }}
{{ __('Error Code') }}: {{ $error['error_code'] ?? '2002' }}
            </div>

            <div class="solutions">
                <h3><i class="fas fa-lightbulb"></i> {{ __('How to Fix This Issue') }}</h3>
                
                <div class="solution-item">
                    <i class="fas fa-server"></i>
                    <div>
                        <strong>{{ __('1. Start MySQL Service') }}</strong>
                        {{ __('Make sure MySQL service is running in XAMPP Control Panel. Click "Start" next to MySQL.') }}
                    </div>
                </div>

                <div class="solution-item">
                    <i class="fas fa-cog"></i>
                    <div>
                        <strong>{{ __('2. Check Database Configuration') }}</strong>
                        {{ __('Verify your .env file has correct database settings: DB_HOST=127.0.0.1, DB_PORT=3306, DB_DATABASE=crm') }}
                    </div>
                </div>

                <div class="solution-item">
                    <i class="fas fa-database"></i>
                    <div>
                        <strong>{{ __('3. Create Database') }}</strong>
                        {{ __('Create the "crm" database in phpMyAdmin if it doesn\'t exist.') }}
                    </div>
                </div>

                <div class="solution-item">
                    <i class="fas fa-sync"></i>
                    <div>
                        <strong>{{ __('4. Run Migrations') }}</strong>
                        {{ __('Execute: php artisan migrate to create database tables.') }}
                    </div>
                </div>

                <div class="solution-item">
                    <i class="fas fa-user-plus"></i>
                    <div>
                        <strong>{{ __('5. Create Admin User') }}</strong>
                        {{ __('Run: php artisan db:seed to create the admin user.') }}
                    </div>
                </div>
            </div>

            <div class="actions">
                <a href="javascript:history.back()" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i>
                    {{ __('Go Back') }}
                </a>
                <a href="{{ url('/') }}" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    {{ __('Home Page') }}
                </a>
                <button onclick="toggleErrorDetails()" class="btn btn-secondary">
                    <i class="fas fa-bug"></i>
                    {{ __('Toggle Error Details') }}
                </button>
            </div>
        </div>
    </div>

    <script>
        function toggleLanguage() {
            const currentLang = '{{ app()->getLocale() }}';
            const newLang = currentLang === 'ar' ? 'en' : 'ar';
            const url = new URL(window.location);
            url.searchParams.set('lang', newLang);
            window.location.href = url.toString();
        }

        function toggleErrorDetails() {
            const details = document.querySelector('.error-details');
            const isVisible = details.style.display !== 'none';
            details.style.display = isVisible ? 'none' : 'block';
        }

        // Auto-hide error details on mobile
        if (window.innerWidth <= 768) {
            document.querySelector('.error-details').style.display = 'none';
        }
    </script>
</body>
</html>
