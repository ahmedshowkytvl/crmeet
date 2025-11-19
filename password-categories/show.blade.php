@extends('layouts.app')

@section('title', $passwordCategory->display_name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-tag me-2"></i>{{ $passwordCategory->display_name }}
    </h2>
    <div class="d-flex gap-2">
        <a href="{{ route('password-categories.edit', $passwordCategory) }}" class="btn btn-warning">
            <i class="fas fa-edit me-2"></i>{{ __('Edit Category') }}
        </a>
        <a href="{{ route('password-categories.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>{{ __('Back') }}
        </a>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <!-- Category Details -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Category Details') }}</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6>{{ __('Name') }}</h6>
                        <p class="text-muted">{{ $passwordCategory->name }}</p>
                        
                        @if($passwordCategory->name_ar)
                            <h6>{{ __('Name (Arabic)') }}</h6>
                            <p class="text-muted">{{ $passwordCategory->name_ar }}</p>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <h6>{{ __('Status') }}</h6>
                        @if($passwordCategory->is_active)
                            <span class="badge bg-success">{{ __('Active') }}</span>
                        @else
                            <span class="badge bg-secondary">{{ __('Inactive') }}</span>
                        @endif
                        
                        <h6 class="mt-3">{{ __('Sort Order') }}</h6>
                        <p class="text-muted">{{ $passwordCategory->sort_order }}</p>
                    </div>
                </div>
                
                @if($passwordCategory->description || $passwordCategory->description_ar)
                    <hr>
                    <h6>{{ __('Description') }}</h6>
                    <p class="text-muted">{{ $passwordCategory->display_description }}</p>
                @endif
                
                <hr>
                <div class="row">
                    <div class="col-md-6">
                        <h6>{{ __('Color') }}</h6>
                        <div class="d-flex align-items-center">
                            <div class="me-2" style="width: 20px; height: 20px; background-color: {{ $passwordCategory->color }}; border-radius: 4px;"></div>
                            <code>{{ $passwordCategory->color }}</code>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6>{{ __('Icon') }}</h6>
                        @if($passwordCategory->icon)
                            <img src="{{ $passwordCategory->icon }}" alt="{{ $passwordCategory->display_name }}" 
                                 style="width: 32px; height: 32px;">
                        @else
                            <div class="d-flex align-items-center justify-content-center" 
                                 style="width: 32px; height: 32px; background-color: {{ $passwordCategory->color }}; border-radius: 6px;">
                                <i class="fas fa-tag text-white"></i>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Password Accounts -->
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="mb-0">{{ __('Password Accounts') }}</h5>
                <span class="badge bg-primary">{{ $passwordAccounts->total() }} {{ __('Accounts') }}</span>
            </div>
            <div class="card-body">
                @if($passwordAccounts->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>{{ __('Account Name') }}</th>
                                    <th>{{ __('Email/Username') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Expires At') }}</th>
                                    <th>{{ __('Created By') }}</th>
                                    <th>{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($passwordAccounts as $account)
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                @if($account->icon)
                                                    <img src="{{ $account->icon }}" alt="{{ $account->display_name }}" 
                                                         class="me-2" style="width: 24px; height: 24px;">
                                                @else
                                                    <i class="fas fa-key me-2 text-muted"></i>
                                                @endif
                                                <div>
                                                    <strong>{{ $account->display_name }}</strong>
                                                    @if($account->requires_2fa)
                                                        <i class="fas fa-shield-alt text-warning ms-1" 
                                                           title="{{ __('Requires 2FA') }}"></i>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>{{ $account->email ?: '-' }}</td>
                                        <td>
                                            @if($account->isExpired())
                                                <span class="badge bg-danger">{{ __('Expired') }}</span>
                                            @elseif($account->isExpiringSoon())
                                                <span class="badge bg-warning">{{ __('Expiring Soon') }}</span>
                                            @elseif($account->is_shared)
                                                <span class="badge bg-info">{{ __('Shared') }}</span>
                                            @else
                                                <span class="badge bg-success">{{ __('Active') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($account->expires_at)
                                                {{ $account->expires_at->format('Y-m-d') }}
                                                @if($account->isExpiringSoon())
                                                    <i class="fas fa-exclamation-triangle text-warning ms-1"></i>
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>{{ $account->creator->name ?? '-' }}</td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('password-accounts.show', $account) }}" 
                                                   class="btn btn-sm btn-outline-primary" title="{{ __('View') }}">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('password-accounts.edit', $account) }}" 
                                                   class="btn btn-sm btn-outline-warning" title="{{ __('Edit') }}">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Pagination -->
                    <div class="d-flex justify-content-center">
                        {{ $passwordAccounts->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <i class="fas fa-key fa-3x text-muted mb-3"></i>
                        <h5 class="text-muted">{{ __('No password accounts in this category') }}</h5>
                        <a href="{{ route('password-accounts.create') }}" class="btn btn-primary mt-3">
                            <i class="fas fa-plus me-2"></i>{{ __('Create Password Account') }}
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    
    <div class="col-lg-4">
        <!-- Category Preview -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Category Preview') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-start mb-3">
                    @if($passwordCategory->icon)
                        <img src="{{ $passwordCategory->icon }}" alt="{{ $passwordCategory->display_name }}" 
                             class="me-3" style="width: 40px; height: 40px;">
                    @else
                        <div class="me-3 d-flex align-items-center justify-content-center" 
                             style="width: 40px; height: 40px; background-color: {{ $passwordCategory->color }}; border-radius: 8px;">
                            <i class="fas fa-tag text-white"></i>
                        </div>
                    @endif
                    <div class="flex-grow-1">
                        <h6 class="mb-1">{{ $passwordCategory->display_name }}</h6>
                        <span class="badge" style="background-color: {{ $passwordCategory->color }};">
                            {{ $passwordAccounts->total() }} {{ __('Accounts') }}
                        </span>
                    </div>
                </div>
                
                @if($passwordCategory->display_description)
                    <p class="text-muted small mb-0">
                        {{ $passwordCategory->display_description }}
                    </p>
                @endif
            </div>
        </div>
        
        <!-- Statistics -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Statistics') }}</h5>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-primary">{{ $passwordAccounts->total() }}</h4>
                        <small class="text-muted">{{ __('Total Accounts') }}</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-success">{{ $passwordAccounts->where('is_active', true)->count() }}</h4>
                        <small class="text-muted">{{ __('Active Accounts') }}</small>
                    </div>
                </div>
                <hr>
                <div class="row text-center">
                    <div class="col-6">
                        <h4 class="text-warning">{{ $passwordAccounts->where('is_expiring_soon')->count() }}</h4>
                        <small class="text-muted">{{ __('Expiring Soon') }}</small>
                    </div>
                    <div class="col-6">
                        <h4 class="text-info">{{ $passwordAccounts->where('is_shared', true)->count() }}</h4>
                        <small class="text-muted">{{ __('Shared Accounts') }}</small>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">{{ __('Quick Actions') }}</h5>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <a href="{{ route('password-accounts.create', ['category_id' => $passwordCategory->id]) }}" 
                       class="btn btn-primary">
                        <i class="fas fa-plus me-2"></i>{{ __('Add Account to Category') }}
                    </a>
                    <a href="{{ route('password-categories.edit', $passwordCategory) }}" 
                       class="btn btn-outline-warning">
                        <i class="fas fa-edit me-2"></i>{{ __('Edit Category') }}
                    </a>
                    <a href="{{ route('password-categories.index') }}" 
                       class="btn btn-outline-secondary">
                        <i class="fas fa-list me-2"></i>{{ __('View All Categories') }}
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection








