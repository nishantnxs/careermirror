@extends('admin.layouts.app')

@section('title', 'Add Plan')
@section('heading', 'Add Plan')
@section('subheading', 'Create a new subscription plan.')

@section('actions')
    <a href="{{ route('admin.plans.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i> Back to plans
    </a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.plans.store') }}">
        @csrf
        @include('admin.plans.form')
    </form>
@endsection
