@extends('layouts.app')
@section('title', 'Add Subject')
@section('page-title', 'Add Subject')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Add New Subject</h5>
        <a href="{{ route('admin.subjects.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route('admin.subjects.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Subject Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Type</label>
                <input type="text" name="type" class="form-control" value="{{ old('type') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Code</label>
                <input type="text" name="code" class="form-control" value="{{ old('code') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Subject</button>
        </div>
    </form>
</div>
@endsection
