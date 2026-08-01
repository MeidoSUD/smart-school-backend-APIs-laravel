@extends('layouts.app')
@section('title', 'Student Details')
@section('page-title', 'Student Details')
@section('content')
<div class="mb-3">
    <a href="{{ route('admin.students.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to List</a>
</div>
<div class="row g-4">
    <div class="col-md-4">
        <div class="card p-4 text-center">
            <img src="{{ $student->student_photo ? asset('storage/'.$student->student_photo) : 'https://ui-avatars.com/api/?name='.urlencode($student->First_name.' '.$student->Last_name).'&size=120' }}" class="rounded-circle mb-3" width="120" alt="Student Photo">
            <h5>{{ $student->First_name }} {{ $student->Last_name }}</h5>
            <p class="text-muted">{{ $student->admission_no }}</p>
            <span class="badge bg-{{ $student->is_active == 'active' ? 'success' : 'danger' }}">{{ $student->is_active }}</span>
        </div>
    </div>
    <div class="col-md-8">
        <div class="card p-4">
            <h5>Personal Information</h5>
            <div class="row g-3">
                <div class="col-md-6"><strong>Email:</strong> {{ $student->student_email }}</div>
                <div class="col-md-6"><strong>Phone:</strong> {{ $student->student_phone }}</div>
                <div class="col-md-6"><strong>Gender:</strong> {{ $student->student_gender }}</div>
                <div class="col-md-6"><strong>DOB:</strong> {{ $student->dob?->format('d M Y') }}</div>
                <div class="col-md-6"><strong>Blood Group:</strong> {{ $student->blood_group }}</div>
                <div class="col-md-6"><strong>Religion:</strong> {{ $student->religion }}</div>
                <div class="col-md-12"><strong>Address:</strong> {{ $student->student_address }}</div>
            </div>
            <hr>
            <h5>Parent Information</h5>
            <div class="row g-3">
                <div class="col-md-6"><strong>Father:</strong> {{ $student->Father_name }}</div>
                <div class="col-md-6"><strong>Father Phone:</strong> {{ $student->Father_phone }}</div>
                <div class="col-md-6"><strong>Mother:</strong> {{ $student->Mother_name }}</div>
                <div class="col-md-6"><strong>Mother Phone:</strong> {{ $student->Mother_phone }}</div>
            </div>
        </div>
    </div>
</div>
@endsection
