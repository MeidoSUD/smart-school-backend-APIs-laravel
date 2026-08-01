@extends('layouts.app')
@section('title', 'Homework')
@section('page-title', 'Homework')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All Homework</h5>
        <a href="{{ route('admin.homework.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Homework</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr>
                    <th>#</th>
                    <th>Subject</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Date</th>
                    <th>Submit Date</th>
                    <th>Marks</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($homework as $hw)
                <tr>
                    <td>{{ $hw->id }}</td>
                    <td>{{ $hw->subject?->name ?? '-' }}</td>
                    <td>{{ $hw->class?->class ?? '-' }}</td>
                    <td>{{ $hw->section?->section ?? '-' }}</td>
                    <td>{{ $hw->homework_date ?? '-' }}</td>
                    <td>{{ $hw->submission_date ?? '-' }}</td>
                    <td>{{ $hw->marks ?? '-' }}</td>
                    <td>
                        <a href="{{ route('admin.homework.edit', $hw->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.homework.destroy', $hw->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">No homework found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $homework->links() }}
</div>
@endsection
