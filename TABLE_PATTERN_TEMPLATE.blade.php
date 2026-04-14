{{-- 
    TABLE MODULE TEMPLATE
    Use this as a reference for creating new table modules
    All 12 existing modules follow this exact pattern
--}}

@extends('layouts.app')

@section('content')
<div class="container-fluid">
    
    {{-- ============================================
         1. PAGE HEADER
         ============================================ --}}
    <div class="d-flex justify-content-between align-items-center mb-7">
        <div>
            <h1 class="fs-2 fw-bold text-gray-900 mb-2">Module Title</h1>
            <p class="text-gray-600 fs-6 mb-0">Module description goes here.</p>
        </div>
        <a href="{{ route('module.create') }}" class="btn btn-primary">
            <i class="ki-solid ki-plus fs-2"></i>
            Create New
        </a>
    </div>

    {{-- ============================================
         2. KPI CARDS (Optional)
         ============================================ --}}
    <div class="row mb-7">
        <div class="col-md-4">
            <div class="card bg-warning">
                <div class="card-body">
                    <span class="text-white fs-7 fw-bold">Metric 1</span>
                    <div class="text-white fs-2x fw-bold mt-2">{{ $metric1 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary">
                <div class="card-body">
                    <span class="text-white fs-7 fw-bold">Metric 2</span>
                    <div class="text-white fs-2x fw-bold mt-2">{{ $metric2 }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger">
                <div class="card-body">
                    <span class="text-white fs-7 fw-bold">Metric 3</span>
                    <div class="text-white fs-2x fw-bold mt-2">{{ $metric3 }}</div>
                </div>
            </div>
        </div>
    </div>

    {{-- ============================================
         3. FILTER BAR
         ============================================ --}}
    <div class="card mb-5">
        <div class="card-body">
            <form action="{{ route('module.index') }}" method="GET" class="d-flex flex-wrap gap-3">
                {{-- Hidden inputs for preserving state --}}
                @if(request('status'))
                    <input type="hidden" name="status" value="{{ request('status') }}">
                @endif
                
                {{-- Search input --}}
                <div class="flex-grow-1" style="max-width: 400px;">
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}" 
                           placeholder="Search..." 
                           class="form-control form-control-solid">
                </div>
                
                {{-- Additional filters (optional) --}}
                <select name="filter1" class="form-select form-select-solid" style="max-width: 200px;">
                    <option value="">All Types</option>
                    <option value="type1">Type 1</option>
                    <option value="type2">Type 2</option>
                </select>
                
                {{-- Filter button --}}
                <button type="submit" class="btn btn-dark">
                    <i class="ki-solid ki-magnifier fs-2"></i>
                    Filter
                </button>
                
                {{-- Reset button (show when filters active) --}}
                @if(request('search') || request('filter1'))
                    <a href="{{ route('module.index', request()->except(['search', 'filter1', 'page'])) }}" 
                       class="btn btn-light">
                        <i class="ki-solid ki-cross fs-2"></i>
                        Reset
                    </a>
                @endif
            </form>
        </div>
    </div>

    {{-- ============================================
         4. MAIN CARD WITH TABS (Optional)
         ============================================ --}}
    <div class="card">
        
        {{-- TABS (Optional) --}}
        <div class="card-header border-0 pt-6 pb-2">
            <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x nav-stretch fs-6 fw-bold border-0">
                @php
                    $tabs = [
                        '' => ['label' => 'All Items', 'icon' => 'ki-home-2', 'color' => 'primary'],
                        'active' => ['label' => 'Active', 'icon' => 'ki-check-circle', 'color' => 'success'],
                        'pending' => ['label' => 'Pending', 'icon' => 'ki-time', 'color' => 'warning'],
                        'inactive' => ['label' => 'Inactive', 'icon' => 'ki-cross-circle', 'color' => 'danger'],
                    ];
                @endphp
                
                @foreach($tabs as $value => $tab)
                    @php 
                        $isActive = (string)request('status', '') === (string)$value;
                        $count = $items->where('status', $value === '' ? null : $value)->count();
                    @endphp
                    <li class="nav-item">
                        <a href="{{ route('module.index', array_merge(request()->except(['status', 'page']), ['status' => $value === '' ? null : $value])) }}" 
                           class="nav-link text-active-primary d-flex align-items-center {{ $isActive ? 'active' : '' }}">
                            <i class="ki-solid {{ $tab['icon'] }} fs-4 me-2 text-{{ $tab['color'] }}"></i>
                            <span class="fs-6 fw-bold">{{ $tab['label'] }}</span>
                            <span class="badge {{ $isActive ? 'badge-primary' : 'badge-light-' . $tab['color'] }} ms-auto">
                                {{ $count }}
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- CARD BODY --}}
        <div class="card-body">
            
            {{-- ============================================
                 5. TABLE
                 ============================================ --}}
            <div class="table-responsive">
                <table class="table table-row-dashed table-row-gray-300 align-middle gs-7 gy-4">
                    <thead>
                        <tr class="fw-bold text-muted bg-light">
                            <th class="ps-4 rounded-start min-w-200px">ID / Name</th>
                            <th class="min-w-150px">Info Column</th>
                            <th class="min-w-100px">Status</th>
                            <th class="text-end min-w-100px">Amount</th>
                            <th class="min-w-125px">Date</th>
                            <th class="text-end pe-4 rounded-end min-w-100px">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr>
                                {{-- First Column: ID/Name with link --}}
                                <td class="ps-4">
                                    <div class="d-flex flex-column">
                                        <a href="{{ route('module.show', $item) }}" 
                                           class="text-gray-900 text-hover-primary fw-bold fs-6">
                                            {{ $item->name }}
                                        </a>
                                        <span class="text-gray-500 fs-7 mt-1">ID: {{ $item->id }}</span>
                                    </div>
                                </td>
                                
                                {{-- Info Column --}}
                                <td>
                                    <span class="text-gray-800 fw-semibold">{{ $item->info }}</span>
                                </td>
                                
                                {{-- Status Badge --}}
                                <td>
                                    @php
                                        $statusColor = match($item->status) {
                                            'active', 'completed', 'paid' => 'success',
                                            'pending', 'unpaid' => 'warning',
                                            'rejected', 'overdue' => 'danger',
                                            'draft' => 'secondary',
                                            default => 'primary'
                                        };
                                    @endphp
                                    <span class="badge badge-{{ $statusColor }}">
                                        {{ strtoupper($item->status) }}
                                    </span>
                                </td>
                                
                                {{-- Amount (right-aligned) --}}
                                <td class="text-end">
                                    <span class="text-gray-900 fw-bold fs-6">
                                        Rp {{ number_format($item->amount, 0, ',', '.') }}
                                    </span>
                                </td>
                                
                                {{-- Date --}}
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-gray-800 fw-semibold">
                                            {{ $item->date->format('d M Y') }}
                                        </span>
                                        <span class="text-gray-500 fs-7 mt-1">
                                            {{ $item->date->diffForHumans() }}
                                        </span>
                                    </div>
                                </td>
                                
                                {{-- Actions --}}
                                <td class="text-end pe-4">
                                    <div class="d-flex justify-content-end gap-2">
                                        {{-- View button --}}
                                        <a href="{{ route('module.show', $item) }}" 
                                           class="btn btn-sm btn-light-primary">
                                            <i class="ki-solid ki-eye fs-4"></i>
                                            View
                                        </a>
                                        
                                        {{-- Edit button --}}
                                        <a href="{{ route('module.edit', $item) }}" 
                                           class="btn btn-sm btn-light">
                                            <i class="ki-solid ki-message-edit fs-4"></i>
                                        </a>
                                        
                                        {{-- Delete button --}}
                                        <form action="{{ route('module.destroy', $item) }}" 
                                              method="POST" 
                                              class="d-inline"
                                              onsubmit="return confirm('Are you sure?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-light-danger">
                                                <i class="ki-solid ki-trash fs-4"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            {{-- Empty State --}}
                            <tr>
                                <td colspan="6" class="text-center py-10">
                                    <div class="d-flex flex-column align-items-center">
                                        <i class="ki-solid ki-file-deleted fs-3x text-gray-400 mb-3"></i>
                                        <span class="text-gray-700 fs-5 fw-semibold mb-2">No items found</span>
                                        <span class="text-gray-500 fs-6">Try adjusting your filters or create a new item.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- ============================================
                 6. PAGINATION
                 ============================================ --}}
            @if($items->hasPages())
                <div class="d-flex flex-stack flex-wrap pt-7">
                    {{-- Count info --}}
                    <div class="fs-6 fw-semibold text-gray-700">
                        Menampilkan {{ $items->firstItem() }} - {{ $items->lastItem() }} dari {{ $items->total() }} items
                    </div>
                    {{-- Pagination links --}}
                    <div>
                        {{ $items->links() }}
                    </div>
                </div>
            @endif
            
        </div>
    </div>
