@extends('admin.layouts.app')

@section('title', 'Add Moderator')
@section('heading', 'Add Moderator')
@section('subheading', 'Create a moderator account and assign a role.')

@section('actions')
    <a href="{{ route('admin.moderators.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.moderators.store') }}">
        @csrf
        @include('admin.moderators.form')
    </form>
@endsection
