@extends('admin.layouts.app')

@section('title', 'Add Category')
@section('heading', 'Add Category')
@section('subheading', 'Create a category employers can choose when posting jobs.')

@section('actions')
    <a href="{{ route('admin.categories.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @csrf
                @include('admin.categories.form')
                <div class="mt-3">
                    <button type="submit" class="btn btn-brand">Save category</button>
                </div>
            </form>
        </div>
    </div>
@endsection
