@if ($paginator->hasPages())
    <nav aria-label="" class='flex justify-center mt-5'>
        <ul class="list-style-none flex">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <li class="disabled relative block rounded bg-transparent px-3 py-1.5 text-sm text-neutral-600 transition-all duration-300 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-white"
                    aria-disabled="true" aria-label="@lang('pagination.previous')">
                    <span aria-hidden="true">&lsaquo; {{ __('pagination.previous') }}</span>

                </li>
            @else
                <li>
                    <a class="relative block rounded bg-transparent px-3 py-1.5 text-sm text-neutral-600 transition-all duration-300 hover:bg-neutral-100 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-white"
                        aria-label="@lang('pagination.previous')" href="{{ $paginator->previousPageUrl() }}" rel="prev">&lsaquo;
                        {{ __('pagination.previous') }}</a>
                </li>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <li class="disabled relative block rounded bg-transparent px-3 py-1.5 text-sm text-neutral-600 transition-all duration-300 hover:bg-neutral-100 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-white"
                        aria-disabled="true"><span>{{ $element }}</span></li>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <li class="active relative block rounded bg-transparent px-3 py-1.5 text-sm text-dark-800 transition-all duration-300 bg-neutral-300 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-white"
                                aria-current="page"><span>{{ $page }}</span></li>
                        @else
                            <li
                                class="relative block rounded bg-transparent px-3 py-1.5 text-sm text-neutral-600 transition-all duration-300 hover:bg-neutral-100 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-white">
                                <a href="{{ $url }}">{{ $page }}</a>
                            </li>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <li>
                    <a class="relative block rounded bg-transparent px-3 py-1.5 text-sm text-neutral-600 transition-all duration-300 hover:bg-neutral-100 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-white"
                        href="{{ $paginator->nextPageUrl() }}" aria-label="@lang('pagination.next')"
                        rel="next">{{ __('pagination.next') }} &rsaquo;</a>
                    {{-- <a href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a> --}}
                </li>
            @else
                <li class="disabled relative block rounded bg-transparent px-3 py-1.5 text-sm text-neutral-600 transition-all duration-300 dark:text-white dark:hover:bg-neutral-700 dark:hover:text-white"
                    aria-disabled="true" aria-label="@lang('pagination.next')">
                    <span aria-hidden="true">{{ __('pagination.next') }} &rsaquo;</span>
                </li>
            @endif
        </ul>
    </nav>
@endif
