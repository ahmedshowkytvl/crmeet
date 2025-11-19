// Batch Create Advanced JavaScript

class BatchCreateAdvanced {
    constructor() {
        this.uploadedData = null;
        this.columnMapping = {};
        this.isDarkTheme = localStorage.getItem('theme') === 'dark';
        this.processingQueue = [];
        this.batchSize = 10; // معالجة 10 موظفين في كل مرة
        
        this.init();
    }

    init() {
        this.initializeTheme();
        this.initializeEventListeners();
        this.loadDepartments();
        this.setupServiceWorker();
        this.initializeDragAndDrop();
        this.setupKeyboardShortcuts();
    }

    // Theme Management
    toggleTheme() {
        this.isDarkTheme = !this.isDarkTheme;
        document.body.classList.toggle('dark-theme', this.isDarkTheme);
        localStorage.setItem('theme', this.isDarkTheme ? 'dark' : 'light');
        
        const icon = document.getElementById('theme-icon');
        icon.className = this.isDarkTheme ? 'fas fa-sun' : 'fas fa-moon';
        
        // إضافة تأثير انتقال سلس
        document.body.style.transition = 'all 0.5s ease';
        setTimeout(() => {
            document.body.style.transition = '';
        }, 500);
    }

    initializeTheme() {
        if (this.isDarkTheme) {
            document.body.classList.add('dark-theme');
            document.getElementById('theme-icon').className = 'fas fa-sun';
        }
    }

    // Event Listeners
    initializeEventListeners() {
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');
        const downloadTemplate = document.getElementById('downloadTemplate');
        const processData = document.getElementById('processData');
        const saveData = document.getElementById('saveData');

        // File Upload Events
        uploadArea.addEventListener('click', () => fileInput.click());
        uploadArea.addEventListener('dragover', this.handleDragOver.bind(this));
        uploadArea.addEventListener('dragleave', this.handleDragLeave.bind(this));
        uploadArea.addEventListener('drop', this.handleDrop.bind(this));
        fileInput.addEventListener('change', this.handleFileSelect.bind(this));

        // Button Events
        downloadTemplate.addEventListener('click', this.downloadExcelTemplate.bind(this));
        processData.addEventListener('click', this.processUploadedData.bind(this));
        saveData.addEventListener('click', this.saveEmployeeData.bind(this));

        // Auto-save mapping preferences
        document.addEventListener('change', this.saveMappingPreferences.bind(this));
        
        // Load saved preferences
        this.loadMappingPreferences();
    }

