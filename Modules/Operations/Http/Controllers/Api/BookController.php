<?php

namespace Modules\Operations\Http\Controllers\Api;

use Modules\Operations\Entities\Book;
use Modules\Operations\Entities\BookIssue;
use Modules\Operations\Entities\LibraryMember;
use Modules\Academic\Entities\StudentSession;
use Modules\Academic\Entities\Student;
use Modules\Core\Entities\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Converted from CodeIgniter: codelgiterControllers/user/Book.php
 */
class BookController extends \Modules\Core\Http\Controllers\Api\Controller
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
        $studentSession = $this->studentSession($request);
        
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



}
