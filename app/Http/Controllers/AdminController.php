<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    /**
     * Admin Dashboard with overview KPIs and charts.
     */
    public function dashboard()
    {
        $totalUsers = User::count();
        $activeUsers = User::where('status', 'active')->count();
        $adminCount = User::where('role', 'admin')->count();
        $recentUsers = User::latest()->take(5)->get();

        // System information
        $dbVersion = DB::select('select version() as ver')[0]->ver ?? 'MySQL';
        $phpVersion = PHP_VERSION;
        $laravelVersion = app()->version();

        // Mock monthly data for Chart.js
        $chartData = [
            'labels' => ['Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11'],
            'visits' => [1250, 1900, 2400, 3100, 4200, 5600],
            'users' => [12, 19, 32, 45, 68, 89],
        ];

        return view('admin.dashboard', compact(
            'totalUsers',
            'activeUsers',
            'adminCount',
            'recentUsers',
            'dbVersion',
            'phpVersion',
            'laravelVersion',
            'chartData'
        ));
    }

    /**
     * User Management list.
     */
    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        return view('admin.users', compact('users'));
    }

    /**
     * Store new user.
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:6',
            'role' => 'required|in:admin,user',
            'status' => 'required|in:active,inactive',
        ]);

        $validated['password'] = bcrypt($validated['password']);

        User::create($validated);

        return back()->with('success', 'Thêm người dùng mới thành công!');
    }

    /**
     * Toggle user status (active / inactive).
     */
    public function toggleUserStatus($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể tự khóa tài khoản của chính bạn!');
        }

        $user->status = ($user->status === 'active') ? 'inactive' : 'active';
        $user->save();

        return back()->with('success', 'Cập nhật trạng thái người dùng thành công!');
    }

    /**
     * Delete user.
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Không thể xóa tài khoản của chính bạn!');
        }

        $user->delete();

        return back()->with('success', 'Xóa người dùng thành công!');
    }

    /**
     * Admin Settings page.
     */
    public function settings()
    {
        return view('admin.settings');
    }

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        Artisan::call('optimize:clear');
        return back()->with('success', 'Đã xóa bộ nhớ đệm (Cache & View) thành công!');
    }
}
