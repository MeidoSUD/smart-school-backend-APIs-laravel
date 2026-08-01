@extends('layouts.app')
@section('title', 'Transport Vehicles')
@section('page-title', 'Transport Vehicles')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All Vehicles</h5>
        <a href="{{ route('admin.vehicles.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Vehicle</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr><th>#</th><th>Vehicle No</th><th>Vehicle Type</th><th>Capacity</th><th>Driver Name</th><th>Driver Phone</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($vehicles as $vehicle)
                <tr>
                    <td>{{ $vehicle->id }}</td>
                    <td>{{ $vehicle->vehicle_no ?? '-' }}</td>
                    <td>{{ $vehicle->vehicle_type ?? '-' }}</td>
                    <td>{{ $vehicle->capacity ?? '-' }}</td>
                    <td>{{ $vehicle->driver_name ?? '-' }}</td>
                    <td>{{ $vehicle->driver_phone ?? '-' }}</td>
                    <td>
                        <a href="{{ route('admin.vehicles.edit', $vehicle->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.vehicles.destroy', $vehicle->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted">No vehicles found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $vehicles->links() }}
</div>
@endsection
