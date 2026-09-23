<?php
use Illuminate\Support\Facades\Route;
use Modules\Operations\Http\Controllers\Api\BookController;
use Modules\Operations\Http\Controllers\Api\ChatController;
use Modules\Operations\Http\Controllers\Api\ContentController;
use Modules\Operations\Http\Controllers\Api\HostelController;
use Modules\Operations\Http\Controllers\Api\HostelRoomController;
use Modules\Operations\Http\Controllers\Api\NotificationController;
use Modules\Operations\Http\Controllers\Api\RouteController;
use Modules\Operations\Http\Controllers\Api\VideoTutorialController;
use Modules\Operations\Http\Controllers\Api\VisitorController;
use Modules\Operations\Http\Controllers\Api\AdmissionController;
use Modules\Operations\Http\Controllers\Api\BehaviourController;

Route::prefix('api')->group(function () {
    // Public admission routes
    Route::get('/admission', [AdmissionController::class, 'index']);
    Route::get('/admission/form_config', [AdmissionController::class, 'form_config']);
    Route::get('/admission/classes', [AdmissionController::class, 'classes']);
    Route::get('/admission/sections', [AdmissionController::class, 'sections']);
    Route::post('/admission/submit', [AdmissionController::class, 'submit'])->middleware('throttle:10,1');
    Route::get('/admission/status', [AdmissionController::class, 'status']);
});

Route::middleware('auth:sanctum')->prefix('api')->group(function () {
    Route::get('/behaviour', [BehaviourController::class, 'index']);
    Route::post('/behaviour/addmessage', [BehaviourController::class, 'addmessage']);
    Route::post('/behaviour/getmessage', [BehaviourController::class, 'getmessage']);
    Route::post('/behaviour/delete_comment', [BehaviourController::class, 'delete_comment']);

    Route::get('/book', [BookController::class, 'index']);
    Route::get('/book/issue', [BookController::class, 'issue']);
    
    Route::get('/chat/myuser', [ChatController::class, 'myuser']);
    Route::post('/chat/getChatRecord', [ChatController::class, 'getChatRecord']);
    Route::post('/chat/chatUpdate', [ChatController::class, 'chatUpdate'])->middleware('throttle:30,1');
    Route::post('/chat/searchuser', [ChatController::class, 'searchuser']);
    Route::post('/chat/mynewuser', [ChatController::class, 'mynewuser']);
    Route::post('/chat/adduser', [ChatController::class, 'adduser']);
    Route::get('/chat/mychatnotification', [ChatController::class, 'mychatnotification']);
    Route::post('/chat/newMessage', [ChatController::class, 'newMessage'])->middleware('throttle:30,1');
    
    Route::get('/content/list', [ContentController::class, 'list']);
    Route::get('/content/getsharelist', [ContentController::class, 'getsharelist']);
    Route::get('/content/assignment', [ContentController::class, 'assignment']);
    Route::get('/content/studymaterial', [ContentController::class, 'studymaterial']);
    Route::get('/content/syllabus', [ContentController::class, 'syllabus']);
    Route::get('/content/other', [ContentController::class, 'other']);
    Route::get('/content/download/{file}', [ContentController::class, 'download']);
    Route::get('/content/{id}', [ContentController::class, 'view']);
    
    Route::get('/hostel', [HostelController::class, 'index']);
    Route::get('/hostel/room', [HostelRoomController::class, 'index']);
    
    Route::get('/notification', [NotificationController::class, 'index']);
    Route::post('/notification/updatestatus', [NotificationController::class, 'updatestatus']);
    Route::post('/notification/read', [NotificationController::class, 'read']);
    Route::get('/notification/download/{id}', [NotificationController::class, 'download']);
    Route::get('/notification/{id}', [NotificationController::class, 'notification']);
    
    Route::get('/route', [RouteController::class, 'index']);
    Route::post('/route/getbusdetail', [RouteController::class, 'getbusdetail']);
    
    Route::get('/video_tutorial', [VideoTutorialController::class, 'index']);
    Route::get('/video_tutorial/{id}', [VideoTutorialController::class, 'view']);
    
    Route::get('/visitors', [VisitorController::class, 'index']);
});
