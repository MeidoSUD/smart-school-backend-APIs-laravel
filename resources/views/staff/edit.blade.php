@extends('layouts.app')
@section('title', 'Edit Staff')
@section('page-title', 'Edit Staff')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Edit Staff: {{ $member->staff_name }}</h5>
        <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('admin.staff.update', $member->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Employee ID</label>
                <input type="text" name="employee_id" class="form-control" value="{{ old('employee_id', $member->employee_id) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Staff Name</label>
                <input type="text" name="staff_name" class="form-control" value="{{ old('staff_name', $member->staff_name) }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $member->email) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="{{ old('phone', $member->phone) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Gender</label>
                <select name="gender" class="form-select">
                    <option value="">Select</option>
                    <option value="Male" {{ old('gender', $member->gender) == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('gender', $member->gender) == 'Female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $member->date_of_birth?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Designation</label>
                <select name="staff_designation_id" class="form-select">
                    <option value="">Select</option>
                    @foreach($designations as $desig)
                        <option value="{{ $desig->id }}" {{ old('staff_designation_id', $member->staff_designation_id) == $desig->id ? 'selected' : '' }}>{{ $desig->staff_designation_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Department</label>
                <select name="department_id" class="form-select">
                    <option value="">Select</option>
                    @foreach($departments as $dept)
                        <option value="{{ $dept->id }}" {{ old('department_id', $member->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->department_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Qualification</label>
                <input type="text" name="qualification" class="form-control" value="{{ old('qualification', $member->qualification) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Salary</label>
                <input type="number" name="employee_salary" class="form-control" value="{{ old('employee_salary', $member->employee_salary) }}" step="0.01">
            </div>
            <div class="col-md-6">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2">{{ old('address', $member->address) }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ old('is_active', $member->is_active) == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $member->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Staff</button>
        </div>
    </form>
</div>
@endsection
