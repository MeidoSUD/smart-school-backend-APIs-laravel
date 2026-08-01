@extends('layouts.app')
@section('title', 'Edit Expense')
@section('page-title', 'Edit Expense')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Edit Expense</h5>
        <a href="{{ route('admin.expenses.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route('admin.expenses.update', $expense->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $expense->name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Expense Head</label>
                <select name="exp_head_id" class="form-select">
                    <option value="">Select</option>
                    @foreach($expenseHeads as $head)
                        <option value="{{ $head->id }}" {{ old('exp_head_id', $expense->exp_head_id) == $head->id ? 'selected' : '' }}>{{ $head->expense_head }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Date</label>
                <input type="date" name="date" class="form-control" value="{{ old('date', $expense->date?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Amount</label>
                <input type="number" name="amount" class="form-control" value="{{ old('amount', $expense->amount) }}" step="0.01" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ old('is_active', $expense->is_active) == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $expense->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Expense</button>
        </div>
    </form>
</div>
@endsection
