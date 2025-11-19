@extends('layouts.app')

@section('title', __('messages.template_details'))

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <div class="col-md-8 mx-auto">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">
                        <i class="fas fa-clipboard-list me-2"></i>
                        {{ __('messages.template_details') }}
                    </h4>
                    @can('manage-tasks')
                        <div class="btn-group">
                            <a href="{{ route('task-templates.edit', $taskTemplate) }}" class="btn btn-primary">
                                <i class="fas fa-edit me-2"></i>
                                تعديل
                            </a>
                            <a href="{{ route('task-templates.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-right me-2"></i>
                                العودة للقائمة
                            </a>
                        </div>
                    @endcan
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="text-primary">معلومات أساسية</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>الاسم (إنجليزي):</strong></td>
                                    <td>{{ $taskTemplate->name }}</td>
                                </tr>
                                @if($taskTemplate->name_ar)
                                    <tr>
                                        <td><strong>الاسم (عربي):</strong></td>
                                        <td>{{ $taskTemplate->name_ar }}</td>
                                    </tr>
                                @endif
                                <tr>
                                    <td><strong>القسم:</strong></td>
                                    <td>
                                        <span class="badge bg-secondary">{{ $taskTemplate->department_name }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>الوقت المقدر:</strong></td>
                                    <td>
                                        <span class="badge bg-info">{{ $taskTemplate->estimated_time }} ساعة</span>
                                        <small class="text-muted">({{ $taskTemplate->estimated_time_in_minutes }} دقيقة)</small>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>الحالة:</strong></td>
                                    <td>
                                        @if($taskTemplate->is_active)
                                            <span class="badge bg-success">نشط</span>
                                        @else
                                            <span class="badge bg-warning">غير نشط</span>
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="text-primary">إحصائيات الاستخدام</h5>
                            <table class="table table-borderless">
                                <tr>
                                    <td><strong>عدد الاستخدامات:</strong></td>
                                    <td>
                                        <span class="badge bg-primary">{{ $taskTemplate->usage_count }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>إجمالي الوقت المقدر:</strong></td>
                                    <td>
                                        <span class="badge bg-success">{{ $taskTemplate->total_estimated_time }} ساعة</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>تاريخ الإنشاء:</strong></td>
                                    <td>{{ $taskTemplate->created_at->format('Y-m-d H:i') }}</td>
                                </tr>
                                <tr>
                                    <td><strong>آخر تحديث:</strong></td>
                                    <td>{{ $taskTemplate->updated_at->format('Y-m-d H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    @if($taskTemplate->description || $taskTemplate->description_ar)
                        <hr>
                        <h5 class="text-primary">الوصف</h5>
                        <div class="row">
                            @if($taskTemplate->description)
                                <div class="col-md-6">
                                    <h6>الوصف (إنجليزي)</h6>
                                    <p class="text-muted">{{ $taskTemplate->description }}</p>
                                </div>
                            @endif
                            @if($taskTemplate->description_ar)
                                <div class="col-md-6">
                                    <h6>الوصف (عربي)</h6>
                                    <p class="text-muted">{{ $taskTemplate->description_ar }}</p>
                                </div>
                            @endif
                        </div>
                    @endif

                    @if($taskTemplate->tasks->count() > 0)
                        <hr>
                        <h5 class="text-primary">المهام التي تستخدم هذا القالب</h5>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>رقم المهمة</th>
                                        <th>العنوان</th>
                                        <th>المكلف</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الإنشاء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($taskTemplate->tasks->take(10) as $task)
                                        <tr>
                                            <td>#{{ $task->id }}</td>
                                            <td>{{ Str::limit($task->display_title, 30) }}</td>
                                            <td>{{ $task->assignedTo->name ?? 'غير محدد' }}</td>
                                            <td>
                                                @switch($task->status)
                                                    @case('completed')
                                                        <span class="badge bg-success">مكتملة</span>
                                                        @break
                                                    @case('in_progress')
                                                        <span class="badge bg-primary">قيد التنفيذ</span>
                                                        @break
                                                    @case('pending')
                                                        <span class="badge bg-warning">في الانتظار</span>
                                                        @break
                                                    @default
                                                        <span class="badge bg-secondary">{{ $task->status }}</span>
                                                @endswitch
                                            </td>
                                            <td>{{ $task->created_at->format('Y-m-d') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($taskTemplate->tasks->count() > 10)
                            <p class="text-muted">عرض 10 من أصل {{ $taskTemplate->tasks->count() }} مهمة</p>
                        @endif
                    @else
                        <hr>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            لم يتم استخدام هذا القالب في أي مهمة بعد.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
