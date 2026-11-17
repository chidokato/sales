@extends('layouts.admin')

@section('title', 'Quản lý Người dùng & Phân quyền')
@section('page-title', 'Quản lý Người dùng & Phân quyền')

@section('header-actions')
    <button 
        type="button" 
        @click="$dispatch('open-create-modal')" 
        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition-all duration-200 transform hover:-translate-y-0.5"
        title="Thêm tài khoản người dùng mới"
    >
        <i class="fa-solid fa-user-plus text-xs"></i>
        <span>Thêm người dùng mới</span>
    </button>
@endsection

@section('content')

    <div 
        x-data="{ 
            createModalOpen: false, 
            editModalOpen: false, 
            viewModalOpen: false,
            currentUser: {
                id: '',
                name: '',
                email: '',
                phone: '',
                role: 'user',
                status: 'active',
                permissions: [],
                effective_permissions: [],
                created_at: '',
                last_login_at: ''
            },
            createRole: 'user',
            createPermissions: [],
            editPermissions: [],
            showCreatePassword: false,
            showEditPassword: false,

            // Hàm mở modal sửa
            openEditModal(user) {
                this.currentUser = JSON.parse(JSON.stringify(user));
                this.editPermissions = Array.isArray(user.permissions) ? [...user.permissions] : [];
                this.editModalOpen = true;
            },

            // Hàm mở modal xem chi tiết
            openViewModal(user) {
                this.currentUser = JSON.parse(JSON.stringify(user));
                this.viewModalOpen = true;
            },

            // Toggle chọn tất cả quyền trong 1 nhóm
            toggleGroupPermissions(groupKey, permKeys, targetArray) {
                const allSelected = permKeys.every(k => this[targetArray].includes(k));
                if (allSelected) {
                    this[targetArray] = this[targetArray].filter(k => !permKeys.includes(k));
                } else {
                    const newSet = new Set([...this[targetArray], ...permKeys]);
                    this[targetArray] = Array.from(newSet);
                }
            },

            // Kiểm tra cả nhóm có được chọn hết hay chưa
            isGroupAllSelected(permKeys, targetArray) {
                return permKeys.length > 0 && permKeys.every(k => this[targetArray].includes(k));
            }
        }" 
        @open-create-modal.window="createModalOpen = true"
        class="space-y-6"
    >

        <!-- KPI Summary Cards (Thống kê người dùng & phân quyền) -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3.5">
            <!-- Tổng số người dùng -->
            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-users"></i>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs text-slate-500 font-medium truncate">Tổng người dùng</p>
                    <p class="text-xl font-extrabold text-slate-900 mt-0.5">{{ number_format($stats['total']) }}</p>
                </div>
            </div>

            <!-- Đang hoạt động -->
            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-circle-check"></i>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs text-slate-500 font-medium truncate">Đang hoạt động</p>
                    <p class="text-xl font-extrabold text-emerald-600 mt-0.5">{{ number_format($stats['active']) }}</p>
                </div>
            </div>

            <!-- Quản trị viên (Admin) -->
            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-shield-halved"></i>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs text-slate-500 font-medium truncate">Quản trị viên</p>
                    <p class="text-xl font-extrabold text-indigo-600 mt-0.5">{{ number_format($stats['admins']) }}</p>
                </div>
            </div>

            <!-- Nhân sự quản lý & biên tập (Staff) -->
            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-sky-50 text-sky-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-user-tie"></i>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs text-slate-500 font-medium truncate">Nhân sự quản lý</p>
                    <p class="text-xl font-extrabold text-sky-600 mt-0.5">{{ number_format($stats['staff']) }}</p>
                </div>
            </div>

            <!-- Tài khoản bị khóa -->
            <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-3.5 col-span-2 sm:col-span-1">
                <div class="w-11 h-11 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg shrink-0">
                    <i class="fa-solid fa-user-lock"></i>
                </div>
                <div class="overflow-hidden">
                    <p class="text-xs text-slate-500 font-medium truncate">Tạm khóa</p>
                    <p class="text-xl font-extrabold text-rose-600 mt-0.5">{{ number_format($stats['inactive']) }}</p>
                </div>
            </div>
        </div>

        <!-- Search & Filters Bar -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm">
            <form action="{{ route('admin.users') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                
                <!-- Search Input -->
                <div class="sm:col-span-5 relative">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                        <i class="fa-solid fa-magnifying-glass text-xs"></i>
                    </div>
                    <input 
                        type="text" 
                        name="search" 
                        value="{{ request('search') }}"
                        placeholder="Tìm theo họ tên, email hoặc số điện thoại..." 
                        class="w-full pl-9 pr-4 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium text-slate-800"
                    >
                </div>

                <!-- Role Filter -->
                <div class="sm:col-span-3">
                    <select name="role" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium text-slate-700">
                        <option value="">-- Tất cả vai trò --</option>
                        @foreach ($roles as $rKey => $rInfo)
                            <option value="{{ $rKey }}" {{ request('role') === $rKey ? 'selected' : '' }}>
                                {{ $rInfo['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="sm:col-span-2">
                    <select name="status" class="w-full px-3 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 font-medium text-slate-700">
                        <option value="">-- Trạng thái --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Đang hoạt động</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tạm khóa</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="sm:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2.5 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-sm">
                        <i class="fa-solid fa-filter text-[11px]"></i>
                        <span>Lọc</span>
                    </button>
                    @if (request()->hasAny(['search', 'role', 'status']))
                        <a href="{{ route('admin.users') }}" class="py-2.5 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold transition" title="Bỏ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>

            </form>
        </div>

        <!-- Users Table Container -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100">
                            <th class="py-4 px-6">ID</th>
                            <th class="py-4 px-6">Họ và Tên</th>
                            <th class="py-4 px-6">Email & SĐT</th>
                            <th class="py-4 px-6">Vai trò</th>
                            <th class="py-4 px-6">Phân quyền</th>
                            <th class="py-4 px-6">Trạng thái</th>
                            <th class="py-4 px-6">Đăng nhập cuối</th>
                            <th class="py-4 px-6 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        @forelse ($users as $user)
                            @php
                                $userDataPayload = [
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
                                ];
                            @endphp
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <!-- ID -->
                                <td class="py-4 px-6 text-slate-400 font-mono">#{{ $user->id }}</td>

                                <!-- Họ và Tên + Avatar -->
                                <td class="py-4 px-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold flex items-center justify-center text-xs shadow-sm shrink-0">
                                            {{ strtoupper(substr($user->name, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="flex items-center gap-1.5">
                                                <span class="font-bold text-slate-900">{{ $user->name }}</span>
                                                @if ($user->id === auth()->id())
                                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-100 text-blue-700">Bạn</span>
                                                @endif
                                            </div>
                                            <span class="text-[11px] text-slate-400 block">Tạo ngày {{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}</span>
                                        </div>
                                    </div>
                                </td>

                                <!-- Email & Số điện thoại -->
                                <td class="py-4 px-6">
                                    <div class="space-y-0.5">
                                        <div class="flex items-center gap-1.5 text-slate-700 font-mono">
                                            <i class="fa-regular fa-envelope text-slate-400 text-[10px]"></i>
                                            <span>{{ $user->email }}</span>
                                        </div>
                                        @if ($user->phone)
                                            <div class="flex items-center gap-1.5 text-slate-500 font-mono text-[11px]">
                                                <i class="fa-solid fa-phone text-slate-400 text-[10px]"></i>
                                                <span>{{ $user->phone }}</span>
                                            </div>
                                        @else
                                            <span class="text-slate-300 text-[11px] italic">Chưa có SĐT</span>
                                        @endif
                                    </div>
                                </td>

                                <!-- Vai trò (Role Badge) -->
                                <td class="py-4 px-6">
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $user->role_badge }}">
                                        <i class="fa-solid {{ $user->role_icon }} text-[10px]"></i>
                                        <span>{{ $user->role_name }}</span>
                                    </span>
                                </td>

                                <!-- Phân quyền (Permissions Status) -->
                                <td class="py-4 px-6">
                                    @if ($user->isAdmin())
                                        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-md border border-indigo-100">
                                            <i class="fa-solid fa-star text-[10px] text-amber-500"></i> Toàn quyền
                                        </span>
                                    @elseif (is_array($user->permissions) && count($user->permissions) > 0)
                                        <button 
                                            type="button" 
                                            @click="openViewModal({{ json_encode($userDataPayload) }})" 
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold text-blue-700 bg-blue-50 hover:bg-blue-100 px-2 py-0.5 rounded-md border border-blue-200 transition"
                                            title="Bấm để xem danh sách quyền tùy biến"
                                        >
                                            <i class="fa-solid fa-sliders text-[10px]"></i>
                                            <span>{{ count($user->permissions) }} quyền tùy biến</span>
                                        </button>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-slate-500 bg-slate-50 px-2 py-0.5 rounded-md border border-slate-200">
                                            <i class="fa-regular fa-circle-check text-[10px]"></i> Mặc định vai trò
                                        </span>
                                    @endif
                                </td>

                                <!-- Trạng thái -->
                                <td class="py-4 px-6">
                                    @if ($user->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                            <span>Hoạt động</span>
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span>Tạm khóa</span>
                                        </span>
                                    @endif
                                </td>

                                <!-- Đăng nhập cuối -->
                                <td class="py-4 px-6 text-slate-500">
                                    @if ($user->last_login_at)
                                        <div class="flex items-center gap-1.5 text-[11px]">
                                            <i class="fa-regular fa-clock text-slate-400"></i>
                                            <span>{{ $user->last_login_at->format('d/m/Y H:i') }}</span>
                                        </div>
                                    @else
                                        <span class="text-slate-300 italic text-[11px]">Chưa đăng nhập</span>
                                    @endif
                                </td>

                                <!-- Thao tác -->
                                <td class="py-4 px-6 text-right">
                                    <div class="inline-flex items-center gap-1">
                                        <!-- Xem chi tiết -->
                                        <button 
                                            type="button" 
                                            @click="openViewModal({{ json_encode($userDataPayload) }})"
                                            title="Xem thông tin & phân quyền chi tiết"
                                            class="w-8 h-8 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 flex items-center justify-center transition"
                                        >
                                            <i class="fa-solid fa-eye text-xs"></i>
                                        </button>

                                        <!-- Sửa thông tin & Phân quyền -->
                                        <button 
                                            type="button" 
                                            @click="openEditModal({{ json_encode($userDataPayload) }})"
                                            title="Chỉnh sửa thông tin & phân quyền"
                                            class="w-8 h-8 rounded-lg text-slate-500 hover:text-amber-600 hover:bg-amber-50 flex items-center justify-center transition"
                                        >
                                            <i class="fa-solid fa-pen-to-square text-xs"></i>
                                        </button>

                                        @if ($user->id !== auth()->id())
                                            <!-- Khóa / Mở khóa -->
                                            <form id="toggleUserForm-{{ $user->id }}" action="{{ route('admin.users.toggle', $user->id) }}" method="POST" class="inline">
                                                @csrf
                                                <button 
                                                    type="button" 
                                                    onclick="confirmToggleStatus('toggleUserForm-{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->status }}')"
                                                    title="{{ $user->status === 'active' ? 'Khóa tài khoản' : 'Kích hoạt tài khoản' }}"
                                                    class="w-8 h-8 rounded-lg {{ $user->status === 'active' ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }} flex items-center justify-center transition"
                                                >
                                                    <i class="fa-solid {{ $user->status === 'active' ? 'fa-lock' : 'fa-lock-open' }} text-xs"></i>
                                                </button>
                                            </form>

                                            <!-- Xóa người dùng -->
                                            <form id="deleteUserForm-{{ $user->id }}" action="{{ route('admin.users.delete', $user->id) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="button" 
                                                    onclick="confirmDeleteUser('deleteUserForm-{{ $user->id }}', '{{ addslashes($user->name) }}')"
                                                    title="Xóa vĩnh viễn tài khoản" 
                                                    class="w-8 h-8 rounded-lg text-rose-500 hover:bg-rose-50 flex items-center justify-center transition"
                                                >
                                                    <i class="fa-solid fa-trash-can text-xs"></i>
                                                </button>
                                            </form>
                                        @else
                                            <span class="w-8 h-8 flex items-center justify-center text-slate-300" title="Tài khoản hiện tại của bạn">
                                                <i class="fa-solid fa-user-shield text-xs"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400">
                                    <div class="max-w-xs mx-auto text-center space-y-3">
                                        <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-400 flex items-center justify-center text-xl mx-auto">
                                            <i class="fa-solid fa-user-slash"></i>
                                        </div>
                                        <p class="font-bold text-slate-700 text-sm">Không tìm thấy người dùng</p>
                                        <p class="text-xs text-slate-400">Không có tài khoản nào phù hợp với điều kiện tìm kiếm và lọc của bạn.</p>
                                        @if (request()->hasAny(['search', 'role', 'status']))
                                            <a href="{{ route('admin.users') }}" class="inline-block px-3.5 py-1.5 rounded-xl bg-slate-800 text-white font-bold text-xs hover:bg-slate-900 transition">
                                                Xóa bộ lọc
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if ($users->hasPages())
                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $users->links() }}
                </div>
            @endif
        </div>

        <!-- ============================================================================== -->
        <!-- MODAL 1: THÊM NGƯỜI DÙNG MỚI & PHÂN QUYỀN                                      -->
        <!-- ============================================================================== -->
        <div 
            x-show="createModalOpen" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="create-modal-title" role="dialog" aria-modal="true"
        >
            <!-- Backdrop -->
            <div 
                x-show="createModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="createModalOpen = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div 
                    x-show="createModalOpen"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-2xl bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100 my-8 max-h-[90vh] flex flex-col"
                >
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shadow-sm">
                                <i class="fa-solid fa-user-plus"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900">Thêm người dùng mới</h4>
                                <p class="text-xs text-slate-500">Cấp tài khoản truy cập và thiết lập quyền hạn chi tiết</p>
                            </div>
                        </div>
                        <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-lg hover:bg-slate-100 transition">
                            <i class="fa-solid fa-xmark text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body (Cuộn scroll nếu dài) -->
                    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4.5 mt-5 flex-1 overflow-y-auto pr-1">
                        @csrf
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Họ và Tên -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Họ và Tên <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    name="name" 
                                    required 
                                    placeholder="Ví dụ: Nguyễn Văn A" 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium"
                                >
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Địa chỉ Email <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    name="email" 
                                    required 
                                    placeholder="user@idc.vn" 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium"
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Số điện thoại -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Số điện thoại
                                </label>
                                <input 
                                    type="text" 
                                    name="phone" 
                                    placeholder="0912 345 678" 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium"
                                >
                            </div>

                            <!-- Mật khẩu ban đầu -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Mật khẩu ban đầu <span class="text-rose-500">*</span>
                                </label>
                                <div class="relative">
                                    <input 
                                        :type="showCreatePassword ? 'text' : 'password'" 
                                        name="password" 
                                        required 
                                        minlength="6" 
                                        placeholder="Tối thiểu 6 ký tự" 
                                        class="w-full px-3.5 py-2.5 pr-10 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium"
                                    >
                                    <button 
                                        type="button" 
                                        @click="showCreatePassword = !showCreatePassword" 
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600"
                                    >
                                        <i class="fa-solid" :class="showCreatePassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Vai trò chính -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Vai trò hệ thống <span class="text-rose-500">*</span>
                                </label>
                                <select 
                                    name="role" 
                                    x-model="createRole" 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none font-semibold text-slate-800"
                                >
                                    @foreach ($roles as $rKey => $rInfo)
                                        <option value="{{ $rKey }}">{{ $rInfo['name'] }}</option>
                                    @endforeach
                                </select>
                                <p class="text-[11px] text-slate-500 mt-1 italic" x-show="createRole === 'admin'">
                                    * Quản trị viên (Admin) tự động có toàn bộ quyền hạn trong hệ thống.
                                </p>
                            </div>

                            <!-- Trạng thái -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Trạng thái tài khoản <span class="text-rose-500">*</span>
                                </label>
                                <select 
                                    name="status" 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none font-semibold text-slate-800"
                                >
                                    <option value="active">Đang hoạt động (Active)</option>
                                    <option value="inactive">Tạm khóa (Inactive)</option>
                                </select>
                            </div>
                        </div>

                        <!-- KHU VỰC THIẾT LẬP PHÂN QUYỀN CHI TIẾT (PERMISSIONS MATRIX) -->
                        <div class="pt-4 border-t border-slate-100" x-show="createRole !== 'admin'">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h5 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-key text-blue-600"></i>
                                        <span>Phân quyền chi tiết (Tùy chọn)</span>
                                    </h5>
                                    <p class="text-[11px] text-slate-500">Mặc định tài khoản sẽ thừa hưởng quyền theo vai trò. Bạn có thể chọn riêng các quyền bổ sung bên dưới.</p>
                                </div>
                            </div>

                            <div class="space-y-3.5">
                                @foreach ($permissionGroups as $gKey => $group)
                                    @php
                                        $permKeys = array_keys($group['items']);
                                    @endphp
                                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/50 p-3.5">
                                        <div class="flex items-center justify-between pb-2 border-b border-slate-200/60 mb-2.5">
                                            <span class="text-xs font-bold text-slate-800 flex items-center gap-2">
                                                <i class="fa-solid {{ $group['group_icon'] }} text-blue-600"></i>
                                                <span>{{ $group['group_name'] }}</span>
                                            </span>
                                            <button 
                                                type="button" 
                                                @click="toggleGroupPermissions('{{ $gKey }}', {{ json_encode($permKeys) }}, 'createPermissions')"
                                                class="text-[11px] font-bold text-blue-600 hover:text-blue-800 transition"
                                            >
                                                <span x-text="isGroupAllSelected({{ json_encode($permKeys) }}, 'createPermissions') ? 'Bỏ chọn nhóm' : 'Chọn cả nhóm'"></span>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach ($group['items'] as $pKey => $pLabel)
                                                <label class="flex items-start gap-2 p-2 rounded-xl bg-white border border-slate-100 hover:border-blue-200 cursor-pointer transition">
                                                    <input 
                                                        type="checkbox" 
                                                        name="permissions[]" 
                                                        value="{{ $pKey }}"
                                                        x-model="createPermissions"
                                                        class="mt-0.5 rounded text-blue-600 focus:ring-blue-500 border-slate-300"
                                                    >
                                                    <div class="text-[11px]">
                                                        <span class="font-bold text-slate-800 block">{{ $pLabel }}</span>
                                                        <span class="text-[10px] text-slate-400 font-mono">{{ $pKey }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Footer Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 shrink-0">
                            <button 
                                type="button" 
                                @click="createModalOpen = false" 
                                class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
                            >
                                Hủy bỏ
                            </button>
                            <button 
                                type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition flex items-center gap-2"
                            >
                                <i class="fa-solid fa-check"></i>
                                <span>Tạo tài khoản</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================================================== -->
        <!-- MODAL 2: CHỈNH SỬA NGƯỜI DÙNG & PHÂN QUYỀN (EDIT USER)                          -->
        <!-- ============================================================================== -->
        <div 
            x-show="editModalOpen" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="edit-modal-title" role="dialog" aria-modal="true"
        >
            <!-- Backdrop -->
            <div 
                x-show="editModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="editModalOpen = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div 
                    x-show="editModalOpen"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-2xl bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100 my-8 max-h-[90vh] flex flex-col"
                >
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg shadow-sm">
                                <i class="fa-solid fa-user-pen"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900">
                                    Chỉnh sửa người dùng: <span class="text-blue-600" x-text="currentUser.name"></span>
                                </h4>
                                <p class="text-xs text-slate-500">Cập nhật thông tin, thay đổi vai trò hoặc cấp quyền tùy chỉnh</p>
                            </div>
                        </div>
                        <button @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-lg hover:bg-slate-100 transition">
                            <i class="fa-solid fa-xmark text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Body Form -->
                    <form 
                        :action="'{{ url('admin/users') }}/' + currentUser.id" 
                        method="POST" 
                        class="space-y-4.5 mt-5 flex-1 overflow-y-auto pr-1"
                    >
                        @csrf
                        @method('PUT')
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Họ và Tên -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Họ và Tên <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="text" 
                                    name="name" 
                                    x-model="currentUser.name"
                                    required 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium"
                                >
                            </div>

                            <!-- Email -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Địa chỉ Email <span class="text-rose-500">*</span>
                                </label>
                                <input 
                                    type="email" 
                                    name="email" 
                                    x-model="currentUser.email"
                                    required 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium"
                                >
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Số điện thoại -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Số điện thoại
                                </label>
                                <input 
                                    type="text" 
                                    name="phone" 
                                    x-model="currentUser.phone"
                                    placeholder="0912 345 678" 
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium"
                                >
                            </div>

                            <!-- Mật khẩu mới (Tùy chọn) -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Đổi mật khẩu mới <span class="text-slate-400 font-normal lowercase">(bỏ trống nếu giữ nguyên)</span>
                                </label>
                                <div class="relative">
                                    <input 
                                        :type="showEditPassword ? 'text' : 'password'" 
                                        name="password" 
                                        autocomplete="new-password"
                                        minlength="6" 
                                        placeholder="Nhập nếu muốn đổi mật khẩu" 
                                        class="w-full px-3.5 py-2.5 pr-10 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none font-medium"
                                    >
                                    <button 
                                        type="button" 
                                        @click="showEditPassword = !showEditPassword" 
                                        class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-slate-400 hover:text-slate-600"
                                    >
                                        <i class="fa-solid" :class="showEditPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <!-- Vai trò -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Vai trò hệ thống <span class="text-rose-500">*</span>
                                </label>
                                
                                <!-- Hidden input để luôn submit role khi select bị disabled -->
                                <template x-if="currentUser.id == {{ auth()->id() }}">
                                    <input type="hidden" name="role" :value="currentUser.role">
                                </template>
                                
                                <select 
                                    :name="currentUser.id == {{ auth()->id() }} ? '' : 'role'" 
                                    x-model="currentUser.role"
                                    :disabled="currentUser.id == {{ auth()->id() }}"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none font-semibold text-slate-800 disabled:opacity-60 disabled:cursor-not-allowed disabled:bg-slate-100"
                                >
                                    @foreach ($roles as $rKey => $rInfo)
                                        <option value="{{ $rKey }}">{{ $rInfo['name'] }}</option>
                                    @endforeach
                                </select>
                                <template x-if="currentUser.id == {{ auth()->id() }}">
                                    <p class="text-[11px] text-amber-600 mt-1">
                                        <i class="fa-solid fa-lock text-[10px]"></i> Bạn không thể tự thay đổi vai trò của chính mình.
                                    </p>
                                </template>
                            </div>

                            <!-- Trạng thái -->
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">
                                    Trạng thái tài khoản <span class="text-rose-500">*</span>
                                </label>
                                
                                <!-- Hidden input để luôn submit status khi select bị disabled -->
                                <template x-if="currentUser.id == {{ auth()->id() }}">
                                    <input type="hidden" name="status" :value="currentUser.status">
                                </template>

                                <select 
                                    :name="currentUser.id == {{ auth()->id() }} ? '' : 'status'" 
                                    x-model="currentUser.status"
                                    :disabled="currentUser.id == {{ auth()->id() }}"
                                    class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none font-semibold text-slate-800 disabled:opacity-60 disabled:cursor-not-allowed disabled:bg-slate-100"
                                >
                                    <option value="active">Đang hoạt động (Active)</option>
                                    <option value="inactive">Tạm khóa (Inactive)</option>
                                </select>
                                <template x-if="currentUser.id == {{ auth()->id() }}">
                                    <p class="text-[11px] text-amber-600 mt-1">
                                        <i class="fa-solid fa-lock text-[10px]"></i> Bạn không thể tự khóa tài khoản của chính mình.
                                    </p>
                                </template>
                            </div>
                        </div>

                        <!-- PHÂN QUYỀN CHI TIẾT KHI SỬA -->
                        <div class="pt-4 border-t border-slate-100" x-show="currentUser.role !== 'admin'">
                            <div class="flex items-center justify-between mb-3">
                                <div>
                                    <h5 class="text-xs font-bold uppercase tracking-wider text-slate-800 flex items-center gap-1.5">
                                        <i class="fa-solid fa-key text-amber-600"></i>
                                        <span>Phân quyền chi tiết</span>
                                    </h5>
                                    <p class="text-[11px] text-slate-500">Tùy biến các quyền cụ thể cho người dùng này:</p>
                                </div>
                            </div>

                            <div class="space-y-3.5">
                                @foreach ($permissionGroups as $gKey => $group)
                                    @php
                                        $permKeys = array_keys($group['items']);
                                    @endphp
                                    <div class="rounded-2xl border border-slate-200/80 bg-slate-50/50 p-3.5">
                                        <div class="flex items-center justify-between pb-2 border-b border-slate-200/60 mb-2.5">
                                            <span class="text-xs font-bold text-slate-800 flex items-center gap-2">
                                                <i class="fa-solid {{ $group['group_icon'] }} text-amber-600"></i>
                                                <span>{{ $group['group_name'] }}</span>
                                            </span>
                                            <button 
                                                type="button" 
                                                @click="toggleGroupPermissions('{{ $gKey }}', {{ json_encode($permKeys) }}, 'editPermissions')"
                                                class="text-[11px] font-bold text-blue-600 hover:text-blue-800 transition"
                                            >
                                                <span x-text="isGroupAllSelected({{ json_encode($permKeys) }}, 'editPermissions') ? 'Bỏ chọn nhóm' : 'Chọn cả nhóm'"></span>
                                            </button>
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                            @foreach ($group['items'] as $pKey => $pLabel)
                                                <label class="flex items-start gap-2 p-2 rounded-xl bg-white border border-slate-100 hover:border-amber-200 cursor-pointer transition">
                                                    <input 
                                                        type="checkbox" 
                                                        name="permissions[]" 
                                                        value="{{ $pKey }}"
                                                        x-model="editPermissions"
                                                        class="mt-0.5 rounded text-amber-600 focus:ring-amber-500 border-slate-300"
                                                    >
                                                    <div class="text-[11px]">
                                                        <span class="font-bold text-slate-800 block">{{ $pLabel }}</span>
                                                        <span class="text-[10px] text-slate-400 font-mono">{{ $pKey }}</span>
                                                    </div>
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Footer Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100 shrink-0">
                            <button 
                                type="button" 
                                @click="editModalOpen = false" 
                                class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
                            >
                                Hủy bỏ
                            </button>
                            <button 
                                type="submit" 
                                class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition flex items-center gap-2"
                            >
                                <i class="fa-solid fa-floppy-disk"></i>
                                <span>Lưu thay đổi</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- ============================================================================== -->
        <!-- MODAL 3: XEM CHI TIẾT TÀI KHOẢN & QUYỀN HẠN (VIEW USER DETAILS)                -->
        <!-- ============================================================================== -->
        <div 
            x-show="viewModalOpen" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="view-modal-title" role="dialog" aria-modal="true"
        >
            <div 
                x-show="viewModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="viewModalOpen = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div 
                    x-show="viewModalOpen"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-xl bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100 my-8 max-h-[90vh] flex flex-col"
                >
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100 shrink-0">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-id-card"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900">Chi tiết tài khoản</h4>
                                <p class="text-xs text-slate-500">Xem thông tin và toàn bộ quyền hạn được cấp</p>
                            </div>
                        </div>
                        <button @click="viewModalOpen = false" class="text-slate-400 hover:text-slate-600 p-2 rounded-lg hover:bg-slate-100 transition">
                            <i class="fa-solid fa-xmark text-base"></i>
                        </button>
                    </div>

                    <!-- Body -->
                    <div class="py-4 space-y-4 flex-1 overflow-y-auto pr-1">
                        <!-- Profile Card -->
                        <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                            <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-extrabold text-2xl flex items-center justify-center shadow-md shrink-0">
                                <span x-text="currentUser.name ? currentUser.name.charAt(0).toUpperCase() : 'U'"></span>
                            </div>
                            <div class="overflow-hidden flex-1">
                                <div class="flex items-center gap-2">
                                    <h3 class="text-sm font-bold text-slate-900 truncate" x-text="currentUser.name"></h3>
                                    <span 
                                        class="px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                        :class="currentUser.role_badge"
                                    >
                                        <i class="fa-solid" :class="currentUser.role_icon"></i>
                                        <span x-text="currentUser.role_name"></span>
                                    </span>
                                </div>
                                <p class="text-xs text-slate-500 font-mono mt-0.5" x-text="currentUser.email"></p>
                                <p class="text-xs text-slate-600 font-mono mt-0.5" x-show="currentUser.phone">
                                    <i class="fa-solid fa-phone text-[10px] text-slate-400 mr-1"></i>
                                    <span x-text="currentUser.phone"></span>
                                </p>
                            </div>
                        </div>

                        <!-- Thông tin metadata -->
                        <div class="grid grid-cols-2 gap-3 text-xs">
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-slate-500 font-medium block">Trạng thái:</span>
                                <span 
                                    class="inline-flex items-center gap-1.5 font-bold mt-1 text-xs"
                                    :class="currentUser.status === 'active' ? 'text-emerald-600' : 'text-rose-600'"
                                >
                                    <span class="w-2 h-2 rounded-full" :class="currentUser.status === 'active' ? 'bg-emerald-500' : 'bg-rose-500'"></span>
                                    <span x-text="currentUser.status === 'active' ? 'Đang hoạt động' : 'Tạm khóa'"></span>
                                </span>
                            </div>
                            <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                <span class="text-slate-500 font-medium block">Đăng nhập gần nhất:</span>
                                <span class="font-bold text-slate-800 block mt-1" x-text="currentUser.last_login_at"></span>
                            </div>
                        </div>

                        <!-- Danh sách quyền có hiệu lực -->
                        <div class="pt-2">
                            <h5 class="text-xs font-bold uppercase tracking-wider text-slate-800 mb-2 flex items-center gap-1.5">
                                <i class="fa-solid fa-shield-halved text-blue-600"></i>
                                <span>Danh sách quyền hạn có hiệu lực</span>
                            </h5>

                            <template x-if="currentUser.role === 'admin'">
                                <div class="p-4 rounded-2xl bg-indigo-50/70 border border-indigo-100 text-indigo-900 text-xs flex items-start gap-3">
                                    <i class="fa-solid fa-star text-amber-500 text-base mt-0.5 shrink-0"></i>
                                    <div>
                                        <p class="font-bold">Quản trị viên tối cao (Full Access)</p>
                                        <p class="text-[11px] text-indigo-700 mt-0.5">Tài khoản này có toàn quyền truy cập, chỉnh sửa, xóa và cấu hình tất cả các tính năng trong hệ thống IDC.</p>
                                    </div>
                                </div>
                            </template>

                            <template x-if="currentUser.role !== 'admin'">
                                <div class="space-y-3">
                                    <div class="flex flex-wrap gap-1.5">
                                        <template x-for="perm in currentUser.effective_permissions" :key="perm">
                                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                                <i class="fa-solid fa-check text-[10px] text-blue-500"></i>
                                                <span x-text="perm"></span>
                                            </span>
                                        </template>
                                    </div>
                                    <template x-if="!currentUser.effective_permissions || currentUser.effective_permissions.length === 0">
                                        <p class="text-xs text-slate-400 italic">Không có quyền quản trị nào được cấp.</p>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="flex items-center justify-between pt-4 border-t border-slate-100 shrink-0">
                        <button 
                            type="button" 
                            @click="viewModalOpen = false; openEditModal(currentUser)" 
                            class="px-4 py-2 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-700 text-xs font-bold border border-amber-200 transition flex items-center gap-1.5"
                        >
                            <i class="fa-solid fa-pen-to-square"></i>
                            <span>Chỉnh sửa tài khoản này</span>
                        </button>
                        <button 
                            type="button" 
                            @click="viewModalOpen = false" 
                            class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition"
                        >
                            Đóng
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    // Xác nhận Thay đổi trạng thái tài khoản (Khóa / Mở khóa)
    function confirmToggleStatus(formId, userName, currentStatus) {
        const isLocking = currentStatus === 'active';
        confirmAction({
            title: isLocking ? 'Khóa tài khoản người dùng?' : 'Kích hoạt lại tài khoản?',
            text: isLocking 
                ? `Tài khoản "${userName}" sẽ bị tạm khóa và không thể đăng nhập vào hệ thống.` 
                : `Tài khoản "${userName}" sẽ được kích hoạt lại và có thể truy cập bình thường.`,
            icon: isLocking ? 'warning' : 'question',
            confirmButtonText: isLocking ? 'Có, khóa tài khoản' : 'Có, kích hoạt lại',
            cancelButtonText: 'Không, hủy bỏ',
            confirmButtonColor: isLocking ? '#d97706' : '#10b981'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }

    // Xác nhận Xóa người dùng vĩnh viễn
    function confirmDeleteUser(formId, userName) {
        confirmAction({
            title: 'Xác nhận xóa người dùng?',
            text: `Bạn có chắc chắn muốn xóa vĩnh viễn tài khoản "${userName}"? Thao tác này KHÔNG THỂ hoàn tác!`,
            icon: 'error',
            confirmButtonText: 'Có, xóa vĩnh viễn',
            cancelButtonText: 'Không, hủy bỏ',
            confirmButtonColor: '#e11d48'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }
</script>
@endpush
