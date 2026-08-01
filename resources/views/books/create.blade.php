@extends('layouts.app')
@section('title', 'Add Book')
@section('page-title', 'Add Book')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Add New Book</h5>
        <a href="{{ route('admin.books.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route('admin.books.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Book Title</label>
                <input type="text" name="book_title" class="form-control" value="{{ old('book_title') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Author</label>
                <input type="text" name="author" class="form-control" value="{{ old('author') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">ISBN No</label>
                <input type="text" name="isbn_no" class="form-control" value="{{ old('isbn_no') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Book No</label>
                <input type="text" name="book_no" class="form-control" value="{{ old('book_no') }}">
            </div>
            <div class="col-md-4">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="form-control" value="{{ old('subject') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Quantity</label>
                <input type="number" name="qty" class="form-control" value="{{ old('qty', 1) }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Unit Cost</label>
                <input type="number" name="perunitcost" class="form-control" value="{{ old('perunitcost') }}" step="0.01">
            </div>
            <div class="col-md-3">
                <label class="form-label">Rack No</label>
                <input type="text" name="rack_no" class="form-control" value="{{ old('rack_no') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-12">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="2">{{ old('description') }}</textarea>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Book</button>
        </div>
    </form>
</div>
@endsection
