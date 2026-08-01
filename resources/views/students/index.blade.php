@extends('layouts.app')
@section('title', 'Students')
@section('page-title', 'Students')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All Students</h5>
        <a href="{{ route('admin.students.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Student</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Admission No</th>
                    <th>Name</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($students as $student)
                <tr>
                    <td>{{ $student->id }}</td>
                    <td>{{ $student->admission_no }}</td>
                    <td>{{ $student->First_name }} {{ $student->Last_name }}</td>
                    <td>{{ $student->studentSessions->last()?->class?->class ?? '-' }}</td>
                    <td>{{ $student->studentSessions->last()?->section?->section ?? '-' }}</td>
                    <td>{{ $student->student_phone }}</td>
                    <td><span class="badge bg-{{ $student->is_active == 'active' ? 'success' : 'danger' }}">{{ $student->is_active }}</span></td>
                    <td>
                        <a href="{{ route('admin.students.show', $student->id) }}" class="btn btn-sm btn-info text-white"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('admin.students.edit', $student->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.students.destroy', $student->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">No students found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $students->links() }}
</div>
@endsection
