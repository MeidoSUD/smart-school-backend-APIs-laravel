@extends('layouts.app')
@section('title', 'Income')
@section('page-title', 'Income')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All Income</h5>
        <a href="{{ route('admin.incomes.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Income</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Income Head</th><th>Date</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($incomes as $income)
                <tr>
                    <td>{{ $income->id }}</td>
                    <td>{{ $income->name }}</td>
                    <td>{{ $income->incomeHead?->income_head ?? '-' }}</td>
                    <td>{{ $income->date?->format('d M Y') ?? '-' }}</td>
                    <td>{{ number_format($income->amount, 2) }}</td>
                    <td><span class="badge bg-{{ $income->is_active == 1 ? 'success' : 'danger' }}">{{ $income->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('admin.incomes.edit', $income->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.incomes.destroy', $income->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted">No income records found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $incomes->links() }}
</div>
@endsection
