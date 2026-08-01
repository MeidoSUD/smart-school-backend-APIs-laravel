@extends('layouts.app')
@section('title', 'Sections')
@section('page-title', 'Sections')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All Sections</h5>
        <a href="{{ route('admin.sections.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Section</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr><th>#</th><th>Section Name</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($sections as $section)
                <tr>
                    <td>{{ $section->id }}</td>
                    <td>{{ $section->section }}</td>
                    <td><span class="badge bg-{{ $section->is_active == 1 ? 'success' : 'danger' }}">{{ $section->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('admin.sections.edit', $section->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.sections.destroy', $section->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" class="text-center text-muted">No sections found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $sections->links() }}
</div>
@endsection
