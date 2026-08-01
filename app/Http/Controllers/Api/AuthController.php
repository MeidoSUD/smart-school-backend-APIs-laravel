<?php

namespace App\Http\Controllers\Api;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\ChangePasswordRequest;
use App\Models\User;
use App\Services\ApiLogger;
use App\Services\UserResponseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AuthController extends \App\Http\Controllers\Api\Controller
{
    public function __construct(
        private readonly UserResponseService $userResponseService
    ) {
        $this->setControllerName('AuthController');
    }

    public function login(LoginRequest $request): JsonResponse
    {
        $credentials = $request->validated();

        ApiLogger::logAuth('login_attempt', $credentials['username'], false);

        $user = User::where('username', $credentials['username'])->first();

        if (!$user) {
            ApiLogger::logAuth('login_failed', $credentials['username'], false);
            return $this->errorResponse('Invalid username or password', null, 401);
        }

        if (!$user->isActive()) {
            ApiLogger::logAuth('login_disabled', $credentials['username'], false, $user->id);
            return $this->errorResponse('Your account is disabled, please contact administrator.', null, 403);
        }

        if (!Hash::check($credentials['password'], $user->password)) {
            ApiLogger::logAuth('login_failed', $credentials['username'], false, $user->id);
            return $this->errorResponse('Invalid username or password', null, 401);
        }

        $token = $user->createToken('api-token', [$user->role])->plainTextToken;

        ApiLogger::logAuth('login_success', $credentials['username'], true, $user->id);

        $userData = $this->userResponseService->buildUserResponse($user);

        return $this->successResponse([
            'token' => $token,
            'user' => $userData,
        ], 'Login successful');
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user) {
            $userId = $user->id;
            $username = $user->username;

            if ($request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            } else {
                $user->tokens()->delete();
            }

            ApiLogger::logAuth('logout', $username, true, $userId);

            return $this->successResponse(null, 'Logged out successfully');
        }

        return $this->errorResponse('Not logged in', null, 401);
    }

    public function changePassword(ChangePasswordRequest $request): JsonResponse
    {
        $user = $request->user();

        if (!$user) {
            return $this->errorResponse('Unauthorized', null, 401);
        }

        if ($user->role === 'guest') {
            return $this->errorResponse('Guest users cannot change password here');
        }

        $validated = $request->validated();

        if (!Hash::check($validated['current_pass'], $user->password)) {
            return $this->errorResponse('Invalid current password');
        }

        DB::transaction(function () use ($user, $validated) {
            $user->password = Hash::make($validated['new_pass']);
            $user->save();
            $user->tokens()->delete();
        });

        return $this->successResponse(null, 'Password changed successfully');
    }
}
