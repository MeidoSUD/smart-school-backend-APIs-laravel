@extends('layouts.app')
@section('title', 'Edit Hostel')
@section('page-title', 'Edit Hostel')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Edit Hostel</h5>
        <a href="{{ route('admin.hostels.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route('admin.hostels.update', $hostel->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Hostel Name</label>
                <input type="text" name="hostel_name" class="form-control" value="{{ old('hostel_name', $hostel->hostel_name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Type</label>
                <select name="type" class="form-select">
                    <option value="">Select</option>
                    <option value="Boys" {{ old('type', $hostel->type) == 'Boys' ? 'selected' : '' }}>Boys</option>
                    <option value="Girls" {{ old('type', $hostel->type) == 'Girls' ? 'selected' : '' }}>Girls</option>
                    <option value="Both" {{ old('type', $hostel->type) == 'Both' ? 'selected' : '' }}>Both</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Address</label>
                <input type="text" name="address" class="form-control" value="{{ old('address', $hostel->address) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Intake</label>
                <input type="number" name="intake" class="form-control" value="{{ old('intake', $hostel->intake) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ old('is_active', $hostel->is_active) == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $hostel->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $hostel->description) }}</textarea>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Hostel</button>
        </div>
    </form>
</div>
@endsection
