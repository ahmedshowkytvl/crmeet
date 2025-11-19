@extends('layouts.app')

@section('title', 'لوحة المتصدرين - Zoho')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-2">
                <i class="fas fa-trophy me-2"></i>
                لوحة المتصدرين
            </h2>
            <p class="text-muted">أفضل الموظفين أداءً في Zoho Desk</p>
        </div>
        <div class="col-md-4">
            <form method="GET" class="row g-2">
                <div class="col-md-12">
                    <select name="period" class="form-select" onchange="this.form.submit()">
                        <option value="daily" {{ $periodType == 'daily' ? 'selected' : '' }}>يومي</option>
                        <option value="weekly" {{ $periodType == 'weekly' ? 'selected' : '' }}>أسبوعي</option>
                        <option value="monthly" {{ $periodType == 'monthly' ? 'selected' : '' }}>شهري</option>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <!-- Podium (Top 3) -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-lg" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="card-body py-5">
                    <div class="row align-items-end justify-content-center">
                        @if($topPerformers->count() >= 2)
                        <!-- Second Place -->
                        <div class="col-md-3 text-center mb-4 mb-md-0">
                            <div class="position-relative">
                                <div class="mb-3">
                                    @if($topPerformers[1]->user->profile_picture)
                                        <img src="{{ asset('storage/' . $topPerformers[1]->user->profile_picture) }}" 
                                             class="rounded-circle border border-5 border-light shadow" 
                                             width="120" height="120" 
                                             alt="{{ $topPerformers[1]->user->name }}">
                                    @else
                                        <div class="rounded-circle border border-5 border-light shadow bg-secondary d-inline-flex align-items-center justify-content-center" 
                                             style="width: 120px; height: 120px;">
                                            <i class="fas fa-user fa-3x text-white"></i>
                                        </div>
                                    @endif
                                    <div class="position-absolute top-0 start-50 translate-middle">
                                        <i class="fas fa-medal fa-2x text-secondary"></i>
                                    </div>
                                </div>
                                <h5 class="text-white mb-1">{{ $topPerformers[1]->user->name }}</h5>
                                <p class="text-white-50 mb-2">{{ $topPerformers[1]->user->position ?? 'موظف' }}</p>
                                <div class="bg-white bg-opacity-25 rounded p-3">
                                    <h3 class="text-white mb-0">{{ number_format($topPerformers[1]->performance_score, 0) }}</h3>
                                    <small class="text-white-50">نقطة</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($topPerformers->count() >= 1)
                        <!-- First Place -->
                        <div class="col-md-3 text-center mb-4 mb-md-0">
                            <div class="position-relative">
                                <div class="mb-3">
                                    @if($topPerformers[0]->user->profile_picture)
                                        <img src="{{ asset('storage/' . $topPerformers[0]->user->profile_picture) }}" 
                                             class="rounded-circle border border-5 border-warning shadow" 
                                             width="150" height="150" 
                                             alt="{{ $topPerformers[0]->user->name }}">
                                    @else
                                        <div class="rounded-circle border border-5 border-warning shadow bg-primary d-inline-flex align-items-center justify-content-center" 
                                             style="width: 150px; height: 150px;">
                                            <i class="fas fa-user fa-4x text-white"></i>
                                        </div>
                                    @endif
                                    <div class="position-absolute top-0 start-50 translate-middle">
                                        <i class="fas fa-crown fa-3x text-warning"></i>
                                    </div>
                                </div>
                                <h4 class="text-white mb-1">{{ $topPerformers[0]->user->name }}</h4>
                                <p class="text-white-50 mb-2">{{ $topPerformers[0]->user->position ?? 'موظف' }}</p>
                                <div class="bg-white bg-opacity-25 rounded p-3">
                                    <h2 class="text-white mb-0">{{ number_format($topPerformers[0]->performance_score, 0) }}</h2>
                                    <small class="text-white-50">نقطة</small>
                                </div>
                            </div>
                        </div>
                        @endif

                        @if($topPerformers->count() >= 3)
                        <!-- Third Place -->
                        <div class="col-md-3 text-center mb-4 mb-md-0">
                            <div class="position-relative">
                                <div class="mb-3">
                                    @if($topPerformers[2]->user->profile_picture)
                                        <img src="{{ asset('storage/' . $topPerformers[2]->user->profile_picture) }}" 
                                             class="rounded-circle border border-5 border-light shadow" 
                                             width="120" height="120" 
                                             alt="{{ $topPerformers[2]->user->name }}">
                                    @else
                                        <div class="rounded-circle border border-5 border-light shadow bg-info d-inline-flex align-items-center justify-content-center" 
                                             style="width: 120px; height: 120px;">
                                            <i class="fas fa-user fa-3x text-white"></i>
                                        </div>
                                    @endif
                                    <div class="position-absolute top-0 start-50 translate-middle">
                                        <i class="fas fa-medal fa-2x text-success"></i>
                                    </div>
                                </div>
                                <h5 class="text-white mb-1">{{ $topPerformers[2]->user->name }}</h5>
                                <p class="text-white-50 mb-2">{{ $topPerformers[2]->user->position ?? 'موظف' }}</p>
                                <div class="bg-white bg-opacity-25 rounded p-3">
                                    <h3 class="text-white mb-0">{{ number_format($topPerformers[2]->performance_score, 0) }}</h3>
                                    <small class="text-white-50">نقطة</small>
                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Full Leaderboard -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list-ol me-2"></i>
                        الترتيب الكامل
                    </h5>
                </div>
                <div class="card-body">
                    <div class="list-group list-group-flush">
                        @foreach($topPerformers as $index => $stat)
                        <div class="list-group-item border-0 py-3">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="display-6 fw-bold 
                                        @if($index < 3) text-warning
                                        @else text-muted
                                        @endif">
                                        #{{ $index + 1 }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    @if($stat->user->profile_picture)
                                        <img src="{{ asset('storage/' . $stat->user->profile_picture) }}" 
                                             class="rounded-circle" 
                                             width="50" height="50" 
                                             alt="{{ $stat->user->name }}">
                                    @else
                                        <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-user text-white"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="col">
                                    <h6 class="mb-0">{{ $stat->user->name }}</h6>
                                    <small class="text-muted">{{ $stat->user->department->name ?? 'غير محدد' }}</small>
                                </div>
                                <div class="col-auto text-center">
                                    <h5 class="mb-0">{{ $stat->tickets_closed_count }}</h5>
                                    <small class="text-muted">تذكرة</small>
                                </div>
                                <div class="col-auto text-center">
                                    <h5 class="mb-0">{{ number_format($stat->tickets_per_hour, 1) }}</h5>
                                    <small class="text-muted">TPH</small>
                                </div>
                                <div class="col-auto">
                                    <div class="text-center">
                                        <h4 class="mb-0 text-{{ $stat->performance_score >= 80 ? 'success' : ($stat->performance_score >= 60 ? 'warning' : 'danger') }}">
                                            {{ number_format($stat->performance_score, 0) }}
                                        </h4>
                                        <small class="text-muted">نقطة</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