    // Advanced Drag and Drop
    initializeDragAndDrop() {
        const uploadArea = document.getElementById('uploadArea');
        
        // إضافة مؤشرات بصرية متقدمة
        uploadArea.addEventListener('dragenter', (e) => {
            e.preventDefault();
            uploadArea.classList.add('dragover');
            this.showDropIndicator();
        });

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            this.updateDropIndicator(e);
        });

        uploadArea.addEventListener('dragleave', (e) => {
            if (!uploadArea.contains(e.relatedTarget)) {
                uploadArea.classList.remove('dragover');
                this.hideDropIndicator();
            }
        });
    }

    showDropIndicator() {
        const indicator = document.createElement('div');
        indicator.id = 'drop-indicator';
        indicator.innerHTML = `
            <div style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; 
                        background: rgba(102,126,234,0.2); z-index: 9999; 
                        display: flex; align-items: center; justify-content: center;
                        backdrop-filter: blur(5px);">
                <div style="background: white; padding: 40px; border-radius: 20px; 
                           box-shadow: 0 20px 40px rgba(0,0,0,0.3); text-align: center;">
                    <i class="fas fa-cloud-upload-alt" style="font-size: 4rem; color: #667eea; margin-bottom: 20px;"></i>
                    <h3 style="color: #667eea; margin-bottom: 10px;">إفلات الملف هنا</h3>
                    <p style="color: #666;">سيتم معالجة الملف تلقائياً</p>
                </div>
            </div>
        `;
        document.body.appendChild(indicator);
    }

    hideDropIndicator() {
        const indicator = document.getElementById('drop-indicator');
        if (indicator) {
            indicator.remove();
        }
    }

    updateDropIndicator(e) {
        const indicator = document.getElementById('drop-indicator');
        if (indicator) {
            const rect = e.target.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            indicator.style.background = `radial-gradient(circle at ${x}px ${y}px, 
                rgba(102,126,234,0.3), rgba(102,126,234,0.1))`;
        }
    }

    // File Processing with Progress
    async processFile(file) {
        if (!this.validateFile(file)) {
            return;
        }

        this.showProgress();
        
        try {
            // محاكاة معالجة الملف مع شريط التقدم
            await this.simulateProgress();
            
            const data = await this.readExcelFile(file);
            if (data) {
                this.uploadedData = data;
                this.showDataPreview(data);
                this.showToast('تم رفع الملف بنجاح', 'success');
                this.analyzeDataQuality(data);
            }
            
        } catch (error) {
            console.error('خطأ في معالجة الملف:', error);
            this.showToast('خطأ في معالجة الملف', 'error');
        } finally {
            this.hideProgress();
        }
    }

    validateFile(file) {
        const allowedTypes = [
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-excel'
        ];
        
        if (!allowedTypes.includes(file.type) && !file.name.match(/\.(xlsx|xls)$/)) {
            this.showToast('يرجى اختيار ملف Excel صحيح (.xlsx أو .xls)', 'error');
            return false;
        }
        
        if (file.size > 10 * 1024 * 1024) { // 10MB
            this.showToast('حجم الملف كبير جداً (الحد الأقصى 10MB)', 'error');
            return false;
        }
        
        return true;
    }

    async readExcelFile(file) {
        return new Promise((resolve, reject) => {
            const reader = new FileReader();
            
            reader.onload = (e) => {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, {type: 'array'});
                    const firstSheet = workbook.Sheets[workbook.SheetNames[0]];
                    const jsonData = XLSX.utils.sheet_to_json(firstSheet, {header: 1});
                    
                    if (jsonData.length < 2) {
                        reject(new Error('الملف فارغ أو لا يحتوي على بيانات كافية'));
                        return;
                    }
                    
                    resolve(jsonData);
                } catch (error) {
                    reject(error);
                }
            };
            
            reader.onerror = () => reject(new Error('خطأ في قراءة الملف'));
            reader.readAsArrayBuffer(file);
        });
    }

    async simulateProgress() {
        return new Promise((resolve) => {
            const progressFill = document.getElementById('progressFill');
            const progressText = document.getElementById('progressText');
            
            let progress = 0;
            const interval = setInterval(() => {
                progress += Math.random() * 15;
                if (progress > 100) progress = 100;
                
                progressFill.style.width = progress + '%';
                progressText.textContent = `جاري المعالجة... ${Math.round(progress)}%`;
                
                if (progress >= 100) {
                    clearInterval(interval);
                    progressText.textContent = 'تم الانتهاء!';
                    setTimeout(resolve, 500);
                }
            }, 100);
        });
    }

    // Data Quality Analysis
    analyzeDataQuality(data) {
        const headers = data[0];
        const rows = data.slice(1);
        
        const analysis = {
            totalRows: rows.length,
            emptyRows: rows.filter(row => row.every(cell => !cell || cell.toString().trim() === '')).length,
            duplicateEmails: this.findDuplicateEmails(rows, headers),
            invalidEmails: this.findInvalidEmails(rows, headers),
            missingRequiredFields: this.findMissingRequiredFields(rows, headers)
        };
        
        this.showDataQualityReport(analysis);
    }

    findDuplicateEmails(rows, headers) {
        const emailIndex = headers.findIndex(h => 
            h && h.toLowerCase().includes('email') || h && h.toLowerCase().includes('بريد')
        );
        
        if (emailIndex === -1) return [];
        
        const emails = rows.map(row => row[emailIndex]).filter(email => email);
        const duplicates = emails.filter((email, index) => emails.indexOf(email) !== index);
        
        return [...new Set(duplicates)];
    }

    findInvalidEmails(rows, headers) {
        const emailIndex = headers.findIndex(h => 
            h && h.toLowerCase().includes('email') || h && h.toLowerCase().includes('بريد')
        );
        
        if (emailIndex === -1) return [];
        
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return rows
            .map(row => row[emailIndex])
            .filter(email => email && !emailRegex.test(email));
    }

    findMissingRequiredFields(rows, headers) {
        const requiredFields = ['name', 'email', 'phone'];
        const missing = [];
        
        requiredFields.forEach(field => {
            const fieldIndex = headers.findIndex(h => 
                h && this.autoMapColumn(field, [h]) !== -1
            );
            
            if (fieldIndex !== -1) {
                const emptyCount = rows.filter(row => !row[fieldIndex] || row[fieldIndex].toString().trim() === '').length;
                if (emptyCount > 0) {
                    missing.push({
                        field: field,
                        count: emptyCount,
                        percentage: Math.round((emptyCount / rows.length) * 100)
                    });
                }
            }
        });
        
        return missing;
    }

    showDataQualityReport(analysis) {
        const report = document.createElement('div');
        report.className = 'glass-card';
        report.style.marginTop = '20px';
        report.innerHTML = `
            <h4 style="color: white; margin-bottom: 20px;">
                <i class="fas fa-chart-bar"></i> تقرير جودة البيانات
            </h4>
            <div style="background: rgba(255,255,255,0.95); border-radius: 15px; padding: 20px;">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px;">
                    <div style="text-align: center; padding: 15px; background: rgba(102,126,234,0.1); border-radius: 10px;">
                        <h5 style="color: #667eea; margin-bottom: 5px;">${analysis.totalRows}</h5>
                        <p style="margin: 0; color: #666;">إجمالي الصفوف</p>
                    </div>
                    <div style="text-align: center; padding: 15px; background: rgba(231,76,60,0.1); border-radius: 10px;">
                        <h5 style="color: #e74c3c; margin-bottom: 5px;">${analysis.emptyRows}</h5>
                        <p style="margin: 0; color: #666;">صفوف فارغة</p>
                    </div>
                    <div style="text-align: center; padding: 15px; background: rgba(241,196,15,0.1); border-radius: 10px;">
                        <h5 style="color: #f1c40f; margin-bottom: 5px;">${analysis.duplicateEmails.length}</h5>
                        <p style="margin: 0; color: #666;">بريد مكرر</p>
                    </div>
                    <div style="text-align: center; padding: 15px; background: rgba(230,126,34,0.1); border-radius: 10px;">
                        <h5 style="color: #e67e22; margin-bottom: 5px;">${analysis.invalidEmails.length}</h5>
                        <p style="margin: 0; color: #666;">بريد غير صحيح</p>
                    </div>
                </div>
                ${analysis.missingRequiredFields.length > 0 ? `
                    <div style="margin-top: 20px; padding: 15px; background: rgba(231,76,60,0.1); border-radius: 10px;">
                        <h6 style="color: #e74c3c; margin-bottom: 10px;">حقول مطلوبة مفقودة:</h6>
                        ${analysis.missingRequiredFields.map(field => `
                            <p style="margin: 5px 0; color: #666;">
                                ${field.field}: ${field.count} صف (${field.percentage}%)
                            </p>
                        `).join('')}
                    </div>
                ` : ''}
            </div>
        `;
        
        document.querySelector('.container').appendChild(report);
        
        // إضافة تأثير fadeInUp
        report.style.animation = 'fadeInUp 0.8s ease';
    }

   
    // Enhanced Auto-mapping
    autoMapColumn(fieldKey, headers) {
        const mappingRules = {
            'name': ['اسم', 'الاسم', 'name', 'full_name', 'employee_name', 'الموظف'],
            'email': ['بريد', 'إيميل', 'email', 'e_mail', 'mail', 'البريد'],
            'phone': ['هاتف', 'تلفون', 'phone', 'mobile', 'telephone', 'رقم'],
            'position': ['منصب', 'وظيفة', 'position', 'job', 'title', 'المسمى'],
            'department': ['قسم', 'إدارة', 'department', 'division', 'القسم'],
            'hiring_date': ['تاريخ', 'تعيين', 'hiring', 'start_date', 'تاريخ التعيين', 'hiring date', 'issuing date', 'تاريخ الاصدار', 'birth date', 'تاريخ الميلاد', 'expire date', 'تاريخ الانتهاء'],
            'address': ['عنوان', 'address', 'location', 'العنوان'],
            'notes': ['ملاحظات', 'notes', 'comments', 'remarks', 'ملاحظة']
        };
        
        const rules = mappingRules[fieldKey] || [];
        
        // البحث الدقيق أولاً
        for (let i = 0; i < headers.length; i++) {
            const header = (headers[i] || '').toLowerCase().trim();
            if (rules.some(rule => header === rule.toLowerCase())) {
                return i;
            }
        }
        
        // البحث الجزئي
        for (let i = 0; i < headers.length; i++) {
            const header = (headers[i] || '').toLowerCase().trim();
            if (rules.some(rule => header.includes(rule.toLowerCase()))) {
                return i;
            }
        }
        
        return -1;
    }

    // تحسين معالجة التواريخ في JavaScript
    parseDateClient(dateString) {
        if (!dateString) return null;
        
        try {
            // تنظيف النص
            const cleanDate = dateString.toString().trim();
            
            // معالجة تنسيق DD-MM-YYYY (مثل: 03-11-1995)
            if (/^(\d{1,2})-(\d{1,2})-(\d{4})$/.test(cleanDate)) {
                const matches = cleanDate.match(/^(\d{1,2})-(\d{1,2})-(\d{4})$/);
                const day = matches[1].padStart(2, '0');
                const month = matches[2].padStart(2, '0');
                const year = matches[3];
                
                // التحقق من صحة التاريخ
                const date = new Date(year, month - 1, day);
                if (!isNaN(date.getTime()) && date.getFullYear() == year && date.getMonth() == month - 1 && date.getDate() == day) {
                    const monthNames = [
                        'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                        'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
                    ];
                    
                    return `${day} ${monthNames[date.getMonth()]} ${year}`;
                }
            }
            
            // معالجة التواريخ المختلطة (يوم + شهر + رقم تسلسلي) مثل: 01 يناير 47950
            if (/^(\d{1,2})\s+([^0-9]+)\s+(\d{5,})$/.test(cleanDate)) {
                const matches = cleanDate.match(/^(\d{1,2})\s+([^0-9]+)\s+(\d{5,})$/);
                const day = matches[1].padStart(2, '0');
                const monthText = matches[2].trim();
                const serialNumber = matches[3];
                
                // تحويل الشهر العربي إلى إنجليزي
                const arabicMonths = {
                    'يناير': 'January', 'فبراير': 'February', 'مارس': 'March',
                    'أبريل': 'April', 'مايو': 'May', 'يونيو': 'June',
                    'يوليو': 'July', 'أغسطس': 'August', 'سبتمبر': 'September',
                    'أكتوبر': 'October', 'نوفمبر': 'November', 'ديسمبر': 'December'
                };
                
                if (arabicMonths[monthText]) {
                    const englishMonth = arabicMonths[monthText];
                    
                    // تحويل الرقم التسلسلي إلى سنة
                    if (!isNaN(serialNumber) && parseFloat(serialNumber) > 25569) {
                        const excelDate = parseFloat(serialNumber);
                        const timestamp = (excelDate - 25569) * 86400;
                        const date = new Date(timestamp * 1000);
                        
                        if (!isNaN(date.getTime())) {
                            const year = date.getFullYear();
                            const finalDateString = `${day} ${englishMonth} ${year}`;
                            const finalDate = new Date(finalDateString);
                            
                            if (!isNaN(finalDate.getTime())) {
                                const monthNames = [
                                    'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                                    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
                                ];
                                
                                return `${day} ${monthNames[finalDate.getMonth()]} ${year}`;
                            }
                        }
                    }
                }
            }
            
            // معالجة التواريخ العربية مع الشرطات مثل: 01-ديسمبر-2024
            if (/^(\d{1,2})-([^0-9]+)-(\d{4})$/.test(cleanDate)) {
                const matches = cleanDate.match(/^(\d{1,2})-([^0-9]+)-(\d{4})$/);
                const day = matches[1].padStart(2, '0');
                const monthText = matches[2].trim();
                const year = matches[3];
                
                // تحويل الشهر العربي إلى إنجليزي
                const arabicMonths = {
                    'يناير': 'January', 'فبراير': 'February', 'مارس': 'March',
                    'أبريل': 'April', 'مايو': 'May', 'يونيو': 'June',
                    'يوليو': 'July', 'أغسطس': 'August', 'سبتمبر': 'September',
                    'أكتوبر': 'October', 'نوفمبر': 'November', 'ديسمبر': 'December'
                };
                
                if (arabicMonths[monthText]) {
                    const englishMonth = arabicMonths[monthText];
                    const finalDateString = `${day} ${englishMonth} ${year}`;
                    const finalDate = new Date(finalDateString);
                    
                    if (!isNaN(finalDate.getTime())) {
                        const monthNames = [
                            'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                            'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
                        ];
                        
                        return `${day} ${monthNames[finalDate.getMonth()]} ${year}`;
                    }
                }
            }
            
            // معالجة الأرقام التسلسلية لـ Excel (مثل: 47950، 46203)
            if (!isNaN(cleanDate) && parseFloat(cleanDate) > 25569) {
                const excelDate = parseFloat(cleanDate);
                // تحويل من Excel serial date إلى timestamp
                const timestamp = (excelDate - 25569) * 86400; // 25569 = 1970-01-01 في Excel
                const date = new Date(timestamp * 1000); // JavaScript uses milliseconds
                
                if (!isNaN(date.getTime())) {
                    const day = date.getDate().toString().padStart(2, '0');
                    const month = date.getMonth();
                    const year = date.getFullYear();
                    
                    const monthNames = [
                        'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                        'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
                    ];
                    
                    return `${day} ${monthNames[month]} ${year}`;
                }
            }
            
            // معالجة تنسيق DD-Mon-YYYY (مثل: 01-JAN-2025)
            if (/^\d{1,2}-[A-Z]{3}-\d{4}$/.test(cleanDate)) {
                const parts = cleanDate.split('-');
                const day = parts[0].padStart(2, '0');
                const month = parts[1];
                const year = parts[2];
                
                const monthMap = {
                    'JAN': 'يناير', 'FEB': 'فبراير', 'MAR': 'مارس', 'APR': 'أبريل',
                    'MAY': 'مايو', 'JUN': 'يونيو', 'JUL': 'يوليو', 'AUG': 'أغسطس',
                    'SEP': 'سبتمبر', 'OCT': 'أكتوبر', 'NOV': 'نوفمبر', 'DEC': 'ديسمبر'
                };
                
                if (monthMap[month]) {
                    return `${day} ${monthMap[month]} ${year}`;
                }
            }
            
            // معالجة تنسيق DD-Mon-YYYY (مثل: 15-Jan-2025)
            if (/^\d{1,2}-[A-Za-z]{3}-\d{4}$/.test(cleanDate)) {
                const parts = cleanDate.split('-');
                const day = parts[0].padStart(2, '0');
                const month = parts[1].toUpperCase();
                const year = parts[2];
                
                const monthMap = {
                    'JAN': 'يناير', 'FEB': 'فبراير', 'MAR': 'مارس', 'APR': 'أبريل',
                    'MAY': 'مايو', 'JUN': 'يونيو', 'JUL': 'يوليو', 'AUG': 'أغسطس',
                    'SEP': 'سبتمبر', 'OCT': 'أكتوبر', 'NOV': 'نوفمبر', 'DEC': 'ديسمبر'
                };
                
                if (monthMap[month]) {
                    return `${day} ${monthMap[month]} ${year}`;
                }
            }
            
            // معالجة تنسيق MM/YYYY (مثل: 12/2024)
            if (/^\d{1,2}\/\d{4}$/.test(cleanDate)) {
                const parts = cleanDate.split('/');
                const month = parts[0].padStart(2, '0');
                const year = parts[1];
                
                const monthMap = {
                    '01': 'يناير', '02': 'فبراير', '03': 'مارس', '04': 'أبريل',
                    '05': 'مايو', '06': 'يونيو', '07': 'يوليو', '08': 'أغسطس',
                    '09': 'سبتمبر', '10': 'أكتوبر', '11': 'نوفمبر', '12': 'ديسمبر'
                };
                
                return `01 ${monthMap[month]} ${year}`;
            }
            
            // محاولة تحليل التاريخ باستخدام Date
            const date = new Date(cleanDate);
            if (!isNaN(date.getTime())) {
                const day = date.getDate().toString().padStart(2, '0');
                const month = date.getMonth();
                const year = date.getFullYear();
                
                const monthNames = [
                    'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
                    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'
                ];
                
                return `${day} ${monthNames[month]} ${year}`;
            }
            
            return cleanDate; // إرجاع النص الأصلي إذا فشل التحليل
            
        } catch (error) {
            console.error('خطأ في تحليل التاريخ:', error);
            return dateString;
        }
    }

    // تحسين معاينة البيانات لتشمل معالجة التواريخ
    enhanceDataPreview(data) {
        const headers = data[0];
        const rows = data.slice(1);
        
        // البحث عن أعمدة التواريخ
        const dateColumns = [];
        headers.forEach((header, index) => {
            if (header && (
                header.toLowerCase().includes('date') ||
                header.toLowerCase().includes('تاريخ') ||
                header.toLowerCase().includes('hiring') ||
                header.toLowerCase().includes('تعيين') ||
                header.toLowerCase().includes('issuing') ||
                header.toLowerCase().includes('اصدار') ||
                header.toLowerCase().includes('birth') ||
                header.toLowerCase().includes('ميلاد') ||
                header.toLowerCase().includes('expire') ||
                header.toLowerCase().includes('انتهاء')
            )) {
                dateColumns.push(index);
            }
        });
        
        // معالجة التواريخ في المعاينة
        const processedRows = rows.map(row => {
            const processedRow = [...row];
            dateColumns.forEach(colIndex => {
                if (row[colIndex]) {
                    const parsedDate = this.parseDateClient(row[colIndex]);
                    if (parsedDate && parsedDate !== row[colIndex]) {
                        processedRow[colIndex] = parsedDate;
                    }
                }
            });
            return processedRow;
        });
        
        return [headers, ...processedRows];
    }

    // Save/Load Preferences
    saveMappingPreferences() {
        const preferences = {
            columnMapping: this.columnMapping,
            defaultValues: {
                department: document.getElementById('defaultDepartment').value,
                position: document.getElementById('defaultPosition').value,
                phone: document.getElementById('defaultPhone').value
            }
        };
        
        localStorage.setItem('batchCreatePreferences', JSON.stringify(preferences));
    }

    loadMappingPreferences() {
        const saved = localStorage.getItem('batchCreatePreferences');
        if (saved) {
            try {
                const preferences = JSON.parse(saved);
                
                if (preferences.defaultValues) {
                    document.getElementById('defaultDepartment').value = preferences.defaultValues.department || '';
                    document.getElementById('defaultPosition').value = preferences.defaultValues.position || '';
                    document.getElementById('defaultPhone').value = preferences.defaultValues.phone || '';
                }
            } catch (error) {
                console.error('خطأ في تحميل التفضيلات:', error);
            }
        }
    }

    // Keyboard Shortcuts
    setupKeyboardShortcuts() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + O لفتح الملف
            if ((e.ctrlKey || e.metaKey) && e.key === 'o') {
                e.preventDefault();
                document.getElementById('fileInput').click();
            }
            
            // Ctrl/Cmd + S للحفظ
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                if (document.getElementById('saveData').style.display !== 'none') {
                    this.saveEmployeeData();
                }
            }
            
            // Ctrl/Cmd + D لتحميل القالب
            if ((e.ctrlKey || e.metaKey) && e.key === 'd') {
                e.preventDefault();
                this.downloadExcelTemplate();
            }
            
            // Escape لإغلاق النوافذ المنبثقة
            if (e.key === 'Escape') {
                this.closeAllModals();
            }
        });
    }

    closeAllModals() {
        // إغلاق جميع النوافذ المنبثقة
        const modals = document.querySelectorAll('.modal, .toast');
        modals.forEach(modal => modal.remove());
    }

    // Service Worker for Offline Support
    setupServiceWorker() {
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => {
                    console.log('Service Worker registered successfully');
                })
                .catch(error => {
                    console.log('Service Worker registration failed');
                });
        }
    }

    // Advanced Save with Batch Processing
    async saveEmployeeData() {
         if (!this.validateMapping()) {
             this.showToast('يرجى التأكد من ربط جميع الحقول المطلوبة: كود الموظف، الاسم، البريد الشخصي، كلمة المرور، تأكيد كلمة المرور، القسم، الدور', 'error');
             return;
         }
         
         // التحقق من خيار السماح بالتكرار
         const allowDuplicates = document.getElementById('allowDuplicateEmails').checked;
         if (!allowDuplicates) {
             const confirmMessage = '⚠️ تحذير: لم يتم تفعيل خيار "السماح بالأيميلات المكررة"\n\n' +
                                  'إذا كان لديك موظفين بنفس البريد الإلكتروني، سيتم تجاهلهم.\n\n' +
                                  'هل تريد المتابعة أم تفعيل الخيار أولاً؟';
             
             const userChoice = confirm(confirmMessage);
             if (!userChoice) {
                 // المستخدم اختار إلغاء، نركز على خيار السماح بالتكرار
                 document.getElementById('allowDuplicateEmails').focus();
                 this.showToast('يرجى تفعيل خيار "السماح بالأيميلات المكررة" إذا كان لديك موظفين بنفس البريد الإلكتروني', 'warning');
                 return;
             }
         }
        
        // التحقق من تكرار كود الموظف
        let processedData;
        
        if (this.processedEmployees && this.processedEmployees.length > 0) {
            // استخدام البيانات المُعالجة بالفعل
            processedData = this.processedEmployees;
            console.log('Using already processed employees:', processedData.length);
        } else {
            // معالجة البيانات للمرة الأولى
            processedData = this.processEmployeeData();
            console.log('Processing employees for the first time:', processedData.length);
        }
        
        const duplicateEmployeeCodes = this.findDuplicateEmployeeCodes(processedData);
        
        if (duplicateEmployeeCodes.length > 0) {
            this.processedEmployees = processedData; // حفظ البيانات المُعالجة
            this.showDuplicateEmployeeCodesError(duplicateEmployeeCodes);
            return;
        }
        
        this.showLoading();
        
        try {
            const batches = this.createBatches(processedData, this.batchSize);
            
            let totalSaved = 0;
            let totalFailed = 0;
            const allErrors = [];
            
            for (let i = 0; i < batches.length; i++) {
                const batch = batches[i];
                const result = await this.saveBatch(batch, i + 1, batches.length);
                
                totalSaved += result.saved;
                totalFailed += result.failed;
                allErrors.push(...result.errors);
                
                // تحديث شريط التقدم
                this.updateBatchProgress(i + 1, batches.length);
            }
            
            this.showSaveResults(totalSaved, totalFailed, allErrors);
            
        } catch (error) {
            console.error('خطأ في الحفظ:', error);
            this.showToast(`خطأ في الحفظ: ${error.message}`, 'error');
        } finally {
            this.hideLoading();
        }
    }

    createBatches(data, batchSize) {
        const batches = [];
        for (let i = 0; i < data.length; i += batchSize) {
            batches.push(data.slice(i, i + batchSize));
        }
        return batches;
    }

    async saveBatch(batch, batchNumber, totalBatches) {
        try {
            console.log('Sending batch:', batch);
            console.log('First employee data:', batch[0]);
            
            // التحقق من صحة البيانات قبل الإرسال
            const validatedBatch = batch.map(employee => {
                // التأكد من أن كلمة المرور نص
                if (typeof employee.password === 'number') {
                    employee.password = employee.password.toString();
                }
                if (typeof employee.password_confirmation === 'number') {
                    employee.password_confirmation = employee.password_confirmation.toString();
                }
                
                // التأكد من أن employee_id رقم
                if (typeof employee.employee_id === 'string' && employee.employee_id !== 'Egy Ball') {
                    const numericId = parseInt(employee.employee_id);
                    if (!isNaN(numericId)) {
                        employee.employee_id = numericId;
                    }
                }
                
                return employee;
            });
            
            const response = await fetch('/users/batch-create', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    employees: validatedBatch,
                    defaultValues: this.getDefaultValues(),
                    batchInfo: {
                        batchNumber: batchNumber,
                        totalBatches: totalBatches
                    }
                })
            });
            
            if (!response.ok) {
                const errorText = await response.text();
                console.error('Server error:', response.status, errorText);
                throw new Error(`خطأ في الخادم: ${response.status}`);
            }
            
            const result = await response.json();
            console.log('Batch result:', result);
            
            // Log detailed errors for debugging
            if (result.failed_employees && result.failed_employees.length > 0) {
                console.log('Failed employees details:', result.failed_employees);
                result.failed_employees.forEach((failed, index) => {
                    console.log(`Failed employee ${index + 1}:`, failed.data, 'Error:', failed.error);
                });
                
                // تحسين رسالة الخطأ
                const emailErrors = result.failed_employees.filter(failed => 
                    failed.error && failed.error.includes('البريد الإلكتروني مستخدم مسبقاً')
                );
                
                if (emailErrors.length > 0) {
                    this.showToast(
                        `⚠️ فشل حفظ ${emailErrors.length} موظف بسبب تكرار البريد الإلكتروني. يرجى تفعيل خيار "السماح بالأيميلات المكررة"`, 
                        'error'
                    );
                }
            }
            
            return result;
            
        } catch (error) {
            console.error('Error in saveBatch:', error);
            throw error;
        }
    }

    updateBatchProgress(currentBatch, totalBatches) {
        const progress = (currentBatch / totalBatches) * 100;
        const progressText = document.getElementById('progressText');
        if (progressText) {
            progressText.textContent = `معالجة الدفعة ${currentBatch} من ${totalBatches} (${Math.round(progress)}%)`;
        }
    }

    showSaveResults(saved, failed, errors) {
        const message = `تم حفظ ${saved} موظف بنجاح من أصل ${saved + failed}`;
        this.showToast(message, saved > 0 ? 'success' : 'error');
        
        if (errors.length > 0) {
            this.showDetailedErrorReport(errors);
        }
        
        if (saved > 0) {
            this.resetForm();
        }
    }

    showDetailedErrorReport(errors) {
        const report = document.createElement('div');
        report.className = 'glass-card';
        report.style.marginTop = '20px';
        report.innerHTML = `
            <h4 style="color: white; margin-bottom: 20px;">
                <i class="fas fa-exclamation-triangle"></i> تقرير الأخطاء
            </h4>
            <div style="background: rgba(255,255,255,0.95); border-radius: 15px; padding: 20px; max-height: 300px; overflow-y: auto;">
                ${errors.map(error => `
                    <div style="padding: 10px; margin-bottom: 10px; background: rgba(231,76,60,0.1); border-radius: 8px; border-left: 4px solid #e74c3c;">
                        <p style="margin: 0; color: #e74c3c; font-weight: 600;">${error}</p>
                    </div>
                `).join('')}
            </div>
        `;
        
        document.querySelector('.container').appendChild(report);
    }

    // Utility Methods
    showProgress() {
        document.getElementById('progressContainer').style.display = 'block';
        this.animateProgress(0, 100, 2000);
    }

    hideProgress() {
        document.getElementById('progressContainer').style.display = 'none';
    }

    showLoading() {
        document.getElementById('loadingSpinner').style.display = 'block';
    }

    hideLoading() {
        document.getElementById('loadingSpinner').style.display = 'none';
    }

    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            ${message}
        `;
        
        document.getElementById('toastContainer').appendChild(toast);
        
        setTimeout(() => toast.classList.add('show'), 100);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 300);
        }, 4000);
    }

    resetForm() {
        this.uploadedData = null;
        this.columnMapping = {};
        
        document.getElementById('dataPreview').style.display = 'none';
        document.getElementById('mappingContainer').style.display = 'none';
        document.getElementById('defaultValues').style.display = 'none';
        document.getElementById('processData').style.display = 'none';
        document.getElementById('saveData').style.display = 'none';
        document.getElementById('fileInput').value = '';
        
        // إعادة تعيين checkbox السماح بالأيميلات المكررة
        const allowDuplicateEmailsCheckbox = document.getElementById('allowDuplicateEmails');
        if (allowDuplicateEmailsCheckbox) {
            allowDuplicateEmailsCheckbox.checked = false;
        }
        
        // إزالة تقارير الجودة والأخطاء
        const reports = document.querySelectorAll('.glass-card:not(:first-child):not(:nth-child(2))');
        reports.forEach(report => report.remove());
    }

    // بقية الطرق الأساسية...
    handleDragOver(e) {
        e.preventDefault();
        e.currentTarget.classList.add('dragover');
    }

    handleDragLeave(e) {
        e.preventDefault();
        e.currentTarget.classList.remove('dragover');
    }

    handleDrop(e) {
        e.preventDefault();
        e.currentTarget.classList.remove('dragover');
        const files = e.dataTransfer.files;
        if (files.length > 0) {
            this.processFile(files[0]);
        }
    }

    handleFileSelect(e) {
        const file = e.target.files[0];
        if (file) {
            this.processFile(file);
        }
    }

    processUploadedData() {
         if (!this.uploadedData || !this.validateMapping()) {
             this.showToast('يرجى التأكد من ربط جميع الحقول المطلوبة: كود الموظف، الاسم، البريد الشخصي، كلمة المرور، تأكيد كلمة المرور، القسم، الدور', 'error');
             return;
         }
        
        this.showToast('تم معالجة البيانات بنجاح، جاهز للحفظ', 'success');
        document.getElementById('saveData').style.display = 'inline-flex';
    }

     validateMapping() {
         const requiredFields = ['employee_id', 'name', 'email', 'password', 'password_confirmation', 'department', 'role'];
         const missingFields = [];
        
        requiredFields.forEach(field => {
            if (!this.columnMapping[field] && this.columnMapping[field] !== 0) {
                missingFields.push(field);
            }
        });
        
        if (missingFields.length > 0) {
            console.log('Missing required fields:', missingFields);
            console.log('Current mapping:', this.columnMapping);
        }
        
        return missingFields.length === 0;
    }

    processEmployeeData() {
        const employees = [];
        const dataRows = this.uploadedData.slice(1);
        
        dataRows.forEach((row, index) => {
            // تخطي الصفوف الفارغة
            if (!row || row.every(cell => !cell || cell.toString().trim() === '')) {
                return;
            }
            
            const employee = {};
            
            Object.keys(this.columnMapping).forEach(field => {
                const columnIndex = this.columnMapping[field];
                if (columnIndex !== null && row[columnIndex] !== undefined) {
                    let value = row[columnIndex];
                    
                    // تنظيف القيمة
                    if (value && typeof value === 'string') {
                        value = value.trim();
                    }
                    
                    // معالجة خاصة للتواريخ
                    if ((field === 'birthday' || field === 'birth_date' || field === 'hiring_date') && value) {
                        const parsedDate = this.parseDateClient(value);
                        employee[field] = parsedDate || value;
                    } else {
                        employee[field] = value;
                    }
                }
            });
            
             // التأكد من وجود الحقول المطلوبة
             if (employee.employee_id && employee.name) {
                // إذا لم يكن هناك بريد إلكتروني أو كان "Egy Ball"، إنشاء بريد افتراضي
                if (!employee.email || employee.email === 'Egy Ball') {
                    const originalEmail = employee.email;
                    const employeeCode = employee.employee_id || (index + 1000);
                    const nameParts = employee.name.toLowerCase().replace(/[^a-z0-9]/g, '');
                    employee.email = `${nameParts}${employeeCode}@company.com`;
                    console.log(`Created default email for row ${index + 2}: ${employee.email} (original: ${originalEmail})`);
                }
                
                // معالجة خاصة للبيانات التي تحتوي على "Egy Ball" في كلمة المرور
                if (employee.password === 'Egy Ball') {
                    employee.password = 'TempPass123!';
                    employee.password_confirmation = 'TempPass123!';
                    console.log(`Fixed password for row ${index + 2}: ${employee.password}`);
                }
                
                // تحويل كلمة المرور إلى نص إذا كانت رقماً
                if (employee.password && typeof employee.password === 'number') {
                    employee.password = employee.password.toString();
                    console.log(`Converted numeric password to string for row ${index + 2}: ${employee.password}`);
                }
                
                // إضافة قيم افتراضية للحقول المطلوبة إذا لم تكن موجودة
                if (!employee.password) {
                    employee.password = 'TempPass123!';
                }
                if (!employee.password_confirmation) {
                    employee.password_confirmation = employee.password;
                }
                
                // التأكد من أن كلمة المرور نص
                if (typeof employee.password !== 'string') {
                    employee.password = employee.password.toString();
                }
                if (typeof employee.password_confirmation !== 'string') {
                    employee.password_confirmation = employee.password_confirmation.toString();
                }
                
                // معالجة employee_id إذا كان "Egy Ball" أو نص غير صالح
                if (employee.employee_id === 'Egy Ball' || employee.employee_id === '' || !employee.employee_id) {
                    // إنشاء رقم عشوائي كـ employee_id
                    const randomId = Math.floor(Math.random() * 9000) + 1000;
                    employee.employee_id = randomId;
                    console.log(`Fixed employee_id for row ${index + 2}: ${employee.employee_id} (was: ${employee.employee_id === 'Egy Ball' ? 'Egy Ball' : 'empty'})`);
                }
                if (!employee.department) {
                    employee.department = 'قسم افتراضي';
                }
                if (!employee.role) {
                    employee.role = 'موظف';
                }
                
                // إزالة الحقول التي قد تسبب مشاكل
                delete employee.position;
                delete employee.position_ar;
                delete employee.address_ar;
                
                employee._row_number = index + 2;
                employees.push(employee);
             } else {
                 console.log(`Skipping row ${index + 2}: missing required fields (employee_id, name)`, employee);
             }
        });
        
        console.log('Processed employees:', employees.length);
        
        // Log sample employee data for debugging
        if (employees.length > 0) {
            console.log('Sample employee data:', employees[0]);
        }
        
        return employees;
    }

    getDefaultValues() {
        const departmentId = document.getElementById('defaultDepartment').value;
        return {
            department_id: departmentId || '1', // قيمة افتراضية: HR Department
            position: document.getElementById('defaultPosition').value,
            phone: document.getElementById('defaultPhone').value,
            allowDuplicateEmails: document.getElementById('allowDuplicateEmails').checked
        };
    }

    async downloadExcelTemplate() {
        try {
            const response = await fetch('/api/template');
            const result = await response.json();
            
            if (result.success) {
                // إنشاء ملف Excel باستخدام SheetJS
                const worksheet = XLSX.utils.aoa_to_sheet(result.template_data);
                
                // تحديد عرض الأعمدة
                const colWidths = [
                    { wch: 20 }, // الاسم
                    { wch: 25 }, // البريد الإلكتروني
                    { wch: 15 }, // رقم الهاتف
                    { wch: 20 }, // المنصب
                    { wch: 20 }, // القسم
                    { wch: 18 }, // تاريخ التعيين
                    { wch: 18 }, // تاريخ الميلاد
                    { wch: 20 }, // تاريخ انتهاء العقد
                    { wch: 30 }, // العنوان
                    { wch: 25 }  // ملاحظات
                ];
                worksheet['!cols'] = colWidths;
                
                const workbook = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(workbook, worksheet, 'الموظفين');
                
                XLSX.writeFile(workbook, result.filename);
                this.showToast('تم تحميل القالب بنجاح مع أمثلة على تنسيقات التواريخ المختلفة', 'success');
            }
        } catch (error) {
            console.error('خطأ في تحميل القالب:', error);
            this.showToast('خطأ في تحميل القالب', 'error');
        }
    }

    async loadDepartments() {
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

    showDataPreview(data) {
        const preview = document.getElementById('dataPreview');
        const tableHead = document.getElementById('tableHead');
        const tableBody = document.getElementById('tableBody');
        
        tableHead.innerHTML = '';
        tableBody.innerHTML = '';
        
        // معالجة البيانات لتشمل تحسين التواريخ
        const processedData = this.enhanceDataPreview(data);
        
        // إنشاء الرأس
        const headerRow = document.createElement('tr');
        processedData[0].forEach((header, index) => {
            const th = document.createElement('th');
            th.textContent = header || `العمود ${index + 1}`;
            
            // إضافة أيقونة للتواريخ
            if (header && (
                header.toLowerCase().includes('date') ||
                header.toLowerCase().includes('تاريخ') ||
                header.toLowerCase().includes('hiring') ||
                header.toLowerCase().includes('تعيين') ||
                header.toLowerCase().includes('issuing') ||
                header.toLowerCase().includes('اصدار') ||
                header.toLowerCase().includes('birth') ||
                header.toLowerCase().includes('ميلاد') ||
                header.toLowerCase().includes('expire') ||
                header.toLowerCase().includes('انتهاء')
            )) {
                th.innerHTML = `<i class="fas fa-calendar-alt me-1"></i>${header}`;
                th.style.color = '#667eea';
            }
            
            headerRow.appendChild(th);
        });
        tableHead.appendChild(headerRow);
        
        // إنشاء الجسم (أول 10 صفوف للمعاينة)
        const previewRows = processedData.slice(1, 11);
        previewRows.forEach((row, rowIndex) => {
            const tr = document.createElement('tr');
            row.forEach((cell, cellIndex) => {
                const td = document.createElement('td');
                
                // التحقق من أن هذا العمود يحتوي على تواريخ
                const header = processedData[0][cellIndex];
                const isDateColumn = header && (
                    header.toLowerCase().includes('date') ||
                    header.toLowerCase().includes('تاريخ') ||
                    header.toLowerCase().includes('hiring') ||
                    header.toLowerCase().includes('تعيين') ||
                    header.toLowerCase().includes('issuing') ||
                    header.toLowerCase().includes('اصدار') ||
                    header.toLowerCase().includes('birth') ||
                    header.toLowerCase().includes('ميلاد') ||
                    header.toLowerCase().includes('expire') ||
                    header.toLowerCase().includes('انتهاء')
                );
                
                if (isDateColumn && cell) {
                    // إضافة تنسيق خاص للتواريخ
                    td.innerHTML = `<span style="color: #667eea; font-weight: 500;"><i class="fas fa-calendar me-1"></i>${cell}</span>`;
                } else {
                    td.textContent = cell || '';
                }
                
                tr.appendChild(td);
            });
            tableBody.appendChild(tr);
        });
        
        preview.style.display = 'block';
        this.showColumnMapping(data[0]); // استخدام البيانات الأصلية للربط
        document.getElementById('processData').style.display = 'inline-flex';
        
        preview.style.animation = 'fadeInUp 0.8s ease';
    }

    animateProgress(start, end, duration) {
        const progressFill = document.getElementById('progressFill');
        const progressText = document.getElementById('progressText');
        const startTime = performance.now();
        
        const updateProgress = (currentTime) => {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const currentValue = start + (end - start) * progress;
            
            progressFill.style.width = currentValue + '%';
            
            if (progress < 1) {
                requestAnimationFrame(updateProgress);
            }
        };
        
        requestAnimationFrame(updateProgress);
    }

    // Column Mapping - NEW VERSION
    showColumnMapping(headers) {
        console.log('🚀 showColumnMapping called with headers:', headers);
        const container = document.getElementById('mappingContainer');
        const grid = document.getElementById('mappingGrid');
        
        // Clear grid completely
        grid.innerHTML = '';
        console.log('Grid cleared. Previous children count:', grid.children.length);
        
         // Define all 28 fields (added employee_id and separated work/personal email)
         const fields = [
             { key: 'employee_id', label: 'Employee Code *', required: true },
             { key: 'name', label: 'Name *', required: true },
             { key: 'name_arabic', label: 'Name in Arabic', required: false },
             { key: 'profile_picture', label: 'Profile Picture', required: false },
             { key: 'email', label: 'Personal Email *', required: true },
             { key: 'work_email', label: 'Work Email', required: false },
             { key: 'password', label: 'Password *', required: true },
             { key: 'password_confirmation', label: 'Confirm Password *', required: true },
             { key: 'department', label: 'Department *', required: true },
             { key: 'role', label: 'Role *', required: true },
             { key: 'work_phone', label: 'Work Phone', required: false },
             { key: 'mobile_phone', label: 'Mobile Phone', required: false },
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
        
         console.log('📋 Creating', fields.length, 'fields for mapping (including Employee Code and separated emails)...');
        
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
                select.addEventListener('change', (event) => {
                    const selectedValue = event.target.value;
                    this.columnMapping[field.key] = selectedValue ? parseInt(selectedValue) : null;
                    console.log(`Field ${field.key} mapped to column:`, selectedValue);
                });
                
                // Auto-mapping
                const autoMapping = this.autoMapColumn(field.key, headers);
                if (autoMapping !== -1) {
                    select.value = autoMapping;
                    this.columnMapping[field.key] = autoMapping;
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
         console.log('✅ Created', mappingItems.length, 'fields out of', fields.length, 'expected (including Employee Code and separated emails)');
        console.log('Grid children count:', grid.children.length);
        
         if (mappingItems.length === fields.length) {
             console.log('🎉 SUCCESS: All 28 fields are visible!');
         } else {
             console.error('❌ ERROR: Field count mismatch! Expected 28 fields.');
         }
    }

     // Auto-mapping logic
     autoMapColumn(fieldKey, headers) {
         const mappingRules = {
             'employee_id': ['employee code', 'employee_code', 'emp code', 'emp_code', 'empcode', '.emp code', 'كود الموظف', 'كود العمل', 'employee id', 'employee_id', 'employee_number', 'رقم الموظف', 'id', 'معرف الموظف'],
             'name': ['اسم', 'الاسم', 'name', 'full_name', 'employee_name', 'english name', 'الاسم بالانجليزية'],
             'name_arabic': ['اسم عربي', 'arabic name', 'الاسم العربي', 'arabic name/ الاسم بالعربية'],
             'profile_picture': ['صورة', 'picture', 'photo', 'avatar', 'صورة شخصية'],
             'email': ['personal email', 'بريد شخصي', 'إيميل شخصي', 'email', 'e_mail', 'mail', 'بريد إلكتروني', 'إيميل', 'بريد'],
             'work_email': ['work email', 'ايميل العمل', 'work email / ايميل العمل', 'بريد عمل', 'إيميل عمل'],
            'password': ['كلمة مرور', 'password', 'كلمة السر'],
            'password_confirmation': ['تأكيد كلمة المرور', 'confirm password', 'تأكيد كلمة السر'],
            'department': ['قسم', 'إدارة', 'department', 'division', 'organization', 'القسم', 'organization/ القسم'],
            'role': ['دور', 'role', 'صلاحية', 'permission', 'roles template/ نموذج القواعد'],
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
            'notes': ['ملاحظات', 'notes', 'comments', 'ملاحظات إضافية']
        };

        const rules = mappingRules[fieldKey] || [];
        
        for (let i = 0; i < headers.length; i++) {
            const header = headers[i].toLowerCase();
            for (const rule of rules) {
                if (header.includes(rule.toLowerCase())) {
                    return i;
                }
            }
        }
        
        return -1;
    }

     // وظائف التعامل مع تكرار كود الموظف
     findDuplicateEmployeeCodes(employees) {
         const employeeCodes = {};
         const duplicates = [];
         
         employees.forEach((employee, index) => {
             const employeeCode = employee.employee_id || employee.employee_code || employee.employee_number;
            if (employeeCode) {
                if (employeeCodes[employeeCode]) {
                    // إضافة الموظف الحالي للقائمة
                    duplicates.push({
                        employeeCode: employeeCode,
                        name: employee.name,
                        index: index + 1
                    });
                    
                    // إضافة الموظف الأول إذا لم يكن موجوداً
                    if (!employeeCodes[employeeCode].isFirstAdded) {
                        duplicates.push({
                            employeeCode: employeeCode,
                            name: employeeCodes[employeeCode].name,
                            index: employeeCodes[employeeCode].index,
                            isFirst: true
                        });
                        employeeCodes[employeeCode].isFirstAdded = true;
                    }
                } else {
                    employeeCodes[employeeCode] = {
                        name: employee.name,
                        index: index + 1,
                        isFirstAdded: false
                    };
                }
            }
        });
        
        return duplicates;
    }

    showDuplicateEmployeeCodesError(duplicates) {
        const modal = document.createElement('div');
        modal.className = 'modal-overlay';
        modal.style.cssText = `
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            z-index: 10000;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        `;
        
        modal.innerHTML = `
            <div class="glass-card" style="max-width: 700px; margin: 50px auto; transform: translateY(-50px); transition: transform 0.3s ease;">
                <div style="text-align: center; margin-bottom: 20px;">
                    <i class="fas fa-exclamation-triangle" style="font-size: 3rem; color: #ff6b6b; margin-bottom: 15px;"></i>
                    <h2 style="color: #2c3e50; margin-bottom: 10px;">تكرار في كود الموظف</h2>
                    <p style="color: #7f8c8d;">تم العثور على موظفين بنفس كود الموظف. الموظف الأول سيتم الاحتفاظ به والمكررين سيتم تجاهلهم:</p>
                </div>
                
                <div style="background: #f8f9fa; border-radius: 10px; padding: 20px; margin-bottom: 20px;">
                    <h3 style="color: #2c3e50; margin-bottom: 15px; text-align: center;">
                        <i class="fas fa-list"></i> قائمة الموظفين المكررين
                    </h3>
                    <div style="max-height: 250px; overflow-y: auto;">
                        ${duplicates.map((dup, index) => `
                            <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px; border-bottom: 1px solid #dee2e6; ${index === duplicates.length - 1 ? 'border-bottom: none;' : ''}">
                                <div>
                                    <strong style="color: #2c3e50;">${dup.name}</strong>
                                    <br>
                                    <small style="color: #6c757d;">كود الموظف: ${dup.employeeCode}</small>
                                </div>
                                <div style="text-align: center;">
                                    <span style="background: #e74c3c; color: white; padding: 4px 8px; border-radius: 12px; font-size: 0.8rem; font-weight: bold;">
                                        صف ${dup.index}
                                    </span>
                                    ${dup.isFirst ? '<br><small style="color: #e74c3c;">(أول ظهور)</small>' : ''}
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
                
                <div style="background: #e8f5e8; border: 2px solid #28a745; border-radius: 10px; padding: 20px; margin-bottom: 20px;">
                    <h4 style="color: #155724; margin-bottom: 15px; text-align: center;">
                        <i class="fas fa-cogs"></i> خيارات المعالجة
                    </h4>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                        <button id="ignoreDuplicates" class="btn btn-success" style="padding: 15px; border-radius: 8px; font-weight: bold; transition: all 0.3s ease; background: linear-gradient(135deg, #28a745, #20c997); border: none; color: white;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(40, 167, 69, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <i class="fas fa-forward"></i><br>
                            تجاهل المكررين<br>
                            <small>ومتابعة الحفظ</small>
                        </button>
                        <button id="cancelSave" class="btn btn-danger" style="padding: 15px; border-radius: 8px; font-weight: bold; transition: all 0.3s ease; background: linear-gradient(135deg, #dc3545, #e74c3c); border: none; color: white;" onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 8px 20px rgba(220, 53, 69, 0.4)'" onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='none'">
                            <i class="fas fa-times"></i><br>
                            إلغاء الحفظ<br>
                            <small>وتصحيح البيانات يدوياً</small>
                        </button>
                    </div>
                </div>
                
                <div style="text-align: center;">
                    <button onclick="this.closest('.modal-overlay').remove()" class="btn btn-secondary" style="padding: 10px 20px; font-size: 0.9rem;">
                        <i class="fas fa-times"></i> إغلاق
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // إضافة تأثير الظهور
        setTimeout(() => {
            modal.style.opacity = '1';
            modal.querySelector('.glass-card').style.transform = 'translateY(0)';
        }, 10);
        
        // ربط الأحداث
        modal.querySelector('#ignoreDuplicates').addEventListener('click', () => {
            modal.remove();
            this.handleDuplicateEmployeeCodes(duplicates);
        });
        
        modal.querySelector('#cancelSave').addEventListener('click', () => {
            modal.remove();
            this.showToast('تم إلغاء عملية الحفظ', 'warning');
        });
    }

    handleDuplicateEmployeeCodes(duplicates) {
        console.log('=== Starting duplicate handling ===');
        console.log('Duplicates found:', duplicates);
        console.log('Total employees before filtering:', this.processedEmployees.length);
        console.log('First few employees before filtering:', this.processedEmployees.slice(0, 3));
        
        this.showToast('جاري تجاهل الموظفين المكررين...', 'info');
        
        // تجاهل الموظفين المكررين (عدا الأول)
        const duplicateRows = duplicates.filter(dup => !dup.isFirst).map(dup => dup.index);
        
        console.log('Duplicate rows to ignore:', duplicateRows);
        console.log('Duplicates details:', duplicates.map(d => ({name: d.name, row: d.index, isFirst: d.isFirst})));
        
        const processedEmployees = this.processedEmployees.filter(employee => {
            const isDuplicate = duplicateRows.includes(employee._row_number);
            
            if (isDuplicate) {
                console.log(`❌ Ignoring duplicate employee: ${employee.name} (Row ${employee._row_number})`);
                return false; // تجاهل الموظف المكرر
            }
            
            console.log(`✅ Keeping employee: ${employee.name} (Row ${employee._row_number})`);
            return true; // الاحتفاظ بالموظف
        });
        
        console.log('Employees after filtering:', processedEmployees.length);
        console.log('First few employees after filtering:', processedEmployees.slice(0, 3));
        console.log('=== End duplicate handling ===');
        
        this.processedEmployees = processedEmployees;
        
        // متابعة عملية الحفظ مباشرة بعد تجاهل المكررين
        const ignoredCount = duplicates.filter(dup => !dup.isFirst).length;
        const remainingCount = processedEmployees.length;
        
        if (remainingCount > 0) {
            this.showToast(`تم تجاهل ${ignoredCount} موظف مكرر! سيتم حفظ ${remainingCount} موظف...`, 'success');
            
            // متابعة عملية الحفظ مباشرة
            setTimeout(() => {
                this.continueSaveProcess();
            }, 1000);
        } else {
            this.showToast('لم يتم العثور على موظفين للحفظ!', 'error');
        }
    }

    continueSaveProcess() {
        // حفظ البيانات بدون فحص التكرار (لأنه تم إصلاحه بالفعل)
        console.log('=== Continuing save process ===');
        console.log('Processed employees count:', this.processedEmployees.length);
        console.log('Processed employees sample:', this.processedEmployees.slice(0, 3));
        
        if (this.processedEmployees.length === 0) {
            console.log('❌ No employees to save!');
            this.showToast('لا يوجد موظفين للحفظ!', 'error');
            return;
        }
        
        // تقسيم البيانات إلى دفعات
        const batches = this.createBatches(this.processedEmployees, 10);
        console.log('Created batches:', batches.length);
        console.log('First batch sample:', batches[0]?.slice(0, 2));
        
        let totalSaved = 0;
        let totalFailed = 0;
        const allErrors = [];
        
        // حفظ الدفعات
        this.saveBatchesSequentially(batches, totalSaved, totalFailed, allErrors);
    }

    async saveBatchesSequentially(batches, totalSaved, totalFailed, allErrors) {
        try {
            console.log('=== Starting batch save process ===');
            console.log('Total batches to process:', batches.length);
            
            this.showLoading();
            
            for (let i = 0; i < batches.length; i++) {
                const batch = batches[i];
                console.log(`Processing batch ${i + 1}/${batches.length} with ${batch.length} employees`);
                
                const result = await this.saveBatch(batch, i + 1, batches.length);
                
                totalSaved += result.saved;
                totalFailed += result.failed;
                allErrors.push(...result.errors);
                
                console.log(`Batch ${i + 1} result:`, result);
                
                // تحديث شريط التقدم
                this.updateBatchProgress(i + 1, batches.length);
            }
            
            console.log('=== Batch save completed ===');
            console.log('Total saved:', totalSaved);
            console.log('Total failed:', totalFailed);
            
            this.showSaveResults(totalSaved, totalFailed, allErrors);
            
        } catch (error) {
            console.error('خطأ في الحفظ:', error);
            this.showToast(`خطأ في الحفظ: ${error.message}`, 'error');
        } finally {
            this.hideLoading();
        }
    }
}

// تهيئة النظام عند تحميل الصفحة
document.addEventListener('DOMContentLoaded', function() {
    window.batchCreate = new BatchCreateAdvanced();
    
    // ربط دالة تبديل الثيم
    window.toggleTheme = () => window.batchCreate.toggleTheme();
});
