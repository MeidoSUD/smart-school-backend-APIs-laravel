@extends('layouts.app')
@section('title', 'Add Section')
@section('page-title', 'Add Section')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">Add New Section</h5>
        <a href="{{ route('admin.sections.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back</a>
    </div>
    @if($errors->any())
        <div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif
    <form action="{{ route('admin.sections.store') }}" method="POST">
        @csrf
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label">Section Name</label>
                <input type="text" name="section" class="form-control" value="{{ old('section') }}" required>
            </div>
            <div class="col-md-6">
                <label class="form-label">Status</label>
                <select name="is_active" class="form-select">
                    <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
        </div>
        <div class="mt-4">
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Save Section</button>
        </div>
    </form>
</div>
@endsection
