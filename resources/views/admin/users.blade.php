@extends('layouts.admin')

@section('title', 'Quản lý Người dùng')
@section('page-title', 'Quản lý Người dùng')

@section('header-actions')
    <!-- Chỉ hiển thị nút Thêm mới vì trang này chỉ có tính năng thêm dữ liệu -->
    <button 
        type="button" 
        @click="$dispatch('open-create-modal')" 
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition"
        title="Thêm người dùng mới"
    >
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Thêm người dùng mới</span>
    </button>
@endsection

@section('content')

    <div x-data="{ createModalOpen: false }" @open-create-modal.window="createModalOpen = true">

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
                        placeholder="Tìm kiếm theo họ tên hoặc email..." 
                        class="w-full pl-9 pr-4 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500"
                    >
                </div>

                <!-- Role Filter -->
                <div class="sm:col-span-3">
                    <select name="role" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Tất cả vai trò --</option>
                        <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                        <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>Người dùng thường (User)</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="sm:col-span-2">
                    <select name="status" class="w-full px-3 py-2 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Trạng thái --</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Hoạt động</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Tạm khóa</option>
                    </select>
                </div>

                <!-- Action Buttons -->
                <div class="sm:col-span-2 flex items-center gap-2">
                    <button type="submit" class="flex-1 py-2 px-3 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs transition">
                        Lọc
                    </button>
                    @if (request()->hasAny(['search', 'role', 'status']))
                        <a href="{{ route('admin.users') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-semibold" title="Bỏ lọc">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>

            </form>

        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mt-6">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs">
                    <thead>
                        <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100">
                            <th class="py-3.5 px-6">ID</th>
                            <th class="py-3.5 px-6">Họ và Tên</th>
                            <th class="py-3.5 px-6">Email</th>
                            <th class="py-3.5 px-6">Vai trò</th>
                            <th class="py-3.5 px-6">Trạng thái</th>
                            <th class="py-3.5 px-6">Ngày tạo</th>
                            <th class="py-3.5 px-6 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                        @forelse ($users as $user)
                            <tr class="hover:bg-slate-50/60 transition">
                                <td class="py-4 px-6 text-slate-400 font-mono">#{{ $user->id }}</td>
                                <td class="py-4 px-6 flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold flex items-center justify-center text-xs shadow-sm">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-bold text-slate-900">{{ $user->name }}</p>
                                        @if ($user->id === auth()->id())
                                            <span class="text-[10px] text-blue-600 font-bold">(Bạn)</span>
                                        @endif
                                    </div>
                                </td>
                                <td class="py-4 px-6 text-slate-600 font-mono">{{ $user->email }}</td>
                                <td class="py-4 px-6">
                                    @if ($user->role === 'admin')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                            <i class="fa-solid fa-shield-halved text-[10px]"></i> Admin
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-600 border border-slate-200">
                                            <i class="fa-solid fa-user text-[10px]"></i> User
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6">
                                    @if ($user->status === 'active')
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Hoạt động
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-700 border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Tạm khóa
                                        </span>
                                    @endif
                                </td>
                                <td class="py-4 px-6 text-slate-500">
                                    {{ $user->created_at ? $user->created_at->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-4 px-6 text-right">
                                    @if ($user->id !== auth()->id())
                                        <div class="inline-flex items-center gap-1.5">
                                            <!-- Toggle Status Form -->
                                            <form id="toggleUserForm-{{ $user->id }}" action="{{ route('admin.users.toggle', $user->id) }}" method="POST">
                                                @csrf
                                                <button 
                                                    type="button" 
                                                    onclick="confirmToggleStatus('toggleUserForm-{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->status }}')"
                                                    title="{{ $user->status === 'active' ? 'Khóa tài khoản' : 'Kích hoạt tài khoản' }}"
                                                    class="p-2 rounded-lg {{ $user->status === 'active' ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }} transition"
                                                >
                                                    <i class="fa-solid {{ $user->status === 'active' ? 'fa-lock' : 'fa-lock-open' }}"></i>
                                                </button>
                                            </form>

                                            <!-- Delete Form -->
                                            <form id="deleteUserForm-{{ $user->id }}" action="{{ route('admin.users.delete', $user->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="button" 
                                                    onclick="confirmDeleteUser('deleteUserForm-{{ $user->id }}', '{{ addslashes($user->name) }}')"
                                                    title="Xóa người dùng" 
                                                    class="p-2 rounded-lg text-rose-500 hover:bg-rose-50 transition"
                                                >
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    @else
                                        <span class="text-slate-400 italic">Hiện tại</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-slate-400">Không tìm thấy người dùng nào phù hợp.</td>
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

        <!-- Create User Modal (Alpine.js) -->
        <div 
            x-show="createModalOpen" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true"
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
                    class="relative w-full max-w-lg bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100"
                >
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-user-plus"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900">Thêm người dùng mới</h4>
                                <p class="text-xs text-slate-500">Cấp tài khoản truy cập vào hệ thống</p>
                            </div>
                        </div>
                        <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600 p-2">
                            <i class="fa-solid fa-xmark text-base"></i>
                        </button>
                    </div>

                    <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4 mt-5">
                        @csrf
                        
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Họ và Tên</label>
                            <input type="text" name="name" required placeholder="Ví dụ: Nguyễn Văn A" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Địa chỉ Email</label>
                            <input type="email" name="email" required placeholder="user@idc.vn" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>

                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Mật khẩu ban đầu</label>
                            <input type="password" name="password" required minlength="6" placeholder="Tối thiểu 6 ký tự" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Vai trò</label>
                                <select name="role" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="user">Người dùng (User)</option>
                                    <option value="admin">Quản trị viên (Admin)</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Trạng thái</label>
                                <select name="status" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                                    <option value="active">Hoạt động (Active)</option>
                                    <option value="inactive">Tạm khóa (Inactive)</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                Hủy bỏ
                            </button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition">
                                Tạo tài khoản
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

    </div>

@endsection

@push('scripts')
<script>
    // Xác nhận Thay đổi trạng thái tài khoản (Khóa / Mở khóa) - Hiển thị giữa màn hình
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

    // Xác nhận Xóa người dùng vĩnh viễn - Hiển thị giữa màn hình
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
