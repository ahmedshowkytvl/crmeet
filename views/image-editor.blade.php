<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>محرر الصور - تحديد الوجه</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.css" rel="stylesheet">
    <style>
        .image-cropper-container {
            display: flex;
            gap: 20px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin: 20px 0;
        }

        .cropper-tools {
            flex: 0 0 300px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .cropper-wrapper {
            flex: 1;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .preview-section {
            flex: 0 0 200px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .tool-buttons {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tool-buttons .btn {
            font-size: 12px;
            padding: 8px 12px;
        }

        .aspect-ratio-section,
        .face-detection-section {
            margin-bottom: 20px;
        }

        .aspect-ratio-section label {
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
        }

        .face-detection-section .btn {
            width: 100%;
            margin-bottom: 10px;
        }

        .preview-container {
            text-align: center;
        }

        .cropper-container {
            max-width: 100%;
            max-height: 500px;
        }

        .upload-section {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .upload-area {
            border: 2px dashed #dee2e6;
            border-radius: 8px;
            padding: 40px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-area:hover {
            border-color: #007bff;
            background-color: #f8f9ff;
        }

        .upload-area.dragover {
            border-color: #007bff;
            background-color: #e3f2fd;
        }

        .action-buttons {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            text-align: center;
        }

        .action-buttons .btn {
            margin: 0 10px;
        }

        @media (max-width: 768px) {
            .image-cropper-container {
                flex-direction: column;
            }
            
            .cropper-tools,
            .preview-section {
                flex: none;
            }
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <h2 class="text-center mb-4">
                    <i class="fas fa-image me-2"></i>
                    محرر الصور - تحديد الوجه
                </h2>
            </div>
        </div>

        <!-- منطقة رفع الصور -->
        <div class="upload-section">
            <div class="upload-area" id="uploadArea">
                <i class="fas fa-cloud-upload-alt fa-3x text-muted mb-3"></i>
                <h5>اسحب الصورة هنا أو اضغط للرفع</h5>
                <p class="text-muted">يدعم: JPG, PNG, GIF (حد أقصى 5MB)</p>
                <input type="file" id="fileInput" accept="image/*" style="display: none;">
            </div>
        </div>

        <!-- محرر الصور -->
        <div id="imageCropperContainer" style="display: none;">
            <!-- سيتم إدراج محرر الصور هنا -->
        </div>

        <!-- أزرار الإجراءات -->
        <div class="action-buttons" id="actionButtons" style="display: none;">
            <button type="button" class="btn btn-success" id="saveImage">
                <i class="fas fa-save me-2"></i>حفظ الصورة
            </button>
            <button type="button" class="btn btn-secondary" id="downloadImage">
                <i class="fas fa-download me-2"></i>تحميل الصورة
            </button>
            <button type="button" class="btn btn-warning" id="resetEditor">
                <i class="fas fa-undo me-2"></i>إعادة تعيين
            </button>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.5.12/cropper.min.js"></script>
    <script src="{{ asset('js/image-cropper.js') }}"></script>
    
    <script>
        let imageCropper = null;
        let currentImageFile = null;

        // إعداد رفع الملفات
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const imageCropperContainer = document.getElementById('imageCropperContainer');
        const actionButtons = document.getElementById('actionButtons');

        // رفع الملفات
        uploadArea.addEventListener('click', () => fileInput.click());
        uploadArea.addEventListener('dragover', handleDragOver);
        uploadArea.addEventListener('dragleave', handleDragLeave);
        uploadArea.addEventListener('drop', handleDrop);

        function handleDragOver(e) {
            e.preventDefault();
            uploadArea.classList.add('dragover');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
        }

        function handleDrop(e) {
            e.preventDefault();
            uploadArea.classList.remove('dragover');
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                handleFile(files[0]);
            }
        }


        function handleFile(file) {
            // التحقق من نوع الملف
            if (!file.type.startsWith('image/')) {
                alert('يرجى اختيار ملف صورة صالح');
                return;
            }

            // التحقق من حجم الملف
            if (file.size > 5 * 1024 * 1024) {
                alert('حجم الملف كبير جداً. الحد الأقصى 5MB');
                return;
            }

            currentImageFile = file;
            const reader = new FileReader();
            reader.onload = function(e) {
                loadImage(e.target.result);
            };
            reader.readAsDataURL(file);
        }

        // إضافة معالج للـ file input
        fileInput.addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                handleFile(e.target.files[0]);
            }
        });

        function loadImage(imageSrc) {
            // إخفاء منطقة الرفع وإظهار المحرر
            uploadArea.style.display = 'none';
            imageCropperContainer.style.display = 'block';
            actionButtons.style.display = 'block';

            // إنشاء محرر الصور
            if (imageCropper) {
                imageCropper.destroy();
            }

            // إضافة الصورة إلى المحرر
            const cropperHtml = `
                <div class="cropper-wrapper">
                    <img id="cropperImage" src="${imageSrc}" style="max-width: 100%;">
                </div>
            `;
            
            imageCropperContainer.innerHTML = cropperHtml;
            
            // إنشاء محرر الصور
            imageCropper = new ImageCropper('imageCropperContainer', {
                aspectRatio: 1,
                viewMode: 1,
                dragMode: 'move',
                autoCropArea: 0.8
            });

            // ربط أحداث الأزرار
            bindActionButtons();
        }

        function bindActionButtons() {
            document.getElementById('saveImage').addEventListener('click', saveImage);
            document.getElementById('downloadImage').addEventListener('click', downloadImage);
            document.getElementById('resetEditor').addEventListener('click', resetEditor);
        }

        function saveImage() {
            if (!imageCropper) return;

            const canvas = imageCropper.getCroppedImage({
                width: 300,
                height: 300,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            if (canvas) {
                // تحويل Canvas إلى Blob
                canvas.toBlob(function(blob) {
                    // إنشاء FormData لإرسال الصورة
                    const formData = new FormData();
                    formData.append('image', blob, 'cropped_image.jpg');
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

                    // إرسال الصورة (يمكن تخصيص هذا حسب احتياجاتك)
                    fetch('/upload-cropped-image', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('تم حفظ الصورة بنجاح!');
                            console.log('صورة محفوظة:', data.imagePath);
                        } else {
                            alert('حدث خطأ في حفظ الصورة');
                        }
                    })
                    .catch(error => {
                        console.error('خطأ:', error);
                        alert('حدث خطأ في حفظ الصورة');
                    });
                }, 'image/jpeg', 0.9);
            }
        }

        function downloadImage() {
            if (!imageCropper) return;

            const canvas = imageCropper.getCroppedImage({
                width: 300,
                height: 300,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high'
            });

            if (canvas) {
                const link = document.createElement('a');
                link.download = 'cropped_image.jpg';
                link.href = canvas.toDataURL('image/jpeg', 0.9);
                link.click();
            }
        }

        function resetEditor() {
            if (imageCropper) {
                imageCropper.destroy();
                imageCropper = null;
            }
            
            imageCropperContainer.style.display = 'none';
            actionButtons.style.display = 'none';
            uploadArea.style.display = 'block';
            fileInput.value = '';
            currentImageFile = null;
        }
    </script>
</body>
</html>
