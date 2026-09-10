@extends('admin.layouts.app')

@section('title', 'Add Domain')
@section('heading', 'Add Domain')
@section('subheading', 'Register a new website host managed by this admin panel.')

@section('actions')
    <a href="{{ route('admin.domains.index') }}" class="btn btn-light">Back</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.domains.store') }}" enctype="multipart/form-data">
                @csrf
                @include('admin.domains.form')
                <div class="mt-3">
                    <button type="submit" class="btn btn-brand">Create domain</button>
                </div>
            </form>
        </div>
    </div>
@endsection
