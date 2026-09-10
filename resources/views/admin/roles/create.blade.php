@extends('admin.layouts.app')

@section('title', 'Add Role')
@section('heading', 'Add Role')
@section('subheading', 'Define a role and choose module permissions.')

@section('actions')
    <a href="{{ route('admin.roles.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.roles.store') }}">
        @csrf
        @include('admin.roles.form')
    </form>
@endsection
