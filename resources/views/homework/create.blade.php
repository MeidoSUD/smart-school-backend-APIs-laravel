@extends('layouts.app')
@section('title', 'Add Homework')
@section('page-title', 'Add Homework')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Add New Homework</h5>
        <a href="{{ route('admin.homework.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route('admin.homework.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Subject</label>
                <select name="subject_id" class="form-select" required>
                    <option value="">Select</option>
                    @foreach($subjects as $sub)
                        <option value="{{ $sub->id }}" {{ old('subject_id') == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Class</label>
                <select name="class_id" class="form-select" required>
                    <option value="">Select</option>
                    @foreach($classes as $cls)
                        <option value="{{ $cls->id }}" {{ old('class_id') == $cls->id ? 'selected' : '' }}>{{ $cls->class }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Section</label>
                <select name="section_id" class="form-select" required>
                    <option value="">Select</option>
                    @foreach($sections as $sec)
                        <option value="{{ $sec->id }}" {{ old('section_id') == $sec->id ? 'selected' : '' }}>{{ $sec->section }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Homework Date</label>
                <input type="date" name="homework_date" class="form-control" value="{{ old('homework_date') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Submission Date</label>
                <input type="date" name="submission_date" class="form-control" value="{{ old('submission_date') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Marks</label>
                <input type="number" name="marks" class="form-control" value="{{ old('marks') }}">
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Homework</button>
        </div>
    </form>
</div>
@endsection
