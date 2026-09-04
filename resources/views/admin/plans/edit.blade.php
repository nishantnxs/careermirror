@extends('admin.layouts.app')

@section('title', 'Edit Plan')
@section('heading', 'Edit Plan')
@section('subheading', $plan->title)

@section('actions')
    <a href="{{ route('admin.plans.show', $plan) }}" class="btn btn-light">
        <i class="bi bi-eye me-1"></i> Preview
    </a>
    <a href="{{ route('admin.plans.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i> Back to plans
    </a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.plans.update', $plan) }}">
        @csrf
        @method('PUT')
        @include('admin.plans.form')
    </form>
@endsection
