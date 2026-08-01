@extends('layouts.app')
@section('title', 'Subjects')
@section('page-title', 'Subjects')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All Subjects</h5>
        <a href="{{ route('admin.subjects.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Subject</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Type</th><th>Code</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($subjects as $subject)
                <tr>
                    <td>{{ $subject->id }}</td>
                    <td>{{ $subject->name }}</td>
                    <td>{{ $subject->type ?? '-' }}</td>
                    <td>{{ $subject->code ?? '-' }}</td>
                    <td><span class="badge bg-{{ $subject->is_active == 1 ? 'success' : 'danger' }}">{{ $subject->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('admin.subjects.edit', $subject->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.subjects.destroy', $subject->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center text-muted">No subjects found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $subjects->links() }}
</div>
@endsection