</div>
@endsection

{{-- 
    ============================================
    DESIGN TOKENS REFERENCE
    ============================================
    
    BUTTONS:
    - Primary: btn-primary (create/main action)
    - Success: btn-success (approve/confirm)
    - Danger: btn-danger (delete/reject)
    - Light: btn-light (view/secondary)
    
    BADGES:
    - Success: badge-success (completed/paid/approved)
    - Warning: badge-warning (pending/unpaid)
    - Danger: badge-danger (rejected/overdue)
    - Secondary: badge-secondary (draft/inactive)
    - Primary: badge-primary (submitted/active)
    
    ICONS:
    - Format: ki-solid ki-{name}
    - Button size: fs-2
    - Inline size: fs-4
    
    TYPOGRAPHY:
    - Page title: fs-2 fw-bold text-gray-900
    - Section title: fs-3 fw-bold
    - Body text: fs-6
    - Labels/meta: fs-7
    
    SPACING:
    - Section spacing: mb-7
    - Card spacing: mb-5
    - Button gaps: gap-3
    - Table padding: gs-7 gy-4
    
    TABLE CLASSES:
    - Table: table table-row-dashed table-row-gray-300 align-middle gs-7 gy-4
    - Header: fw-bold text-muted bg-light
    - First column: ps-4 rounded-start
    - Last column: text-end pe-4 rounded-end
    - Min-width: min-w-{size}px
--}}
