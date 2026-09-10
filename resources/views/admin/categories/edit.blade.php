@extends('admin.layouts.app')

@section('title', 'Edit Category')
@section('heading', 'Edit Category')
@section('subheading', 'Update category details.')

@section('actions')
    <a href="{{ route('admin.categories.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                @csrf
                @method('PUT')
                @include('admin.categories.form')
                <div class="mt-3">
                    <button type="submit" class="btn btn-brand">Update category</button>
                </div>
            </form>
        </div>
    </div>
@endsection
