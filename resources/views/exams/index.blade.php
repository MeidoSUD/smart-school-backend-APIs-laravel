@extends('layouts.app')
@section('title', 'Exams')
@section('page-title', 'Exams')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All Exams</h5>
        <a href="{{ route('admin.exams.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Exam</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr><th>#</th><th>Exam Name</th><th>Note</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($exams as $exam)
                <tr>
                    <td>{{ $exam->id }}</td>
                    <td>{{ $exam->name }}</td>
                    <td>{{ $exam->note ?? '-' }}</td>
                    <td><span class="badge bg-{{ $exam->is_active == 1 ? 'success' : 'danger' }}">{{ $exam->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('admin.exams.edit', $exam->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.exams.destroy', $exam->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="text-center text-muted">No exams found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $exams->links() }}
</div>
@endsection
