@extends('layouts.app')
@section('title', 'Add Role')
@section('page-title', 'Add Role')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Add New Role</h5>
        <a href="{{ route('admin.roles.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route('admin.roles.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Role Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Super Admin</label>
                <select name="is_superadmin" class="form-select">
                    <option value="0" {{ old('is_superadmin', '0') == '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('is_superadmin') == '1' ? 'selected' : '' }}>Yes</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Staff</label>
                <select name="is_staff" class="form-select">
                    <option value="0" {{ old('is_staff', '0') == '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('is_staff') == '1' ? 'selected' : '' }}>Yes</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Student</label>
                <select name="is_student" class="form-select">
                    <option value="0" {{ old('is_student', '0') == '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('is_student') == '1' ? 'selected' : '' }}>Yes</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Parent</label>
                <select name="is_parent" class="form-select">
                    <option value="0" {{ old('is_parent', '0') == '0' ? 'selected' : '' }}>No</option>
                    <option value="1" {{ old('is_parent') == '1' ? 'selected' : '' }}>Yes</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Role</button>
        </div>
    </form>
</div>
@endsection
