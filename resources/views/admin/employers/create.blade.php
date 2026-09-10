@extends('admin.layouts.app')

@section('title', 'Add Employer')
@section('heading', 'Add Employer')
@section('subheading', 'Create a new employer account from the admin panel.')

@section('actions')
    <a href="{{ route('admin.employers.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i> Back to employers
    </a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.employers.store') }}" novalidate>
        @csrf
        @include('admin.employers.form')
    </form>
@endsection
