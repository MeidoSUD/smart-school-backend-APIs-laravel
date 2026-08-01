@extends('layouts.app')
@section('title', 'Edit Vehicle')
@section('page-title', 'Edit Vehicle')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Edit Vehicle</h5>
        <a href="{{ route('admin.vehicles.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route('admin.vehicles.update', $vehicle->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Vehicle No</label>
                <input type="text" name="vehicle_no" class="form-control" value="{{ old('vehicle_no', $vehicle->vehicle_no ?? '') }}">
            </div>
            <div class="col-md-6">
                <label class="form-label">Vehicle Type</label>
                <input type="text" name="vehicle_type" class="form-control" value="{{ old('vehicle_type', $vehicle->vehicle_type ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Capacity</label>
                <input type="number" name="capacity" class="form-control" value="{{ old('capacity', $vehicle->capacity ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Driver Name</label>
                <input type="text" name="driver_name" class="form-control" value="{{ old('driver_name', $vehicle->driver_name ?? '') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Driver Phone</label>
                <input type="text" name="driver_phone" class="form-control" value="{{ old('driver_phone', $vehicle->driver_phone ?? '') }}">
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Vehicle</button>
        </div>
    </form>
</div>
@endsection
