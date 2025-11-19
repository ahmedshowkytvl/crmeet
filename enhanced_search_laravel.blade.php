{{-- Enhanced Search Form for Laravel --}}
<div class="enhanced-search-container">
    <form method="GET" action="{{ route('users.index') }}" class="enhanced-search-form">
        <input type="text" 
               class="enhanced-search-input" 
               name="search" 
               value="{{ request('search') }}" 
               placeholder="{{ __('messages.search_users') }}..." 
               autocomplete="off">
        <button class="enhanced-search-btn" type="submit" title="{{ __('messages.search') }}">
            <i class="fas fa-search search-icon"></i>
        </button>
        @if(request('search'))
            <a href="{{ route('users.index') }}" class="enhanced-search-clear" title="{{ __('messages.clear_search') }}">
                <i class="fas fa-times"></i>
            </a>
        @endif
    </form>
</div>

{{-- Include CSS --}}
<link rel="stylesheet" href="{{ asset('css/enhanced-search-simple.css') }}">

{{-- Include JavaScript --}}
<script src="{{ asset('js/enhanced-search-simple.js') }}"></script>
