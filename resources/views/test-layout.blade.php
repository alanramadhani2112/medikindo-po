@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Test Layout</h3>
        </div>
        <div class="card-body">
            <p>This is a test page to verify the layout is working correctly.</p>
            <div class="alert alert-info">
                <i class="ki-outline ki-information-5 fs-2 me-3"></i>
                If you can see this properly styled, the layout is working.
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Test Card 1</h5>
                            <p class="card-text">This is a test card to check styling.</p>
                            <button class="btn btn-primary">Primary Button</button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Test Card 2</h5>
                            <p class="card-text">Another test card with different styling.</p>
                            <button class="btn btn-success">Success Button</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection