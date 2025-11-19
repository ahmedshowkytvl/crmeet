<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}" {{ app()->getLocale() === 'ar' ? 'html="ar"' : '' }}>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('messages.login') }} - {{ __('messages.system_title') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #52BAD1;
            --secondary-color: #EEB902;
            --success-color: #97cc04;
            --info-color: #2660A4;
        }
        
        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--info-color) 100%);
            min-height: 100vh;
            font-family: 'Figtree', sans-serif;
        }
        
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--info-color) 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-header h2 {
            margin: 0;
            font-weight: 600;
        }
        
        .login-header p {
            margin: 0.5rem 0 0 0;
            opacity: 0.9;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(82, 186, 209, 0.25);
        }
        
        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--info-color) 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(82, 186, 209, 0.4);
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-right: none;
            border-radius: 10px 0 0 10px;
        }
        
        .form-control.with-icon {
            border-left: none;
            border-radius: 0;
        }
        
        .language-switcher {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        
        .language-switcher .btn {
            background: rgba(255,255,255,0.2);
            border: 1px solid rgba(255,255,255,0.3);
            color: white;
            border-radius: 20px;
            padding: 5px 15px;
            font-size: 0.9rem;
        }
        
        .language-switcher .btn:hover {
            background: rgba(255,255,255,0.3);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            border: none;
        }
        
        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }
        
        .form-check-input:focus {
            box-shadow: 0 0 0 0.2rem rgba(82, 186, 209, 0.25);
        }
        
        .input-group .btn {
            border-radius: 0 10px 10px 0;
            border-left: none;
            border-color: #e9ecef;
        }
        
        .input-group .btn:hover {
            background-color: #e9ecef;
            border-color: #e9ecef;
        }
        
        .input-group .btn:focus {
            box-shadow: none;
        }
        
        /* CSS خاص للغة العربية */
        .input-group {
            position: relative;
            display: flex;
            flex-wrap: wrap;
            align-items: stretch;
            width: 100%;
            direction: ltr !important;
        }
        
        /* ضبط اتجاه العناصر في العربية */
        [html="ar"] .input-group-text {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
            border-top-right-radius: 0.375rem;
            border-bottom-right-radius: 0.375rem;
            border-left: 0;
            border-right: 1px solid #ced4da;
        }
        
        [html="ar"] .input-group .form-control {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
            border-top-left-radius: 0.375rem;
            border-bottom-left-radius: 0.375rem;
            border-right: 0;
            border-left: 1px solid #ced4da;
        }
        
        [html="ar"] .input-group .form-control:focus {
            border-right: 0;
            border-left: 1px solid #ced4da;
        }
        
        [html="ar"] .input-group .form-control:focus + .input-group-text {
            border-left: 0;
            border-right: 1px solid #ced4da;
        }
        
        /* ضبط اتجاه الأيقونات في العربية */
        [html="ar"] .input-group-text i {
            transform: scaleX(-1);
        }
        
        /* ضبط اتجاه النص في العربية */
        [html="ar"] .form-control {
            text-align: right;
        }
        
        [html="ar"] .form-control::placeholder {
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="language-switcher">
        <a href="{{ route('lang.switch', 'en') }}" class="btn {{ app()->getLocale() === 'en' ? 'active' : '' }}">
            <i class="fas fa-globe me-1"></i>EN
        </a>
        <a href="{{ route('lang.switch', 'ar') }}" class="btn {{ app()->getLocale() === 'ar' ? 'active' : '' }}">
            <i class="fas fa-globe me-1"></i>عربي
        </a>
    </div>

    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <h2><i class="fas fa-user-shield me-2"></i>{{ __('messages.login') }}</h2>
                <p>{{ __('messages.welcome_back') }}</p>
            </div>
            
            <div class="login-body">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    
                    <div class="mb-3">
                        <label for="email" class="form-label">
                            {{ __('messages.username_or_email') }} / {{ __('messages.employee_id') }}
                        </label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-id-card"></i>
                            </span>
                            <input type="text" class="form-control with-icon @error('email') is-invalid @enderror" 
                                   id="email" name="email" value="{{ old('email') }}" 
                                   placeholder="{{ __('messages.enter_username_email_or_employee_id') }}" required autofocus>
                        </div>
                        <small class="form-text text-muted">
                            {{ __('messages.login_field_hint') }}
                        </small>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">{{ __('messages.password') }}</label>
                        <div class="input-group">
                            <span class="input-group-text">
                                <i class="fas fa-lock"></i>
                            </span>
                            <input type="password" class="form-control with-icon @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="fas fa-eye" id="togglePasswordIcon"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            {{ __('messages.remember_me') }}
                        </label>
                    </div>

                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-login">
                            <i class="fas fa-sign-in-alt me-2"></i>{{ __('messages.login') }}
                        </button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <p class="text-muted">
                        {{ __('messages.dont_have_account') }}
                        <a href="{{ route('register') }}" class="text-decoration-none">
                            {{ __('messages.register_here') }}
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Toggle password visibility
        document.getElementById('togglePassword').addEventListener('click', function() {
            const passwordField = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                passwordField.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        });
    </script>
</body>
</html>
