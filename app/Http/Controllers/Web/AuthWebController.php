<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Modules\Core\Entities\User;

class AuthWebController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('privacy.policy');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $credentials = $request->only('username', 'password');

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'username' => __('messages.account_disabled'),
                ])->withInput($request->only('username'));
            }

            return redirect()->intended(route('privacy.policy'));
        }

        return back()->withErrors([
            'username' => __('messages.invalid_credentials'),
        ])->withInput($request->only('username'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('web.login');
    }

    public function showForgotPassword()
    {
        return view('auth.forgot-password');
    }

    public function forgotPassword(Request $request)
    {
        $request->validate([
            'identifier' => 'required|string',
        ]);

        $identifier = trim($request->input('identifier'));

        $user = $this->findUserByIdentifier($identifier);

        if (!$user) {
            return back()->withErrors([
                'identifier' => __('messages.no_account_found'),
            ])->withInput();
        }

        $email = $this->getUserEmail($user);

        if (!$email) {
            return back()->withErrors([
                'identifier' => __('messages.no_email_on_account'),
            ])->withInput();
        }

        $token = Password::createToken($user);

        try {
            $user->notify(new \Illuminate\Auth\Notifications\ResetPassword($token));
        } catch (\Exception $e) {
            // Token created — notification transport may be unconfigured
        }

        return back()->with('status', __('messages.reset_link_sent'));
    }

    public function showResetPassword(Request $request)
    {
        $token = $request->query('token');

        if (!$token) {
            return redirect()->route('web.forgot.password');
        }

        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password'       => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('web.login')->with('status', __('messages.password_reset_success'))
            : back()->withErrors(['email' => __('messages.password_reset_failed')]);
    }

    /**
     * Find a User by email or phone number.
     * The users table has no email/phone columns — we look up via
     * students.mobileno / students.email and staff.contact_no / staff.email.
     */
    protected function findUserByIdentifier(string $identifier): ?User
    {
        // Try student email
        $student = \Modules\Academic\Entities\Student::where('email', $identifier)->first();
        if ($student) {
            return User::find($student->parent_id);
        }

        // Try student phone
        $student = \Modules\Academic\Entities\Student::where('mobileno', $identifier)->first();
        if ($student) {
            return User::find($student->parent_id);
        }

        // Try staff email
        $staff = \Modules\Staff\Entities\Staff::where('email', $identifier)->first();
        if ($staff) {
            return User::where('id', $staff->user_id)->first();
        }

        // Try staff phone
        $staff = \Modules\Staff\Entities\Staff::where('contact_no', $identifier)->first();
        if ($staff) {
            return User::where('id', $staff->user_id)->first();
        }

        return null;
    }

    /**
     * Get the email address associated with a User (via student or staff).
     */
    protected function getUserEmail(User $user): ?string
    {
        if ($user->role === 'student' || $user->role === 'parent') {
            $student = $user->student;
            if ($student && !empty($student->email)) {
                return $student->email;
            }
        }

        if (in_array($user->role, ['teacher', 'staff', 'accountant', 'librarian'])) {
            $staff = $user->staff;
            if ($staff && !empty($staff->email)) {
                return $staff->email;
            }
        }

        return null;
    }
}
