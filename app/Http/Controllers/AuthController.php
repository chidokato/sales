<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Show the login form.
     */
    public function showLogin()
    {
        if (Auth::check()) {
            if (Auth::user()->canAccessAdmin()) {
                return redirect()->route('admin.dashboard');
            }
            return redirect()->route('home');
        }

        return view('auth.login');
    }

    /**
     * Handle an authentication attempt.
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ], [
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không hợp lệ.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if ($user->status !== 'active') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Tài khoản của bạn hiện đang bị khóa hoặc chưa kích hoạt.',
                    ], 403);
                }

                return back()->withErrors([
                    'email' => 'Tài khoản của bạn hiện đang bị khóa hoặc chưa kích hoạt.',
                ]);
            }

            $request->session()->regenerate();

            // Cập nhật thời điểm đăng nhập gần nhất
            $user->update(['last_login_at' => now()]);

            $redirectUrl = $user->canAccessAdmin() ? route('admin.dashboard') : route('home');

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Chào mừng bạn quay trở lại, ' . $user->name . '!',
                    'redirect' => $redirectUrl,
                    'user' => [
                        'name' => $user->name,
                        'email' => $user->email,
                        'role' => $user->role,
                    ]
                ]);
            }

            return redirect()->intended($redirectUrl)
                ->with('success', 'Chào mừng bạn quay trở lại, ' . $user->name . '!');
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Thông tin đăng nhập không chính xác hoặc tài khoản không tồn tại.',
            ], 422);
        }

        return back()->withErrors([
            'email' => 'Thông tin đăng nhập không chính xác hoặc tài khoản không tồn tại.',
        ])->onlyInput('email');
    }

    /**
     * Log the user out of the application.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Bạn đã đăng xuất thành công.',
                'redirect' => route('home'),
            ]);
        }

        return redirect()->route('home')->with('info', 'Bạn đã đăng xuất thành công.');
    }
}
