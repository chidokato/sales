<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Danh sách các vai trò (Roles) trong hệ thống
     */
    public const ROLES = [
        'admin' => [
            'name' => 'Quản trị viên (Admin)',
            'short_name' => 'Admin',
            'badge' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'icon' => 'fa-shield-halved',
            'desc' => 'Toàn quyền quản trị hệ thống, quản lý tài khoản, chỉ căn và cấu hình.'
        ],
        'manager' => [
            'name' => 'Quản lý dự án (Manager)',
            'short_name' => 'Manager',
            'badge' => 'bg-blue-50 text-blue-700 border-blue-200',
            'icon' => 'fa-user-tie',
            'desc' => 'Quản lý toàn bộ dự án, chỉ căn, xem danh sách người dùng và dữ liệu.'
        ],
        'editor' => [
            'name' => 'Biên tập viên (Editor)',
            'short_name' => 'Editor',
            'badge' => 'bg-amber-50 text-amber-700 border-amber-200',
            'icon' => 'fa-pen-to-square',
            'desc' => 'Biên tập chỉ căn dự án, cập nhật trạng thái căn và tải lên mặt bằng.'
        ],
        'user' => [
            'name' => 'Người dùng thường (User)',
            'short_name' => 'User',
            'badge' => 'bg-slate-100 text-slate-600 border-slate-200',
            'icon' => 'fa-user',
            'desc' => 'Người dùng/Khách hàng chỉ xem thông tin ngoài giao diện frontend.'
        ],
    ];

    /**
     * Danh sách tất cả các quyền hạn (Permissions) theo nhóm
     */
    public const AVAILABLE_PERMISSIONS = [
        'users' => [
            'group_name' => 'Quản lý Người dùng & Phân quyền',
            'group_icon' => 'fa-users-gear',
            'items' => [
                'users.view' => 'Xem danh sách tài khoản',
                'users.create' => 'Thêm tài khoản người dùng mới',
                'users.edit' => 'Chỉnh sửa thông tin & phân quyền',
                'users.delete' => 'Xóa hoặc khóa tài khoản',
            ]
        ],
        'unit_locators' => [
            'group_name' => 'Chỉ căn Dự án Bất động sản',
            'group_icon' => 'fa-map-location-dot',
            'items' => [
                'unit_locators.view' => 'Xem danh sách & bản đồ chỉ căn',
                'unit_locators.create' => 'Tạo chỉ căn & tải mặt bằng tầng mới',
                'unit_locators.edit' => 'Chỉnh sửa tọa độ SVG & trạng thái căn',
                'unit_locators.delete' => 'Xóa chỉ căn, mặt bằng & cấu hình',
                'unit_locators.import' => 'Nhập dữ liệu Excel căn hộ',
            ]
        ],
        'databases' => [
            'group_name' => 'Cơ sở dữ liệu dùng chung',
            'group_icon' => 'fa-database',
            'items' => [
                'databases.view' => 'Xem danh sách database dùng chung',
                'databases.manage' => 'Thêm, sửa, kiểm tra kết nối & quản lý CSDL',
            ]
        ],
        'settings' => [
            'group_name' => 'Cấu hình & Hệ thống',
            'group_icon' => 'fa-sliders',
            'items' => [
                'settings.view' => 'Xem thông tin & trạng thái hệ thống',
                'settings.manage' => 'Dọn dẹp bộ nhớ đệm cache & cấu hình',
            ]
        ]
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'status',
        'permissions',
        'avatar',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'permissions' => 'array',
    ];

    /**
     * Check if user has admin role.
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    /**
     * Check if user has manager role.
     */
    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    /**
     * Check if user has editor role.
     */
    public function isEditor(): bool
    {
        return $this->role === 'editor';
    }

    /**
     * Check if user has access to Admin Dashboard.
     */
    public function canAccessAdmin(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        return in_array($this->role, ['admin', 'manager', 'editor']) || $this->hasPermission('admin.access');
    }

    /**
     * Lấy danh sách quyền mặc định theo vai trò (Role defaults).
     */
    public static function getDefaultPermissionsForRole(string $role): array
    {
        switch ($role) {
            case 'admin':
                $all = [];
                foreach (self::AVAILABLE_PERMISSIONS as $group) {
                    foreach (array_keys($group['items']) as $permKey) {
                        $all[] = $permKey;
                    }
                }
                return $all;

            case 'manager':
                return [
                    'users.view',
                    'unit_locators.view',
                    'unit_locators.create',
                    'unit_locators.edit',
                    'unit_locators.delete',
                    'unit_locators.import',
                    'databases.view',
                    'settings.view',
                ];

            case 'editor':
                return [
                    'unit_locators.view',
                    'unit_locators.create',
                    'unit_locators.edit',
                    'unit_locators.import',
                ];

            default:
                return [];
        }
    }

    /**
     * Lấy tất cả quyền hạn có hiệu lực của user.
     */
    public function getAllPermissions(): array
    {
        if ($this->isAdmin()) {
            return self::getDefaultPermissionsForRole('admin');
        }

        // Nếu user có permissions tùy biến được gán riêng
        if (is_array($this->permissions) && count($this->permissions) > 0) {
            return $this->permissions;
        }

        // Ngược lại lấy quyền mặc định theo vai trò
        return self::getDefaultPermissionsForRole($this->role ?? 'user');
    }

    /**
     * Kiểm tra xem user có quyền cụ thể hay không.
     */
    public function hasPermission(string $permission): bool
    {
        // Admin luôn có mọi quyền
        if ($this->isAdmin()) {
            return true;
        }

        // Quyền truy cập cơ bản admin
        if ($permission === 'admin.access') {
            return in_array($this->role, ['admin', 'manager', 'editor']);
        }

        $effectivePermissions = $this->getAllPermissions();

        return in_array($permission, $effectivePermissions);
    }

    /**
     * Kiểm tra user có ít nhất 1 trong các quyền được truyền vào.
     */
    public function hasAnyPermission(array $permissions): bool
    {
        if ($this->isAdmin()) {
            return true;
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermission($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Accessor: Tên hiển thị của vai trò
     */
    public function getRoleNameAttribute(): string
    {
        return self::ROLES[$this->role]['name'] ?? ucfirst($this->role);
    }

    /**
     * Accessor: Badge CSS của vai trò
     */
    public function getRoleBadgeAttribute(): string
    {
        return self::ROLES[$this->role]['badge'] ?? 'bg-slate-100 text-slate-600 border-slate-200';
    }

    /**
     * Accessor: Icon của vai trò
     */
    public function getRoleIconAttribute(): string
    {
        return self::ROLES[$this->role]['icon'] ?? 'fa-user';
    }
}
