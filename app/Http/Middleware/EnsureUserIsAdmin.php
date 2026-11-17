<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string|null  $permission
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, ?string $permission = null)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        if ($user->status !== 'active') {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('login')->withErrors(['email' => 'Tài khoản của bạn hiện đang bị khóa hoặc ngưng hoạt động.']);
        }

        if (!$user->canAccessAdmin()) {
            abort(403, 'Bạn không có quyền truy cập vào khu vực quản trị.');
        }

        if ($permission && !$user->hasPermission($permission)) {
            abort(403, 'Bạn không có quyền hạn [' . $permission . '] để truy cập chức năng này.');
        }

        return $next($request);
    }
}
