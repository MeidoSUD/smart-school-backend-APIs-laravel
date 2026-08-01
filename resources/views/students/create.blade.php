@extends('layouts.app')
@section('title', 'Add Student')
@section('page-title', 'Add Student')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Add New Student</h5>
        <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
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
    <form action="{{ route('admin.students.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Admission No</label>
                <input type="text" name="admission_no" class="form-control" value="{{ old('admission_no') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">First Name</label>
                <input type="text" name="First_name" class="form-control" value="{{ old('First_name') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Last Name</label>
                <input type="text" name="Last_name" class="form-control" value="{{ old('Last_name') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Email</label>
                <input type="email" name="student_email" class="form-control" value="{{ old('student_email') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Phone</label>
                <input type="text" name="student_phone" class="form-control" value="{{ old('student_phone') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Gender</label>
                <select name="student_gender" class="form-select">
                    <option value="">Select</option>
                    <option value="Male" {{ old('student_gender') == 'Male' ? 'selected' : '' }}>Male</option>
                    <option value="Female" {{ old('student_gender') == 'Female' ? 'selected' : '' }}>Female</option>
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date of Birth</label>
                <input type="date" name="dob" class="form-control" value="{{ old('dob') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Blood Group</label>
                <input type="text" name="blood_group" class="form-control" value="{{ old('blood_group') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Religion</label>
                <input type="text" name="religion" class="form-control" value="{{ old('religion') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Father Name</label>
                <input type="text" name="Father_name" class="form-control" value="{{ old('Father_name') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Father Phone</label>
                <input type="text" name="Father_phone" class="form-control" value="{{ old('Father_phone') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Mother Name</label>
                <input type="text" name="Mother_name" class="form-control" value="{{ old('Mother_name') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Mother Phone</label>
                <input type="text" name="Mother_phone" class="form-control" value="{{ old('Mother_phone') }}">
            </div>
            <div class="col-md-12">
                <label class="form-label">Address</label>
                <textarea name="student_address" class="form-control" rows="2">{{ old('student_address') }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="active" {{ old('is_active', 'active') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ old('is_active') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Student</button>
        </div>
    </form>
</div>
@endsection
