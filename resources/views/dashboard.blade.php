@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('content')
<div class="row g-4 mb-4">
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-primary bg-opacity-10 text-primary me-3"><i class="fas fa-user-graduate"></i></div>
                <div><h4 class="mb-0">{{ $stats['students'] ?? 0 }}</h4><small class="text-muted">Students</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-success bg-opacity-10 text-success me-3"><i class="fas fa-chalkboard-teacher"></i></div>
                <div><h4 class="mb-0">{{ $stats['staff'] ?? 0 }}</h4><small class="text-muted">Staff</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-warning bg-opacity-10 text-warning me-3"><i class="fas fa-chalkboard"></i></div>
                <div><h4 class="mb-0">{{ $stats['classes'] ?? 0 }}</h4><small class="text-muted">Classes</small></div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card card-stat p-3">
            <div class="d-flex align-items-center">
                <div class="stat-icon bg-info bg-opacity-10 text-info me-3"><i class="fas fa-money-bill-wave"></i></div>
                <div><h4 class="mb-0">{{ $stats['fees_collected'] ?? 0 }}</h4><small class="text-muted">Fees Collected</small></div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-8">
        <div class="card p-4">
            <h5>Recent Activities</h5>
            <p class="text-muted">Welcome to Smart School ERP. Use the sidebar to navigate.</p>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card p-4">
            <h5>Quick Actions</h5>
            <div class="d-grid gap-2">
                <a href="{{ route('admin.students.create') }}" class="btn btn-outline-primary"><i class="fas fa-plus me-2"></i>Add Student</a>
                <a href="{{ route('admin.fee-masters.index') }}" class="btn btn-outline-success"><i class="fas fa-money-bill me-2"></i>Collect Fees</a>
                <a href="{{ route('admin.homework.index') }}" class="btn btn-outline-warning"><i class="fas fa-tasks me-2"></i>Assign Homework</a>
            </div>
        </div>
    </div>
</div>
@endsection
