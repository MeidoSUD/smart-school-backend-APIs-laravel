<?php

namespace App\Http\Controllers\Api;

use App\Models\Book;
use App\Models\BookIssue;
use App\Models\LibraryMember;
use App\Models\StudentSession;
use App\Models\Student;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BookController extends \App\Http\Controllers\Api\Controller
{
    public function __construct()
    {
        $this->setControllerName('BookController');
    }

    public function index(): JsonResponse
    {
        $listbook = Book::where('is_active', 'yes')->get();

        $data = [
            'title' => 'Add Book',
            'title_list' => 'Book Details',
            'listbook' => $listbook,
        ];

        return $this->successResponse($data);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'book_title' => 'required|string|max:255',
            'book_no' => 'nullable|string|max:255',
            'isbn_no' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'rack_no' => 'nullable|string|max:255',
            'publish' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'qty' => 'required|integer|min:1',
            'perunitcost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $validated['available'] = 'yes';
        $validated['is_active'] = 'yes';
        $validated['postdate'] = now()->toDateString();

        $book = Book::create($validated);

        return $this->successResponse(['book' => $book], 'Book created successfully', 201);
    }

    public function show($id): JsonResponse
    {
        $book = Book::find($id);

        if (!$book) {
            return $this->errorResponse('Book not found', null, 404);
        }

        return $this->successResponse(['book' => $book]);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $book = Book::find($id);

        if (!$book) {
            return $this->errorResponse('Book not found', null, 404);
        }

        $validated = $request->validate([
            'book_title' => 'sometimes|required|string|max:255',
            'book_no' => 'nullable|string|max:255',
            'isbn_no' => 'nullable|string|max:255',
            'subject' => 'nullable|string|max:255',
            'rack_no' => 'nullable|string|max:255',
            'publish' => 'nullable|string|max:255',
            'author' => 'nullable|string|max:255',
            'qty' => 'sometimes|required|integer|min:1',
            'perunitcost' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
            'available' => 'nullable|string|in:yes,no',
        ]);

        $book->update($validated);

        return $this->successResponse(['book' => $book], 'Book updated successfully');
    }

    public function destroy($id): JsonResponse
    {
        $book = Book::find($id);

        if (!$book) {
            return $this->errorResponse('Book not found', null, 404);
        }

        $book->delete();

        return $this->successResponse(null, 'Book deleted successfully');
    }

    public function issue(Request $request): JsonResponse
    {
        $user = $request->user();
        $studentSession = $this->getStudentSession($user);

        if (!$studentSession) {
            return $this->errorResponse('Student session not found');
        }

        $memberType = 'student';
        $checkIsMember = LibraryMember::where('member_id', $studentSession->student_id)
            ->where('member_type', $memberType)
            ->first();

        $data = [
            'title' => 'Add Book',
            'title_list' => 'Book Details',
        ];

        if ($checkIsMember) {
            $data['bookList'] = Book::where('available', 'yes')->get();
            $data['isCheck'] = '1';
        } else {
            $data['isCheck'] = '0';
        }

        return $this->successResponse($data);
    }

    private function getStudentSession($user)
    {
        $studentId = null;

        if ($user->role === 'student') {
            $studentId = $user->user_id;
        } elseif ($user->role === 'parent') {
            $student = Student::where('parent_id', $user->id)->first();
            $studentId = $student ? $student->id : null;
        }

        if (!$studentId) {
            return null;
        }

        $setting = Setting::where('is_active', 'yes')->first();

        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->id))
            ->first();
    }
}
