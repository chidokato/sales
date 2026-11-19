<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Dashboard') - INDOCHINE Portal</title>
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js for lightweight UI interactivity -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- Chart.js for data visualization -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 CDN for professional alerts and dialogs -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                    colors: {
                        brand: {
                            50: '#eff6ff',
                            100: '#dbeafe',
                            500: '#3b82f6',
                            600: '#2563eb',
                            700: '#1d4ed8',
                            900: '#1e3a8a',
                        }
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Triệt tiêu lỗi SweetAlert2 tự chèn height: auto làm hở khoảng trắng đáy màn hình */
        html, body {
            height: 100% !important;
            min-height: 100vh !important;
        }
        html.swal2-height-auto, body.swal2-height-auto {
            height: 100% !important;
            min-height: 100vh !important;
        }
        .swal2-container {
            height: 100vh !important;
        }

        /* Thanh cuộn tinh tế cho khung menu sidebar ở giữa */
        .sidebar-scroll::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
    </style>
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-50 text-slate-800 antialiased" x-data="{ sidebarOpen: false, userInfoModalOpen: false }">

    <div class="min-h-screen flex">

        <!-- Mobile Sidebar Backdrop -->
        <div 
            x-show="sidebarOpen" 
            x-cloak
            @click="sidebarOpen = false"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm lg:hidden"
        ></div>

        <!-- Sidebar Navigation (3 Khung: Trên cố định, Dưới cố định, Giữa cuộn) -->
        <aside 
            :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 w-72 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out lg:sticky lg:top-0 h-screen max-h-screen flex flex-col shrink-0 border-r border-slate-800 shadow-xl lg:shadow-none"
        >
            <!-- KHUNG 1 (CỐ ĐỊNH PHÍA TRÊN): Logo & Tên hệ thống -->
            <div class="h-20 shrink-0 flex items-center justify-between px-6 border-b border-slate-800/80 bg-slate-900 z-10">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-blue-500/20">
                        <i class="fa-solid fa-cube text-lg"></i>
                    </div>
                    <div>
                        <span class="text-white font-extrabold text-lg tracking-wider">INDOCHINE</span>
                        <span class="text-blue-400 text-xs font-bold uppercase tracking-widest block -mt-1">ADMIN PORTAL</span>
                    </div>
                </a>
                <!-- Mobile Close Button -->
                <button @click="sidebarOpen = false" class="lg:hidden text-slate-400 hover:text-white p-2">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- KHUNG 2 (Ở GIỮA - TỰ ĐỘNG CUỘN SCROLL NẾU DÀI): Menu Điều hướng -->
            <div class="flex-1 overflow-y-auto p-4 space-y-1.5 sidebar-scroll">
                <div class="px-3 py-2 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                    Bảng điều khiển
                </div>

                <!-- Dashboard -->
                <a 
                    href="{{ route('admin.dashboard') }}" 
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.dashboard') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}"
                >
                    <i class="fa-solid fa-chart-pie text-base w-5 text-center"></i>
                    <span>Tổng quan</span>
                </a>

                <!-- Users Management -->
                <a 
                    href="{{ route('admin.users') }}" 
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.users') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}"
                >
                    <i class="fa-solid fa-users text-base w-5 text-center"></i>
                    <span>Quản lý Người dùng</span>
                </a>

                <div class="pt-4 px-3 py-2 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                    Bất động sản
                </div>

                <!-- Unit Locators (Chỉ căn) -->
                <a 
                    href="{{ route('admin.unit-locators.index') }}" 
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.unit-locators.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}"
                >
                    <i class="fa-solid fa-map-location-dot text-base w-5 text-center"></i>
                    <span>Chỉ căn Dự án</span>
                </a>

                <div class="pt-4 px-3 py-2 text-[11px] font-bold tracking-wider text-slate-500 uppercase">
                    Hệ thống
                </div>

                <!-- Settings -->
                <a 
                    href="{{ route('admin.settings') }}" 
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.settings') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}"
                >
                    <i class="fa-solid fa-sliders text-base w-5 text-center"></i>
                    <span>Cấu hình & Trạng thái</span>
                </a>

                <!-- Shared Databases -->
                <a 
                    href="{{ route('admin.databases.index') }}" 
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold transition-all {{ request()->routeIs('admin.databases.*') ? 'bg-blue-600 text-white shadow-lg shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/60' }}"
                >
                    <i class="fa-solid fa-database text-base w-5 text-center"></i>
                    <span>Database Dùng chung</span>
                </a>

                <!-- Clear Cache Button -->
                <form id="sidebarClearCacheForm" action="{{ route('admin.clear-cache') }}" method="POST">
                    @csrf
                    <button 
                        type="button" 
                        onclick="handleClearCache('sidebarClearCacheForm')"
                        class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-semibold text-slate-400 hover:text-amber-300 hover:bg-slate-800/60 transition-all text-left"
                    >
                        <i class="fa-solid fa-broom text-base w-5 text-center text-amber-400"></i>
                        <span>Dọn sạch Cache</span>
                    </button>
                </form>
            </div>

            <!-- KHUNG 3 (CỐ ĐỊNH Ở ĐÁY): User Profile & Logout -->
            <div class="p-4 shrink-0 border-t border-slate-800 bg-slate-950/60 z-10">
                <!-- User Basic Info -->
                <div class="flex items-center gap-3 mb-3">
                    <div class="relative shrink-0">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white text-sm shadow-md shadow-blue-500/20">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <span class="absolute -bottom-0.5 -right-0.5 w-3 h-3 rounded-full bg-emerald-500 border-2 border-slate-900" title="Đang online"></span>
                    </div>
                    <div class="overflow-hidden flex-1">
                        <p class="text-xs font-bold text-white truncate">{{ auth()->user()->name }}</p>
                        <p class="text-[11px] text-slate-400 truncate">{{ auth()->user()->email }}</p>
                    </div>
                </div>

                <!-- Bottom-Left Action Buttons: Thông tin & Đăng xuất -->
                <div class="grid grid-cols-2 gap-2 pt-2 border-t border-slate-800/80">
                    <button 
                        type="button" 
                        @click="userInfoModalOpen = true" 
                        class="flex items-center justify-center gap-1.5 py-2 px-2.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 hover:text-white text-xs font-semibold transition"
                        title="Xem thông tin chi tiết người dùng"
                    >
                        <i class="fa-regular fa-id-badge text-blue-400"></i>
                        <span>Thông tin</span>
                    </button>

                    <form id="logoutFormBottomLeft" action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button 
                            type="button" 
                            onclick="handleConfirmLogout()" 
                            class="w-full flex items-center justify-center gap-1.5 py-2 px-2.5 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 text-xs font-semibold border border-rose-500/20 transition"
                            title="Đăng xuất khỏi hệ thống"
                        >
                            <i class="fa-solid fa-right-from-bracket text-xs"></i>
                            <span>Đăng xuất</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col min-w-0 min-h-screen justify-between overflow-y-auto">

            <!-- Top Header (Thanh trên cùng: Action Toolbar Add, Edit, Save...) -->
            <header class="h-20 bg-white border-b border-slate-200/80 sticky top-0 z-30 px-6 sm:px-8 flex items-center justify-between gap-4">
                
                <!-- Left: Sidebar Trigger & Breadcrumb -->
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-slate-600 hover:text-slate-900 p-2 rounded-lg bg-slate-100">
                        <i class="fa-solid fa-bars text-lg"></i>
                    </button>
                    <div>
                        <h2 class="text-lg sm:text-xl font-bold text-slate-900 tracking-tight">@yield('page-title', 'Bảng điều khiển')</h2>
                        <p class="text-xs text-slate-500 hidden sm:block">Hệ thống Quản lý & Giám sát INDOCHINE Tool</p>
                    </div>
                </div>

                <!-- Right: Action Toolbar (Các nút Add, Edit, Save...) -->
                <div class="flex items-center gap-2.5 sm:gap-3">
                    
                    <!-- Nút mở nhanh trang Frontend người dùng -->
                    <a 
                        href="{{ route('home') }}" 
                        target="_blank"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-700 text-xs font-semibold border border-slate-200 transition shadow-sm"
                        title="Xem giao diện người dùng (Frontend)"
                    >
                        <i class="fa-solid fa-arrow-up-right-from-square text-xs text-blue-600"></i>
                        <span class="hidden md:inline">Xem Trang Người dùng</span>
                    </a>

                    <!-- System Status Badge -->
                    <div class="hidden xl:flex items-center gap-2 px-3 py-1.5 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold mr-1">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        <span>MySQL & Apache Active</span>
                    </div>

                    @hasSection('header-actions')
                        <div class="flex items-center gap-2.5 sm:gap-3">
                            @yield('header-actions')
                        </div>
                    @endif

                </div>

            </header>

            <!-- Main Body Wrapper -->
            <main class="flex-1 p-6 sm:p-8 w-full space-y-6">

                <!-- Yielded Content -->
                @yield('content')

            </main>

        </div>

    </div>

    <!-- User Information Modal (Hiển thị chi tiết thông tin người dùng từ góc dưới bên trái) -->
    <div 
        x-show="userInfoModalOpen" 
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="user-info-title" role="dialog" aria-modal="true"
    >
        <div 
            x-show="userInfoModalOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
            @click="userInfoModalOpen = false"
        ></div>

        <div class="flex min-h-full items-center justify-center p-4">
            <div 
                x-show="userInfoModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="relative w-full max-w-md bg-white rounded-3xl p-6 sm:p-7 shadow-2xl border border-slate-100"
            >
                <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                            <i class="fa-regular fa-id-badge"></i>
                        </div>
                        <div>
                            <h4 class="text-base font-bold text-slate-900">Thông tin Người dùng</h4>
                            <p class="text-xs text-slate-500">Tài khoản quản trị đang truy cập</p>
                        </div>
                    </div>
                    <button @click="userInfoModalOpen = false" class="text-slate-400 hover:text-slate-600 p-2">
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>

                <div class="py-5 space-y-4">
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-slate-50 border border-slate-100">
                        <div class="w-14 h-14 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-extrabold text-xl flex items-center justify-center shadow-md">
                            {{ strtoupper(substr(auth()->user()->name ?? 'A', 0, 1)) }}
                        </div>
                        <div class="overflow-hidden">
                            <h3 class="text-sm font-bold text-slate-900">{{ auth()->user()->name }}</h3>
                            <p class="text-xs text-slate-500 font-mono">{{ auth()->user()->email }}</p>
                            <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ auth()->user()->role_badge ?? 'bg-blue-100 text-blue-700' }}">
                                <i class="fa-solid {{ auth()->user()->role_icon ?? 'fa-user' }} mr-1"></i>
                                {{ auth()->user()->role_name ?? ucfirst(auth()->user()->role) }}
                            </span>
                        </div>
                    </div>

                    <div class="space-y-2.5 text-xs">
                        <div class="flex items-center justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-500 font-medium">Mã ID tài khoản:</span>
                            <span class="font-bold text-slate-800 font-mono">#{{ auth()->id() }}</span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-500 font-medium">Trạng thái tài khoản:</span>
                            <span class="inline-flex items-center gap-1.5 text-emerald-600 font-bold">
                                <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Đang hoạt động
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-slate-100">
                            <span class="text-slate-500 font-medium">Ngày kích hoạt:</span>
                            <span class="font-medium text-slate-700">{{ auth()->user()->created_at ? auth()->user()->created_at->format('d/m/Y H:i') : 'Mới tạo' }}</span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-slate-100">
                    <button type="button" @click="userInfoModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                        Đóng
                    </button>
                    <a href="{{ route('admin.settings') }}" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition">
                        Cấu hình hệ thống
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 Notification & Confirmation Setup -->
    <script>
        // 1. Toast Notification (Góc dưới bên trái - Dành cho thông báo không cần thao tác Có/Không)
        const Toast = Swal.mixin({
            toast: true,
            position: 'bottom-start',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            showCloseButton: true,
            heightAuto: false,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            },
            customClass: {
                popup: 'rounded-2xl shadow-2xl border border-slate-100 bg-white text-slate-800 text-xs font-sans',
                title: 'text-xs font-semibold'
            }
        });

        // 2. Dialog Xác nhận (Chính giữa màn hình - Dành cho thông báo cần thao tác Có/Không)
        function confirmAction({
            title = 'Xác nhận thao tác?',
            text = 'Bạn có chắc chắn muốn thực hiện hành động này?',
            icon = 'warning',
            confirmButtonText = 'Có, đồng ý',
            cancelButtonText = 'Không, hủy bỏ',
            confirmButtonColor = '#2563eb', // Brand blue
            cancelButtonColor = '#64748b'   // Slate
        }) {
            return Swal.fire({
                title: title,
                text: text,
                icon: icon,
                position: 'center',
                heightAuto: false, // Ngăn SweetAlert2 tự chèn height: auto vào body gây hở khoảng trắng đáy trang
                showCancelButton: true,
                confirmButtonText: confirmButtonText,
                cancelButtonText: cancelButtonText,
                confirmButtonColor: confirmButtonColor,
                cancelButtonColor: cancelButtonColor,
                reverseButtons: true,
                customClass: {
                    popup: 'rounded-3xl shadow-2xl p-6 font-sans border border-slate-100',
                    title: 'text-lg font-bold text-slate-900',
                    htmlContainer: 'text-xs text-slate-600 leading-relaxed mt-2',
                    confirmButton: 'px-5 py-2.5 rounded-xl text-xs font-bold shadow-md shadow-blue-500/20',
                    cancelButton: 'px-5 py-2.5 rounded-xl text-xs font-bold mr-2'
                }
            });
        }

        // Xử lý xác nhận Đăng xuất từ góc dưới bên trái
        function handleConfirmLogout() {
            confirmAction({
                title: 'Xác nhận Đăng xuất?',
                text: 'Bạn có chắc chắn muốn kết thúc phiên làm việc và đăng xuất khỏi trang quản trị?',
                icon: 'question',
                confirmButtonText: 'Có, đăng xuất ngay',
                cancelButtonText: 'Ở lại',
                confirmButtonColor: '#e11d48',
                cancelButtonColor: '#64748b'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('logoutFormBottomLeft').submit();
                }
            });
        }

        // Xử lý xác nhận dọn dẹp cache
        function handleClearCache(formId = 'sidebarClearCacheForm') {
            confirmAction({
                title: 'Dọn sạch bộ nhớ Cache?',
                text: 'Hành động này sẽ xóa cache cấu hình, views blade biên dịch và tối ưu hóa lại hệ thống.',
                icon: 'question',
                confirmButtonText: 'Có, dọn dẹp ngay',
                cancelButtonText: 'Không',
                confirmButtonColor: '#f59e0b'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }

        // Tự động kích hoạt Toast từ Session Flash Laravel (Góc dưới bên trái)
        document.addEventListener('DOMContentLoaded', function () {
            @if (session('success'))
                Toast.fire({
                    icon: 'success',
                    title: '{{ session('success') }}'
                });
            @endif

            @if (session('error'))
                Toast.fire({
                    icon: 'error',
                    title: '{{ session('error') }}'
                });
            @endif

            @if (session('info'))
                Toast.fire({
                    icon: 'info',
                    title: '{{ session('info') }}'
                });
            @endif

            @if (session('warning'))
                Toast.fire({
                    icon: 'warning',
                    title: '{{ session('warning') }}'
                });
            @endif

            @if (isset($errors) && $errors->any())
                Toast.fire({
                    icon: 'error',
                    title: '{{ $errors->first() }}'
                });
            @endif
        });
    </script>

    @stack('scripts')
</body>
</html>
