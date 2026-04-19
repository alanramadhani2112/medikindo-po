@props(['title', 'description' => null, 'breadcrumbs' => []])

<x-layout :title="$title" :breadcrumbs="$breadcrumbs">
    {{-- 1. HEADER --}}
    <x-page-header :title="$title" :description="$description">
        @isset($actions)
            <x-slot name="actions">
                {{ $actions }}
            </x-slot>
        @endisset
    </x-page-header>

    <div class="row g-5 g-xl-10">
        <div class="col-md-12">

            {{-- OPTIONAL TOP CONTENT (e.g. KPI Cards) --}}
            @isset($top)
                <div class="mb-7">
                    {{ $top }}
                </div>
            @endisset

            {{-- 2. TOOLBAR (Filters / Search) --}}
            @isset($toolbar)
                <div class="mb-5">
                    {{ $toolbar }}
                </div>
            @endisset

            {{-- 3. TABLIST (Classic Line Tabs) --}}
            @isset($tabs)
                <div class="card mb-5">
                    <div class="card-header border-0 py-6 min-h-auto overflow-x-auto">
                        <ul
                            class="nav nav-tabs nav-line-tabs nav-line-tabs-2x nav-stretch fs-6 fw-bold border-0 flex-nowrap">
                            {{ $tabs }}
                        </ul>
                    </div>
                </div>
            @endisset

            {{-- 4. MAIN CONTENT --}}
            @isset($content)
                {{ $content }}
            @else
                <div class="card card-flush">
                    @isset($tableHeader)
                        <div class="card-header pt-5">
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-800">{{ $tableHeader }}</span>
                            </h3>
                        </div>
                    @endisset
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            {{ $slot }}
                        </div>
                    </div>
                </div>
            @endisset

        </div>
    </div>
</x-layout>
