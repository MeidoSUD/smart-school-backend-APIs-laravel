@extends('layouts.app')
@section('title', 'Staff')
@section('page-title', 'Staff')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All Staff</h5>
        <a href="{{ route('admin.staff.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Staff</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Designation</th>
                    <th>Department</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($staff as $member)
                <tr>
                    <td>{{ $member->id }}</td>
                    <td>{{ $member->employee_id }}</td>
                    <td>{{ $member->staff_name }}</td>
                    <td>{{ $member->email }}</td>
                    <td>{{ $member->phone }}</td>
                    <td>{{ $member->designation?->staff_designation_name ?? '-' }}</td>
                    <td>{{ $member->department?->department_name ?? '-' }}</td>
                    <td><span class="badge bg-{{ $member->is_active == 1 ? 'success' : 'danger' }}">{{ $member->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('admin.staff.show', $member->id) }}" class="btn btn-sm btn-info text-white"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('admin.staff.edit', $member->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.staff.destroy', $member->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" class="text-center text-muted">No staff found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $staff->links() }}
</div>
@endsection
