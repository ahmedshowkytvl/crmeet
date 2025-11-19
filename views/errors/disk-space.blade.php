<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('errors.disk_space.title') }}</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .error-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .error-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
        }
        
        .error-icon {
            font-size: 4rem;
            color: #ff6b6b;
            margin-bottom: 20px;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
        
        .error-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 20px;
        }
        
        .error-message {
            font-size: 1.2rem;
            color: #7f8c8d;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        
        .error-solutions {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin: 30px 0;
            text-align: left;
        }
        
        .solution-item {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            padding: 10px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .solution-icon {
            font-size: 1.5rem;
            color: #27ae60;
            margin-right: 15px;
            width: 30px;
        }
        
        .solution-text {
            flex: 1;
            color: #2c3e50;
            font-weight: 500;
        }
        
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
            margin-top: 30px;
        }
        
        .btn-custom {
            padding: 12px 30px;
            border-radius: 25px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }
        
        .btn-primary-custom {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
            color: white;
        }
        
        .btn-secondary-custom {
            background: #ecf0f1;
            color: #2c3e50;
        }
        
        .btn-secondary-custom:hover {
            background: #d5dbdb;
            transform: translateY(-2px);
            color: #2c3e50;
        }
        
        .technical-details {
            margin-top: 30px;
            padding: 20px;
            background: #2c3e50;
            color: white;
            border-radius: 15px;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
            text-align: left;
            display: none;
        }
        
        .toggle-details {
            background: none;
            border: none;
            color: #7f8c8d;
            font-size: 0.9rem;
            cursor: pointer;
            text-decoration: underline;
        }
        
        .toggle-details:hover {
            color: #2c3e50;
        }
        
        @media (max-width: 768px) {
            .error-card {
                padding: 30px 20px;
            }
            
            .error-title {
                font-size: 2rem;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
            
            .btn-custom {
                width: 100%;
                max-width: 300px;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-card">
            <div class="error-icon">
                <i class="fas fa-hdd"></i>
            </div>
            
            <h1 class="error-title">{{ __('errors.disk_space.title') }}</h1>
            
            <p class="error-message">
                {{ __('errors.disk_space.message') }}
            </p>
            
            <div class="error-solutions">
                <h5 class="mb-3">
                    <i class="fas fa-lightbulb text-warning me-2"></i>
                    {{ __('errors.disk_space.solutions_title') }}
                </h5>
                
                <div class="solution-item">
                    <i class="fas fa-trash-alt solution-icon"></i>
                    <div class="solution-text">{{ __('errors.disk_space.solution_1') }}</div>
                </div>
                
                <div class="solution-item">
                    <i class="fas fa-download solution-icon"></i>
                    <div class="solution-text">{{ __('errors.disk_space.solution_2') }}</div>
                </div>
                
                <div class="solution-item">
                    <i class="fas fa-cog solution-icon"></i>
                    <div class="solution-text">{{ __('errors.disk_space.solution_3') }}</div>
                </div>
                
                <div class="solution-item">
                    <i class="fas fa-headset solution-icon"></i>
                    <div class="solution-text">{{ __('errors.disk_space.solution_4') }}</div>
                </div>
            </div>
            
            <div class="action-buttons">
                <a href="{{ route('dashboard') }}" class="btn-custom btn-primary-custom">
                    <i class="fas fa-home me-2"></i>
                    {{ __('errors.disk_space.back_home') }}
                </a>
                
                <button onclick="window.location.reload()" class="btn-custom btn-secondary-custom">
                    <i class="fas fa-redo me-2"></i>
                    {{ __('errors.disk_space.retry') }}
                </button>
            </div>
            
            <div class="mt-4">
                <button class="toggle-details" onclick="toggleTechnicalDetails()">
                    {{ __('errors.disk_space.show_details') }}
                </button>
            </div>
            
            <div id="technical-details" class="technical-details">
                <h6>{{ __('errors.disk_space.technical_title') }}</h6>
                <p><strong>{{ __('errors.disk_space.error_type') }}:</strong> {{ $error['error_type'] ?? 'Unknown' }}</p>
                <p><strong>{{ __('errors.disk_space.error_message') }}:</strong> {{ $error['error_message'] ?? 'N/A' }}</p>
                <p><strong>{{ __('errors.disk_space.file') }}:</strong> {{ $error['file'] ?? 'N/A' }}</p>
                <p><strong>{{ __('errors.disk_space.line') }}:</strong> {{ $error['line'] ?? 'N/A' }}</p>
                <p><strong>{{ __('errors.disk_space.timestamp') }}:</strong> {{ $error['timestamp'] ?? 'N/A' }}</p>
            </div>
        </div>
    </div>
    
    <script>
        function toggleTechnicalDetails() {
            const details = document.getElementById('technical-details');
            const button = document.querySelector('.toggle-details');
            
            if (details.style.display === 'none' || details.style.display === '') {
                details.style.display = 'block';
                button.textContent = '{{ __("errors.disk_space.hide_details") }}';
            } else {
                details.style.display = 'none';
                button.textContent = '{{ __("errors.disk_space.show_details") }}';
            }
        }
        
        // Auto-hide technical details initially
        document.getElementById('technical-details').style.display = 'none';
    </script>
</body>
</html>
