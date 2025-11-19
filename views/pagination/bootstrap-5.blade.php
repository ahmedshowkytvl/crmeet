@if ($paginator->hasPages())
    <nav aria-label="Pagination Navigation">
        <div class="pagination-info">
            <div class="info-text">
                <i class="fas fa-info-circle me-1"></i>
                @if(request('per_page') == 'all')
                    {{ __('messages.showing') }} {{ __('messages.all') }} {{ __('messages.results') }} ({{ $paginator->total() }} {{ __('messages.results') }})
                @else
                    {{ __('messages.showing') }} {{ $paginator->firstItem() }} {{ __('messages.to') }} {{ $paginator->lastItem() }} {{ __('messages.of') }} {{ $paginator->total() }} {{ __('messages.results') }}
                @endif
            </div>
            <div class="page-size-selector">
                <label for="perPage">{{ __('messages.showing') }}:</label>
                <select id="perPage" onchange="changePerPage(this.value)">
                    <option value="15" {{ request('per_page') == 15 || !request('per_page') ? 'selected' : '' }}>15</option>
                    <option value="25" {{ request('per_page') == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                    <option value="all" {{ request('per_page') == 'all' ? 'selected' : '' }}>{{ __('messages.all') }}</option>
                </select>
            </div>
        </div>
        
        @if(request('per_page') != 'all')
        <ul class="pagination">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="page-item disabled">
                    <span class="page-link">
                        <i class="fas fa-chevron-left me-1"></i>
                        {{ __('messages.previous') }}
                    </span>
                </li>
            @else
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">
                        <i class="fas fa-chevron-left me-1"></i>
                        {{ __('messages.previous') }}
                    </a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="page-item disabled">
                        <span class="page-link">{{ $element }}</span>
                    </li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="page-item active">
                                <span class="page-link">{{ $page }}</span>
                            </li>
                        @else
                            <li class="page-item">
                                <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li class="page-item">
                    <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">
                        {{ __('messages.next') }}
                        <i class="fas fa-chevron-right ms-1"></i>
                    </a>
                </li>
            @else
                <li class="page-item disabled">
                    <span class="page-link">
                        {{ __('messages.next') }}
                        <i class="fas fa-chevron-right ms-1"></i>
                    </span>
                </li>
            @endif
        </ul>
        @endif
    </nav>

    <script>
        function changePerPage(value) {
            const url = new URL(window.location);
            
            if (value === 'all') {
                url.searchParams.set('per_page', 'all');
                url.searchParams.delete('page'); // Reset to first page
            } else {
                url.searchParams.set('per_page', value);
                url.searchParams.delete('page'); // Reset to first page
            }
            
            window.location.href = url.toString();
        }
    </script>
@endif
