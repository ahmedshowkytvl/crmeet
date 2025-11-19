<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('messages.system_title'))</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{{ asset('css/custom.css') }}" rel="stylesheet">
    <link href="{{ asset('css/contact-card.css') }}" rel="stylesheet">
    <link href="{{ asset('css/contacts.css') }}" rel="stylesheet">
    <link href="{{ asset('css/date-picker.css') }}" rel="stylesheet">
    <link href="{{ asset('css/pagination.css') }}" rel="stylesheet">
    <link href="{{ asset('css/header-styles.css') }}" rel="stylesheet">
    <link href="{{ asset('css/employee-overview.css') }}" rel="stylesheet">
    
    @if(app()->getLocale() == 'ar')
    <!-- RTL Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- Custom RTL Styles -->
    <style>
    /* تحسين عام للتصميم في اللغة العربية */
    .btn {
        border-radius: 8px !important;
        font-weight: 500;
        transition: all 0.3s ease;
        border: 2px solid transparent;
    }

    /* Header Section Styles */
    .header-section {
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        padding-bottom: 1rem;
    }

    .notification-icon .btn,
    .chat-icon .btn {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s ease;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .notification-icon .btn:hover,
    .chat-icon .btn:hover {
        background-color: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.6);
        transform: scale(1.05);
    }

    .notification-icon .badge {
        font-size: 0.7rem;
        animation: pulse 2s infinite;
    }

    @keyframes pulse {
        0% { transform: scale(1); }
        50% { transform: scale(1.1); }
        100% { transform: scale(1); }
    }

    .dropdown-menu {
        border: none;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
        border-radius: 10px;
        min-width: 250px;
    }

    .dropdown-item {
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
    }

    .dropdown-item:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }

     .btn:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }

    .btn-primary {
        border: none;
        box-shadow: 0 2px 6px rgba(0,123,255,0.3);
    }

    .btn-success {
        background: linear-gradient(135deg, #28a745 0%, #1e7e34 100%);
        border: none;
        box-shadow: 0 2px 6px rgba(40,167,69,0.3);
    }

    .btn-warning {
        background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%);
        border: none;
        box-shadow: 0 2px 6px rgba(255,193,7,0.3);
        color: #212529;
    }

    .btn-danger {
        background: linear-gradient(135deg, #dc3545 0%, #c82333 100%);
        border: none;
        box-shadow: 0 2px 6px rgba(220,53,69,0.3);
    }

    .btn-info {
        background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
        border: none;
        box-shadow: 0 2px 6px rgba(23,162,184,0.3);
    }

    .btn-outline-primary {
        border: 2px solid #007bff;
        color: #007bff;
        background: transparent;
    }

    .btn-outline-primary:hover {
        background: #007bff;
        color: white;
        border-color: #007bff;
    }

    .btn-outline-secondary {
        border: 2px solid #6c757d;
        color: #6c757d;
        background: transparent;
    }

    .btn-outline-secondary:hover {
        background: #6c757d;
        color: white;
        border-color: #6c757d;
    }

    .btn-outline-info {
        border: 2px solid #17a2b8;
        color: #17a2b8;
        background: transparent;
    }

    .btn-outline-info:hover {
        background: #17a2b8;
        color: white;
        border-color: #17a2b8;
    }

    .btn-outline-warning {
        border: 2px solid #ffc107;
        color: #ffc107;
        background: transparent;
    }

    .btn-outline-warning:hover {
        background: #ffc107;
        color: #212529;
        border-color: #ffc107;
    }

    .btn-outline-danger {
        border: 2px solid #dc3545;
        color: #dc3545;
        background: transparent;
    }

    .btn-outline-danger:hover {
        background: #dc3545;
        color: white;
        border-color: #dc3545;
    }

    .btn-outline-success {
        border: 2px solid #28a745;
        color: #28a745;
        background: transparent;
    }

    .btn-outline-success:hover {
        background: #28a745;
        color: white;
        border-color: #28a745;
    }

    /* تحسين شكل الكروت */
    .card {
        border-radius: 12px;
        border: 1px solid #e9ecef;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        transition: all 0.3s ease;
    }

    .card:hover {
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    }

    /* تحسين شكل النماذج */
    .form-control,
    .form-select {
        border-radius: 8px;
        border: 2px solid #e9ecef;
        transition: all 0.3s ease;
    }

    .form-control:focus,
    .form-select:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,0.25);
    }

    /* تحسين شكل البادجات */
    .badge {
        border-radius: 20px;
        padding: 6px 12px;
        font-weight: 500;
    }

    /* تحسين الأيقونات */
    .fas {
        transition: all 0.3s ease;
    }

    .btn:hover .fas {
        transform: scale(1.1);
    }

    /* تحسين الروابط */
    .text-decoration-none {
        transition: color 0.3s ease;
    }

    .text-decoration-none:hover {
        color: #007bff !important;
    }

    /* تحسين الجداول */
    .table {
        border-radius: 8px;
        overflow: hidden;
    }

    .table th {
        color: white;
        font-weight: 600;
        border: none;
    }

    .table td {
        border-color: #e9ecef;
        vertical-align: middle;
    }

    .table tbody tr:hover {
        background-color: #f8f9fa;
    }

    /* تحسين المودال */
    .modal-content {
        border-radius: 12px;
        border: none;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    }

    .modal-header {
        border-bottom: 1px solid #e9ecef;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    }

    /* تحسين التنقل */
    .navbar {
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }

    .navbar-brand {
        font-weight: 700;
        font-size: 1.5rem;
    }

    /* تحسين الألوان */
    .text-primary {
        color: #007bff !important;
    }

    .text-success {
        color: #28a745 !important;
    }

    .text-info {
        color: #17a2b8 !important;
    }

    .text-warning {
        color: #ffc107 !important;
    }

    .text-danger {
        color: #dc3545 !important;
    }

    /* تحسين المسافات */
    .mb-4 {
        margin-bottom: 2rem !important;
    }

    .mb-3 {
        margin-bottom: 1.5rem !important;
    }

    .mt-4 {
        margin-top: 2rem !important;
    }

    .mt-3 {
        margin-top: 1.5rem !important;
    }
    
    /* Global Date Input Styles */
    input[type="date"] {
        background-color: white !important;
        color: #333 !important;
    }
    
    input[type="date"]:focus {
        background-color: white !important;
        color: #333 !important;
        border-color: #52BAD1 !important;
        box-shadow: 0 0 0 0.2rem rgba(82, 186, 209, 0.25) !important;
    }
    
    .custom-date-input {
        background-color: white !important;
        color: #333 !important;
    }
    
    .custom-date-input:focus {
        background-color: white !important;
        color: #333 !important;
        border-color: #52BAD1 !important;
        box-shadow: 0 0 0 0.2rem rgba(82, 186, 209, 0.25) !important;
    }
    </style>
    @endif
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @if(app()->getLocale() == 'ar')
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    @endif
    
    <style>
        body {
            @if(app()->getLocale() == 'ar')
            font-family: 'Cairo', sans-serif;
            @else
            font-family: 'Inter', sans-serif;
            @endif
            background-color: #f8f9fa;
        }
        
        .sidebar {
            min-height: 100vh;
            background: radial-gradient(ellipse at center, #ffffff, #d4d4d4);
            filter: brightness(109%);
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.1);
            color: #366F9D;
        }
        
        .sidebar,
        .sidebar *:not(.notification-badge):not(.connection-indicator):not(.spinner) {
            color: #366F9D !important;
        }
        
        .sidebar a {
            text-decoration: none !important;
        }
        
        .sidebar a:hover {
            text-decoration: none !important;
        }
        
        .sidebar .nav-link {
            color: rgba(255,255,255,0.9);
            transition: all 0.3s ease;
            border-radius: 8px;
            margin: 2px 0;
            text-decoration: none !important;
        }
        
        .sidebar .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
            transform: translateX(5px);
            text-decoration: none !important;
        }
        
        .sidebar .nav-link.active {
            background: rgba(255,255,255,0.2);
            color: white;
            font-weight: 600;
        }
        
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .navbar-brand {
            font-weight: 700;
            font-size: 1.5rem;
        }
        
         .language-toggle .btn {
             font-size: 0.875rem;
         }
         
         /* Global Date Input Styles */
         input[type="date"] {
             background-color: white !important;
             color: #333 !important;
         }
         
         input[type="date"]:focus {
             background-color: white !important;
             color: #333 !important;
             border-color: #52BAD1 !important;
             box-shadow: 0 0 0 0.2rem rgba(82, 186, 209, 0.25) !important;
         }
         
         .custom-date-input {
             background-color: white !important;
             color: #333 !important;
         }
         
         .custom-date-input:focus {
             background-color: white !important;
             color: #333 !important;
             border-color: #52BAD1 !important;
             box-shadow: 0 0 0 0.2rem rgba(82, 186, 209, 0.25) !important;
         }
         
         /* Placeholder styles for date inputs */
         .custom-date-input::placeholder {
             color: #6c757d !important;
             opacity: 1;
         }
         
         .custom-date-input::-webkit-input-placeholder {
             color: #6c757d !important;
             opacity: 1;
         }
         
         .custom-date-input::-moz-placeholder {
             color: #6c757d !important;
             opacity: 1;
         }
         
         .custom-date-input:-ms-input-placeholder {
             color: #6c757d !important;
             opacity: 1;
         }
         
         .custom-date-input:-moz-placeholder {
             color: #6c757d !important;
             opacity: 1;
             padding: 6px 12px;
         }
         
         /* Navigation Sections */
         .nav-section {
             border-bottom: 1px solid rgba(255,255,255,0.1);
             padding-bottom: 0.5rem;
         }
         
         .nav-section:last-child {
             border-bottom: none;
         }
         
         .nav-section h6 {
             font-size: 0.75rem;
             font-weight: 600;
             letter-spacing: 0.5px;
             margin-bottom: 0.5rem;
             padding: 0.25rem 0.75rem;
             background: rgba(255,255,255,0.05);
             border-radius: 4px;
             border-left: 3px solid rgba(255,255,255,0.3);
         }
         
         .nav-section .nav-link {
             margin-left: 0.5rem;
             padding: 0.5rem 0.75rem;
             font-size: 0.9rem;
             text-decoration: none !important;
         }
         
         .nav-section .nav-link:hover {
             background: rgba(255,255,255,0.1);
             border-radius: 6px;
             text-decoration: none !important;
         }
         
         .nav-section .nav-link.active {
             background: rgba(255,255,255,0.2);
             border-radius: 6px;
             font-weight: 600;
         }
    </style>
    
    <!-- Axios - Load before Alpine.js and other scripts -->
    <script src="https://cdn.jsdelivr.net/npm/axios@1.6.4/dist/axios.min.js"></script>
    
    <!-- Notifications JS - Load before Alpine.js -->
    <script src="{{ asset('js/notifications.js') }}"></script>
    
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body onload="document.addEventListener('click', function(ev){ if(window.Notification && Notification.permission==='default' && window.notificationsApp && typeof window.notificationsApp.requestNotificationPermission==='function'){ window.notificationsApp.requestNotificationPermission(ev); } }, { once: true });">

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-2 p-0 sidebar">
                <div class="p-3">
                    <!-- Header Section -->
                    <div class="header-section mb-4">
                        <img src="{{ asset('images/eetglobal.png') }}" alt="EET Global Logo" class="img-fluid mb-3" style="max-width: 100%; height: auto;">
                        <h4 class="text-white text-center mb-3">
                            EET Global Management System
                        </h4>
                        
                        <!-- Notifications and Chat Icons -->
                        <div class="d-flex justify-content-center gap-3 mb-3">
                            <!-- Notifications Bell -->
                            <div class="notification-icon">
                            
                            <x-notification-bell :user-id="auth()->id()" />
                            </div>
                            <!-- Chat Icon -->
                            <div class="chat-icon">
                                <a href="{{ route('chat.index') }}" class="btn btn-outline-light btn-sm rounded-circle" title="الدردشة" style="transform: translateY(0px);">
                                    <i class="fas fa-comments"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                     <nav class="nav flex-column">
                         <a class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}" style="{{ request()->routeIs('dashboard') ? 'color: #2F648E;' : '' }}">
                             <i class="fas fa-tachometer-alt me-2"></i>
                             Dashboard
                         </a>
                         
                         <!-- People Section -->
                         <div class="nav-section mb-3">
                             <h6 class="text-white-50 text-uppercase small mb-2 px-3">
                                 <i class="fas fa-users me-1"></i>
                                 People
                             </h6>
                             <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                 <i class="fas fa-user-tie me-2"></i>
                                 employees
                             </a>
                            <a class="nav-link {{ request()->routeIs('password-accounts.*') ? 'active' : '' }}" href="{{ route('password-accounts.index') }}">
                                <i class="fas fa-key me-2"></i>
                                Password Management
                            </a>
                            <a class="nav-link {{ request()->routeIs('password-categories.*') ? 'active' : '' }}" href="{{ route('password-categories.index') }}">
                                <i class="fas fa-tags me-2"></i>
                                Password Categories
                            </a>
                             <a class="nav-link {{ request()->routeIs('suppliers.*') ? 'active' : '' }}" href="{{ route('suppliers.index') }}">
                                 <i class="fas fa-truck me-2"></i>
                                 Suppliers
                             </a>
                             <a class="nav-link {{ request()->routeIs('contacts.*') ? 'active' : '' }}" href="{{ route('contacts.index') }}">
                                 <i class="fas fa-address-book me-2"></i>
                                 Contacts
                             </a>
                             <a class="nav-link {{ request()->routeIs('contact-categories.*') ? 'active' : '' }}" href="{{ route('contact-categories.index') }}">
                                 <i class="fas fa-tags me-2"></i>
                                 Contact Categories
                             </a>
                         </div>
                         
                         <!-- Work Section -->
                         <div class="nav-section mb-3">
                             <h6 class="text-white-50 text-uppercase small mb-2 px-3">
                                 <i class="fas fa-briefcase me-1"></i>
                                 Work
                             </h6>
                             <a class="nav-link {{ request()->routeIs('tasks.*') ? 'active' : '' }}" href="{{ route('tasks.index') }}">
                                 <i class="fas fa-tasks me-2"></i>
                                 Tasks
                             </a>
                             <a class="nav-link {{ request()->routeIs('task-dashboard') ? 'active' : '' }}" href="{{ route('task-dashboard') }}">
                                 <i class="fas fa-chart-line me-2"></i>
                                 Task Dashboard
                             </a>
                             <a class="nav-link {{ request()->routeIs('employees.temp-management') ? 'active' : '' }}" href="{{ route('employees.temp-management') }}">
                                 <i class="fas fa-users-cog me-2"></i>
                                 Temp Employees
                             </a>
                             <a class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}" href="{{ route('departments.index') }}">
                                 <i class="fas fa-building me-2"></i>
                                 Departments
                             </a>
                             <a class="nav-link {{ request()->routeIs('requests.*') ? 'active' : '' }}" href="{{ route('requests.index') }}">
                                 <i class="fas fa-file-alt me-2"></i>
                                 Requests
                             </a>
                            <a class="nav-link {{ request()->routeIs('chat.*') ? 'active' : '' }}" href="{{ route('chat.index') }}">
                                <i class="fas fa-comments me-2"></i>
                                Internal Chat
                            </a>
                            <a class="nav-link {{ request()->routeIs('zoho.advanced-search') ? 'active' : '' }}" href="{{ route('zoho.advanced-search') }}">
                                <i class="fas fa-search me-2"></i>
                                بحث متقدم في Zoho
                            </a>
                            <a class="nav-link {{ request()->routeIs('eet-life.*') ? 'active' : '' }}" href="{{ route('eet-life.index') }}">
                                <i class="fas fa-heart me-2"></i>
                                EET Life
                            </a>
                            <a class="nav-link {{ request()->routeIs('audit.*') ? 'active' : '' }}" href="{{ route('audit.index') }}">
                                <i class="fas fa-clipboard-list me-2"></i>
                                Operations Management
                            </a>
                            <a class="nav-link {{ request()->routeIs('snipe-it.*') ? 'active' : '' }}" href="{{ route('snipe-it.index') }}">
                                <i class="fas fa-plug me-2"></i>
                                Snipe-IT Integration
                            </a>
                            <a class="nav-link {{ request()->routeIs('reports.*') ? 'active' : '' }}" href="{{ route('reports.index') }}">
                                <i class="fas fa-chart-bar me-2"></i>
                                {{ __('messages.reports_and_data') }}
                            </a>
                        </div>
                         
                         <!-- Assets Control Section -->
                         <div class="nav-section mb-3">
                             <h6 class="text-white-50 text-uppercase small mb-2 px-3">
                                 <i class="fas fa-cubes me-1"></i>
                                 Asset Control
                             </h6>
                             <a class="nav-link {{ request()->routeIs('assets.dashboard') ? 'active' : '' }}" href="{{ route('assets.dashboard') }}">
                                 <i class="fas fa-tachometer-alt me-2"></i>
                                 Asset Dashboard
                             </a>
                             <a class="nav-link {{ request()->routeIs('assets.assets.*') ? 'active' : '' }}" href="{{ route('assets.assets.index') }}">
                                 <i class="fas fa-cube me-2"></i>
                                 Assets
                             </a>
                             <a class="nav-link {{ request()->routeIs('assets.asset-categories.*') ? 'active' : '' }}" href="{{ route('assets.asset-categories.index') }}">
                                 <i class="fas fa-tags me-2"></i>
                                 Asset Categories
                             </a>
                             <a class="nav-link {{ request()->routeIs('assets.locations.*') ? 'active' : '' }}" href="{{ route('assets.locations.index') }}">
                                 <i class="fas fa-map-marker-alt me-2"></i>
                                 Asset Locations
                             </a>
                             <a class="nav-link {{ request()->routeIs('assets.assignments.*') ? 'active' : '' }}" href="{{ route('assets.assignments.index') }}">
                                 <i class="fas fa-handshake me-2"></i>
                                 Asset Assignments
                             </a>
                             <a class="nav-link {{ request()->routeIs('assets.logs.*') ? 'active' : '' }}" href="{{ route('assets.logs.index') }}">
                                 <i class="fas fa-history me-2"></i>
                                 Asset Logs
                             </a>
                         </div>
                     </nav>
                    
                    <!-- Language Toggle -->
                    <div class="mt-4 pt-3 border-top">
                        <div class="d-flex flex-column gap-2">
                            <a href="{{ route('lang.switch', 'en') }}" class="btn btn-sm btn-light text-dark" style="transform: translateY(0px);">
                                <i class="fas fa-globe me-1"></i> English
                            </a>
                            <a href="{{ route('lang.switch', 'ar') }}" class="btn btn-sm btn-outline-light text-dark" style="transform: translateY(0px);">
                                <i class="fas fa-globe me-1"></i> العربية
                            </a>
                        </div>
                    </div>
                    
                    <!-- User Menu -->
                    <div class="mt-3 pt-3 border-top">
                        <div class="dropdown">
                            <button class="btn btn-outline-light btn-sm w-100 dropdown-toggle" type="button" data-bs-toggle="dropdown" style="transform: translateY(0px);">
                                <i class="fas fa-user me-1"></i>
                                {{ Auth::user()->name ?? 'User' }}
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('users.show', Auth::user()->id) }}"><i class="fas fa-user me-2"></i>Profile</a></li>
                                <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i>Settings</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">
                                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                                        </button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-10 main-content">
                <div class="p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            {{ session('warning') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            <i class="fas fa-info-circle me-2"></i>
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SortableJS for drag and drop -->
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <!-- Notifications JS -->
    <script src="{{ asset('js/notifications.js') }}"></script>
    <!-- Chat Validation JS -->
    <script src="{{ asset('js/chat-validation.js') }}"></script>
    <!-- Bootstrap Toggle CSS -->
    <link href="{{ asset('css/vendor/bootstrap-toggle.min.css') }}" rel="stylesheet">
    <!-- Bootstrap Toggle JS -->
    <script src="{{ asset('js/vendor/bootstrap-toggle.min.js') }}"></script>
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- Custom JS -->
    <script src="{{ asset('js/language.js') }}"></script>
    
    <!-- Fix for form inputs display issues -->
    <style>
        /* Ensure form inputs are always visible and not affected by animations */
        .card form {
            opacity: 1 !important;
            transform: none !important;
        }
        
        .card form * {
            opacity: 1 !important;
            transform: none !important;
        }
        
        /* Prevent double display of form elements */
        .card[style*="opacity: 0"] form {
            opacity: 1 !important;
        }
    </style>
    
    <!-- Custom JS -->
    <script>
        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                const bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
        
        // Add fade-in animation to cards (only for non-form pages)
        document.addEventListener('DOMContentLoaded', function() {
            // Skip animation for edit forms to prevent double input display
            if (window.location.pathname.includes('/edit') || 
                window.location.pathname.includes('/create') ||
                document.querySelector('form')) {
                return;
            }
            
            const cards = document.querySelectorAll('.card');
            cards.forEach(function(card, index) {
                // Skip cards that contain forms
                if (card.querySelector('form')) {
                    return;
                }
                
                card.style.opacity = '0';
                card.style.transform = 'translateY(20px)';
                setTimeout(function() {
                    card.style.transition = 'all 0.5s ease';
                    card.style.opacity = '1';
                    card.style.transform = 'translateY(0)';
                }, index * 100);
            });
        });
    </script>
    
    <!-- Date Picker JavaScript -->
    <script src="{{ asset('js/date-picker.js') }}"></script>
    
    <!-- User Status JavaScript -->
    <script src="{{ asset('js/user-status.js') }}"></script>
    
    <!-- Double Click Navigation JavaScript -->
    <script src="{{ asset('js/double-click-navigation.js') }}"></script>
    
    @stack('scripts')
</body>
</html>