class ImageCropper {
    constructor(containerId, options = {}) {
        this.container = document.getElementById(containerId);
        this.options = {
            aspectRatio: 1,
            viewMode: 1,
            dragMode: 'move',
            autoCropArea: 0.8,
            restore: false,
            guides: true,
            center: true,
            highlight: true,
            cropBoxMovable: true,
            cropBoxResizable: true,
            toggleDragModeOnDblclick: false,
            ...options
        };
        this.cropper = null;
        this.init();
    }

    init() {
        if (!this.container) {
            console.error('Container not found');
            return;
        }

        // إنشاء عناصر الواجهة
        this.createUI();
        
        // إعداد Cropper.js
        this.setupCropper();
    }

    createUI() {
        const html = `
            <div class="image-cropper-container">
                <div class="cropper-tools">
                    <h5>أدوات التعديل</h5>
                    <div class="tool-buttons">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="zoomIn">
                            <i class="fas fa-search-plus"></i> تكبير
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="zoomOut">
                            <i class="fas fa-search-minus"></i> تصغير
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="rotateLeft">
                            <i class="fas fa-undo"></i> دوران يسار
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="rotateRight">
                            <i class="fas fa-redo"></i> دوران يمين
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="flipHorizontal">
                            <i class="fas fa-arrows-alt-h"></i> قلب أفقياً
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="flipVertical">
                            <i class="fas fa-arrows-alt-v"></i> قلب عمودياً
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="reset">
                            <i class="fas fa-sync"></i> إعادة تعيين
                        </button>
                    </div>
                    
                    <div class="aspect-ratio-section mt-3">
                        <label for="aspectRatio">نسبة العرض إلى الارتفاع:</label>
                        <select class="form-select" id="aspectRatio">
                            <option value="1">1:1 (مربع)</option>
                            <option value="4/3">4:3</option>
                            <option value="16/9">16:9</option>
                            <option value="3/4">3:4</option>
                            <option value="9/16">9:16</option>
                            <option value="NaN">حر</option>
                        </select>
                    </div>
                    
                    <div class="face-detection-section mt-3">
                        <button type="button" class="btn btn-sm btn-success" id="detectFace">
                            <i class="fas fa-user-circle"></i> تحديد الوجه تلقائياً
                        </button>
                        <button type="button" class="btn btn-sm btn-info" id="centerFace">
                            <i class="fas fa-crosshairs"></i> توسيط الوجه
                        </button>
                    </div>
                </div>
                
                <div class="cropper-wrapper">
                    <img id="cropperImage" style="max-width: 100%;">
                </div>
                
                <div class="preview-section">
                    <h6>معاينة</h6>
                    <div class="preview-container">
                        <img id="previewImage" style="width: 150px; height: 150px; border: 2px solid #ddd; border-radius: 8px;">
                    </div>
                </div>
            </div>
        `;
        
        this.container.innerHTML = html;
    }

    setupCropper() {
        const image = document.getElementById('cropperImage');
        if (!image) return;

        // إعداد Cropper.js
        this.cropper = new Cropper(image, this.options);
        
        // ربط الأحداث
        this.bindEvents();
        
        // تحديث المعاينة
        this.updatePreview();
    }

    bindEvents() {
        // أزرار التحكم
        document.getElementById('zoomIn')?.addEventListener('click', () => {
            this.cropper.zoom(0.1);
        });

        document.getElementById('zoomOut')?.addEventListener('click', () => {
            this.cropper.zoom(-0.1);
        });

        document.getElementById('rotateLeft')?.addEventListener('click', () => {
            this.cropper.rotate(-90);
        });

        document.getElementById('rotateRight')?.addEventListener('click', () => {
            this.cropper.rotate(90);
        });

        document.getElementById('flipHorizontal')?.addEventListener('click', () => {
            this.cropper.scaleX(-this.cropper.getImageData().scaleX);
        });

        document.getElementById('flipVertical')?.addEventListener('click', () => {
            this.cropper.scaleY(-this.cropper.getImageData().scaleY);
        });

        document.getElementById('reset')?.addEventListener('click', () => {
            this.cropper.reset();
        });

        // تغيير نسبة العرض إلى الارتفاع
        document.getElementById('aspectRatio')?.addEventListener('change', (e) => {
            const ratio = e.target.value === 'NaN' ? NaN : parseFloat(e.target.value);
            this.cropper.setAspectRatio(ratio);
        });

        // تحديد الوجه تلقائياً
        document.getElementById('detectFace')?.addEventListener('click', () => {
            this.detectFace();
        });

        // توسيط الوجه
        document.getElementById('centerFace')?.addEventListener('click', () => {
            this.centerFace();
        });

        // تحديث المعاينة عند تغيير المحاص
        setTimeout(() => {
            if (this.cropper && typeof this.cropper.on === 'function') {
                this.cropper.on('crop', () => {
                    this.updatePreview();
                });
            }
        }, 100);
    }

    // تحديد الوجه تلقائياً (محاكاة)
    detectFace() {
        const canvas = this.cropper.getCroppedCanvas();
        const imageData = this.cropper.getImageData();
        
        // محاكاة تحديد الوجه - في التطبيق الحقيقي يمكن استخدام مكتبة مثل face-api.js
        const faceArea = this.estimateFaceArea(imageData);
        
        if (faceArea) {
            // تعيين منطقة المحاص لتشمل الوجه
            this.cropper.setCropBoxData({
                left: faceArea.x,
                top: faceArea.y,
                width: faceArea.width,
                height: faceArea.height
            });
        }
    }

    // تقدير منطقة الوجه (محاكاة بسيطة)
    estimateFaceArea(imageData) {
        const { width, height } = imageData;
        
        // تقدير بسيط - الوجه عادة في الثلث العلوي من الصورة
        const faceWidth = Math.min(width, height) * 0.6;
        const faceHeight = faceWidth;
        const faceX = (width - faceWidth) / 2;
        const faceY = height * 0.1; // 10% من الأعلى
        
        return {
            x: faceX,
            y: faceY,
            width: faceWidth,
            height: faceHeight
        };
    }

    // توسيط الوجه
    centerFace() {
        const imageData = this.cropper.getImageData();
        const cropBoxData = this.cropper.getCropBoxData();
        
        // توسيط منطقة المحاص
        const centerX = (imageData.width - cropBoxData.width) / 2;
        const centerY = (imageData.height - cropBoxData.height) / 2;
        
        this.cropper.setCropBoxData({
            left: centerX,
            top: centerY,
            width: cropBoxData.width,
            height: cropBoxData.height
        });
    }

    // تحديث المعاينة
    updatePreview() {
        const canvas = this.cropper.getCroppedCanvas({
            width: 150,
            height: 150
        });
        
        if (canvas) {
            const previewImage = document.getElementById('previewImage');
            previewImage.src = canvas.toDataURL();
        }
    }

    // الحصول على الصورة المحاصة
    getCroppedImage(options = {}) {
        return this.cropper.getCroppedCanvas(options);
    }

    // الحصول على بيانات المحاص
    getCropData() {
        return this.cropper.getData();
    }

    // تعيين صورة جديدة
    setImage(src) {
        this.cropper.replace(src);
    }

    // تدمير المحرر
    destroy() {
        if (this.cropper) {
            this.cropper.destroy();
        }
    }
}

// تصدير الكلاس للاستخدام العام
window.ImageCropper = ImageCropper;