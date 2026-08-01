@extends('layouts.app')
@section('title', 'Roles')
@section('page-title', 'Roles')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All Roles</h5>
        <a href="{{ route('admin.roles.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Role</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Super Admin</th><th>Staff</th><th>Student</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($roles as $role)
                <tr>
                    <td>{{ $role->id }}</td>
                    <td>{{ $role->name }}</td>
                    <td><span class="badge bg-{{ $role->is_superadmin ? 'primary' : 'secondary' }}">{{ $role->is_superadmin ? 'Yes' : 'No' }}</span></td>
                    <td><span class="badge bg-{{ $role->is_staff ? 'success' : 'secondary' }}">{{ $role->is_staff ? 'Yes' : 'No' }}</span></td>
                    <td><span class="badge bg-{{ $role->is_student ? 'info' : 'secondary' }}">{{ $role->is_student ? 'Yes' : 'No' }}</span></td>
                    <td><span class="badge bg-{{ $role->is_active ? 'success' : 'danger' }}">{{ $role->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('admin.roles.edit', $role->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted">No roles found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $roles->links() }}
</div>
@endsection
