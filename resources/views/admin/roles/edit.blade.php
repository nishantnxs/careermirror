@extends('admin.layouts.app')

@section('title', 'Edit Role')
@section('heading', 'Edit Role')
@section('subheading', 'Update role details and module permissions.')

@section('actions')
    <a href="{{ route('admin.roles.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.roles.update', $role) }}">
        @csrf
        @method('PUT')
        @include('admin.roles.form')
    </form>
@endsection
