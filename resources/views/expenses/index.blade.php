@extends('layouts.app')
@section('title', 'Expenses')
@section('page-title', 'Expenses')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All Expenses</h5>
        <a href="{{ route('admin.expenses.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Expense</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr><th>#</th><th>Name</th><th>Expense Head</th><th>Date</th><th>Amount</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                <tr>
                    <td>{{ $expense->id }}</td>
                    <td>{{ $expense->name }}</td>
                    <td>{{ $expense->expenseHead?->expense_head ?? '-' }}</td>
                    <td>{{ $expense->date?->format('d M Y') ?? '-' }}</td>
                    <td>{{ number_format($expense->amount, 2) }}</td>
                    <td><span class="badge bg-{{ $expense->is_active == 1 ? 'success' : 'danger' }}">{{ $expense->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('admin.expenses.edit', $expense->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.expenses.destroy', $expense->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="7" class="text-center text-muted">No expenses found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $expenses->links() }}
</div>
@endsection
