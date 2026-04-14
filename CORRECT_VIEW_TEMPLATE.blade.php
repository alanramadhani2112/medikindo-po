{{-- 
    CORRECT VIEW TEMPLATE - NO DUPLICATES
    Use this template for all views after UI fix
--}}

@extends('layouts.app', ['pageTitle' => 'Module Name'])

@section('content')
{{-- NO container-fluid wrapper! Layout provides it --}}
{{-- NO page header (h1)! Toolbar provides it --}}

{{-- Filter Bar (Optional) --}}
<div class="card mb-5">
    <div class="card-body">
        <form action="{{ route('module.index') }}" method="GET" class="d-flex flex-wrap gap-3">
            <div class="flex-grow-1" style="max-width: 400px;">
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Search..." 
                       class="form-control form-control-solid">
            </div>
            <button type="submit" class="btn btn-dark">
                <i class="ki-solid ki-magnifier fs-2"></i>
                Filter
            </button>
            @if(request('search'))
                <a href="{{ route('module.index') }}" class="btn btn-light">
                    <i class="ki-solid ki-cross fs-2"></i>
                    Reset
                </a>
            @endif
            <div class="ms-auto">
                <a href="{{ route('module.create') }}" class="btn btn-primary">
                    <i class="ki-solid ki-plus fs-2"></i>
                    Create New
                </a>
            </div>
        </form>
    </div>
</div>

{{-- Tabs (Optional) --}}
<div class="card mb-5">
    <div class="card-header border-0 pt-6 pb-2">
        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x nav-stretch fs-6 fw-bold border-0">
            <!-- tabs here -->
        </ul>
    </div>
</div>

{{-- Main Content Card --}}
<div class="card">
    <div class="card-body">
        {{-- Table --}}
        <div class="table-responsive">
            <table class="table table-row-dashed table-row-gray-300 align-middle gs-7 gy-4">
                <!-- table content -->
            </table>
        </div>
        
        {{-- Pagination --}}
        @if($items->hasPages())
        <div class="d-flex flex-stack flex-wrap pt-7">
            <div class="fs-6 fw-semibold text-gray-700">
                Menampilkan {{ $items->firstItem() }} - {{ $items->lastItem() }} dari {{ $items->total() }} items
            </div>
            <div>
                {{ $items->links() }}
            </div>
        </div>
        @endif
    </div>
</div>

@endsection
