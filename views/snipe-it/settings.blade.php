@extends('layouts.app')

@section('title', 'إعدادات التكامل مع Snipe-IT')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('snipe-it.index') }}">التكامل مع Snipe-IT</a></li>
                        <li class="breadcrumb-item active">الإعدادات</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    <i class="fas fa-cog me-2"></i>
                    إعدادات التكامل مع Snipe-IT
                </h4>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-server me-2"></i>
                        إعدادات الاتصال
                    </h5>
                </div>
                <div class="card-body">
                    <form id="settingsForm">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رابط API</label>
                                <input type="url" class="form-control" name="api_url" value="{{ $settings['api_url'] ?? '' }}" required>
                                <small class="form-text text-muted">مثال: http://127.0.0.1</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رمز API</label>
                                <input type="password" class="form-control" name="api_token" value="{{ $settings['api_token'] ?? '' }}" required>
                                <small class="form-text text-muted">رمز API من Snipe-IT</small>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3">
                            <i class="fas fa-sync me-2"></i>
                            إعدادات المزامنة التلقائية
                        </h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="auto_sync_enabled" {{ ($settings['auto_sync_enabled'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label">تفعيل المزامنة التلقائية</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">فترة المزامنة (بالدقائق)</label>
                                <input type="number" class="form-control" name="sync_interval" value="{{ $settings['sync_interval'] ?? 60 }}" min="1" max="1440">
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3">
                            <i class="fas fa-list me-2"></i>
                            خيارات المزامنة
                        </h6>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="sync_assets" {{ ($settings['sync_assets'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">مزامنة الأصول</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="sync_users" {{ ($settings['sync_users'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">مزامنة المستخدمين</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="sync_categories" {{ ($settings['sync_categories'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">مزامنة الفئات</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="sync_locations" {{ ($settings['sync_locations'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">مزامنة المواقع</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="sync_models" {{ ($settings['sync_models'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">مزامنة النماذج</label>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="sync_suppliers" {{ ($settings['sync_suppliers'] ?? true) ? 'checked' : '' }}>
                                    <label class="form-check-label">مزامنة الموردين</label>
                                </div>
                            </div>
                        </div>

                        <hr>

                        <h6 class="mb-3">
                            <i class="fas fa-webhook me-2"></i>
                            إعدادات Webhook
                        </h6>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" name="webhook_enabled" {{ ($settings['webhook_enabled'] ?? false) ? 'checked' : '' }}>
                                    <label class="form-check-label">تفعيل Webhook</label>
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">رابط Webhook</label>
                                <input type="url" class="form-control" name="webhook_url" value="{{ $settings['webhook_url'] ?? '' }}">
                                <small class="form-text text-muted">رابط لاستقبال التحديثات من Snipe-IT</small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save me-2"></i>
                                    حفظ الإعدادات
                                </button>
                                <button type="button" class="btn btn-outline-danger" id="resetSettingsBtn">
                                    <i class="fas fa-undo me-2"></i>
                                    إعادة تعيين
                                </button>
                                <button type="button" class="btn btn-outline-info" id="testConnectionBtn">
                                    <i class="fas fa-wifi me-2"></i>
                                    اختبار الاتصال
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-info-circle me-2"></i>
                        معلومات مفيدة
                    </h5>
                </div>
                <div class="card-body">
                    <div class="alert alert-info">
                        <h6><i class="fas fa-lightbulb me-2"></i>نصائح:</h6>
                        <ul class="mb-0">
                            <li>تأكد من صحة رابط API ورمز API</li>
                            <li>يمكنك اختبار الاتصال قبل حفظ الإعدادات</li>
                            <li>المزامنة التلقائية تعمل في الخلفية</li>
                            <li>يمكنك تخصيص ما تريد مزامنته</li>
                        </ul>
                    </div>

                    <div class="alert alert-warning">
                        <h6><i class="fas fa-exclamation-triangle me-2"></i>تحذير:</h6>
                        <p class="mb-0">تغيير الإعدادات قد يؤثر على المزامنة التلقائية. تأكد من صحة البيانات قبل الحفظ.</p>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-question-circle me-2"></i>
                        مساعدة
                    </h5>
                </div>
                <div class="card-body">
                    <p>للحصول على مساعدة في إعداد التكامل مع Snipe-IT:</p>
                    <ul>
                        <li>راجع وثائق Snipe-IT API</li>
                        <li>تأكد من تفعيل API في Snipe-IT</li>
                        <li>تحقق من إعدادات الشبكة والجدار الناري</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // حفظ الإعدادات
    $('#settingsForm').submit(function(e) {
        e.preventDefault();
        saveSettings();
    });

    // إعادة تعيين الإعدادات
    $('#resetSettingsBtn').click(function() {
        resetSettings();
    });

    // اختبار الاتصال
    $('#testConnectionBtn').click(function() {
        testConnection();
    });
});

function saveSettings() {
    const formData = new FormData(document.getElementById('settingsForm'));
    const data = Object.fromEntries(formData.entries());
    
    // تحويل القيم المنطقية
    data.auto_sync_enabled = data.auto_sync_enabled === 'on';
    data.sync_assets = data.sync_assets === 'on';
    data.sync_users = data.sync_users === 'on';
    data.sync_categories = data.sync_categories === 'on';
    data.sync_locations = data.sync_locations === 'on';
    data.sync_models = data.sync_models === 'on';
    data.sync_suppliers = data.sync_suppliers === 'on';
    data.webhook_enabled = data.webhook_enabled === 'on';

    showLoading('جاري حفظ الإعدادات...');

    $.ajax({
        url: '{{ route("snipe-it.save-settings") }}',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        data: data,
        success: function(response) {
            hideLoading();
            if (response.success) {
                showSuccess('تم حفظ الإعدادات بنجاح');
            } else {
                showError(response.message || 'فشل في حفظ الإعدادات');
            }
        },
        error: function(xhr) {
            hideLoading();
            const response = xhr.responseJSON;
            if (response && response.errors) {
                let errorMessage = 'خطأ في البيانات:\n';
                Object.keys(response.errors).forEach(key => {
                    errorMessage += `- ${response.errors[key].join(', ')}\n`;
                });
                showError(errorMessage);
            } else {
                showError(response?.message || 'حدث خطأ في حفظ الإعدادات');
            }
        }
    });
}

function resetSettings() {
    Swal.fire({
        title: 'تأكيد إعادة التعيين',
        text: 'هل أنت متأكد من إعادة تعيين جميع الإعدادات إلى القيم الافتراضية؟',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'نعم، إعادة تعيين',
        cancelButtonText: 'إلغاء'
    }).then((result) => {
        if (result.isConfirmed) {
            showLoading('جاري إعادة تعيين الإعدادات...');

            $.ajax({
                url: '{{ route("snipe-it.reset-settings") }}',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    hideLoading();
                    if (response.success) {
                        showSuccess('تم إعادة تعيين الإعدادات بنجاح');
                        location.reload();
                    } else {
                        showError(response.message || 'فشل في إعادة تعيين الإعدادات');
                    }
                },
                error: function(xhr) {
                    hideLoading();
                    const response = xhr.responseJSON;
                    showError(response?.message || 'حدث خطأ في إعادة تعيين الإعدادات');
                }
            });
        }
    });
}

function testConnection() {
    const apiUrl = document.querySelector('input[name="api_url"]').value;
    const apiToken = document.querySelector('input[name="api_token"]').value;

    if (!apiUrl || !apiToken) {
        showError('يرجى إدخال رابط API ورمز API أولاً');
        return;
    }

    showLoading('جاري اختبار الاتصال...');

    $.ajax({
        url: '{{ route("snipe-it.test-connection") }}',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            hideLoading();
            if (response.success) {
                showSuccess('تم اختبار الاتصال بنجاح');
            } else {
                showError(response.message || 'فشل في اختبار الاتصال');
            }
        },
        error: function(xhr) {
            hideLoading();
            const response = xhr.responseJSON;
            showError(response?.message || 'حدث خطأ في اختبار الاتصال');
        }
    });
}

function showLoading(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: message,
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
    }
}

function hideLoading() {
    if (typeof Swal !== 'undefined') {
        Swal.close();
    }
}

function showSuccess(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'success',
            title: 'نجح!',
            text: message,
            timer: 3000,
            showConfirmButton: false
        });
    } else {
        alert(message);
    }
}

function showError(message) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            icon: 'error',
            title: 'خطأ!',
            text: message
        });
    } else {
        alert(message);
    }
}
</script>
@endsection
