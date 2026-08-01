@extends('layouts.app')
@section('title', 'Staff Details')
@section('page-title', 'Staff Details')
@section('content')
<div class="mb-3">
    <a href="{{ route('admin.staff.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to List</a>
</div>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-4 text-center">
            <i class="fas fa-user-tie fa-5x text-primary mb-3"></i>
            <h5>{{ $member->staff_name }}</h5>
            <p class="text-muted">{{ $member->employee_id }}</p>
            <span class="badge bg-{{ $member->is_active == 1 ? 'success' : 'danger' }}">{{ $member->is_active ? 'Active' : 'Inactive' }}</span>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-4">
            <h5>Personal Information</h5>
            <div class="row g-3">
                <div class="col-md-6"><strong>Email:</strong> {{ $member->email }}</div>
                <div class="col-md-6"><strong>Phone:</strong> {{ $member->phone }}</div>
                <div class="col-md-6"><strong>Gender:</strong> {{ $member->gender }}</div>
                <div class="col-md-6"><strong>DOB:</strong> {{ $member->date_of_birth?->format('d M Y') }}</div>
                <div class="col-md-6"><strong>Qualification:</strong> {{ $member->qualification }}</div>
                <div class="col-md-6"><strong>CNIC:</strong> {{ $member->cnic_no }}</div>
                <div class="col-md-12"><strong>Address:</strong> {{ $member->address }}</div>
            </div>
            <hr>
            <h5>Employment Details</h5>
            <div class="row g-3">
                <div class="col-md-6"><strong>Designation:</strong> {{ $member->designation?->staff_designation_name ?? '-' }}</div>
                <div class="col-md-6"><strong>Department:</strong> {{ $member->department?->department_name ?? '-' }}</div>
                <div class="col-md-6"><strong>Date of Joining:</strong> {{ $member->date_of_joining?->format('d M Y') }}</div>
                <div class="col-md-6"><strong>Salary:</strong> {{ number_format($member->employee_salary ?? 0, 2) }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
