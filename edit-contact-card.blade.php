@extends('layouts.app')

@section('title', 'تعديل بطاقة الاتصال - ' . $user->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-id-card me-2"></i>تعديل بطاقة الاتصال</h2>
    <div>
        <a href="{{ route('users.contact-card', $user) }}" class="btn btn-info me-2">
            <i class="fas fa-eye me-2"></i>عرض البطاقة
        </a>
        <a href="{{ route('users.show', $user) }}" class="btn btn-secondary">
            <i class="fas fa-arrow-right me-2"></i>العودة
        </a>
    </div>
</div>

<div class="card">
    <div class="card-body">
        <form action="{{ route('users.update-contact-card', $user) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Contact Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-phone me-2"></i>معلومات الاتصال
                    </h5>
                </div>
                
                <div class="col-12 mb-3">
                    <label class="form-label">هاتف العمل</label>
                    <div id="workPhonesContainer">
                        @php
                            $workPhones = $user->phones->filter(function($phone) {
                                return $phone->phoneType && $phone->phoneType->slug === 'work';
                            });
                            // If no work phones exist but phone_work field has value, create a temporary entry
                            if ($workPhones->isEmpty() && $user->phone_work) {
                                $workPhones = collect([(object)[
                                    'id' => 'temp_0',
                                    'phone_number' => $user->phone_work,
                                    'is_primary' => true
                                ]]);
                            }
                        @endphp
                        
                        @if($workPhones->isEmpty())
                            <div class="work-phone-row mb-2">
                                <div class="row g-2">
                                    <div class="col-md-10">
                                        <input type="text" class="form-control work-phone-input" name="work_phones[0][number]" placeholder="هاتف العمل" value="{{ old('work_phones.0.number') }}">
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-check">
                                            <input class="form-check-input main-phone-radio" type="radio" name="main_work_phone" value="0" id="main_phone_0" checked>
                                            <label class="form-check-label" for="main_phone_0">
                                                <span class="badge bg-primary">رئيسي</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @else
                            @foreach($workPhones as $index => $phone)
                                <div class="work-phone-row mb-2">
                                    <div class="row g-2">
                                        <div class="col-md-10">
                                            <input type="hidden" name="work_phones[{{ $index }}][id]" value="{{ $phone->id }}">
                                            <input type="text" class="form-control work-phone-input" name="work_phones[{{ $index }}][number]" placeholder="هاتف العمل" value="{{ old("work_phones.$index.number", $phone->phone_number) }}">
                                        </div>
                                        <div class="col-md-2">
                                            <div class="form-check">
                                                <input class="form-check-input main-phone-radio" type="radio" name="main_work_phone" value="{{ $index }}" id="main_phone_{{ $index }}" {{ $phone->is_primary || ($index == 0 && !$workPhones->contains('is_primary', true)) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="main_phone_{{ $index }}">
                                                    <span class="badge {{ ($phone->is_primary || ($index == 0 && !$workPhones->contains('is_primary', true))) ? 'bg-success' : 'bg-secondary' }}">{{ ($phone->is_primary || ($index == 0 && !$workPhones->contains('is_primary', true))) ? 'رئيسي' : 'عادي' }}</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                    @if($index > 0)
                                        <button type="button" class="btn btn-sm btn-danger remove-phone-btn mt-1">
                                            <i class="fas fa-trash"></i> حذف
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addWorkPhoneBtn">
                        <i class="fas fa-plus"></i> إضافة رقم آخر
                    </button>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="phone_home" class="form-label">هاتف المنزل</label>
                    <input type="text" class="form-control" id="phone_home" name="phone_home" value="{{ old('phone_home', $user->phone_home) }}">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="phone_personal" class="form-label">هاتف شخصي</label>
                    <input type="text" class="form-control" id="phone_personal" name="phone_personal" value="{{ old('phone_personal', $user->phone_personal) }}">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="phone_mobile" class="form-label">الهاتف المحمول</label>
                    <input type="text" class="form-control" id="phone_mobile" name="phone_mobile" value="{{ old('phone_mobile', $user->phone_mobile) }}">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="phone_emergency" class="form-label">هاتف الطوارئ</label>
                    <input type="text" class="form-control" id="phone_emergency" name="phone_emergency" value="{{ old('phone_emergency', $user->phone_emergency) }}">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="extension" class="form-label">التحويلة</label>
                    <input type="text" class="form-control" id="extension" name="extension" value="{{ old('extension', $user->extension) }}">
                </div>
            </div>

            <!-- Social Media -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-share-alt me-2"></i>وسائل التواصل الاجتماعي
                    </h5>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="whatsapp" class="form-label">واتساب</label>
                    <input type="text" class="form-control" id="whatsapp" name="whatsapp" value="{{ old('whatsapp', $user->whatsapp) }}" placeholder="رقم الهاتف">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="telegram" class="form-label">تيليجرام</label>
                    <input type="text" class="form-control" id="telegram" name="telegram" value="{{ old('telegram', $user->telegram) }}" placeholder="اسم المستخدم">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="skype" class="form-label">سكايب</label>
                    <input type="text" class="form-control" id="skype" name="skype" value="{{ old('skype', $user->skype) }}" placeholder="اسم المستخدم">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="facebook" class="form-label">فيسبوك</label>
                    <input type="text" class="form-control" id="facebook" name="facebook" value="{{ old('facebook', $user->facebook) }}" placeholder="رابط الملف الشخصي">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="instagram" class="form-label">إنستجرام</label>
                    <input type="text" class="form-control" id="instagram" name="instagram" value="{{ old('instagram', $user->instagram) }}" placeholder="اسم المستخدم">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="linkedin_url" class="form-label">لينكد إن</label>
                    <input type="text" class="form-control" id="linkedin_url" name="linkedin_url" value="{{ old('linkedin_url', $user->linkedin_url) }}" placeholder="رابط الملف الشخصي">
                </div>
            </div>

            <!-- Work Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-briefcase me-2"></i>معلومات العمل
                    </h5>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="job_title" class="form-label">المسمى الوظيفي</label>
                    <input type="text" class="form-control" id="job_title" name="job_title" value="{{ old('job_title', $user->job_title) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="employee_id" class="form-label">رقم الموظف</label>
                    <input type="text" class="form-control" id="employee_id" name="employee_id" value="{{ old('employee_id', $user->employee_id) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="hire_date" class="form-label">تاريخ التعيين</label>
                    <input type="date" class="form-control" id="hire_date" name="hire_date" value="{{ old('hire_date', $user->hire_date?->format('Y-m-d')) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="work_location" class="form-label">موقع العمل</label>
                    <input type="text" class="form-control" id="work_location" name="work_location" value="{{ old('work_location', $user->work_location) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="office_room" class="form-label">المكتب</label>
                    <input type="text" class="form-control" id="office_room" name="office_room" value="{{ old('office_room', $user->office_room) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="company" class="form-label">الشركة</label>
                    <input type="text" class="form-control" id="company" name="company" value="{{ old('company', $user->company) }}">
                </div>
            </div>

            <!-- Personal Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-user me-2"></i>المعلومات الشخصية
                    </h5>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="birth_date" class="form-label">تاريخ الميلاد</label>
                    <input type="date" class="form-control" id="birth_date" name="birth_date" value="{{ old('birth_date', $user->birth_date?->format('Y-m-d')) }}">
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="nationality" class="form-label">الجنسية</label>
                    <input type="text" class="form-control" id="nationality" name="nationality" value="{{ old('nationality', $user->nationality) }}">
                </div>
                
                <div class="col-md-12 mb-3">
                    <label for="address" class="form-label">العنوان</label>
                    <textarea class="form-control" id="address" name="address" rows="2">{{ old('address', $user->address) }}</textarea>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="city" class="form-label">المدينة</label>
                    <input type="text" class="form-control" id="city" name="city" value="{{ old('city', $user->city) }}">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="country" class="form-label">البلد</label>
                    <input type="text" class="form-control" id="country" name="country" value="{{ old('country', $user->country) }}">
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="postal_code" class="form-label">الرمز البريدي</label>
                    <input type="text" class="form-control" id="postal_code" name="postal_code" value="{{ old('postal_code', $user->postal_code) }}">
                </div>
            </div>

            <!-- Additional Information -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-info-circle me-2"></i>معلومات إضافية
                    </h5>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label for="bio" class="form-label">نبذة شخصية</label>
                    <textarea class="form-control" id="bio" name="bio" rows="3">{{ old('bio', $user->bio) }}</textarea>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="skills" class="form-label">المهارات (مفصولة بفواصل)</label>
                    <textarea class="form-control" id="skills" name="skills" rows="3" placeholder="مثال: PHP, Laravel, JavaScript">{{ old('skills', is_array($user->skills) ? implode(', ', $user->skills) : $user->skills) }}</textarea>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="interests" class="form-label">الاهتمامات (مفصولة بفواصل)</label>
                    <textarea class="form-control" id="interests" name="interests" rows="3" placeholder="مثال: القراءة, الرياضة, السفر">{{ old('interests', is_array($user->interests) ? implode(', ', $user->interests) : $user->interests) }}</textarea>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="languages" class="form-label">اللغات (مفصولة بفواصل)</label>
                    <textarea class="form-control" id="languages" name="languages" rows="3" placeholder="مثال: العربية, الإنجليزية, الفرنسية">{{ old('languages', is_array($user->languages) ? implode(', ', $user->languages) : $user->languages) }}</textarea>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="timezone" class="form-label">المنطقة الزمنية</label>
                    <select class="form-select" id="timezone" name="timezone">
                        <option value="">اختر المنطقة الزمنية</option>
                        <option value="Asia/Riyadh" {{ old('timezone', $user->timezone) == 'Asia/Riyadh' ? 'selected' : '' }}>الرياض (GMT+3)</option>
                        <option value="Asia/Dubai" {{ old('timezone', $user->timezone) == 'Asia/Dubai' ? 'selected' : '' }}>دبي (GMT+4)</option>
                        <option value="Europe/London" {{ old('timezone', $user->timezone) == 'Europe/London' ? 'selected' : '' }}>لندن (GMT+0)</option>
                        <option value="America/New_York" {{ old('timezone', $user->timezone) == 'America/New_York' ? 'selected' : '' }}>نيويورك (GMT-5)</option>
                    </select>
                </div>
                
                <div class="col-md-12 mb-3">
                    <label for="notes" class="form-label">ملاحظات</label>
                    <textarea class="form-control" id="notes" name="notes" rows="3">{{ old('notes', $user->notes) }}</textarea>
                </div>
            </div>

            <!-- Profile Image -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-image me-2"></i>صورة الملف الشخصي
                    </h5>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="profile_photo" class="form-label">رفع صورة</label>
                    <input type="file" class="form-control" id="profile_photo" name="profile_photo" accept="image/*">
                    @if($user->profile_photo)
                        <div class="mt-2">
                            <img src="{{ asset('storage/' . $user->profile_photo) }}" alt="الصورة الحالية" style="max-width: 100px; max-height: 100px;" class="rounded">
                        </div>
                    @endif
                </div>
            </div>

            <!-- Privacy Settings -->
            <div class="row mb-4">
                <div class="col-12">
                    <h5 class="text-primary mb-3">
                        <i class="fas fa-shield-alt me-2"></i>إعدادات الخصوصية
                    </h5>
                </div>
                
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="show_phone_work" name="show_phone_work" {{ old('show_phone_work', $user->show_phone_work) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_phone_work">إظهار هاتف العمل</label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="show_phone_personal" name="show_phone_personal" {{ old('show_phone_personal', $user->show_phone_personal) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_phone_personal">إظهار الهاتف الشخصي</label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="show_phone_mobile" name="show_phone_mobile" {{ old('show_phone_mobile', $user->show_phone_mobile) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_phone_mobile">إظهار الهاتف المحمول</label>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="show_email" name="show_email" {{ old('show_email', $user->show_email) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_email">إظهار الإيميل</label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="show_address" name="show_address" {{ old('show_address', $user->show_address) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_address">إظهار العنوان</label>
                    </div>
                    
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" id="show_social_media" name="show_social_media" {{ old('show_social_media', $user->show_social_media) ? 'checked' : '' }}>
                        <label class="form-check-label" for="show_social_media">إظهار وسائل التواصل الاجتماعي</label>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>حفظ التغييرات
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Work phones management
    const workPhonesContainer = document.getElementById('workPhonesContainer');
    const addWorkPhoneBtn = document.getElementById('addWorkPhoneBtn');
    
    // Initialize index based on existing rows
    let workPhoneIndex = workPhonesContainer.querySelectorAll('.work-phone-row').length;
    
    // Add new work phone
    if (addWorkPhoneBtn) {
        addWorkPhoneBtn.addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.className = 'work-phone-row mb-2';
            newRow.innerHTML = `
                <div class="row g-2">
                    <div class="col-md-10">
                        <input type="text" class="form-control work-phone-input" name="work_phones[${workPhoneIndex}][number]" placeholder="هاتف العمل">
                    </div>
                    <div class="col-md-2">
                        <div class="form-check">
                            <input class="form-check-input main-phone-radio" type="radio" name="main_work_phone" value="${workPhoneIndex}" id="main_phone_${workPhoneIndex}">
                            <label class="form-check-label" for="main_phone_${workPhoneIndex}">
                                <span class="badge bg-secondary">عادي</span>
                            </label>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-danger remove-phone-btn mt-1">
                    <i class="fas fa-trash"></i> حذف
                </button>
            `;
            workPhonesContainer.appendChild(newRow);
            
            // Update radio buttons to change badge color
            const radioButtons = workPhonesContainer.querySelectorAll('.main-phone-radio');
            radioButtons.forEach(radio => {
                radio.addEventListener('change', updateBadges);
            });
            
            workPhoneIndex++;
        });
    }
    
    // Update badge colors when radio changes
    function updateBadges() {
        const rows = workPhonesContainer.querySelectorAll('.work-phone-row');
        const radios = workPhonesContainer.querySelectorAll('.main-phone-radio');
        
        radios.forEach((radio, index) => {
            const row = rows[index];
            if (row) {
                const badge = row.querySelector('.badge');
                if (badge) {
                    if (radio.checked) {
                        badge.className = 'badge bg-success';
                        badge.textContent = 'رئيسي';
                    } else {
                        badge.className = 'badge bg-secondary';
                        badge.textContent = 'عادي';
                    }
                }
            }
        });
    }
    
    // Add event listeners to existing radio buttons
    const radioButtons = workPhonesContainer.querySelectorAll('.main-phone-radio');
    radioButtons.forEach(radio => {
        radio.addEventListener('change', updateBadges);
    });
    
    // Remove work phone
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-phone-btn')) {
            const row = e.target.closest('.work-phone-row');
            const allRows = workPhonesContainer.querySelectorAll('.work-phone-row');
            if (allRows.length > 1) {
                row.remove();
                updatePhoneIndexes();
            } else {
                alert('يجب أن يكون هناك رقم واحد على الأقل');
            }
        }
    });
    
    function updatePhoneIndexes() {
        const rows = workPhonesContainer.querySelectorAll('.work-phone-row');
        rows.forEach((row, index) => {
            const input = row.querySelector('input[type="text"]');
            const hiddenInput = row.querySelector('input[type="hidden"]');
            const radio = row.querySelector('input[type="radio"]');
            const label = row.querySelector('label');
            
            if (input && input.name.includes('work_phones')) {
                input.name = `work_phones[${index}][number]`;
            }
            if (hiddenInput && hiddenInput.name.includes('work_phones')) {
                hiddenInput.name = `work_phones[${index}][id]`;
            }
            if (radio) {
                radio.value = index;
                radio.id = `main_phone_${index}`;
            }
            if (label) {
                label.setAttribute('for', `main_phone_${index}`);
            }
        });
        workPhoneIndex = rows.length;
        updateBadges();
    }
});
</script>
@endsection
