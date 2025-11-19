@extends('layouts.app')

@section('title', '403 - ' . __('messages.access_denied'))

@section('content')
<style>
        .error-container {
            text-align: center;
            max-width: 600px;
            padding: 2rem;
            margin: 0 auto;
        }
        
        .error-icon {
            font-size: 8rem;
            color: #dc3545;
            margin-bottom: 2rem;
            animation: bounce 2s infinite;
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
            font-size: 3rem;
            font-weight: 700;
            color: #dc3545;
            margin-bottom: 1rem;
        }
        
        .error-subtitle {
            font-size: 1.5rem;
            color: #6c757d;
            margin-bottom: 2rem;
        }
        
        .error-message {
            font-size: 1.1rem;
            color: #495057;
            line-height: 1.6;
            margin-bottom: 2rem;
        }
        
        .error-actions {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .btn-custom {
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #007bff 0%, #0056b3 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(0,123,255,0.3);
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,123,255,0.4);
            color: white;
        }
        
        .btn-outline-custom {
            background: transparent;
            color: #007bff;
            border: 2px solid #007bff;
        }
        
        .btn-outline-custom:hover {
            background: #007bff;
            color: white;
            transform: translateY(-2px);
        }
        
        .btn-danger-custom {
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
            color: white;
            border: none;
            box-shadow: 0 4px 15px rgba(220,53,69,0.3);
        }
        
        .btn-danger-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(220,53,69,0.4);
            color: white;
        }
        
        .help-section {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            margin-top: 3rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            text-align: right;
        }
        
        .help-title {
            color: #495057;
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.2rem;
        }
        
        .help-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .help-list li {
            padding: 0.5rem 0;
            color: #6c757d;
            border-bottom: 1px solid #e9ecef;
        }
        
        .help-list li:last-child {
            border-bottom: none;
        }
        
        .help-list li i {
            color: #007bff;
            margin-left: 0.5rem;
        }
        
        @media (max-width: 768px) {
            .error-title {
                font-size: 2rem;
            }
            
            .error-subtitle {
                font-size: 1.2rem;
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
        }
        
        /* RTL Specific Styles */
        @if(app()->getLocale() == 'ar')
        .help-section {
            text-align: right;
        }
        
        .help-list li i {
            margin-left: 0;
            margin-right: 0.5rem;
        }
        @endif
    </style>

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

<script>
    // Add some interactive effects
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to buttons
        const buttons = document.querySelectorAll('.btn-custom');
        buttons.forEach(button => {
            button.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-2px) scale(1.05)';
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
@endsection
