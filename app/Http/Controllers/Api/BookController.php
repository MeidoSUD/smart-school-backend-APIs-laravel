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

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Book.php
 */
class BookController extends Controller
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


        
        $setting = Setting::where('is_active', 1)->first();
        
        return StudentSession::where('student_id', $studentId)
            ->when($setting, fn($q) => $q->where('session_id', $setting->id))
            ->first();
        }


    }
