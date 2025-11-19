<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>staffly v1 - نظام إدارة الموظفين</title>
    <meta http-equiv="Cache-Control" content="no-cache, no-store, must-revalidate">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
    <link href="{{ asset('css/batch-create-advanced.css') }}?v={{ time() }}" rel="stylesheet">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Cairo', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            overflow-x: hidden;
            transition: all 0.3s ease;
        }

        body.dark-theme {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
        }

        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 40px;
            animation: fadeInUp 0.8s ease;
            position: relative;
        }

        .header-actions {
            position: absolute;
            top: 0;
            right: 0;
            z-index: 10;
        }

        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 25px;
            background: rgba(255, 255, 255, 0.2);
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-weight: 700;
            font-size: 1rem;
            transition: all 0.3s ease;
            backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            min-width: 180px;
            justify-content: center;
        }

        .back-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
            color: white;
            text-decoration: none;
            border-color: rgba(255, 255, 255, 0.5);
        }

        .back-btn i {
            font-size: 1rem;
            transition: transform 0.3s ease;
        }

        .back-btn:hover i {
            transform: translateX(5px);
        }

        .header h1 {
            color: white;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
            text-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }

        .header p {
            color: rgba(255,255,255,0.9);
            font-size: 1.1rem;
            font-weight: 400;
        }

        /* Theme Toggle */
        .theme-toggle {
            position: fixed;
            top: 30px;
            left: 30px;
            z-index: 1000;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 50px;
            padding: 12px 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            color: white;
            font-size: 1.2rem;
        }

        .theme-toggle:hover {
            transform: scale(1.05);
            background: rgba(255,255,255,0.2);
        }

        /* Language Toggle */
        .language-toggle {
            position: fixed;
            top: 30px;
            right: 30px;
            z-index: 1000;
            display: flex;
            gap: 10px;
        }

        .lang-btn {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 10px 18px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(15px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 25px;
            color: white;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            min-width: 100px;
            justify-content: center;
        }

        .lang-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
        }

        .lang-btn.active {
            background: rgba(255, 255, 255, 0.4);
            border-color: rgba(255, 255, 255, 0.6);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.4);
        }

        .lang-btn i {
            font-size: 0.9rem;
        }

        /* Ensure buttons are visible */
        .language-toggle, .back-btn {
            opacity: 1 !important;
            visibility: visible !important;
            display: flex !important;
        }

        /* Add glow effect for better visibility */
        .lang-btn, .back-btn {
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .lang-btn:hover, .back-btn:hover {
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
        }

        /* RTL/LTR Support */
        .header-actions {
            right: 0;
            left: auto;
        }

        [dir="ltr"] .header-actions {
            left: 0;
            right: auto;
        }

        .back-btn i {
            transform: scaleX(-1);
        }

        [dir="ltr"] .back-btn i {
            transform: scaleX(1);
        }

        [dir="ltr"] .back-btn:hover i {
            transform: translateX(-3px);
        }

        .back-btn:hover i {
            transform: translateX(3px);
        }

        /* Glass Cards */
        .glass-card {
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255,255,255,0.2);
            border-radius: 20px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            animation: fadeInUp 0.8s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.2);
        }

        /* File Upload Area */
        .upload-area {
            border: 3px dashed rgba(255,255,255,0.3);
            border-radius: 20px;
            padding: 60px 20px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
            position: relative;
            overflow: hidden;
        }

        .upload-area.dragover {
            border-color: #4CAF50;
            background: rgba(76,175,80,0.1);
            transform: scale(1.02);
        }

        .upload-area:hover {
            border-color: rgba(255,255,255,0.5);
            background: rgba(255,255,255,0.05);
        }

        .upload-icon {
            font-size: 4rem;
            color: rgba(255,255,255,0.7);
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        .upload-text {
            color: white;
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .upload-subtext {
            color: rgba(255,255,255,0.7);
            font-size: 1rem;
        }

        /* Progress Bar */
        .progress-container {
            display: none;
            margin-top: 20px;
        }

        .progress-bar {
            width: 100%;
            height: 8px;
            background: rgba(255,255,255,0.2);
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #4CAF50, #45a049);
            border-radius: 10px;
            transition: width 0.3s ease;
            animation: progressShine 2s infinite;
        }

        @keyframes progressShine {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        /* Data Preview Table */
        .data-preview {
            display: none;
        }

        /* Data Controls */
        .data-controls {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .control-group {
            margin-bottom: 20px;
        }

        .control-group:last-child {
            margin-bottom: 0;
        }

        .control-group h4 {
            font-size: 1rem;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sort-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .sort-btn {
            padding: 8px 15px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            color: white;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .sort-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
        }

        .sort-btn.active {
            background: rgba(74, 144, 226, 0.8);
            border-color: rgba(74, 144, 226, 1);
        }

        .date-controls, .filter-controls {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .control-btn {
            padding: 10px 18px;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 25px;
            color: white;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 120px;
            justify-content: center;
        }

        .control-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .date-btn:hover {
            background: rgba(46, 204, 113, 0.3);
            border-color: rgba(46, 204, 113, 0.5);
        }

        .format-btn:hover {
            background: rgba(52, 152, 219, 0.3);
            border-color: rgba(52, 152, 219, 0.5);
        }

        .clear-btn:hover {
            background: rgba(231, 76, 60, 0.3);
            border-color: rgba(231, 76, 60, 0.5);
        }

        .save-btn:hover {
            background: rgba(46, 204, 113, 0.3);
            border-color: rgba(46, 204, 113, 0.5);
        }

        .test-btn:hover {
            background: rgba(155, 89, 182, 0.3);
            border-color: rgba(155, 89, 182, 0.5);
        }

        .filter-btn:hover {
            background: rgba(230, 126, 34, 0.3);
            border-color: rgba(230, 126, 34, 0.5);
        }

        .changes-btn:hover {
            background: rgba(52, 73, 94, 0.3);
            border-color: rgba(52, 73, 94, 0.5);
        }

        .search-input {
            padding: 10px 15px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 25px;
            color: white;
            font-size: 0.9rem;
            min-width: 200px;
            backdrop-filter: blur(10px);
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }

        .search-input:focus {
            outline: none;
            border-color: rgba(74, 144, 226, 0.8);
            background: rgba(255, 255, 255, 0.15);
        }

        /* Date Format Dialog */
        .date-format-dialog {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            backdrop-filter: blur(5px);
        }

        .dialog-content {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 30px;
            max-width: 500px;
            width: 90%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 0.3s ease;
        }

        .dialog-content h3 {
            color: #333;
            margin-bottom: 15px;
            text-align: center;
        }

        .dialog-content p {
            color: #666;
            margin-bottom: 20px;
            text-align: center;
        }

        .format-options {
            margin-bottom: 25px;
        }

        .format-options label {
            display: block;
            margin-bottom: 12px;
            padding: 10px 15px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .format-options label:hover {
            background: rgba(74, 144, 226, 0.1);
            border-color: rgba(74, 144, 226, 0.3);
        }

        .format-options input[type="radio"] {
            margin-left: 10px;
        }

        .dialog-buttons {
            display: flex;
            gap: 10px;
            justify-content: center;
        }

        .dialog-buttons .control-btn {
            min-width: 100px;
        }

        /* Date Test Dialog */
        .date-test-dialog {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10001;
            backdrop-filter: blur(5px);
        }

        .date-test-dialog .dialog-content {
            max-width: 800px;
            max-height: 80vh;
            overflow-y: auto;
        }

        .test-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .stat-item {
            background: rgba(255, 255, 255, 0.1);
            padding: 15px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .stat-label {
            color: #666;
            font-weight: 600;
        }

        .stat-value {
            color: #333;
            font-weight: 700;
            font-size: 1.2rem;
        }

        .test-results {
            margin-bottom: 20px;
        }

        .test-results h4 {
            color: #333;
            margin-bottom: 15px;
        }

        .results-list {
            max-height: 300px;
            overflow-y: auto;
            border: 1px solid rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            padding: 10px;
        }

        .result-item {
            background: rgba(255, 255, 255, 0.5);
            padding: 10px;
            margin-bottom: 8px;
            border-radius: 8px;
            border-left: 4px solid #4a90e2;
        }

        .result-info {
            display: flex;
            justify-content: space-between;
            margin-bottom: 5px;
        }

        .location {
            font-weight: 600;
            color: #333;
        }

        .format {
            color: #666;
            font-size: 0.9rem;
        }

        .result-values {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
        }

        .original {
            color: #e74c3c;
        }

        .fixed {
            color: #27ae60;
            font-weight: 600;
        }

        .table-container {
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            padding: 20px;
            margin-top: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-family: 'Cairo', sans-serif;
        }

        .data-table th,
        .data-table td {
            padding: 12px;
            text-align: right;
            border-bottom: 1px solid #eee;
            transition: all 0.3s ease;
        }

        .data-table th {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .data-table tr:hover {
            background: rgba(102,126,234,0.1);
            transform: scale(1.01);
        }

        /* Column Mapping */
        .mapping-container {
            display: none;
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
        }

        /* Force visibility for all mapping items */
        .mapping-item,
        .mapping-item *,
        #mappingGrid,
        #mappingGrid *,
        .mapping-grid,
        .mapping-grid * {
            display: block !important;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            z-index: auto !important;
        }

        .mapping-item {
            display: flex !important;
            margin-bottom: 15px !important;
        }

        .mapping-grid {
            display: grid !important;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
            max-height: 600px;
            overflow-y: auto;
            padding: 15px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .mapping-item {
            display: flex !important;
            align-items: center;
            justify-content: space-between;
            visibility: visible !important;
            opacity: 1 !important;
            position: relative !important;
            padding: 15px;
            background: rgba(102,126,234,0.1);
            border-radius: 10px;
            border: 2px solid transparent;
            transition: all 0.3s ease;
            min-height: 60px;
            margin-bottom: 10px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .mapping-item.required {
            border-color: #e74c3c;
            background: rgba(231,76,60,0.1);
        }

        .mapping-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .mapping-label {
            font-weight: 600;
            color: #2c3e50;
        }

        .mapping-select {
            padding: 8px 15px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-family: 'Cairo', sans-serif;
            background: white;
            transition: all 0.3s ease;
        }

        .mapping-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 10px rgba(102,126,234,0.3);
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            margin-top: 30px;
        }

        .btn {
            padding: 15px 30px;
            border: none;
            border-radius: 50px;
            font-family: 'Cairo', sans-serif;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            position: relative;
            overflow: hidden;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(255,255,255,0.3);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: all 0.5s ease;
        }

        .btn:hover::before {
            width: 300px;
            height: 300px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 5px 15px rgba(102,126,234,0.4);
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(102,126,234,0.6);
        }

        .btn-success {
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            box-shadow: 0 5px 15px rgba(76,175,80,0.4);
        }

        .btn-success:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(76,175,80,0.6);
        }

        .btn-secondary {
            background: linear-gradient(135deg, #6c757d, #5a6268);
            color: white;
            box-shadow: 0 5px 15px rgba(108,117,125,0.4);
        }

        .btn-secondary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(108,117,125,0.6);
        }

        /* Default Values Section */
        .default-values {
            display: none;
            background: rgba(255,255,255,0.95);
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            font-family: 'Cairo', sans-serif;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 10px rgba(102,126,234,0.3);
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        /* Toast Notifications */
        .toast {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 25px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            z-index: 1000;
            transform: translateX(400px);
            transition: all 0.3s ease;
        }

        .toast.show {
            transform: translateX(0);
        }

        .toast.success {
            background: linear-gradient(135deg, #4CAF50, #45a049);
        }

        .toast.error {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }
            
            .header h1 {
                font-size: 2rem;
            }
            
            .glass-card {
                padding: 20px;
            }
            
            .language-toggle {
                top: 20px;
                right: 20px;
                flex-direction: column;
                gap: 8px;
            }
            
            .lang-btn {
                padding: 8px 15px;
                font-size: 0.8rem;
                min-width: 80px;
            }
            
            .back-btn {
                padding: 12px 20px;
                font-size: 0.9rem;
                min-width: 150px;
            }
            
            .header-actions {
                position: relative;
                top: auto;
                right: auto;
                margin-bottom: 20px;
                text-align: center;
            }
            
            .upload-area {
                padding: 40px 15px;
            }
            
            .mapping-grid {
                grid-template-columns: 1fr;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: center;
            }
        }

        /* Loading Spinner */
        .spinner {
            display: none;
            width: 40px;
            height: 40px;
            border: 4px solid rgba(255,255,255,0.3);
            border-top: 4px solid white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* RTL Support */
        body[dir="rtl"] .data-table th,
        body[dir="rtl"] .data-table td {
            text-align: right;
        }

        body[dir="rtl"] .mapping-item {
            flex-direction: row-reverse;
        }
    </style>
</head>
<body>
    <!-- Theme Toggle -->
    <div class="theme-toggle" onclick="toggleTheme()">
        <i class="fas fa-moon" id="theme-icon"></i>
    </div>
    
    <!-- Language Toggle -->
    <div class="language-toggle">
        <button onclick="changeLanguage('ar')" class="lang-btn" id="arBtn">
            <i class="fas fa-globe"></i>
            <span>العربية</span>
        </button>
        <button onclick="changeLanguage('en')" class="lang-btn" id="enBtn">
            <i class="fas fa-globe"></i>
            <span>English</span>
        </button>
    </div>

    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-actions">
                <a href="{{ route('dashboard') }}" class="back-btn" id="backToDashboard">
                    <i class="fas fa-arrow-right"></i>
                    <span>{{ __('messages.back_to_dashboard') }}</span>
                </a>
            </div>
            <h1>🚀 staffly v1</h1>
            <p>رفع ومعالجة ملفات Excel لإضافة عدة موظفين دفعة واحدة</p>
        </div>

        <!-- File Upload Card -->
        <div class="glass-card">
            <div class="upload-area" id="uploadArea">
                <div class="upload-icon">
                    <i class="fas fa-cloud-upload-alt"></i>
                </div>
                <div class="upload-text">اسحب ملف Excel هنا أو انقر للاختيار</div>
                <div class="upload-subtext">يدعم ملفات .xlsx و .xls</div>
                <input type="file" id="fileInput" accept=".xlsx,.xls" style="display: none;">
            </div>
            
            <div class="progress-container" id="progressContainer">
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
                <div style="text-align: center; margin-top: 10px; color: white;" id="progressText">جاري المعالجة...</div>
            </div>
        </div>

        <!-- Data Preview Card -->
        <div class="glass-card data-preview" id="dataPreview">
            <h3 style="color: white; margin-bottom: 20px; text-align: center;">
                <i class="fas fa-table"></i> معاينة البيانات
            </h3>
            
            <!-- Data Control Buttons -->
            <div class="data-controls">
                <div class="control-group">
                    <h4 style="color: white; margin-bottom: 10px;">
                        <i class="fas fa-sort"></i> ترتيب البيانات
                    </h4>
                    <div class="sort-buttons" id="sortButtons">
                        <!-- Sort buttons will be generated dynamically -->
                    </div>
                </div>
                
                <div class="control-group">
                    <h4 style="color: white; margin-bottom: 10px;">
                        <i class="fas fa-calendar-alt"></i> معالجة التواريخ
                    </h4>
                    <div class="date-controls">
                        <button onclick="detectAndFixDates()" class="control-btn date-btn">
                            <i class="fas fa-search"></i>
                            <span>البحث عن التواريخ وإصلاحها</span>
                        </button>
                        <button onclick="showDateFormatDialog()" class="control-btn format-btn">
                            <i class="fas fa-edit"></i>
                            <span>تحديد تنسيق التواريخ</span>
                        </button>
                        <button onclick="testDateDetection()" class="control-btn test-btn">
                            <i class="fas fa-bug"></i>
                            <span>اختبار التواريخ</span>
                        </button>
                        <button onclick="filterRealDates()" class="control-btn filter-btn">
                            <i class="fas fa-filter"></i>
                            <span>تصفية التواريخ الحقيقية</span>
                        </button>
                        <button onclick="showDateChanges()" class="control-btn changes-btn">
                            <i class="fas fa-history"></i>
                            <span>عرض التغييرات</span>
                        </button>
                    </div>
                </div>
                
                <div class="control-group">
                    <h4 style="color: white; margin-bottom: 10px;">
                        <i class="fas fa-filter"></i> تصفية البيانات
                    </h4>
                    <div class="filter-controls">
                        <input type="text" id="dataSearch" placeholder="البحث في البيانات..." class="search-input">
                        <button onclick="clearFilters()" class="control-btn clear-btn">
                            <i class="fas fa-times"></i>
                            <span>مسح الفلاتر</span>
                        </button>
                    </div>
                </div>
            </div>
            <div class="table-container">
                <div style="overflow-x: auto;">
                    <table class="data-table" id="dataTable">
                        <thead id="tableHead"></thead>
                        <tbody id="tableBody"></tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Column Mapping Card -->
        <div class="glass-card">
            <div class="mapping-container" id="mappingContainer">
                <h3 style="color: #2c3e50; margin-bottom: 20px; text-align: center;">
                    <i class="fas fa-link"></i> ربط الأعمدة
                </h3>
                <p style="color: #7f8c8d; text-align: center; margin-bottom: 20px;">
                    قم بربط أعمدة Excel مع حقول النظام المطلوبة
                </p>
                
                <!-- معلومات تنسيقات التواريخ -->
                <div style="background: rgba(102,126,234,0.1); border-radius: 10px; padding: 15px; margin-bottom: 20px; border-left: 4px solid #667eea;">
                    <h6 style="color: #667eea; margin-bottom: 10px;">
                        <i class="fas fa-calendar-alt"></i> تنسيقات التواريخ المدعومة:
                    </h6>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 10px; font-size: 0.9rem; color: #555;">
                        <div>• 01-JAN-2025</div>
                        <div>• 15-Jan-2024</div>
                        <div>• 12/2023</div>
                        <div>• 2024-06-15</div>
                        <div>• 03-11-1995</div>
                        <div>• 15-يناير-1990</div>
                        <div>• 31-ديسمبر-2025</div>
                        <div>• 25-12-2000</div>
                    </div>
                    <p style="margin: 10px 0 0 0; font-size: 0.85rem; color: #666;">
                        <i class="fas fa-info-circle"></i> جميع التواريخ ستتحول إلى تنسيق موحد: <strong>DD Month YYYY</strong>
                    </p>
                </div>
                
                <div class="mapping-grid" id="mappingGrid"></div>
            </div>
        </div>

        <!-- Default Values Card -->
        <div class="glass-card">
            <div class="default-values" id="defaultValues">
                <h3 style="color: #2c3e50; margin-bottom: 20px; text-align: center;">
                    <i class="fas fa-cog"></i> القيم الافتراضية
                </h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
                    <div class="form-group">
                        <label class="form-label">القسم الافتراضي</label>
                        <select class="form-control" id="defaultDepartment">
                            <option value="">اختر القسم</option>
                            <!-- سيتم ملء هذا من قاعدة البيانات -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">المنصب الافتراضي</label>
                        <input type="text" class="form-control" id="defaultPosition" placeholder="مثال: مطور ويب">
                    </div>
                    <div class="form-group">
                        <label class="form-label">رقم الهاتف الافتراضي</label>
                        <input type="text" class="form-control" id="defaultPhone" placeholder="مثال: 966501234567">
                    </div>
                    <div class="form-group">
                        <label class="form-label" style="color: #e74c3c; font-weight: bold;">
                            <input type="checkbox" id="allowDuplicateEmails" style="margin-left: 10px; transform: scale(1.2);">
                            ⚠️ السماح بالأيميلات المكررة
                        </label>
                        <small class="form-text" style="color: #e67e22; font-weight: 500;">
                            <i class="fas fa-exclamation-triangle"></i> 
                            <strong>مهم:</strong> إذا كان لديك موظفين بنفس البريد الإلكتروني في الملف، يجب تفعيل هذا الخيار لتجنب فشل الحفظ
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <div class="user-info" style="margin-bottom: 15px; padding: 10px; background: rgba(255, 255, 255, 0.1); border-radius: 8px; text-align: center;">
                <i class="fas fa-user"></i> 
                <strong>مرحباً {{ $currentUser->name ?? 'المستخدم' }}</strong>
                @if($currentUser->name_ar)
                    <br><span style="font-size: 0.9em; color: #666;">{{ $currentUser->name_ar }}</span>
                @endif
            </div>
            <button class="btn btn-secondary" id="downloadTemplate">
                <i class="fas fa-download"></i> تحميل قالب Excel
            </button>
            <button class="btn btn-primary" id="processData" style="display: none;">
                <i class="fas fa-cogs"></i> معالجة البيانات
            </button>
            <button class="btn btn-success" id="saveData" style="display: none;">
                <i class="fas fa-save"></i> حفظ البيانات
            </button>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toastContainer"></div>

    <!-- Loading Spinner -->
    <div class="spinner" id="loadingSpinner"></div>

    <script src="{{ asset('js/batch-create-advanced.js') }}?v={{ time() }}"></script>
    <script>
        // Global Variables
        let uploadedData = null;
        let columnMapping = {};
        let isDarkTheme = localStorage.getItem('theme') === 'dark';
        let currentLanguage = localStorage.getItem('language') || 'ar';
        
        // Language translations
        const translations = {
            ar: {
                backToDashboard: 'العودة للوحة التحكم',
                advancedEmployeeAddition: 'staffly v1',
                uploadAndProcessExcel: 'رفع ومعالجة ملفات Excel لإضافة عدة موظفين دفعة واحدة',
                uploadFile: 'رفع ملف Excel',
                dragDropFile: 'اسحب وأفلت ملف Excel هنا أو انقر للاختيار',
                supportedFormats: 'الملفات المدعومة: .xlsx, .xls',
                maxFileSize: 'الحد الأقصى: 10 ميجابايت',
                downloadTemplate: 'تحميل قالب',
                processData: 'معالجة البيانات',
                saveData: 'حفظ البيانات',
                back: 'رجوع',
                next: 'التالي',
                cancel: 'إلغاء',
                save: 'حفظ',
                name: 'الاسم',
                email: 'البريد الإلكتروني',
                phone: 'رقم الهاتف',
                position: 'المنصب',
                department: 'القسم',
                hiringDate: 'تاريخ التعيين',
                address: 'العنوان',
                notes: 'ملاحظات'
            },
            en: {
                backToDashboard: 'Back to Dashboard',
                advancedEmployeeAddition: 'staffly v1',
                uploadAndProcessExcel: 'Upload and process Excel files to add multiple employees at once',
                uploadFile: 'Upload Excel File',
                dragDropFile: 'Drag and drop Excel file here or click to select',
                supportedFormats: 'Supported formats: .xlsx, .xls',
                maxFileSize: 'Maximum size: 10MB',
                downloadTemplate: 'Download Template',
                processData: 'Process Data',
                saveData: 'Save Data',
                back: 'Back',
                next: 'Next',
                cancel: 'Cancel',
                save: 'Save',
                name: 'Name',
                email: 'Email',
                phone: 'Phone',
                position: 'Position',
                department: 'Department',
                hiringDate: 'Hiring Date',
                address: 'Address',
                notes: 'Notes'
            }
        };

        // Language functions
        function updateLanguage() {
            const t = translations[currentLanguage];
            
            // Update header text
            document.querySelector('.header h1').textContent = t.advancedEmployeeAddition;
            document.querySelector('.header p').textContent = t.uploadAndProcessExcel;
            
            // Update back button
            const backBtn = document.getElementById('backToDashboard');
            if (backBtn) {
                backBtn.querySelector('span').textContent = t.backToDashboard;
            }
            
            // Update other elements
            const uploadText = document.querySelector('.upload-text');
            if (uploadText) {
                uploadText.textContent = t.uploadFile;
            }
            
            const dragText = document.querySelector('.drag-text');
            if (dragText) {
                dragText.textContent = t.dragDropFile;
            }
            
            const formatsText = document.querySelector('.formats-text');
            if (formatsText) {
                formatsText.textContent = t.supportedFormats;
            }
            
            const sizeText = document.querySelector('.size-text');
            if (sizeText) {
                sizeText.textContent = t.maxFileSize;
            }
            
            // Update buttons
            const downloadBtn = document.getElementById('downloadTemplate');
            if (downloadBtn) {
                downloadBtn.textContent = t.downloadTemplate;
            }
            
            const processBtn = document.getElementById('processData');
            if (processBtn) {
                processBtn.textContent = t.processData;
            }
            
            const saveBtn = document.getElementById('saveData');
            if (saveBtn) {
                saveBtn.textContent = t.saveData;
            }
        }
        
        function changeLanguage(lang) {
            currentLanguage = lang;
            localStorage.setItem('language', lang);
            updateLanguage();
            
            // Update document direction
            if (lang === 'ar') {
                document.documentElement.setAttribute('dir', 'rtl');
                document.documentElement.setAttribute('lang', 'ar');
            } else {
                document.documentElement.setAttribute('dir', 'ltr');
                document.documentElement.setAttribute('lang', 'en');
            }
            
            // Update active language button
            document.querySelectorAll('.lang-btn').forEach(btn => {
                btn.classList.remove('active');
            });
            document.getElementById(lang + 'Btn').classList.add('active');
        }

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOMContentLoaded event fired');
            initializeTheme();
            initializeEventListeners();
            updateLanguage();
            loadDepartments();
            
            // Set active language button
            document.getElementById(currentLanguage + 'Btn').classList.add('active');
            
            // Set document direction based on current language
            if (currentLanguage === 'ar') {
                document.documentElement.setAttribute('dir', 'rtl');
                document.documentElement.setAttribute('lang', 'ar');
            } else {
                document.documentElement.setAttribute('dir', 'ltr');
                document.documentElement.setAttribute('lang', 'en');
            }
            
            // Initialize search functionality
            const searchInput = document.getElementById('dataSearch');
            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    filterData(this.value);
                });
            }
        });

        // Filter data based on search term
        function filterData(searchTerm) {
            if (!window.originalData || window.originalData.length < 2) return;
            
            if (!searchTerm.trim()) {
                window.currentData = [...window.originalData];
                updateTableDisplay();
                return;
            }
            
            const data = [...window.originalData];
            const header = data[0];
            const rows = data.slice(1);
            
            const filteredRows = rows.filter(row => {
                return row.some(cell => {
                    const cellValue = (cell || '').toString().toLowerCase();
                    return cellValue.includes(searchTerm.toLowerCase());
                });
            });
            
            window.currentData = [header, ...filteredRows];
            updateTableDisplay();
        }

        // Theme Management
        function toggleTheme() {
            isDarkTheme = !isDarkTheme;
            document.body.classList.toggle('dark-theme', isDarkTheme);
            localStorage.setItem('theme', isDarkTheme ? 'dark' : 'light');
            
            const icon = document.getElementById('theme-icon');
            icon.className = isDarkTheme ? 'fas fa-sun' : 'fas fa-moon';
        }

        function initializeTheme() {
            if (isDarkTheme) {
                document.body.classList.add('dark-theme');
                document.getElementById('theme-icon').className = 'fas fa-sun';
            }
        }

        // Event Listeners
        // Flag to prevent duplicate event listeners
        let eventListenersInitialized = false;

        function initializeEventListeners() {
            console.log('initializeEventListeners called, eventListenersInitialized:', eventListenersInitialized);
            // Prevent duplicate initialization
            if (eventListenersInitialized) {
                console.log('Event listeners already initialized, skipping...');
                return;
            }

            const uploadArea = document.getElementById('uploadArea');
            const fileInput = document.getElementById('fileInput');
            const downloadTemplate = document.getElementById('downloadTemplate');
            const processData = document.getElementById('processData');
            const saveData = document.getElementById('saveData');

            // Check if elements exist
            if (!uploadArea || !fileInput) {
                console.error('Upload elements not found');
                return;
            }

            // File Upload Events
            uploadArea.addEventListener('click', () => {
                console.log('Upload area clicked');
                console.log('Calling fileInput.click()');
                fileInput.click();
                console.log('fileInput.click() called');
            });
            uploadArea.addEventListener('dragover', handleDragOver);
            uploadArea.addEventListener('dragleave', handleDragLeave);
            uploadArea.addEventListener('drop', handleDrop);
            fileInput.addEventListener('change', (e) => {
                console.log('File input change event fired');
                handleFileSelect(e);
            });

            // Button Events (check if elements exist)
            if (downloadTemplate) {
                downloadTemplate.addEventListener('click', downloadExcelTemplate);
            }
            if (processData) {
                processData.addEventListener('click', processUploadedData);
            }
            if (saveData) {
                saveData.addEventListener('click', saveEmployeeData);
            }

            // Mark as initialized
            eventListenersInitialized = true;
        }

        // File Upload Handlers
        function handleDragOver(e) {
            e.preventDefault();
            e.currentTarget.classList.add('dragover');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.currentTarget.classList.remove('dragover');
        }

        function handleDrop(e) {
            console.log('handleDrop called');
            e.preventDefault();
            e.currentTarget.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                console.log('Processing dropped file:', files[0].name);
                processFile(files[0]);
            }
        }

        // Flag to prevent duplicate file processing
        let isProcessingFile = false;

        function handleFileSelect(e) {
            console.log('handleFileSelect called, isProcessingFile:', isProcessingFile);
            // Prevent duplicate processing
            if (isProcessingFile) {
                console.log('File processing already in progress, skipping...');
                return;
            }

            const file = e.target.files[0];
            if (file) {
                console.log('Processing file:', file.name);
                isProcessingFile = true;
                processFile(file);
                
                // Reset flag after processing
                setTimeout(() => {
                    isProcessingFile = false;
                    console.log('File processing flag reset');
                }, 1000);
            }
        }

        // File Processing
        function processFile(file) {
            console.log('processFile called with file:', file.name);
            if (!file.name.match(/\.(xlsx|xls)$/)) {
                showToast('يرجى اختيار ملف Excel صحيح', 'error');
                return;
            }

            // Clear previous file input to prevent duplicate processing
            const fileInput = document.getElementById('fileInput');
            if (fileInput) {
                fileInput.value = '';
            }

            showProgress();
            
            const reader = new FileReader();
            reader.onload = function(e) {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, {type: 'array'});
                    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                    const jsonData = XLSX.utils.sheet_to_json(firstSheet, {header: 1});
                    
                    if (jsonData.length < 2) {
                        showToast('الملف فارغ أو لا يحتوي على بيانات كافية', 'error');
                        hideProgress();
                        return;
                    }

                    // Store original data
                    uploadedData = jsonData;
                    window.originalData = jsonData;
                    window.currentData = jsonData;
                    
                    // Auto-detect and fix dates after upload
                    const fixedData = autoDetectAndFixDates(jsonData);
                    
                    hideProgress();
                    showDataPreview(fixedData);
                    showToast('تم رفع الملف ومعالجة التواريخ تلقائياً', 'success');
                    
                } catch (error) {
                    console.error('خطأ في معالجة الملف:', error);
                    showToast('خطأ في معالجة الملف', 'error');
                    hideProgress();
                }
            };
            
            reader.readAsArrayBuffer(file);
        }

        // Auto-detect and fix dates after file upload
        function autoDetectAndFixDates(data) {
            if (!data || data.length < 2) return data;
            
            let fixedCount = 0;
            const processedData = [...data];
            
            // Process each row (skip header)
            for (let i = 1; i < processedData.length; i++) {
                const row = processedData[i];
                
                // Check each cell for date-like content
                row.forEach((cell, cellIndex) => {
                    if (cell && typeof cell === 'string') {
                        const originalValue = cell;
                        const fixedDate = tryAllDateFormats(originalValue);
                        
                        if (fixedDate && fixedDate !== originalValue) {
                            processedData[i][cellIndex] = fixedDate;
                            fixedCount++;
                        }
                    }
                });
            }
            
            // Update current data
            window.currentData = processedData;
            
            if (fixedCount > 0) {
                showToast(`تم إصلاح ${fixedCount} تاريخ تلقائياً`, 'success');
            }
            
            return processedData;
        }

        // Data Preview
        function showDataPreview(data) {
            console.log('showDataPreview called with data length:', data.length);
            const preview = document.getElementById('dataPreview');
            const tableHead = document.getElementById('tableHead');
            const tableBody = document.getElementById('tableBody');
            
            // Clear previous data
            tableHead.innerHTML = '';
            tableBody.innerHTML = '';
            
            // Create header
            const headerRow = document.createElement('tr');
            data[0].forEach((header, index) => {
                const th = document.createElement('th');
                th.textContent = header || `العمود ${index + 1}`;
                headerRow.appendChild(th);
            });
            tableHead.appendChild(headerRow);
            
            // Create body (limit to first 10 rows for preview)
            const previewRows = data.slice(1, 11);
            previewRows.forEach(row => {
                const tr = document.createElement('tr');
                row.forEach(cell => {
                    const td = document.createElement('td');
                    td.textContent = cell || '';
                    tr.appendChild(td);
                });
                tableBody.appendChild(tr);
            });
            
            // Store original data for sorting
            window.originalData = data;
            window.currentData = data;
            
            // Create sort buttons
            createSortButtons(data[0]);
            
            // Show preview and mapping
            preview.style.display = 'block';
            console.log('Calling showColumnMapping with headers:', data[0]);
            showColumnMapping(data[0]);
            document.getElementById('processData').style.display = 'inline-flex';
            
            // Animate
            preview.style.animation = 'fadeInUp 0.8s ease';
        }

        // Create sort buttons for each column
        function createSortButtons(headers) {
            const sortButtons = document.getElementById('sortButtons');
            sortButtons.innerHTML = '';
            
            headers.forEach((header, index) => {
                const btn = document.createElement('button');
                btn.className = 'sort-btn';
                btn.innerHTML = `<i class="fas fa-sort"></i> ${header || `العمود ${index + 1}`}`;
                btn.onclick = () => sortData(index);
                sortButtons.appendChild(btn);
            });
        }

        // Sort data by column
        function sortData(columnIndex) {
            if (!window.currentData || window.currentData.length < 2) return;
            
            const data = [...window.currentData];
            const header = data[0];
            const rows = data.slice(1);
            
            // Toggle sort direction
            const isAscending = !window.sortDirection || window.sortDirection !== 'asc';
            window.sortDirection = isAscending ? 'asc' : 'desc';
            
            rows.sort((a, b) => {
                const aVal = a[columnIndex] || '';
                const bVal = b[columnIndex] || '';
                
                // Try to parse as numbers first
                const aNum = parseFloat(aVal);
                const bNum = parseFloat(bVal);
                
                if (!isNaN(aNum) && !isNaN(bNum)) {
                    return isAscending ? aNum - bNum : bNum - aNum;
                }
                
                // Sort as strings
                const comparison = aVal.toString().localeCompare(bVal.toString());
                return isAscending ? comparison : -comparison;
            });
            
            // Update data
            window.currentData = [header, ...rows];
            
            // Update table
            updateTableDisplay();
            
            // Update sort button states
            document.querySelectorAll('.sort-btn').forEach(btn => btn.classList.remove('active'));
            event.target.classList.add('active');
            
            // Update sort icon
            const icon = event.target.querySelector('i');
            icon.className = isAscending ? 'fas fa-sort-up' : 'fas fa-sort-down';
        }

        // Update table display
        function updateTableDisplay() {
            const tableBody = document.getElementById('tableBody');
            tableBody.innerHTML = '';
            
            const previewRows = window.currentData.slice(1, 11);
            previewRows.forEach(row => {
                const tr = document.createElement('tr');
                row.forEach(cell => {
                    const td = document.createElement('td');
                    td.textContent = cell || '';
                    tr.appendChild(td);
                });
                tableBody.appendChild(tr);
            });
        }

        // Detect and fix dates
        function detectAndFixDates() {
            if (!window.currentData || window.currentData.length < 2) {
                showToast('لا توجد بيانات لمعالجة التواريخ', 'error');
                return;
            }
            
            let fixedCount = 0;
            let detectedFormats = new Set();
            const data = [...window.currentData];
            
            // Get saved date format or use default
            const savedFormat = localStorage.getItem('defaultDateFormat') || 'excel';
            
            // Process each row (skip header)
            for (let i = 1; i < data.length; i++) {
                const row = data[i];
                
                // Check each cell for date-like content
                row.forEach((cell, cellIndex) => {
                    if (cell && typeof cell === 'string') {
                        const originalValue = cell;
                        const fixedDate = parseExcelDateWithFormat(cell, savedFormat);
                        
                        if (fixedDate && fixedDate !== cell) {
                            data[i][cellIndex] = fixedDate;
                            fixedCount++;
                            
                            // Detect what format was used
                            const detectedFormat = detectDateFormat(originalValue);
                            if (detectedFormat) {
                                detectedFormats.add(detectedFormat);
                            }
                        }
                    }
                });
            }
            
            // Update data
            window.currentData = data;
            updateTableDisplay();
            
            // Show detailed results
            let message = `تم إصلاح ${fixedCount} تاريخ`;
            if (detectedFormats.size > 0) {
                message += `\nالتنسيقات المكتشفة: ${Array.from(detectedFormats).join(', ')}`;
            }
            
            showToast(message, 'success');
        }

        // Detect date format from string
        function detectDateFormat(dateString) {
            if (!dateString) return null;
            
            const trimmed = dateString.toString().trim();
            
            // Check for Excel serial
            if (isNumeric(trimmed) && parseFloat(trimmed) >= 1 && parseFloat(trimmed) <= 100000) {
                return 'Excel التسلسلي';
            }
            
            // Check for DD/MM/YYYY
            if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(trimmed)) {
                return 'يوم/شهر/سنة';
            }
            
            // Check for MM/DD/YYYY
            if (/^\d{1,2}\/\d{1,2}\/\d{4}$/.test(trimmed)) {
                return 'شهر/يوم/سنة';
            }
            
            // Check for YYYY-MM-DD
            if (/^\d{4}-\d{1,2}-\d{1,2}$/.test(trimmed)) {
                return 'سنة-شهر-يوم';
            }
            
            // Check for DD-MM-YYYY
            if (/^\d{1,2}-\d{1,2}-\d{4}$/.test(trimmed)) {
                return 'يوم-شهر-سنة';
            }
            
            // Check for DD.MM.YYYY
            if (/^\d{1,2}\.\d{1,2}\.\d{4}$/.test(trimmed)) {
                return 'يوم.شهر.سنة';
            }
            
            // Check for Arabic dates - more specific patterns
            if (isArabicDate(trimmed)) {
                return 'عربي';
            }
            
            // Check for English dates like "25-Apr-2029"
            if (/^\d{1,2}-[A-Za-z]{3}-\d{4}$/.test(trimmed)) {
                return 'إنجليزي';
            }
            
            return 'غير معروف';
        }

        // Check if string is a valid Arabic date
        function isArabicDate(dateString) {
            if (!dateString) return false;
            
            const trimmed = dateString.toString().trim();
            
            // Must contain Arabic characters
            if (!/[\u0600-\u06FF]/.test(trimmed)) {
                return false;
            }
            
            // Check for common Arabic date patterns
            const arabicDatePatterns = [
                // DD Month YYYY (e.g., "15 يناير 2025")
                /^\d{1,2}\s+[^\d\s]+\s+\d{4}$/,
                // DD-Month-YYYY (e.g., "15-يناير-2025")
                /^\d{1,2}-[^\d-]+-\d{4}$/,
                // DD/Month/YYYY (e.g., "15/يناير/2025")
                /^\d{1,2}\/[^\d\/]+\/\d{4}$/,
                // Month DD, YYYY (e.g., "يناير 15، 2025")
                /^[^\d\s]+\s+\d{1,2}،\s+\d{4}$/,
                // Month DD YYYY (e.g., "يناير 15 2025")
                /^[^\d\s]+\s+\d{1,2}\s+\d{4}$/
            ];
            
            // Check if it matches any Arabic date pattern
            for (const pattern of arabicDatePatterns) {
                if (pattern.test(trimmed)) {
                    return true;
                }
            }
            
            // Check for specific Arabic month names
            const arabicMonths = [
                'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر',
                'كانون الثاني', 'شباط', 'آذار', 'نيسان', 'أيار', 'حزيران',
                'تموز', 'آب', 'أيلول', 'تشرين الأول', 'تشرين الثاني', 'كانون الأول'
            ];
            
            // Check if it contains Arabic month names
            for (const month of arabicMonths) {
                if (trimmed.includes(month)) {
                    return true;
                }
            }
            
            return false;
        }

        // Parse Excel date (same logic as backend)
        function parseExcelDate(dateString) {
            if (!dateString) return null;
            
            const trimmed = dateString.toString().trim();
            
            // Check if it's a numeric serial date
            if (isNumeric(trimmed)) {
                const serialNumber = parseFloat(trimmed);
                
                if (serialNumber >= 1 && serialNumber <= 100000) {
                    // Convert Excel serial date to actual date
                    const excelEpoch = new Date('1900-01-01');
                    excelEpoch.setDate(excelEpoch.getDate() - 2); // Excel bug correction
                    
                    const days = serialNumber - 1;
                    excelEpoch.setDate(excelEpoch.getDate() + days);
                    
                    return excelEpoch.toLocaleDateString('ar-SA');
                }
            }
            
            // Try to parse as regular date
            const date = new Date(trimmed);
            if (!isNaN(date.getTime())) {
                return date.toLocaleDateString('ar-SA');
            }
            
            return null;
        }

        // Check if string is numeric
        function isNumeric(str) {
            return !isNaN(parseFloat(str)) && isFinite(str);
        }

        // Show date format dialog
        function showDateFormatDialog() {
            const savedFormat = localStorage.getItem('defaultDateFormat') || 'excel';
            
            const dialog = document.createElement('div');
            dialog.className = 'date-format-dialog';
            dialog.innerHTML = `
                <div class="dialog-content">
                    <h3>تحديد تنسيق التواريخ</h3>
                    <p>اختر التنسيق المناسب للتواريخ في ملفك:</p>
                    <div class="format-options">
                        <label><input type="radio" name="dateFormat" value="excel" ${savedFormat === 'excel' ? 'checked' : ''}> أرقام تسلسلية Excel (مثل: 47950)</label>
                        <label><input type="radio" name="dateFormat" value="dd/mm/yyyy" ${savedFormat === 'dd/mm/yyyy' ? 'checked' : ''}> يوم/شهر/سنة (مثل: 15/01/2025)</label>
                        <label><input type="radio" name="dateFormat" value="mm/dd/yyyy" ${savedFormat === 'mm/dd/yyyy' ? 'checked' : ''}> شهر/يوم/سنة (مثل: 01/15/2025)</label>
                        <label><input type="radio" name="dateFormat" value="yyyy-mm-dd" ${savedFormat === 'yyyy-mm-dd' ? 'checked' : ''}> سنة-شهر-يوم (مثل: 2025-01-15)</label>
                        <label><input type="radio" name="dateFormat" value="arabic" ${savedFormat === 'arabic' ? 'checked' : ''}> تواريخ عربية (مثل: 15 يناير 2025)</label>
                    </div>
                    <div class="dialog-buttons">
                        <button onclick="saveAsDefaultFormat()" class="control-btn save-btn">
                            <i class="fas fa-save"></i>
                            <span>حفظ كتنسيق أساسي</span>
                        </button>
                        <button onclick="applyDateFormat()" class="control-btn">تطبيق</button>
                        <button onclick="closeDateFormatDialog()" class="control-btn clear-btn">إلغاء</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(dialog);
        }

        // Save as default format
        function saveAsDefaultFormat() {
            const selectedFormat = document.querySelector('input[name="dateFormat"]:checked');
            if (!selectedFormat) {
                showToast('يرجى اختيار تنسيق التواريخ أولاً', 'error');
                return;
            }
            
            // Save format to localStorage
            localStorage.setItem('defaultDateFormat', selectedFormat.value);
            
            // Apply format immediately
            detectAndFixDates();
            closeDateFormatDialog();
            
            showToast(`تم حفظ تنسيق ${getFormatName(selectedFormat.value)} كتنسيق أساسي`, 'success');
        }

        // Apply date format
        function applyDateFormat() {
            const selectedFormat = document.querySelector('input[name="dateFormat"]:checked');
            if (!selectedFormat) {
                showToast('يرجى اختيار تنسيق التواريخ', 'error');
                return;
            }
            
            // Apply format based on selection
            detectAndFixDates();
            closeDateFormatDialog();
        }

        // Parse Excel date with specific format
        function parseExcelDateWithFormat(dateString, format) {
            if (!dateString) return null;
            
            const trimmed = dateString.toString().trim();
            
            // Try specific format first
            let result = null;
            switch (format) {
                case 'excel':
                    result = parseExcelSerialDate(trimmed);
                    break;
                case 'dd/mm/yyyy':
                    result = parseDDMMYYYY(trimmed);
                    break;
                case 'mm/dd/yyyy':
                    result = parseMMDDYYYY(trimmed);
                    break;
                case 'yyyy-mm-dd':
                    result = parseYYYYMMDD(trimmed);
                    break;
                case 'arabic':
                    result = parseArabicDate(trimmed);
                    break;
                default:
                    result = parseExcelSerialDate(trimmed);
            }
            
            // If specific format didn't work, try all formats
            if (!result) {
                result = tryAllDateFormats(trimmed);
            }
            
            return result;
        }

        // Try all date formats as fallback
        function tryAllDateFormats(dateString) {
            if (!dateString) return null;
            
            const trimmed = dateString.toString().trim();
            
            // Skip if it's clearly not a date (too long, contains letters in wrong places)
            if (trimmed.length > 20) return null;
            
            const formats = [
                () => parseExcelSerialDate(trimmed),
                () => parseDDMMYYYY(trimmed),
                () => parseMMDDYYYY(trimmed),
                () => parseYYYYMMDD(trimmed),
                () => parseArabicDate(trimmed),
                () => parseEnglishDate(trimmed),
                () => parseFlexibleDate(trimmed),
                () => parseMixedFormatDate(trimmed),
                () => parseMonthYear(trimmed)
            ];
            
            for (const format of formats) {
                const result = format();
                if (result) {
                    return result;
                }
            }
            
            return null;
        }

        // Parse month/year format (e.g., "12/2024", "7/2019")
        function parseMonthYear(dateString) {
            const match = dateString.match(/^(\d{1,2})\/(\d{4})$/);
            if (match) {
                const month = parseInt(match[1]);
                const year = parseInt(match[2]);
                
                // Validate month
                if (month >= 1 && month <= 12) {
                    const date = new Date(year, month - 1, 1);
                    if (!isNaN(date.getTime())) {
                        return date.toLocaleDateString('ar-SA', { 
                            year: 'numeric', 
                            month: 'long' 
                        });
                    }
                }
            }
            
            return null;
        }

        // Parse flexible date format
        function parseFlexibleDate(dateString) {
            // Try common date patterns
            const patterns = [
                // DD-MM-YYYY or DD/MM/YYYY
                /^(\d{1,2})[-/](\d{1,2})[-/](\d{4})$/,
                // YYYY-MM-DD or YYYY/MM/DD
                /^(\d{4})[-/](\d{1,2})[-/](\d{1,2})$/,
                // MM-DD-YYYY or MM/DD/YYYY
                /^(\d{1,2})[-/](\d{1,2})[-/](\d{4})$/
            ];
            
            for (const pattern of patterns) {
                const match = dateString.match(pattern);
                if (match) {
                    let year, month, day;
                    
                    if (match[1].length === 4) {
                        // YYYY-MM-DD format
                        year = parseInt(match[1]);
                        month = parseInt(match[2]);
                        day = parseInt(match[3]);
                    } else {
                        // Try to determine if it's DD/MM or MM/DD
                        const first = parseInt(match[1]);
                        const second = parseInt(match[2]);
                        const year = parseInt(match[3]);
                        
                        // If first number > 12, it's likely DD/MM
                        if (first > 12) {
                            day = first;
                            month = second;
                        } else if (second > 12) {
                            month = first;
                            day = second;
                        } else {
                            // Ambiguous case, try DD/MM first
                            day = first;
                            month = second;
                        }
                    }
                    
                    const date = new Date(year, month - 1, day);
                    if (!isNaN(date.getTime())) {
                        return date.toLocaleDateString('ar-SA');
                    }
                }
            }
            
            return null;
        }

        // Parse mixed format dates
        function parseMixedFormatDate(dateString) {
            // Handle dates with mixed separators and formats
            const patterns = [
                // DD.MM.YYYY
                /^(\d{1,2})\.(\d{1,2})\.(\d{4})$/,
                // YYYY.MM.DD
                /^(\d{4})\.(\d{1,2})\.(\d{1,2})$/,
                // DD MM YYYY
                /^(\d{1,2})\s+(\d{1,2})\s+(\d{4})$/,
                // YYYY MM DD
                /^(\d{4})\s+(\d{1,2})\s+(\d{1,2})$/
            ];
            
            for (const pattern of patterns) {
                const match = dateString.match(pattern);
                if (match) {
                    let year, month, day;
                    
                    if (match[1].length === 4) {
                        year = parseInt(match[1]);
                        month = parseInt(match[2]);
                        day = parseInt(match[3]);
                    } else {
                        const first = parseInt(match[1]);
                        const second = parseInt(match[2]);
                        const third = parseInt(match[3]);
                        
                        if (first > 12) {
                            day = first;
                            month = second;
                            year = third;
                        } else if (second > 12) {
                            month = first;
                            day = second;
                            year = third;
                        } else {
                            day = first;
                            month = second;
                            year = third;
                        }
                    }
                    
                    const date = new Date(year, month - 1, day);
                    if (!isNaN(date.getTime())) {
                        return date.toLocaleDateString('ar-SA');
                    }
                }
            }
            
            return null;
        }

        // Parse Excel serial date
        function parseExcelSerialDate(dateString) {
            if (isNumeric(dateString)) {
                const serialNumber = parseFloat(dateString);
                
                // Check if it's a valid Excel serial date
                if (serialNumber >= 1 && serialNumber <= 100000) {
                    // Excel epoch is 1900-01-01, but Excel has a bug where it treats 1900 as a leap year
                    const excelEpoch = new Date('1899-12-30');
                    
                    // Add the serial number of days
                    const days = Math.floor(serialNumber);
                    excelEpoch.setDate(excelEpoch.getDate() + days);
                    
                    // Check if the result is a valid date
                    if (!isNaN(excelEpoch.getTime())) {
                        return excelEpoch.toLocaleDateString('ar-SA');
                    }
                }
            }
            return null;
        }

        // Parse DD/MM/YYYY format
        function parseDDMMYYYY(dateString) {
            const match = dateString.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (match) {
                const day = match[1];
                const month = match[2];
                const year = match[3];
                const date = new Date(year, month - 1, day);
                if (!isNaN(date.getTime())) {
                    return date.toLocaleDateString('ar-SA');
                }
            }
            return null;
        }

        // Parse MM/DD/YYYY format
        function parseMMDDYYYY(dateString) {
            const match = dateString.match(/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/);
            if (match) {
                const month = match[1];
                const day = match[2];
                const year = match[3];
                const date = new Date(year, month - 1, day);
                if (!isNaN(date.getTime())) {
                    return date.toLocaleDateString('ar-SA');
                }
            }
            return null;
        }

        // Parse YYYY-MM-DD format
        function parseYYYYMMDD(dateString) {
            const match = dateString.match(/^(\d{4})-(\d{1,2})-(\d{1,2})$/);
            if (match) {
                const year = match[1];
                const month = match[2];
                const day = match[3];
                const date = new Date(year, month - 1, day);
                if (!isNaN(date.getTime())) {
                    return date.toLocaleDateString('ar-SA');
                }
            }
            return null;
        }

        // Parse Arabic date
        function parseArabicDate(dateString) {
            const arabicMonths = {
                'يناير': 0, 'فبراير': 1, 'مارس': 2, 'أبريل': 3,
                'مايو': 4, 'يونيو': 5, 'يوليو': 6, 'أغسطس': 7,
                'سبتمبر': 8, 'أكتوبر': 9, 'نوفمبر': 10, 'ديسمبر': 11,
                'كانون الثاني': 0, 'شباط': 1, 'آذار': 2, 'نيسان': 3,
                'أيار': 4, 'حزيران': 5, 'تموز': 6, 'آب': 7,
                'أيلول': 8, 'تشرين الأول': 9, 'تشرين الثاني': 10, 'كانون الأول': 11
            };
            
            const englishMonths = {
                'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'May': 4, 'Jun': 5,
                'Jul': 6, 'Aug': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11,
                'January': 0, 'February': 1, 'March': 2, 'April': 3, 'May': 4, 'June': 5,
                'July': 6, 'August': 7, 'September': 8, 'October': 9, 'November': 10, 'December': 11
            };
            
            // Try different Arabic date patterns
            const patterns = [
                /^(\d{1,2})\s+([^0-9]+)\s+(\d{4})$/,
                /^(\d{1,2})-([^0-9]+)-(\d{4})$/,
                /^(\d{1,2})\/([^0-9]+)\/(\d{4})$/
            ];
            
            for (const pattern of patterns) {
                const match = dateString.match(pattern);
                if (match) {
                    const day = match[1];
                    const monthName = match[2].trim();
                    const year = match[3];
                    
                    // Try Arabic months first
                    if (arabicMonths[monthName] !== undefined) {
                        const date = new Date(year, arabicMonths[monthName], day);
                        if (!isNaN(date.getTime())) {
                            return date.toLocaleDateString('ar-SA');
                        }
                    }
                    
                    // Try English months
                    if (englishMonths[monthName] !== undefined) {
                        const date = new Date(year, englishMonths[monthName], day);
                        if (!isNaN(date.getTime())) {
                            return date.toLocaleDateString('ar-SA');
                        }
                    }
                }
            }
            
            return null;
        }

        // Parse English date
        function parseEnglishDate(dateString) {
            const englishMonths = {
                'Jan': 0, 'Feb': 1, 'Mar': 2, 'Apr': 3, 'May': 4, 'Jun': 5,
                'Jul': 6, 'Aug': 7, 'Sep': 8, 'Oct': 9, 'Nov': 10, 'Dec': 11,
                'January': 0, 'February': 1, 'March': 2, 'April': 3, 'May': 4, 'June': 5,
                'July': 6, 'August': 7, 'September': 8, 'October': 9, 'November': 10, 'December': 11
            };
            
            // Try different English date patterns
            const patterns = [
                /^(\d{1,2})-([A-Za-z]{3,9})-(\d{4})$/,
                /^(\d{1,2})\s+([A-Za-z]{3,9})\s+(\d{4})$/
            ];
            
            for (const pattern of patterns) {
                const match = dateString.match(pattern);
                if (match) {
                    const day = match[1];
                    const monthName = match[2].trim();
                    const year = match[3];
                    
                    if (englishMonths[monthName] !== undefined) {
                        const date = new Date(year, englishMonths[monthName], day);
                        if (!isNaN(date.getTime())) {
                            return date.toLocaleDateString('ar-SA');
                        }
                    }
                }
            }
            
            return null;
        }

        // Get format name for display
        function getFormatName(format) {
            const formatNames = {
                'excel': 'Excel التسلسلي',
                'dd/mm/yyyy': 'يوم/شهر/سنة',
                'mm/dd/yyyy': 'شهر/يوم/سنة',
                'yyyy-mm-dd': 'سنة-شهر-يوم',
                'arabic': 'عربي'
            };
            return formatNames[format] || format;
        }

        // Test date detection
        function testDateDetection() {
            if (!window.currentData || window.currentData.length < 2) {
                showToast('لا توجد بيانات لاختبارها', 'error');
                return;
            }
            
            const data = [...window.currentData];
            const results = [];
            let totalCells = 0;
            let dateCells = 0;
            let fixedCells = 0;
            
            // Process each row (skip header)
            for (let i = 1; i < data.length; i++) {
                const row = data[i];
                
                row.forEach((cell, cellIndex) => {
                    if (cell && typeof cell === 'string') {
                        totalCells++;
                        const originalValue = cell;
                        const detectedFormat = detectDateFormat(originalValue);
                        
                        if (detectedFormat && detectedFormat !== 'غير معروف') {
                            dateCells++;
                            
                            // Try to fix it
                            const fixedDate = tryAllDateFormats(originalValue);
                            if (fixedDate) {
                                fixedCells++;
                                results.push({
                                    row: i + 1,
                                    column: cellIndex + 1,
                                    original: originalValue,
                                    detected: detectedFormat,
                                    fixed: fixedDate
                                });
                            } else {
                                results.push({
                                    row: i + 1,
                                    column: cellIndex + 1,
                                    original: originalValue,
                                    detected: detectedFormat,
                                    fixed: 'فشل في الإصلاح'
                                });
                            }
                        }
                    }
                });
            }
            
            // Show results
            showDateTestResults(results, totalCells, dateCells, fixedCells);
        }

        // Show date test results
        function showDateTestResults(results, totalCells, dateCells, fixedCells) {
            const dialog = document.createElement('div');
            dialog.className = 'date-test-dialog';
            dialog.innerHTML = `
                <div class="dialog-content">
                    <h3>نتائج اختبار التواريخ</h3>
                    <div class="test-stats">
                        <div class="stat-item">
                            <span class="stat-label">إجمالي الخلايا:</span>
                            <span class="stat-value">${totalCells}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">خلايا تحتوي على تواريخ:</span>
                            <span class="stat-value">${dateCells}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">تم إصلاحها بنجاح:</span>
                            <span class="stat-value">${fixedCells}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">فشل في الإصلاح:</span>
                            <span class="stat-value">${dateCells - fixedCells}</span>
                        </div>
                    </div>
                    <div class="test-results">
                        <h4>التفاصيل:</h4>
                        <div class="results-list">
                            ${results.map(result => `
                                <div class="result-item">
                                    <div class="result-info">
                                        <span class="location">الصف ${result.row}, العمود ${result.column}</span>
                                        <span class="format">التنسيق: ${result.detected}</span>
                                    </div>
                                    <div class="result-values">
                                        <span class="original">الأصلي: ${result.original}</span>
                                        <span class="fixed">المُصلح: ${result.fixed}</span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="dialog-buttons">
                        <button onclick="closeDateTestDialog()" class="control-btn">إغلاق</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(dialog);
        }

        // Close date test dialog
        function closeDateTestDialog() {
            const dialog = document.querySelector('.date-test-dialog');
            if (dialog) {
                dialog.remove();
            }
        }

        // Filter real dates only
        function filterRealDates() {
            if (!window.currentData || window.currentData.length < 2) {
                showToast('لا توجد بيانات لتصفيتها', 'error');
                return;
            }
            
            const data = [...window.currentData];
            const results = [];
            let totalCells = 0;
            let realDateCells = 0;
            let fixedCells = 0;
            
            // Process each row (skip header)
            for (let i = 1; i < data.length; i++) {
                const row = data[i];
                
                row.forEach((cell, cellIndex) => {
                    if (cell && typeof cell === 'string') {
                        totalCells++;
                        const originalValue = cell;
                        
                        // Check if it's a real date (not just Arabic text)
                        if (isRealDate(originalValue)) {
                            realDateCells++;
                            
                            // Try to fix it
                            const fixedDate = tryAllDateFormats(originalValue);
                            if (fixedDate) {
                                fixedCells++;
                                results.push({
                                    row: i + 1,
                                    column: cellIndex + 1,
                                    original: originalValue,
                                    detected: detectDateFormat(originalValue),
                                    fixed: fixedDate
                                });
                            } else {
                                results.push({
                                    row: i + 1,
                                    column: cellIndex + 1,
                                    original: originalValue,
                                    detected: detectDateFormat(originalValue),
                                    fixed: 'فشل في الإصلاح'
                                });
                            }
                        }
                    }
                });
            }
            
            // Show filtered results
            showFilteredDateResults(results, totalCells, realDateCells, fixedCells);
        }

        // Check if string is a real date (not just Arabic text)
        function isRealDate(dateString) {
            if (!dateString) return false;
            
            const trimmed = dateString.toString().trim();
            
            // Check for numeric patterns first
            if (isNumeric(trimmed) && parseFloat(trimmed) >= 1 && parseFloat(trimmed) <= 100000) {
                return true;
            }
            
            // Check for date patterns with numbers
            const datePatterns = [
                /^\d{1,2}\/\d{1,2}\/\d{4}$/,  // DD/MM/YYYY
                /^\d{1,2}-\d{1,2}-\d{4}$/,   // DD-MM-YYYY
                /^\d{1,2}\.\d{1,2}\.\d{4}$/, // DD.MM.YYYY
                /^\d{4}-\d{1,2}-\d{1,2}$/,   // YYYY-MM-DD
                /^\d{1,2}-[A-Za-z]{3}-\d{4}$/, // DD-Mon-YYYY
                /^\d{1,2}\s+[A-Za-z]{3,9}\s+\d{4}$/ // DD Month YYYY
            ];
            
            for (const pattern of datePatterns) {
                if (pattern.test(trimmed)) {
                    return true;
                }
            }
            
            // Check for Arabic dates with specific patterns
            if (isArabicDate(trimmed)) {
                return true;
            }
            
            return false;
        }

        // Show filtered date results
        function showFilteredDateResults(results, totalCells, realDateCells, fixedCells) {
            const dialog = document.createElement('div');
            dialog.className = 'date-test-dialog';
            dialog.innerHTML = `
                <div class="dialog-content">
                    <h3>نتائج تصفية التواريخ الحقيقية</h3>
                    <div class="test-stats">
                        <div class="stat-item">
                            <span class="stat-label">إجمالي الخلايا:</span>
                            <span class="stat-value">${totalCells}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">تواريخ حقيقية:</span>
                            <span class="stat-value">${realDateCells}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">تم إصلاحها بنجاح:</span>
                            <span class="stat-value">${fixedCells}</span>
                        </div>
                        <div class="stat-item">
                            <span class="stat-label">فشل في الإصلاح:</span>
                            <span class="stat-value">${realDateCells - fixedCells}</span>
                        </div>
                    </div>
                    <div class="test-results">
                        <h4>التواريخ الحقيقية فقط:</h4>
                        <div class="results-list">
                            ${results.map(result => `
                                <div class="result-item">
                                    <div class="result-info">
                                        <span class="location">الصف ${result.row}, العمود ${result.column}</span>
                                        <span class="format">التنسيق: ${result.detected}</span>
                                    </div>
                                    <div class="result-values">
                                        <span class="original">الأصلي: ${result.original}</span>
                                        <span class="fixed">المُصلح: ${result.fixed}</span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="dialog-buttons">
                        <button onclick="closeDateTestDialog()" class="control-btn">إغلاق</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(dialog);
        }

        // Show date changes made during upload
        function showDateChanges() {
            if (!window.originalData || !window.currentData) {
                showToast('لا توجد بيانات لمقارنتها', 'error');
                return;
            }
            
            const changes = [];
            let totalChanges = 0;
            
            // Compare original and current data
            for (let i = 1; i < window.originalData.length; i++) {
                const originalRow = window.originalData[i];
                const currentRow = window.currentData[i];
                
                for (let j = 0; j < originalRow.length; j++) {
                    const originalValue = originalRow[j];
                    const currentValue = currentRow[j];
                    
                    if (originalValue !== currentValue && 
                        originalValue && 
                        currentValue && 
                        typeof originalValue === 'string' && 
                        typeof currentValue === 'string') {
                        
                        totalChanges++;
                        changes.push({
                            row: i + 1,
                            column: j + 1,
                            original: originalValue,
                            current: currentValue,
                            detected: detectDateFormat(originalValue)
                        });
                    }
                }
            }
            
            if (changes.length === 0) {
                showToast('لم يتم إجراء أي تغييرات على التواريخ', 'info');
                return;
            }
            
            // Show changes dialog
            showChangesDialog(changes, totalChanges);
        }

        // Show changes dialog
        function showChangesDialog(changes, totalChanges) {
            const dialog = document.createElement('div');
            dialog.className = 'date-test-dialog';
            dialog.innerHTML = `
                <div class="dialog-content">
                    <h3>التغييرات التي تمت على التواريخ</h3>
                    <div class="test-stats">
                        <div class="stat-item">
                            <span class="stat-label">إجمالي التغييرات:</span>
                            <span class="stat-value">${totalChanges}</span>
                        </div>
                    </div>
                    <div class="test-results">
                        <h4>التفاصيل:</h4>
                        <div class="results-list">
                            ${changes.map(change => `
                                <div class="result-item">
                                    <div class="result-info">
                                        <span class="location">الصف ${change.row}, العمود ${change.column}</span>
                                        <span class="format">التنسيق: ${change.detected}</span>
                                    </div>
                                    <div class="result-values">
                                        <span class="original">الأصلي: ${change.original}</span>
                                        <span class="fixed">المُصلح: ${change.current}</span>
                                    </div>
                                </div>
                            `).join('')}
                        </div>
                    </div>
                    <div class="dialog-buttons">
                        <button onclick="closeDateTestDialog()" class="control-btn">إغلاق</button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(dialog);
        }

        // Close date format dialog
        function closeDateFormatDialog() {
            const dialog = document.querySelector('.date-format-dialog');
            if (dialog) {
                dialog.remove();
            }
        }

        // Clear filters
        function clearFilters() {
            document.getElementById('dataSearch').value = '';
            window.currentData = [...window.originalData];
            updateTableDisplay();
            
            // Reset sort buttons
            document.querySelectorAll('.sort-btn').forEach(btn => {
                btn.classList.remove('active');
                const icon = btn.querySelector('i');
                icon.className = 'fas fa-sort';
            });
            
            showToast('تم مسح جميع الفلاتر', 'success');
        }

        // Column Mapping - NEW VERSION
        function showColumnMapping(headers) {
            console.log('🚀 showColumnMapping called with headers:', headers);
            const container = document.getElementById('mappingContainer');
            const grid = document.getElementById('mappingGrid');
            
            // Clear grid completely
            grid.innerHTML = '';
            console.log('Grid cleared. Previous children count:', grid.children.length);
            
            // Define all 27 fields
            const fields = [
                { key: 'name', label: 'Name *', required: true },
                { key: 'name_arabic', label: 'Name in Arabic', required: false },
                { key: 'employee_id', label: 'Employee ID', required: false },
                { key: 'EmployeeCode', label: 'Employee Code (.emp code)', required: false },
                { key: 'profile_picture', label: 'Profile Picture', required: false },
                { key: 'email', label: 'Email *', required: true },
                { key: 'password', label: 'Password *', required: true },
                { key: 'password_confirmation', label: 'Confirm Password *', required: true },
                { key: 'department', label: 'Department *', required: true },
                { key: 'role', label: 'Role *', required: true },
                { key: 'work_phone', label: 'Work Phone', required: false },
                { key: 'mobile_phone', label: 'Mobile Phone', required: false },
                { key: 'work_email', label: 'Work Email', required: false },
                { key: 'avaya_extension', label: 'AVAYA Extension', required: false },
                { key: 'teams_id', label: 'Microsoft Teams ID', required: false },
                { key: 'job_title', label: 'Job Title', required: false },
                { key: 'company', label: 'Company', required: false },
                { key: 'manager', label: 'Manager', required: false },
                { key: 'office_address', label: 'Office Address', required: false },
                { key: 'linkedin_url', label: 'LinkedIn URL', required: false },
                { key: 'website_url', label: 'Website URL', required: false },
                { key: 'birthday', label: 'Birthday', required: false },
                { key: 'birth_date', label: 'Birth Date', required: false },
                { key: 'nationality', label: 'Nationality', required: false },
                { key: 'address', label: 'Address', required: false },
                { key: 'city', label: 'City', required: false },
                { key: 'country', label: 'Country', required: false },
                { key: 'bio', label: 'Bio', required: false },
                { key: 'notes', label: 'Notes', required: false }
            ];
            
            console.log('📋 Creating', fields.length, 'fields for mapping...');
            
            // Create HTML for all fields at once
            let fieldsHTML = '';
            fields.forEach((field, index) => {
                console.log(`Creating field ${index + 1}:`, field.key, field.label);
                
                fieldsHTML += `
                    <div class="mapping-item ${field.required ? 'required' : ''}" style="display: flex !important; visibility: visible !important; opacity: 1 !important;">
                        <span class="mapping-label" style="flex: 1; margin-right: 10px;">${field.label}</span>
                        <select class="mapping-select" id="mapping_${field.key}" style="flex: 1; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                            <option value="">اختر العمود</option>
                            ${headers.map((header, idx) => `<option value="${idx}">${header || `العمود ${idx + 1}`}</option>`).join('')}
                        </select>
                    </div>
                `;
            });
            
            // Insert all fields at once
            grid.innerHTML = fieldsHTML;
            
            // Add event listeners to all selects
            fields.forEach(field => {
                const select = document.getElementById(`mapping_${field.key}`);
                if (select) {
                    select.addEventListener('change', function() {
                        columnMapping[field.key] = parseInt(this.value) || null;
                        console.log(`Field ${field.key} mapped to column:`, this.value);
                    });
                    
                    // Auto-mapping
                    const autoMapping = autoMapColumn(field.key, headers);
                    if (autoMapping !== -1) {
                        select.value = autoMapping;
                        columnMapping[field.key] = autoMapping;
                    }
                }
            });
            
            // Show containers
            container.style.display = 'block';
            document.getElementById('defaultValues').style.display = 'block';
            
            // Force visibility
            grid.style.display = 'grid';
            grid.style.visibility = 'visible';
            grid.style.opacity = '1';
            
            // Verify
            const mappingItems = grid.querySelectorAll('.mapping-item');
            console.log('✅ Created', mappingItems.length, 'fields out of', fields.length, 'expected');
            console.log('Grid children count:', grid.children.length);
            
            if (mappingItems.length === fields.length) {
                console.log('🎉 SUCCESS: All 27 fields are visible!');
            } else {
                console.error('❌ ERROR: Field count mismatch!');
            }
        }

        // Auto-mapping logic
        function autoMapColumn(fieldKey, headers) {
            const mappingRules = {
                'name': ['اسم', 'الاسم', 'name', 'full_name', 'employee_name'],
                'employee_id': ['employee_id', 'employee_code', 'employee_number', 'كود الموظف', 'رقم الموظف', 'id', 'معرف الموظف'],
                'EmployeeCode': ['emp code', 'employee_code', 'emp_code', 'empcode', '.emp code', 'كود الموظف', 'كود العمل'],
                'name_arabic': ['اسم عربي', 'arabic name', 'الاسم العربي'],
                'profile_picture': ['صورة', 'picture', 'photo', 'avatar', 'صورة شخصية'],
                'email': ['بريد', 'إيميل', 'email', 'e_mail', 'mail', 'work email', 'ايميل العمل'],
                'password': ['كلمة مرور', 'password', 'كلمة السر'],
                'password_confirmation': ['تأكيد كلمة المرور', 'confirm password', 'تأكيد كلمة السر'],
                'department': ['قسم', 'إدارة', 'department', 'division', 'organization', 'القسم'],
                'role': ['دور', 'role', 'صلاحية', 'permission'],
                'work_phone': ['هاتف عمل', 'work phone', 'تلفون العمل'],
                'mobile_phone': ['هاتف محمول', 'mobile', 'mobile phone', 'جوال'],
                'work_email': ['بريد عمل', 'work email', 'ايميل العمل'],
                'avaya_extension': ['avaya', 'extension', 'امتداد', 'رقم داخلي'],
                'teams_id': ['teams', 'microsoft teams', 'teams id'],
                'job_title': ['عنوان وظيفي', 'job title', 'المسمى الوظيفي'],
                'company': ['شركة', 'company', 'مؤسسة'],
                'manager': ['مدير', 'manager', 'رئيس', 'supervisor'],
                'office_address': ['عنوان المكتب', 'office address', 'عنوان العمل'],
                'linkedin_url': ['linkedin', 'لينكد إن', 'linkedin url'],
                'website_url': ['موقع', 'website', 'url', 'الموقع الشخصي'],
                'birthday': ['تاريخ ميلاد', 'birthday', 'birth date', 'تاريخ الميلاد'],
                'birth_date': ['تاريخ الميلاد', 'birth date', 'birthday', 'تاريخ ميلاد'],
                'nationality': ['جنسية', 'nationality', 'citizenship', 'الجنسية'],
                'address': ['عنوان', 'address', 'location', 'العنوان'],
                'city': ['مدينة', 'city', 'town', 'المدينة'],
                'country': ['دولة', 'country', 'nation', 'الدولة'],
                'bio': ['نبذة', 'bio', 'biography', 'نبذة شخصية'],
                'notes': ['ملاحظات', 'notes', 'comments', 'remarks'],
                'hiring_date': ['تاريخ', 'تعيين', 'hiring', 'start_date', 'تاريخ التعيين']
            };
            
            const rules = mappingRules[fieldKey] || [];
            
            for (let i = 0; i < headers.length; i++) {
                const header = (headers[i] || '').toLowerCase();
                if (rules.some(rule => header.includes(rule))) {
                    return i;
                }
            }
            
            return -1;
        }

        // Data Processing
        function processUploadedData() {
            if (!uploadedData || !validateMapping()) {
                showToast('يرجى التأكد من ربط جميع الحقول المطلوبة', 'error');
                return;
            }
            
            showToast('تم معالجة البيانات بنجاح، جاهز للحفظ', 'success');
            document.getElementById('saveData').style.display = 'inline-flex';
        }

        function validateMapping() {
            const requiredFields = ['name', 'email', 'password', 'password_confirmation', 'department', 'role'];
            return requiredFields.every(field => columnMapping[field] !== null && columnMapping[field] !== undefined);
        }

        // Save Data
        async function saveEmployeeData() {
            if (!validateMapping()) {
                showToast('يرجى التأكد من ربط جميع الحقول المطلوبة', 'error');
                return;
            }
            
            showLoading();
            
            try {
                const processedData = processEmployeeData();
                const response = await fetch('/users/batch-create', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        employees: processedData,
                        defaultValues: getDefaultValues()
                    })
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showToast(`تم حفظ ${result.saved} موظف بنجاح`, 'success');
                    resetForm();
                } else {
                    showToast(result.message || 'حدث خطأ أثناء الحفظ', 'error');
                }
                
            } catch (error) {
                console.error('خطأ في الحفظ:', error);
                showToast('خطأ في الاتصال بالخادم', 'error');
            } finally {
                hideLoading();
            }
        }

        function processEmployeeData() {
            const employees = [];
            const dataRows = uploadedData.slice(1); // Skip header row
            
            dataRows.forEach((row, index) => {
                const employee = {};
                
                Object.keys(columnMapping).forEach(field => {
                    const columnIndex = columnMapping[field];
                    if (columnIndex !== null && row[columnIndex] !== undefined) {
                        employee[field] = row[columnIndex];
                    }
                });
                
                // Add row number for error tracking
                employee._row_number = index + 2; // +2 because we skipped header and arrays are 0-indexed
                
                employees.push(employee);
            });
            
            return employees;
        }

        function getDefaultValues() {
            return {
                department_id: document.getElementById('defaultDepartment').value,
                position: document.getElementById('defaultPosition').value,
                phone: document.getElementById('defaultPhone').value
            };
        }

        // Utility Functions
        function downloadExcelTemplate() {
            const templateData = [
                ['الاسم', 'البريد الإلكتروني', 'رقم الهاتف', 'المنصب', 'القسم', 'تاريخ التعيين', 'العنوان', 'ملاحظات'],
                ['أحمد محمد', 'ahmed@example.com', '966501234567', 'مطور ويب', 'تقنية المعلومات', '2025-01-01', 'الرياض، المملكة العربية السعودية', 'موظف جديد'],
                ['فاطمة علي', 'fatima@example.com', '966501234568', 'مصممة جرافيك', 'التسويق', '2025-01-02', 'جدة، المملكة العربية السعودية', 'خبرة 3 سنوات']
            ];
            
            const worksheet = XLSX.utils.aoa_to_sheet(templateData);
            const workbook = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(workbook, worksheet, 'الموظفين');
            
            XLSX.writeFile(workbook, 'قالب_الموظفين.xlsx');
            showToast('تم تحميل القالب بنجاح', 'success');
        }

        async function loadDepartments() {
            try {
                const response = await fetch('/api/departments');
                const departments = await response.json();
                
                const select = document.getElementById('defaultDepartment');
                departments.forEach(dept => {
                    const option = document.createElement('option');
                    option.value = dept.id;
                    option.textContent = dept.name_ar || dept.name;
                    select.appendChild(option);
                });
            } catch (error) {
                console.error('خطأ في تحميل الأقسام:', error);
            }
        }

        function resetForm() {
            uploadedData = null;
            columnMapping = {};
            
            document.getElementById('dataPreview').style.display = 'none';
            document.getElementById('mappingContainer').style.display = 'none';
            document.getElementById('defaultValues').style.display = 'none';
            document.getElementById('processData').style.display = 'none';
            document.getElementById('saveData').style.display = 'none';
            document.getElementById('fileInput').value = '';
        }

        function showProgress() {
            document.getElementById('progressContainer').style.display = 'block';
            animateProgress(0, 100, 2000);
        }

        function hideProgress() {
            document.getElementById('progressContainer').style.display = 'none';
        }

        function animateProgress(start, end, duration) {
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            const startTime = performance.now();
            
            function updateProgress(currentTime) {
                const elapsed = currentTime - startTime;
                const progress = Math.min(elapsed / duration, 1);
                const currentValue = start + (end - start) * progress;
                
                progressFill.style.width = currentValue + '%';
                
                if (progress < 1) {
                    requestAnimationFrame(updateProgress);
                }
            }
            
            requestAnimationFrame(updateProgress);
        }

        function showLoading() {
            document.getElementById('loadingSpinner').style.display = 'block';
        }

        function hideLoading() {
            document.getElementById('loadingSpinner').style.display = 'none';
        }

        function showToast(message, type = 'success') {
            const toast = document.createElement('div');
            toast.className = `toast ${type}`;
            toast.textContent = message;
            
            document.getElementById('toastContainer').appendChild(toast);
            
            setTimeout(() => toast.classList.add('show'), 100);
            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>
