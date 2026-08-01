@extends('layouts.app')
@section('title', 'Edit Fee Master')
@section('page-title', 'Edit Fee Master')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Edit Fee Master</h5>
        <a href="{{ route('admin.fee-masters.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route('admin.fee-masters.update', $feeMaster->id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Fee Name</label>
                <input type="text" name="feemaster_name" class="form-control" value="{{ old('feemaster_name', $feeMaster->feemaster_name) }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Class</label>
                <select name="class_id" class="form-select" required>
                    <option value="">Select</option>
                    @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ old('class_id', $feeMaster->class_id) == $cls->id ? 'selected' : '' }}>{{ $cls->class }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Amount</label>
                <input type="number" name="amount" class="form-control" value="{{ old('amount', $feeMaster->amount) }}" step="0.01" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Due Date</label>
                <input type="date" name="due_date" class="form-control" value="{{ old('due_date', $feeMaster->due_date?->format('Y-m-d')) }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Fine Amount</label>
                <input type="number" name="fine_amount" class="form-control" value="{{ old('fine_amount', $feeMaster->fine_amount) }}" step="0.01">
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description', $feeMaster->description) }}</textarea>
            </div>
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ old('is_active', $feeMaster->is_active) == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active', $feeMaster->is_active) == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Fee Master</button>
        </div>
    </form>
</div>
@endsection
