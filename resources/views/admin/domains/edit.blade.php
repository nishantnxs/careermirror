@extends('admin.layouts.app')

@section('title', 'Edit Domain')
@section('heading', 'Edit Domain')
@section('subheading', $domain->host)

@section('actions')
    <a href="{{ route('admin.domains.settings.edit', $domain) }}" class="btn btn-brand">Website Settings</a>
    <a href="{{ route('admin.domains.index') }}" class="btn btn-light">Back</a>
@endsection

@section('content')
    <div class="card">
        <div class="card-body">
            <form method="POST" action="{{ route('admin.domains.update', $domain) }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @include('admin.domains.form')
                <div class="mt-3">
                    <button type="submit" class="btn btn-brand">Update domain</button>
                </div>
            </form>
        </div>
    </div>
@endsection
