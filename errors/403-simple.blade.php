<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>403 - {{ __('messages.access_denied') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    @if(app()->getLocale() == 'ar')
    <!-- RTL Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    @endif
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @endif
    
    <style>
        :root {
            --primary-color: #2c5aa0;
        }
        
        body {
            @if(app()->getLocale() == 'ar')
            font-family: 'Cairo', sans-serif;
            @else
            font-family: 'Inter', sans-serif;
            @endif
            background: var(--primary-color);
            color: white;
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 3rem 2rem;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            backdrop-filter: blur(10px);
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
        }
        
        .error-icon {
            font-size: 8rem;
            color: white;
            margin-bottom: 2rem;
            animation: bounce 2s infinite;
            text-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-30px);
            }
            60% {
                transform: translateY(-15px);
            }
        }
        
        .error-title {
            font-size: 4rem;
            font-weight: 700;
            color: white;
            margin-bottom: 1rem;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }
        
        .error-subtitle {
            font-size: 1.8rem;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
            font-weight: 500;
        }
        
        .error-message {
            font-size: 1.2rem;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.6;
            margin-bottom: 3rem;
        }
        
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-custom {
            padding: 15px 35px;
            border-radius: 50px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid transparent;
            font-size: 1.1rem;
        }
        
        .btn-primary-custom {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
            backdrop-filter: blur(10px);
        }
        
        .btn-primary-custom:hover {
            background: rgba(255, 255, 255, 0.3);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
        }
        
        .btn-outline-custom {
            background: transparent;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }
        
        .btn-outline-custom:hover {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            transform: translateY(-3px);
            border-color: rgba(255, 255, 255, 0.8);
        }
        
        .help-section {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            padding: 2rem;
            margin-top: 3rem;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .help-title {
            color: white;
            font-weight: 600;
            margin-bottom: 1.5rem;
            font-size: 1.3rem;
        }
        
        .help-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .help-list li {
            padding: 0.8rem 0;
            color: rgba(255, 255, 255, 0.8);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            font-size: 1.1rem;
        }
        
        .help-list li:last-child {
            border-bottom: none;
        }
        
        .help-list li i {
            color: rgba(255, 255, 255, 0.9);
            margin-left: 0.5rem;
            font-size: 1.2rem;
        }
        
        @media (max-width: 768px) {
            .error-title {
                font-size: 3rem;
            }
            
            .error-subtitle {
                font-size: 1.4rem;
            }
            
            .error-icon {
                font-size: 6rem;
            }
            
            .error-actions {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-custom {
                width: 100%;
                max-width: 300px;
            }
            
            .error-container {
                margin: 1rem;
                padding: 2rem 1.5rem;
            }
        }
        
        /* RTL Specific Styles */
        @if(app()->getLocale() == 'ar')
        .help-list li i {
            margin-left: 0;
            margin-right: 0.5rem;
        }
        @endif
    </style>
</head>
<body>
    <div class="error-container">
        <!-- Error Icon -->
        <div class="error-icon">
            <i class="fas fa-shield-alt"></i>
        </div>
        
        <!-- Error Title -->
        <h1 class="error-title">403</h1>
        
        <!-- Error Subtitle -->
        <h2 class="error-subtitle">
            @if(app()->getLocale() == 'ar')
            عذراً، لا يمكنك الوصول إلى هذا المورد
            @else
            Access Denied
            @endif
        </h2>
        
        <!-- Error Message -->
        <div class="error-message">
            @if(app()->getLocale() == 'ar')
            <p>نعتذر، ولكن ليس لديك الصلاحية للوصول إلى هذه الصفحة أو المورد المطلوب.</p>
            <p>قد تحتاج إلى تسجيل الدخول بحساب مختلف أو الحصول على الصلاحيات المناسبة.</p>
            @else
            <p>Sorry, you don't have permission to access this resource.</p>
            <p>You may need to log in with a different account or get the appropriate permissions.</p>
            @endif
        </div>
        
        <!-- Action Buttons -->
        <div class="error-actions">
            <a href="{{ route('dashboard') }}" class="btn btn-custom btn-primary-custom">
                <i class="fas fa-home me-2"></i>
                @if(app()->getLocale() == 'ar')
                العودة للصفحة الرئيسية
                @else
                Go to Dashboard
                @endif
            </a>
            
            <a href="{{ route('login') }}" class="btn btn-custom btn-outline-custom">
                <i class="fas fa-sign-in-alt me-2"></i>
                @if(app()->getLocale() == 'ar')
                تسجيل الدخول
                @else
                Login
                @endif
            </a>
            
            <button onclick="history.back()" class="btn btn-custom btn-outline-custom">
                <i class="fas fa-arrow-left me-2"></i>
                @if(app()->getLocale() == 'ar')
                العودة للصفحة السابقة
                @else
                Go Back
                @endif
            </button>
        </div>
        
        <!-- Help Section -->
        <div class="help-section">
            <h3 class="help-title">
                @if(app()->getLocale() == 'ar')
                <i class="fas fa-question-circle me-2"></i>
                ما يمكنك فعله:
                @else
                <i class="fas fa-question-circle me-2"></i>
                What you can do:
                @endif
            </h3>
            <ul class="help-list">
                @if(app()->getLocale() == 'ar')
                <li><i class="fas fa-check"></i>تأكد من تسجيل الدخول بحسابك الصحيح</li>
                <li><i class="fas fa-check"></i>تحقق من الصلاحيات المطلوبة للوصول لهذا المورد</li>
                <li><i class="fas fa-check"></i>اتصل بالمدير إذا كنت تعتقد أن هذا خطأ</li>
                <li><i class="fas fa-check"></i>ارجع للصفحة الرئيسية واستكشف الأقسام المتاحة</li>
                @else
                <li><i class="fas fa-check"></i>Make sure you're logged in with the correct account</li>
                <li><i class="fas fa-check"></i>Check if you have the required permissions for this resource</li>
                <li><i class="fas fa-check"></i>Contact the administrator if you believe this is an error</li>
                <li><i class="fas fa-check"></i>Return to the dashboard and explore available sections</li>
                @endif
            </ul>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Add some interactive effects
        document.addEventListener('DOMContentLoaded', function() {
            // Add hover effects to buttons
            const buttons = document.querySelectorAll('.btn-custom');
            buttons.forEach(button => {
                button.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-3px) scale(1.05)';
                });
                
                button.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0) scale(1)';
                });
            });
            
            // Add click animation to error icon
            const errorIcon = document.querySelector('.error-icon');
            errorIcon.addEventListener('click', function() {
                this.style.animation = 'none';
                setTimeout(() => {
                    this.style.animation = 'bounce 2s infinite';
                }, 10);
            });
        });
    </script>
</body>
</html>
