@extends('layouts.app')
@section('title', 'Fee Masters')
@section('page-title', 'Fee Masters')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All Fee Masters</h5>
        <a href="{{ route('admin.fee-masters.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Fee</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr><th>#</th><th>Fee Name</th><th>Class</th><th>Fee Type</th><th>Amount</th><th>Due Date</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($feeMasters as $fee)
                <tr>
                    <td>{{ $fee->id }}</td>
                    <td>{{ $fee->feemaster_name }}</td>
                    <td>{{ $fee->class?->class ?? '-' }}</td>
                    <td>{{ $fee->feeType?->name ?? '-' }}</td>
                    <td>{{ number_format($fee->amount, 2) }}</td>
                    <td>{{ $fee->due_date?->format('d M Y') ?? '-' }}</td>
                    <td><span class="badge bg-{{ $fee->is_active == 1 ? 'success' : 'danger' }}">{{ $fee->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('admin.fee-masters.edit', $fee->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.fee-masters.destroy', $fee->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">No fee masters found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $feeMasters->links() }}
</div>
@endsection
