<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;

class BookController extends Controller
{
    public function index()
    {
        $books = Book::latest()->paginate(15);

        return view('books.index', compact('books'));
    }

    public function create()
    {
        return view('books.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_title' => 'required|string|max:255',
            'book_no' => 'nullable|string|max:255',
            'isbn_no' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'rack_no' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'qty' => 'nullable|integer',
            'perunitcost' => 'nullable|numeric',
            'description' => 'nullable|string',
            'is_active' => 'nullable|integer',
        ]);

        $validated['available'] = $validated['qty'] ?? 0;

        Book::create($validated);

        return redirect()->route('admin.books.index')->with('success', 'Book created successfully.');
    }

    public function edit(Book $book)
    {
        return view('books.edit', compact('book'));
    }

    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'book_title' => 'required|string|max:255',
            'book_no' => 'nullable|string|max:255',
            'isbn_no' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'rack_no' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'qty' => 'nullable|integer',
            'perunitcost' => 'nullable|numeric',
            'description' => 'nullable|string',
            'is_active' => 'nullable|integer',
        ]);

        $book->update($validated);

        return redirect()->route('admin.books.index')->with('success', 'Book updated successfully.');
    }

    public function destroy(Book $book)
    {
        $book->delete();

        return redirect()->route('admin.books.index')->with('success', 'Book deleted successfully.');
    }
}
