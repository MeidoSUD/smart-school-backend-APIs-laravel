@extends('layouts.app')
@section('title', 'Classes')
@section('page-title', 'Classes')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All Classes</h5>
        <a href="{{ route('admin.classes.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Class</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Class Name</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($classes as $class)
                <tr>
                    <td>{{ $class->id }}</td>
                    <td>{{ $class->class }}</td>
                    <td><span class="badge bg-{{ $class->is_active == 1 ? 'success' : 'danger' }}">{{ $class->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('admin.classes.edit', $class->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.classes.destroy', $class->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">No classes found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $classes->links() }}
</div>
@endsection
