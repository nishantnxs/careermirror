@extends('admin.layouts.app')

@section('title', 'Add Candidate')
@section('heading', 'Add Candidate')
@section('subheading', 'Create a new candidate account from the admin panel.')

@section('actions')
    <a href="{{ route('admin.candidates.index') }}" class="btn btn-light">
        <i class="bi bi-arrow-left me-1"></i> Back to candidates
    </a>
@endsection

@section('content')
    <form method="POST" action="{{ route('admin.candidates.store') }}" novalidate>
        @csrf
        @include('admin.candidates.form')
    </form>
@endsection
