@extends('layouts.app')
@section('title', 'Library Books')
@section('page-title', 'Library Books')
@section('content')
<div class="card p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="mb-0">All Books</h5>
        <a href="{{ route('admin.books.create') }}" class="btn btn-primary"><i class="fas fa-plus me-2"></i>Add Book</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="table-light">
                <tr><th>#</th><th>Title</th><th>Author</th><th>ISBN</th><th>Qty</th><th>Available</th><th>Status</th><th>Actions</th></tr>
            </thead>
            <tbody>
                @forelse($books as $book)
                <tr>
                    <td>{{ $book->id }}</td>
                    <td>{{ $book->book_title }}</td>
                    <td>{{ $book->author ?? '-' }}</td>
                    <td>{{ $book->isbn_no ?? '-' }}</td>
                    <td>{{ $book->qty ?? 0 }}</td>
                    <td>{{ $book->available ?? 0 }}</td>
                    <td><span class="badge bg-{{ $book->is_active == 1 ? 'success' : 'danger' }}">{{ $book->is_active ? 'Active' : 'Inactive' }}</span></td>
                    <td>
                        <a href="{{ route('admin.books.edit', $book->id) }}" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>
                        <form action="{{ route('admin.books.destroy', $book->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?')">
                            @csrf @method('DELETE')
                            <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr><td colspan="8" class="text-center text-muted">No books found</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $books->links() }}
</div>
@endsection
