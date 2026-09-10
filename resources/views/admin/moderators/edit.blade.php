@extends('admin.layouts.app')

@section('title', 'Edit Moderator')
@section('heading', 'Edit Moderator')
@section('subheading', 'Update moderator details, role and status.')

@section('actions')
    <a href="{{ route('admin.moderators.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.moderators.update', $moderator) }}">
        @csrf
        @method('PUT')
        @include('admin.moderators.form')
    </form>
@endsection
