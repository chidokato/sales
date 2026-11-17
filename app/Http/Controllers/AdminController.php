<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
     * User Management list with statistics and advanced filters.
     */
    public function users(Request $request)
    {
        $query = User::query();

        // Tìm kiếm theo tên, email hoặc số điện thoại
        if ($request->filled('search')) {
            $search = trim($request->get('search'));
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Lọc theo vai trò
        if ($request->filled('role')) {
            $query->where('role', $request->get('role'));
        }

        // Lọc theo trạng thái
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        $users = $query->latest()->paginate(10)->withQueryString();

        // Thống kê nhanh người dùng
        $stats = [
            'total' => User::count(),
            'active' => User::where('status', 'active')->count(),
            'inactive' => User::where('status', 'inactive')->count(),
            'admins' => User::where('role', 'admin')->count(),
            'staff' => User::whereIn('role', ['admin', 'manager', 'editor'])->count(),
        ];

        $roles = User::ROLES;
        $permissionGroups = User::AVAILABLE_PERMISSIONS;

        return view('admin.users', compact('users', 'stats', 'roles', 'permissionGroups'));
    }

    /**
     * Lấy dữ liệu chi tiết của 1 User dưới dạng JSON để phục vụ Modal Xem / Sửa.
     */
    public function getUserData($id)
    {
        $user = User::findOrFail($id);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ?? '',
                'role' => $user->role,
                'role_name' => $user->role_name,
                'role_badge' => $user->role_badge,
                'role_icon' => $user->role_icon,
                'status' => $user->status,
                'permissions' => $user->permissions ?? [],
                'effective_permissions' => $user->getAllPermissions(),
                'created_at' => $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-',
                'last_login_at' => $user->last_login_at ? $user->last_login_at->format('d/m/Y H:i') : 'Chưa đăng nhập',
            ]
        ]);
    }

    /**
     * Thêm người dùng mới & Phân quyền.
     */
    public function storeUser(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'phone' => 'nullable|string|max:20',
            'password' => 'required|string|min:6',
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'status' => 'required|in:active,inactive',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Địa chỉ email này đã tồn tại trong hệ thống.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải có độ dài tối thiểu 6 ký tự.',
            'role.required' => 'Vui lòng chọn vai trò cho người dùng.',
        ]);

        $validated['password'] = Hash::make($validated['password']);

        // Nếu là admin thì không cần lưu permissions tùy biến (admin luôn full quyền)
        if ($validated['role'] === 'admin') {
            $validated['permissions'] = null;
        }

        User::create($validated);

        return back()->with('success', 'Thêm người dùng mới và thiết lập quyền thành công!');
    }

    /**
     * Cập nhật thông tin & Phân quyền người dùng (Edit / Update).
     */
    public function updateUser(Request $request, $id)
    {
        $user = User::findOrFail($id);

        // Bảo vệ an toàn: Nếu tự sửa chính mình thì luôn giữ role hiện tại và status active (kể cả khi form không gửi lên do disabled)
        if ($user->id === auth()->id()) {
            $request->merge([
                'role' => $user->role,
                'status' => 'active',
            ]);
        } else {
            // Nếu role hoặc status bị thiếu trong request, giữ nguyên giá trị hiện tại của user
            if (!$request->filled('role')) {
                $request->merge(['role' => $user->role]);
            }
            if (!$request->filled('status')) {
                $request->merge(['status' => $user->status]);
            }
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => 'nullable|string|max:20',
            'password' => 'nullable|string|min:6',
            'role' => ['required', Rule::in(array_keys(User::ROLES))],
            'status' => 'required|in:active,inactive',
            'permissions' => 'nullable|array',
            'permissions.*' => 'string',
        ], [
            'name.required' => 'Vui lòng nhập họ và tên.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'email.unique' => 'Địa chỉ email này đã được sử dụng bởi người dùng khác.',
            'password.min' => 'Mật khẩu mới phải có tối thiểu 6 ký tự.',
            'role.required' => 'Vui lòng chọn vai trò.',
        ]);

        // Bảo vệ an toàn: Người dùng hiện tại không thể tự hạ quyền hoặc tự khóa chính mình
        if ($user->id === auth()->id()) {
            if ($validated['status'] !== 'active') {
                return back()->with('error', 'Bạn không thể tự khóa tài khoản của chính mình!');
            }
            if ($validated['role'] !== 'admin' && $user->role === 'admin') {
                return back()->with('error', 'Bạn không thể tự hạ vai trò Quản trị viên của chính mình!');
            }
        }

        // Cập nhật mật khẩu nếu có nhập mật khẩu mới
        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        // Nếu vai trò là admin thì permissions đặt về null (tự động full quyền)
        if ($validated['role'] === 'admin') {
            $validated['permissions'] = null;
        }

        $user->update($validated);

        return back()->with('success', 'Cập nhật thông tin và phân quyền người dùng "' . $user->name . '" thành công!');
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

        // Bảo vệ: Không thể khóa quản trị viên duy nhất
        if ($user->isAdmin() && $user->status === 'active') {
            $activeAdminCount = User::where('role', 'admin')->where('status', 'active')->count();
            if ($activeAdminCount <= 1) {
                return back()->with('error', 'Không thể khóa Quản trị viên duy nhất đang hoạt động của hệ thống!');
            }
        }

        $user->status = ($user->status === 'active') ? 'inactive' : 'active';
        $user->save();

        $statusText = ($user->status === 'active') ? 'Kích hoạt' : 'Tạm khóa';
        return back()->with('success', $statusText . ' tài khoản "' . $user->name . '" thành công!');
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

        // Bảo vệ: Không thể xóa quản trị viên duy nhất
        if ($user->isAdmin()) {
            $adminCount = User::where('role', 'admin')->count();
            if ($adminCount <= 1) {
                return back()->with('error', 'Không thể xóa Quản trị viên duy nhất của hệ thống!');
            }
        }

        $userName = $user->name;
        $user->delete();

        return back()->with('success', 'Xóa tài khoản người dùng "' . $userName . '" thành công!');
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
