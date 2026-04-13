@extends('layouts.app')

@section('content')

{{-- ROLE-BASED DASHBOARD --}}
{{-- Structure: Header → Cards → Charts → Tables → Alerts --}}

@if($role === 'healthcare')
    @include('dashboard.partials.healthcare')
@elseif($role === 'approver')
    @include('dashboard.partials.approver')
@elseif($role === 'finance')
    @include('dashboard.partials.finance')
@elseif($role === 'superadmin')
    @include('dashboard.partials.superadmin')
@else
    @include('dashboard.partials.basic')
@endif

@endsection
