<!DOCTYPE html>
<html lang="vi" class="h-full bg-slate-100">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>IDC Real Estate - Cổng Thông tin & Sơ đồ Chỉ căn Bất động sản</title>

    <!-- Google Fonts: Plus Jakarta Sans -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- FontAwesome 6 Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- SweetAlert2 -->
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
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
            overflow: hidden;
        }
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        [x-cloak] { display: none !important; }

        /* Scrollbar tinh tế */
        .sidebar-scroll::-webkit-scrollbar,
        .custom-scroll::-webkit-scrollbar {
            width: 5px;
            height: 5px;
        }
        .sidebar-scroll::-webkit-scrollbar-track,
        .custom-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: #334155;
            border-radius: 4px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: #475569;
        }
        .custom-scroll::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .custom-scroll::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
</head>
<body 
    class="h-full bg-slate-100 text-slate-800 antialiased overflow-hidden" 
    x-data="frontendApp()" 
    x-init="initApp()"
>

    <div class="h-screen flex overflow-hidden">

        <!-- ============================================================== -->
        <!-- MOBILE BACKDROP                                                -->
        <!-- ============================================================== -->
        <div 
            x-show="mobileMenuOpen" 
            x-cloak
            @click="mobileMenuOpen = false"
            x-transition:enter="transition-opacity ease-linear duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity ease-linear duration-300"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 z-40 bg-slate-900/70 backdrop-blur-sm lg:hidden"
        ></div>

        <!-- ============================================================== -->
        <!-- SIDEBAR BÊN TRÁI (LEFT SIDEBAR MENU)                          -->
        <!-- Ghim cố định thanh menu bên trái, các nút tài khoản ghim đáy   -->
        <!-- ============================================================== -->
        <aside 
            :class="mobileMenuOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
            class="fixed inset-y-0 left-0 z-50 w-64 sm:w-72 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out lg:static lg:translate-x-0 h-screen flex flex-col shrink-0 border-r border-slate-800 shadow-2xl lg:shadow-none select-none overflow-hidden"
        >
            <!-- 1. KHUNG TRÊN CÙNG: LOGO & TÊN HỆ THỐNG -->
            <div class="h-16 shrink-0 flex items-center justify-between px-5 border-b border-slate-800/80 bg-slate-900/95 backdrop-blur z-10">
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 via-indigo-600 to-cyan-500 flex items-center justify-center text-white shadow-md shadow-blue-500/25 group-hover:scale-105 transition-transform">
                        <i class="fa-solid fa-building text-sm"></i>
                    </div>
                    <div>
                        <div class="flex items-center gap-1.5">
                            <span class="text-white font-extrabold text-base tracking-wider leading-tight">IDC</span>
                            <span class="px-1.5 py-0.2 rounded text-[9px] font-bold bg-blue-500/20 text-blue-400 border border-blue-500/30">PORTAL</span>
                        </div>
                        <span class="text-slate-400 text-xs font-medium block leading-tight mt-0.5">BĐS & Sơ Đồ Chỉ Căn</span>
                    </div>
                </a>
                <!-- Mobile Close Button -->
                <button @click="mobileMenuOpen = false" class="lg:hidden text-slate-400 hover:text-white p-1.5 rounded-lg hover:bg-slate-800">
                    <i class="fa-solid fa-xmark text-base"></i>
                </button>
            </div>

            <!-- 2. KHUNG Ở GIỮA: DANH SÁCH MENU ĐIỀU HƯỚNG (DÃN CÁCH THOÁNG ĐÃNG) -->
            <div class="flex-1 overflow-y-auto p-4 space-y-6 sidebar-scroll">
                
                <!-- Nhóm: Khám phá Bất Động Sản -->
                <div>
                    <div class="px-3 pb-2.5 text-[11px] font-bold tracking-wider text-slate-400 uppercase">
                        Khám phá Bất động sản
                    </div>
                    <div class="space-y-2">
                        <!-- Menu 1: Khám phá Dự án -->
                        <button 
                            type="button" 
                            @click="switchMenu('projects')" 
                            :class="activeMenu === 'projects' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/70'"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all text-left group"
                        >
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-city text-sm w-5 text-center group-hover:text-blue-400 transition" :class="activeMenu === 'projects' ? 'text-white' : 'text-slate-400'"></i>
                                <span>Khám phá Dự án</span>
                            </div>
                            <span 
                                :class="activeMenu === 'projects' ? 'bg-white/20 text-white' : 'bg-slate-800 text-slate-400'"
                                class="text-[10px] px-2 py-0.5 rounded-full font-bold"
                            >
                                {{ $totalProjectsCount }}
                            </span>
                        </button>

                        <!-- Menu 2: Sơ đồ & Chỉ căn BĐS -->
                        <button 
                            type="button" 
                            @click="switchMenu('locator')" 
                            :class="activeMenu === 'locator' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/70'"
                            class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all text-left group"
                        >
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-map-location-dot text-sm w-5 text-center group-hover:text-amber-400 transition" :class="activeMenu === 'locator' ? 'text-white' : 'text-amber-400'"></i>
                                <span>Sơ đồ Chỉ căn BĐS</span>
                            </div>
                            <span class="inline-flex items-center gap-1.5 text-[10px] font-bold px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                                {{ $unitStats['total'] }} Căn
                            </span>
                        </button>

                        {{-- TẠM ẨN: Menu 3 - Bảng hàng & Tra cứu --}}
                    </div>
                </div>

                <!-- Nhóm: Tiện ích & Tài chính -->
                <div>
                    <div class="px-3 pb-2.5 text-[11px] font-bold tracking-wider text-slate-400 uppercase">
                        Tiện ích & Tài chính
                    </div>
                    <div class="space-y-2">
                        <!-- Menu 4: Bảng tính Vay Ngân hàng -->
                        <button 
                            type="button" 
                            @click="switchMenu('calculator')" 
                            :class="activeMenu === 'calculator' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/70'"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all text-left group"
                        >
                            <i class="fa-solid fa-calculator text-sm w-5 text-center group-hover:text-cyan-400 transition" :class="activeMenu === 'calculator' ? 'text-white' : 'text-cyan-400'"></i>
                            <span>Bảng tính Vay Mua nhà</span>
                        </button>

                        {{-- TẠM ẨN: Menu 5 - Tin tức & Cẩm nang BĐS --}}
                    </div>
                </div>

{{--
                <!-- Nhóm: Hỗ trợ & Liên hệ -->
                <div>
                    <div class="px-3 pb-2.5 text-[11px] font-bold tracking-wider text-slate-400 uppercase">
                        Hỗ trợ & Tư vấn
                    </div>
                    <div class="space-y-2">
                        <!-- Menu 6: Liên hệ & Đăng ký Tư vấn -->
                        <button 
                            type="button" 
                            @click="switchMenu('contact')" 
                            :class="activeMenu === 'contact' ? 'bg-blue-600 text-white shadow-md shadow-blue-600/30' : 'text-slate-400 hover:text-white hover:bg-slate-800/70'"
                            class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs sm:text-sm font-semibold transition-all text-left group"
                        >
                            <i class="fa-solid fa-headset text-sm w-5 text-center group-hover:text-rose-400 transition" :class="activeMenu === 'contact' ? 'text-white' : 'text-rose-400'"></i>
                            <span>Liên hệ & Đăng ký Tư vấn</span>
                        </button>
                    </div>
                </div>

                <!-- Thẻ Hotline tư vấn thoáng đẹp -->
                <div class="p-3.5 rounded-2xl bg-gradient-to-br from-blue-900/40 via-indigo-900/20 to-slate-800/60 border border-blue-500/20 text-xs">
                    <div class="flex items-center gap-1.5 text-blue-400 font-bold mb-1.5 text-xs">
                        <i class="fa-solid fa-phone-volume"></i>
                        <span>Hotline Tư vấn 24/7</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <a href="tel:19008686" class="text-white font-extrabold text-sm tracking-wide hover:text-blue-300 transition">1900 8686</a>
                        <span class="text-xs text-slate-400">0989.123.456</span>
                    </div>
                </div>
--}}

            </div>

            <!-- ============================================================== -->
            <!-- 3. KHUNG DƯỚI CÙNG (GHIM GÓC DƯỚI BÊN TRÁI):                   -->
            <!-- THÔNG TIN NGƯỜI DÙNG, NÚT LOGIN, NÚT LOGOUT                    -->
            <!-- ============================================================== -->
            <div class="p-3.5 shrink-0 border-t border-slate-800 bg-slate-950/95 backdrop-blur z-20">
                
                @auth
                    <!-- TRẠNG THÁI: ĐÃ ĐĂNG NHẬP -->
                    <div class="flex items-center gap-2.5 mb-2.5">
                        <div class="relative shrink-0">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 flex items-center justify-center font-bold text-white text-xs shadow-md shadow-blue-500/20">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <span class="absolute -bottom-0.5 -right-0.5 w-2.5 h-2.5 rounded-full bg-emerald-500 border-2 border-slate-950" title="Đang online"></span>
                        </div>
                        <div class="overflow-hidden flex-1 min-w-0">
                            <div class="flex items-center gap-1">
                                <p class="text-xs font-bold text-white truncate leading-tight">{{ auth()->user()->name }}</p>
                                @if(auth()->user()->isAdmin())
                                    <span class="px-1.5 py-0.2 rounded text-[8px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">ADMIN</span>
                                @else
                                    <span class="px-1.5 py-0.2 rounded text-[8px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">USER</span>
                                @endif
                            </div>
                            <p class="text-[11px] text-slate-400 truncate leading-tight mt-0.5">{{ auth()->user()->email }}</p>
                        </div>
                    </div>

                    <!-- Các nút thao tác ghim góc dưới: THÔNG TIN NGƯỜI DÙNG & ĐĂNG XUẤT -->
                    <div class="grid grid-cols-2 gap-1.5 pt-1.5 border-t border-slate-800/80">
                        <!-- Nút Thông tin người dùng -->
                        <button 
                            type="button" 
                            @click="userInfoModalOpen = true" 
                            class="flex items-center justify-center gap-1.5 py-1.5 px-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 hover:text-white text-xs font-semibold transition border border-slate-700/60 shadow-sm"
                            title="Xem thông tin chi tiết người dùng"
                        >
                            <i class="fa-regular fa-id-badge text-blue-400 text-xs"></i>
                            <span>Thông tin</span>
                        </button>

                        <!-- Nút Đăng xuất (Logout) -->
                        <button 
                            type="button" 
                            @click="handleLogout()" 
                            class="flex items-center justify-center gap-1.5 py-1.5 px-2 rounded-xl bg-rose-500/10 hover:bg-rose-500/20 text-rose-400 hover:text-rose-300 text-xs font-semibold border border-rose-500/20 transition shadow-sm"
                            title="Đăng xuất khỏi hệ thống"
                        >
                            <i class="fa-solid fa-right-from-bracket text-[11px]"></i>
                            <span>Đăng xuất</span>
                        </button>
                    </div>

                    @if(auth()->user()->isAdmin())
                        <!-- Nút mở nhanh Trang Quản trị (Dành riêng cho Admin) -->
                        <a 
                            href="{{ route('admin.dashboard') }}" 
                            class="mt-1.5 w-full flex items-center justify-center gap-1.5 py-1 px-2 rounded-lg bg-blue-500/10 hover:bg-blue-500/20 text-blue-300 text-[10px] font-semibold border border-blue-500/20 transition"
                        >
                            <i class="fa-solid fa-shield-halved text-[10px]"></i>
                            <span>Vào Trang Quản trị (Admin)</span>
                        </a>
                    @endif

                @else
                    <!-- TRẠNG THÁI: CHƯA ĐĂNG NHẬP (KHÁCH THĂM QUAN) -->
                    <div class="flex items-center gap-2.5 mb-2.5">
                        <div class="w-8 h-8 rounded-lg bg-slate-800 border border-slate-700 flex items-center justify-center text-slate-400 text-sm shrink-0">
                            <i class="fa-solid fa-user-secret"></i>
                        </div>
                        <div class="overflow-hidden flex-1 min-w-0">
                            <p class="text-xs font-bold text-white truncate leading-tight">Khách thăm quan</p>
                            <p class="text-[11px] text-slate-400 truncate leading-tight mt-0.5">Chưa đăng nhập</p>
                        </div>
                    </div>

                    <!-- Nút Đăng nhập ghim góc dưới bên trái -->
                    <div class="space-y-1.5">
                        <button 
                            type="button" 
                            @click="loginModalOpen = true" 
                            class="w-full flex items-center justify-center gap-1.5 py-2 px-3 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-xs font-bold shadow-md shadow-blue-600/30 transition"
                        >
                            <i class="fa-solid fa-right-to-bracket text-xs"></i>
                            <span>Đăng nhập hệ thống</span>
                        </button>

                        <div class="text-center text-[10px] text-slate-400 pt-0.5">
                            <a href="{{ route('login') }}" class="hover:text-blue-400 transition">Trang đăng nhập quản trị</a>
                        </div>
                    </div>
                @endauth

            </div>
        </aside>

        <!-- ============================================================== -->
        <!-- GIAO DIỆN CHÍNH BÊN PHẢI (MAIN CONTENT AREA)                   -->
        <!-- "chỉ hiển thị nội dung chính cho từng menu"                    -->
        <!-- ============================================================== -->
        <main class="flex-1 flex flex-col min-w-0 h-screen overflow-hidden bg-slate-100">

            <!-- THANH TRÊN CÙNG TINH GỌN (TOP BAR) -->
            <header class="h-11 shrink-0 bg-white border-b border-slate-200/80 px-3 sm:px-4 flex items-center justify-between gap-3 z-10 shadow-sm">
                <!-- Nút toggle sidebar trên Mobile + Tiêu đề Menu / Dự án đang chọn -->
                <div class="flex items-center gap-2 min-w-0">
                    <button 
                        @click="mobileMenuOpen = true" 
                        class="lg:hidden p-1 rounded-lg text-slate-600 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 transition"
                        title="Mở menu"
                    >
                        <i class="fa-solid fa-bars text-sm"></i>
                    </button>

                    <!-- KHI Ở MENU LOCATOR: HIỂN THỊ DỰ ÁN & SUBTABS CHUYỂN ĐỔI NGAY TRÊN THANH NÀY -->
                    <div x-show="activeMenu === 'locator'" class="flex items-center gap-2 sm:gap-3 min-w-0">
                        <!-- Tên dự án & số căn -->
                        <div class="flex items-center gap-1.5 min-w-0">
                            <div class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-600 border border-amber-500/20 flex items-center justify-center text-xs shrink-0">
                                <i class="fa-solid fa-compass-drafting text-[11px]"></i>
                            </div>
                            <h2 class="text-xs sm:text-sm font-extrabold text-slate-900 leading-tight truncate" x-text="currentProject.name"></h2>
                            <span class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-blue-100 text-blue-700 shrink-0">
                                <span x-text="unitsList.length"></span> Căn
                            </span>
                        </div>

                        <!-- Phân cách dọc -->
                        <div class="hidden sm:block w-px h-5 bg-slate-200 shrink-0"></div>

                        <!-- Subtab buttons chuyển đổi gọn gàng -->
                        <div class="flex items-center p-0.5 bg-slate-100 rounded-lg text-xs font-bold shrink-0">
                            <button 
                                type="button"
                                @click="locatorSubTab = 'units'" 
                                :class="locatorSubTab === 'units' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-md transition flex items-center gap-1.5 text-xs"
                            >
                                <i class="fa-solid fa-shapes text-[10px]"></i>
                                <span>Bản đồ Chỉ căn (<span x-text="filteredUnits.length"></span>)</span>
                            </button>
                            <button 
                                type="button"
                                @click="locatorSubTab = 'floorplans'" 
                                :class="locatorSubTab === 'floorplans' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900'"
                                class="px-2.5 py-1 rounded-md transition flex items-center gap-1.5 text-xs"
                            >
                                <i class="fa-regular fa-map text-[10px]"></i>
                                <span>Sơ đồ Mặt bằng Tầng ({{ $floorplans->count() }})</span>
                            </button>
                        </div>
                    </div>

                    <!-- KHI Ở CÁC MENU KHÁC: HIỂN THỊ ICON & TIÊU ĐỀ MENU THÔNG THƯỜNG -->
                    <div x-show="activeMenu !== 'locator'" class="flex items-center gap-2 truncate">
                        <div class="w-6 h-6 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center text-xs shrink-0">
                            <template x-if="activeMenu === 'projects'"><i class="fa-solid fa-city"></i></template>
                            <template x-if="activeMenu === 'inventory'"><i class="fa-solid fa-table-cells-large"></i></template>
                            <template x-if="activeMenu === 'calculator'"><i class="fa-solid fa-calculator"></i></template>
                            <template x-if="activeMenu === 'news'"><i class="fa-solid fa-newspaper"></i></template>
                            <template x-if="activeMenu === 'contact'"><i class="fa-solid fa-headset"></i></template>
                        </div>
                        <div class="truncate">
                            <h1 class="text-xs sm:text-sm font-bold text-slate-900 truncate" x-text="menuTitles[activeMenu].title"></h1>
                        </div>
                    </div>
                </div>

                <!-- Thao tác nhanh bên phải header: Đổi dự án, Hotline, Tư vấn -->
                <div class="flex items-center gap-2 shrink-0">
{{--
                    <!-- Nút Hotline -->
                    <a 
                        href="tel:19008686" 
                        class="hidden md:inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-[11px] font-semibold transition"
                    >
                        <i class="fa-solid fa-phone text-emerald-600 text-[10px]"></i>
                        <span>1900 8686</span>
                    </a>

                    <!-- Nút Đăng ký tư vấn -->
                    <button 
                        type="button" 
                        @click="openConsultationModal()" 
                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition"
                    >
                        <i class="fa-solid fa-paper-plane text-[10px]"></i>
                        <span>Tư vấn nhanh</span>
                    </button>
--}}
                </div>
            </header>

            <!-- KHUNG NỘI DUNG CHÍNH (KHÔNG LĂN CHUỘT MÀN HÌNH CHÍNH) -->
            <div class="flex-1 overflow-hidden p-2 flex flex-col min-h-0 bg-slate-100">

                <!-- ============================================================== -->
                <!-- MENU 1: KHÁM PHÁ DỰ ÁN BẤT ĐỘNG SẢN                            -->
                <!-- ============================================================== -->
                <div x-show="activeMenu === 'projects'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="h-full flex flex-col min-h-0 overflow-hidden space-y-2">
                    
                    <!-- Thanh tìm kiếm & Bộ lọc dự án gọn gàng -->
                    <div class="bg-white p-2 rounded-xl border border-slate-200/80 shadow-sm flex items-center justify-between gap-3 shrink-0">
                        <div class="relative flex-1 max-w-sm">
                            <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                            <input 
                                type="text" 
                                x-model="projectSearch" 
                                placeholder="Tìm kiếm tên dự án, vị trí..." 
                                class="w-full pl-8 pr-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:outline-none focus:ring-2 focus:ring-blue-500"
                            >
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <div class="px-2.5 py-1 bg-blue-50 border border-blue-200 text-blue-700 rounded-lg text-xs font-bold flex items-center gap-1.5">
                                <i class="fa-solid fa-shapes text-blue-600 text-[11px]"></i>
                                <span>{{ $totalProjectsCount }} Dự án có sơ đồ chỉ căn</span>
                            </div>
                        </div>
                    </div>

                    <!-- Danh sách Dự án BĐS dạng Lưới (Cuộn nội bộ nếu nhiều dự án) -->
                    <div class="flex-1 min-h-0 overflow-y-auto custom-scroll pr-1">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach($projects as $project)
                            <div 
                                x-show="filterProject('{{ addslashes($project->name) }}', '{{ addslashes($project->address ?? '') }}', {{ $project->has_locators ? 'true' : 'false' }})"
                                class="bg-white rounded-2xl border border-slate-200/90 overflow-hidden shadow-sm hover:shadow-md transition-all duration-200 flex flex-col group"
                            >
                                <!-- Thumbnail / Ảnh Tổng Quan Dự Án -->
                                <div class="relative h-48 sm:h-56 bg-slate-900 overflow-hidden">
                                    <img 
                                        src="{{ !empty($project->overview_image) ? $project->overview_image : (!empty($project->img) ? route('admin.unit-locators.project-image', $project->img) : 'https://indochinerealestate.vn/uploads/1777014988.jpg') }}" 
                                        alt="{{ $project->name }}" 
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        loading="lazy"
                                        onerror="this.src='https://indochinerealestate.vn/uploads/1777014988.jpg'"
                                    >
                                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/90 via-slate-950/30 to-black/40"></div>
                                    
                                    <!-- Badges nổi bật góc trên -->
                                    <div class="absolute top-3 left-3 right-3 flex items-center justify-between gap-2">
                                        <div class="flex flex-wrap items-center gap-1.5">
                                            @if($project->has_locators)
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-amber-500 text-slate-900 shadow-md">
                                                    <i class="fa-solid fa-map-location-dot text-[11px]"></i>
                                                    <span>{{ $project->unit_locator_count }} Căn có chỉ căn</span>
                                                </span>
                                            @endif
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-600/90 text-white backdrop-blur-sm shadow-md">
                                                Đang mở bán
                                            </span>
                                        </div>
                                        @if(!empty($project->developer))
                                            <span class="px-2.5 py-1 rounded-lg text-[11px] font-extrabold bg-blue-600/90 text-white backdrop-blur-sm shadow-md uppercase tracking-wider">
                                                {{ $project->developer }}
                                            </span>
                                        @endif
                                    </div>

                                    <!-- Tiêu đề & Địa chỉ nổi bật ở chân ảnh -->
                                    <div class="absolute bottom-3 left-3.5 right-3.5 text-white">
                                        <h3 class="font-extrabold text-base sm:text-xl line-clamp-1 group-hover:text-blue-300 transition drop-shadow-md">
                                            {{ $project->name }}
                                        </h3>
                                        <p class="text-[11px] sm:text-xs text-slate-200 line-clamp-1 flex items-center gap-1.5 mt-1 drop-shadow-md">
                                            <i class="fa-solid fa-location-dot text-rose-400 text-xs shrink-0"></i>
                                            <span>{{ $project->location_detail ?? ($project->address ?: 'Nam An Khánh, Hoài Đức, Hà Nội') }}</span>
                                        </p>
                                    </div>
                                </div>

                                <!-- Thông tin chi tiết dự án -->
                                <div class="p-3.5 sm:p-4 flex-1 flex flex-col justify-between space-y-3">
                                    <div class="space-y-3">
                                        <!-- Thông số tổng quan chi tiết 4 ô cân đối -->
                                        <div class="grid grid-cols-4 gap-1 py-1.5 px-1 bg-slate-50 rounded-xl border border-slate-100 text-center">
                                            <div class="truncate">
                                                <span class="text-[9px] text-slate-400 uppercase font-semibold block">Quy mô</span>
                                                <span class="text-[11px] font-extrabold text-slate-800 truncate block" title="{{ $project->overview_scale }}">{{ $project->overview_scale ?: '...' }}</span>
                                            </div>
                                            <div class="truncate">
                                                <span class="text-[9px] text-slate-400 uppercase font-semibold block">Số lượng</span>
                                                <span class="text-[11px] font-extrabold text-slate-800 block">{{ $project->total_product ? '~' . number_format($project->total_product) : '~3.000' }}</span>
                                            </div>
                                            <div class="truncate">
                                                <span class="text-[9px] text-slate-400 uppercase font-semibold block">Diện tích</span>
                                                <span class="text-[11px] font-extrabold text-slate-800 block">{{ $project->acreage ? $project->acreage . '-' . ($project->acreage_max ?: '122') : '27-122' }}</span>
                                            </div>
                                            <div class="truncate">
                                                <span class="text-[9px] text-slate-400 uppercase font-semibold block">Pháp lý</span>
                                                <span class="text-[11px] font-extrabold text-emerald-700 block truncate" title="{{ $project->legal_status }}">{{ $project->legal_status ?: 'Lâu dài' }}</span>
                                            </div>
                                        </div>

                                        <!-- Chi tiết đơn vị phát triển & loại hình căn hộ -->
                                        <div class="text-[11px] text-slate-600 bg-slate-50/60 p-2.5 rounded-xl border border-slate-100/80 flex items-center justify-between gap-2">
                                            <span class="font-bold text-slate-800 truncate">{{ $project->developer ?: 'MIK Group' }}</span>
                                            <span class="text-slate-300">|</span>
                                            <span class="font-semibold text-slate-700 truncate" title="{{ $project->unit_types_text }}">
                                                {{ $project->unit_types_text ?: 'Studio, 1PN, 2PN, 3PN' }}
                                            </span>
                                        </div>


                                        @if(!empty($project->description))
                                            <p class="text-[11px] text-slate-500 line-clamp-2 leading-relaxed flex items-start gap-1.5 px-0.5">
                                                <i class="fa-solid fa-quote-left text-slate-300 text-[10px] shrink-0 mt-0.5"></i>
                                                <span>{{ $project->description }}</span>
                                            </p>
                                        @endif
                                    </div>

                                    <!-- Hàng nút hành động -->
                                    <div class="pt-2.5 border-t border-slate-100 flex items-center gap-2">
                                        @if($project->has_locators)
                                            <button 
                                                type="button" 
                                                id="btn-project-locator-{{ $project->id }}"
                                                @click="selectProjectAndSwitchLocator({{ $project->id }}, '{{ addslashes($project->name) }}')"
                                                class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition"
                                            >
                                                <i class="fa-solid fa-map-location-dot"></i>
                                                <span>Xem Sơ đồ Chỉ căn ({{ $project->unit_locator_count }})</span>
                                            </button>
                                        @else
                                            <button 
                                                type="button" 
                                                @click="openConsultationModal('{{ addslashes($project->name) }}')"
                                                class="flex-1 inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-slate-800 hover:bg-slate-700 text-white text-xs font-semibold transition"
                                            >
                                                <i class="fa-solid fa-envelope"></i>
                                                <span>Nhận báo giá dự án</span>
                                            </button>
                                        @endif

                                        <button 
                                            type="button" 
                                            @click="openConsultationModal('{{ addslashes($project->name) }}')"
                                            class="px-3.5 py-2.5 rounded-xl bg-slate-100 hover:bg-blue-50 text-slate-700 hover:text-blue-600 text-xs font-bold transition border border-slate-200 flex items-center gap-1.5 shrink-0"
                                            title="Tư vấn dự án này"
                                        >
                                            <i class="fa-regular fa-comment-dots text-blue-600"></i>
                                            <span class="hidden sm:inline">Tư vấn</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                        </div>
                    </div>
                </div>

                <!-- ============================================================== -->
                <!-- MENU 2: SƠ ĐỒ CHỈ CĂN BẤT ĐỘNG SẢN TƯƠNG TÁC (UNIT LOCATOR)     -->
                <!-- ============================================================== -->
                <div x-show="activeMenu === 'locator'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="h-full flex flex-col min-h-0 overflow-hidden">
                    
                    <!-- TAB CON 1: SƠ ĐỒ CHỈ CĂN CHI TIẾT (2 CỘT: CỘT ĐẦU DANH SÁCH CĂN HỘ, BÊN CÒN LẠI HIỂN THỊ ẢNH CHỈ CĂN) -->
                    <!-- 100% HIỂN THỊ TRÊN 1 MÀN HÌNH KHÔNG CẦN LĂN CHUỘT -->
                    <div x-show="locatorSubTab === 'units'" class="flex-1 min-h-0 grid grid-cols-12 gap-2 h-full overflow-hidden">
                        
                        <!-- CỘT 1 (CỘT ĐẦU): DANH SÁCH CĂN HỘ & BỘ LỌC TÌM KIẾM -->
                        <div class="col-span-12 lg:col-span-4 xl:col-span-3 bg-white rounded-xl border border-slate-200/90 shadow-sm p-2 flex flex-col h-full overflow-hidden">
                            <!-- Tiêu đề & Tổng số căn -->
                            <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 shrink-0 mb-1.5">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800 uppercase tracking-wider">
                                    <i class="fa-solid fa-shapes text-blue-600 text-xs"></i>
                                    <span>Danh Sách Căn Hộ</span>
                                </div>
                                <span class="px-2 py-0.2 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                                    <span x-text="filteredUnits.length"></span> Căn
                                </span>
                            </div>

                            <!-- Bộ lọc tìm kiếm nhanh gọn gàng -->
                            <div class="space-y-1.5 pb-2 border-b border-slate-100 shrink-0">
                                <!-- Ô tìm kiếm mã căn hoặc trục căn -->
                                <div class="relative">
                                    <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                                    <input 
                                        type="text" 
                                        x-model="unitFilter.search" 
                                        @input="if (filteredUnits.length > 0 && (!selectedUnit || !filteredUnits.some(u => u.id === selectedUnit.id))) selectedUnit = filteredUnits[0]"
                                        placeholder="Tìm mã căn, trục căn (VD: E1.05, Trục 08)..." 
                                        class="w-full pl-7 pr-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-xs focus:ring-1 focus:ring-blue-500 focus:outline-none"
                                    >
                                </div>

                                <!-- Bộ chọn nhanh Tòa, Tầng, Trục căn, Loại căn (Lưới 2x2 cân đối, rõ ràng) -->
                                <div class="grid grid-cols-2 gap-1.5">
                                    <!-- Tòa -->
                                    <select 
                                        x-model="unitFilter.block" 
                                        @change="if (filteredUnits.length > 0 && (!selectedUnit || !filteredUnits.some(u => u.id === selectedUnit.id))) selectedUnit = filteredUnits[0]"
                                        class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-[11px] focus:ring-1 focus:ring-blue-500 font-medium text-slate-700 truncate"
                                    >
                                        <option value="">Tất cả Tòa</option>
                                        <template x-for="blk in availableBlocks" :key="blk">
                                            <option :value="blk" x-text="'Tòa ' + blk"></option>
                                        </template>
                                    </select>

                                    <!-- Tầng -->
                                    <select 
                                        x-model="unitFilter.floor" 
                                        @change="if (filteredUnits.length > 0 && (!selectedUnit || !filteredUnits.some(u => u.id === selectedUnit.id))) selectedUnit = filteredUnits[0]"
                                        class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-[11px] focus:ring-1 focus:ring-blue-500 font-medium text-slate-700 truncate"
                                    >
                                        <option value="">Tất cả Tầng</option>
                                        <template x-for="flr in availableFloors" :key="flr">
                                            <option :value="flr" x-text="'Tầng ' + flr"></option>
                                        </template>
                                    </select>

                                    <!-- Trục căn -->
                                    <select 
                                        x-model="unitFilter.axis" 
                                        @change="if (filteredUnits.length > 0 && (!selectedUnit || !filteredUnits.some(u => u.id === selectedUnit.id))) selectedUnit = filteredUnits[0]"
                                        class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-[11px] focus:ring-1 focus:ring-blue-500 font-medium text-slate-700 truncate"
                                    >
                                        <option value="">Tất cả Trục căn</option>
                                        <template x-for="ax in availableAxes" :key="ax">
                                            <option :value="ax" x-text="'Trục ' + ax"></option>
                                        </template>
                                    </select>

                                    <!-- Loại căn -->
                                    <select 
                                        x-model="unitFilter.type" 
                                        @change="if (filteredUnits.length > 0 && (!selectedUnit || !filteredUnits.some(u => u.id === selectedUnit.id))) selectedUnit = filteredUnits[0]"
                                        class="w-full px-2 py-1 bg-slate-50 border border-slate-200 rounded-lg text-[11px] focus:ring-1 focus:ring-blue-500 font-medium text-slate-700 truncate"
                                    >
                                        <option value="">Tất cả Loại</option>
                                        <template x-for="tp in availableTypes" :key="tp">
                                            <option :value="tp" x-text="tp"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>

                            <!-- Danh sách các căn hộ để click chọn (Cuộn dọc) -->
                            <div class="flex-1 min-h-0 overflow-y-auto space-y-1.5 custom-scroll pr-0.5 pt-1.5">
                                <template x-for="unit in filteredUnits" :key="unit.id">
                                    <button 
                                        type="button" 
                                        @click="selectedUnit = unit"
                                        :class="selectedUnit && selectedUnit.id === unit.id ? 'bg-blue-600 text-white shadow-sm border-blue-600 ring-1 ring-blue-600' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200'"
                                        class="w-full p-2.5 rounded-lg border text-left transition-all duration-150 flex flex-col gap-0.5 group"
                                    >
                                        <div class="flex items-center justify-between w-full">
                                            <p class="text-[13px] font-extrabold truncate leading-tight" x-text="unit.unit_code"></p>
                                            <span 
                                                class="px-1.5 py-0.5 rounded text-[9px] font-bold border shrink-0"
                                                :class="selectedUnit && selectedUnit.id === unit.id ? 'bg-white/20 text-white border-white/30' : {
                                                    'bg-emerald-50 text-emerald-700 border-emerald-200': unit.status === 'available',
                                                    'bg-amber-50 text-amber-700 border-amber-200': unit.status === 'reserved' || unit.status === 'deposited',
                                                    'bg-rose-50 text-rose-700 border-rose-200': unit.status === 'sold'
                                                }"
                                                x-text="unit.status === 'available' ? 'Trống' : (unit.status === 'sold' ? 'Đã bán' : 'Cọc')"
                                            ></span>
                                        </div>
                                        <div class="flex items-center justify-between w-full">
                                            <p 
                                                :class="selectedUnit && selectedUnit.id === unit.id ? 'text-blue-100' : 'text-slate-500'"
                                                class="text-[11px] truncate leading-snug" 
                                                x-text="(unit.block ? 'Tòa ' + unit.block + ' ' : '') + '• T' + (unit.floor || '-') + (unit.unit_axis ? ' • Trục ' + unit.unit_axis : '') + ' • ' + (unit.unit_type || '2PN') + (unit.area ? ' • ' + unit.area + 'm²' : '')"
                                            ></p>
                                            <p 
                                                :class="selectedUnit && selectedUnit.id === unit.id ? 'text-amber-200 font-extrabold' : 'text-blue-600 font-extrabold'"
                                                class="text-[12px] truncate leading-snug"
                                                x-text="unit.price_text || unit.display_price || 'Liên hệ'"
                                            ></p>
                                        </div>
                                    </button>
                                </template>

                                <!-- Không tìm thấy căn nào -->
                                <div x-show="filteredUnits.length === 0" class="p-6 text-center text-slate-400">
                                    <i class="fa-solid fa-filter-circle-xmark text-xl mb-1 text-slate-300 block"></i>
                                    <p class="text-xs font-semibold text-slate-600">Không tìm thấy căn phù hợp</p>
                                    <button 
                                        type="button" 
                                        @click="resetUnitFilters(); if (unitsList.length > 0) selectedUnit = unitsList[0];" 
                                        class="mt-2 px-2.5 py-1 bg-blue-600 hover:bg-blue-700 text-white text-[11px] rounded-lg font-bold transition"
                                    >
                                        Đặt lại bộ lọc
                                    </button>
                                </div>
                            </div>

                            <div class="pt-1.5 mt-1 border-t border-slate-100 text-[10px] text-slate-400 shrink-0">
                                Click căn hộ để xem sơ đồ chỉ căn chi tiết.
                            </div>
                        </div>

                        <!-- CỘT 2 (BÊN CÒN LẠI): HIỂN THỊ ẢNH POSTER & SƠ ĐỒ CHỈ CĂN CHI TIẾT (CO DÃN 100% VỪA MÀN HÌNH - KHÔNG CUỘN) -->
                        <div class="col-span-12 lg:col-span-8 xl:col-span-9 bg-white rounded-xl border border-slate-200/90 shadow-sm p-2 flex flex-col h-full overflow-hidden">
                            <template x-if="selectedUnit">
                                <div class="h-full flex flex-col min-h-0 overflow-hidden">
                                    
                                    <!-- Header thanh công cụ trên ảnh chỉ căn -->
                                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 shrink-0 mb-1.5 px-1 gap-2">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <h3 class="text-xs sm:text-sm font-extrabold text-slate-900 truncate" x-text="'Căn hộ ' + selectedUnit.unit_code"></h3>
                                            <span 
                                                class="px-2 py-0.2 rounded-full text-[9px] font-bold border shrink-0"
                                                :class="{
                                                    'bg-emerald-50 text-emerald-700 border-emerald-200': selectedUnit.status === 'available',
                                                    'bg-amber-50 text-amber-700 border-amber-200': selectedUnit.status === 'reserved' || selectedUnit.status === 'deposited',
                                                    'bg-rose-50 text-rose-700 border-rose-200': selectedUnit.status === 'sold'
                                                }"
                                                x-text="selectedUnit.status === 'available' ? '🟢 Còn trống' : (selectedUnit.status === 'sold' ? '🔴 Đã bán' : '🟡 Đang giữ chỗ')"
                                            ></span>
                                            <span class="text-xs sm:text-sm font-extrabold text-blue-600 truncate" x-text="(selectedUnit.price_text || selectedUnit.display_price || 'Liên hệ') + (selectedUnit.price_note ? ' ' + selectedUnit.price_note : '')"></span>
                                            <span class="text-[11px] text-slate-400 hidden xl:inline truncate" x-text="(selectedUnit.block ? 'Tòa ' + selectedUnit.block + ' • ' : '') + 'T' + (selectedUnit.floor || '-') + (selectedUnit.unit_axis ? ' • Trục ' + selectedUnit.unit_axis : '') + ' • ' + (selectedUnit.unit_type || '2PN') + (selectedUnit.area ? ' • ' + selectedUnit.area + ' m²' : '') + (selectedUnit.direction ? ' • ' + selectedUnit.direction : '')"></span>
                                        </div>

                                        <!-- Các nút thao tác nhanh: Tư vấn, Tính vay, Phóng to, Tab mới, Tải ảnh -->
                                        <div class="flex items-center gap-1 shrink-0">
                                            <button 
                                                type="button" 
                                                @click="openConsultationModal(currentProject.name, selectedUnit.unit_code)" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white text-[11px] font-bold shadow-sm transition"
                                                title="Đăng ký tư vấn căn này"
                                            >
                                                <i class="fa-solid fa-paper-plane text-[9px]"></i>
                                                <span class="hidden sm:inline">Tư vấn căn này</span>
                                            </button>

                                            <button 
                                                type="button" 
                                                @click="calculateLoanForUnit(selectedUnit)" 
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-[11px] font-semibold transition"
                                                title="Bảng tính trả góp"
                                            >
                                                <i class="fa-solid fa-calculator text-[10px]"></i>
                                                <span class="hidden md:inline">Tính vay</span>
                                            </button>

                                            <button 
                                                type="button" 
                                                @click="openLightbox(getSelectedPosterSource(), 'Sơ đồ chi tiết Căn hộ ' + selectedUnit.unit_code)" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white text-[11px] font-bold transition"
                                                title="Xem phóng to toàn màn hình"
                                            >
                                                <i class="fa-solid fa-expand text-[10px]"></i>
                                                <span>Phóng to</span>
                                            </button>

                                            <a 
                                                :href="getSelectedPosterSource()" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 text-[11px] font-semibold transition"
                                                title="Mở ảnh gốc trong tab mới"
                                            >
                                                <i class="fa-solid fa-up-right-from-square text-[10px]"></i>
                                                <span class="hidden sm:inline">Tab mới</span>
                                            </a>

                                            <button 
                                                type="button" @click="downloadSelectedPoster()"
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 text-[11px] font-semibold transition"
                                                title="Tải ảnh Poster chỉ căn"
                                            >
                                                <i class="fa-solid fa-download text-[10px]"></i>
                                                <span class="hidden sm:inline">Tải về</span>
                                            </button>
                                        </div>
                                    </div>

                                    <!-- Khung hiển thị ảnh Poster/Sơ đồ chỉ căn: Dùng relative + absolute inset-0 để bảo đảm 100% VỪA MÀN HÌNH - KHÔNG CUỘN -->
                                    <div class="flex-1 min-h-0 relative w-full bg-slate-950 rounded-lg overflow-hidden flex items-center justify-center group border border-slate-800">
                                        <template x-if="getSelectedPosterLayout()">
                                            <div class="relative h-full max-w-full overflow-hidden shadow-lg" :style="'aspect-ratio:' + getSelectedPosterLayout().width + '/' + getSelectedPosterLayout().height + ';container-type:inline-size;background:linear-gradient(145deg,#3f1703,#7c2d05,#270e02)'">
                                                <img x-show="getSelectedPosterLayout().background" :src="getSelectedPosterLayout().background" class="absolute inset-0 w-full h-full object-fill pointer-events-none" :alt="selectedUnit.unit_code + ' poster background'">
                                                <template x-for="image in getSelectedPosterLayout().images" :key="image.id">
                                                    <img x-show="image.source" :src="image.source" :style="posterImageStyle(image)" class="absolute select-none pointer-events-none" :alt="image.id">
                                                </template>
                                                <template x-for="text in getSelectedPosterLayout().texts" :key="text.key">
                                                    <div :style="posterTextStyle(text)" class="absolute leading-none whitespace-nowrap" :class="text.badge ? 'inline-flex items-center justify-center px-[0.45cqw] py-[0.25cqw]' : ''" x-text="text.value"></div>
                                                </template>
                                            </div>
                                        </template>
                                        <template x-if="!getSelectedPosterLayout() && getSelectedPosterSource()">
                                            <img :src="getSelectedPosterSource()" :alt="selectedUnit.unit_code" class="absolute inset-0 w-full h-full object-contain p-2 select-none cursor-zoom-in" @click="openLightbox(getSelectedPosterSource(), 'Sơ đồ chi tiết Căn hộ ' + selectedUnit.unit_code)" loading="lazy">
                                        </template>
                                        <template x-if="!getSelectedPosterLayout() && !getSelectedPosterSource()">
                                            <div class="flex flex-col items-center justify-center text-slate-500">
                                                <i class="fa-regular fa-image text-3xl mb-1"></i>
                                                <span class="text-xs">Chưa có ảnh sơ đồ chỉ căn</span>
                                            </div>
                                        </template>

                                    </div>

                                </div>
                            </template>

                            <!-- Trạng thái trống nếu chưa có căn nào được chọn -->
                            <template x-if="!selectedUnit">
                                <div class="h-full flex flex-col items-center justify-center p-8 text-center text-slate-400">
                                    <i class="fa-solid fa-shapes text-3xl mb-2 text-slate-300"></i>
                                    <p class="text-xs font-bold text-slate-600">Chưa chọn căn hộ</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Vui lòng chọn một căn hộ ở danh sách bên trái để xem sơ đồ chỉ căn.</p>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- TAB CON 2: SƠ ĐỒ MẶT BẰNG TỔNG THỂ (2 CỘT: CỘT ĐẦU TÊN MẶT BẰNG, BÊN CÒN LẠI HIỂN THỊ ẢNH MẶT BẰNG) -->
                    <!-- 100% HIỂN THỊ TRÊN 1 MÀN HÌNH KHÔNG CẦN LĂN CHUỘT -->
                    <div x-show="locatorSubTab === 'floorplans'" class="flex-1 min-h-0 grid grid-cols-12 gap-2 h-full overflow-hidden">
                        
                        <!-- CỘT 1 (CỘT ĐẦU): DANH SÁCH TÊN MẶT BẰNG -->
                        <div class="col-span-12 lg:col-span-4 xl:col-span-3 bg-white rounded-xl border border-slate-200/90 shadow-sm p-2 flex flex-col h-full overflow-hidden">
                            <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 shrink-0 mb-1.5">
                                <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800 uppercase tracking-wider">
                                    <i class="fa-solid fa-layer-group text-blue-600 text-xs"></i>
                                    <span>Danh Sách Mặt Bằng</span>
                                </div>
                                <span class="px-2 py-0.2 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">
                                    <span x-text="floorplansList.length"></span> Bản vẽ
                                </span>
                            </div>

                            <!-- Danh sách các tên mặt bằng để click chọn -->
                            <div class="flex-1 min-h-0 overflow-y-auto space-y-1 custom-scroll pr-0.5">
                                <template x-for="(fp, idx) in floorplansList" :key="fp.id">
                                    <button 
                                        type="button" 
                                        @click="selectedFloorplan = fp"
                                        :class="selectedFloorplan && selectedFloorplan.id === fp.id ? 'bg-blue-600 text-white shadow-sm border-blue-600' : 'bg-slate-50 hover:bg-slate-100 text-slate-700 border-slate-200'"
                                        class="w-full p-2 rounded-lg border text-left transition-all duration-150 flex items-center justify-between group"
                                    >
                                        <div class="flex items-center gap-2 min-w-0">
                                            <div 
                                                :class="selectedFloorplan && selectedFloorplan.id === fp.id ? 'bg-white/20 text-white' : 'bg-blue-100/60 text-blue-600'"
                                                class="w-7 h-7 rounded-lg flex items-center justify-center text-xs shrink-0"
                                            >
                                                <i class="fa-regular fa-map text-[11px]"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-xs font-bold truncate leading-tight" x-text="fp.name"></p>
                                                <p 
                                                    :class="selectedFloorplan && selectedFloorplan.id === fp.id ? 'text-blue-100' : 'text-slate-400'"
                                                    class="text-[10px] truncate mt-0.5" 
                                                    x-text="(fp.block ? 'Tòa ' + fp.block + ' ' : '') + (fp.floor_range ? '• Tầng ' + fp.floor_range : 'Mặt bằng tầng điển hình')"
                                                ></p>
                                            </div>
                                        </div>
                                        <i 
                                            :class="selectedFloorplan && selectedFloorplan.id === fp.id ? 'text-white' : 'text-slate-300 group-hover:text-slate-500'"
                                            class="fa-solid fa-chevron-right text-[10px] shrink-0 ml-1"
                                        ></i>
                                    </button>
                                </template>
                            </div>

                            <div class="pt-1.5 mt-1 border-t border-slate-100 text-[10px] text-slate-400 shrink-0">
                                Click tên mặt bằng để đổi bản vẽ.
                            </div>
                        </div>

                        <!-- CỘT 2 (BÊN CÒN LẠI): HIỂN THỊ ẢNH MẶT BẰNG (CO DÃN 100% VỪA MÀN HÌNH - KHÔNG CUỘN) -->
                        <div class="col-span-12 lg:col-span-8 xl:col-span-9 bg-white rounded-xl border border-slate-200/90 shadow-sm p-2 flex flex-col h-full overflow-hidden">
                            <template x-if="selectedFloorplan">
                                <div class="h-full flex flex-col min-h-0 overflow-hidden">
                                    
                                    <!-- Header thanh công cụ nhỏ gọn trên ảnh mặt bằng -->
                                    <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 shrink-0 mb-1.5 px-1">
                                        <div class="flex items-center gap-2 min-w-0">
                                            <h3 class="text-xs sm:text-sm font-extrabold text-slate-900 truncate" x-text="selectedFloorplan.name"></h3>
                                            <span class="px-2 py-0.2 rounded-full text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 shrink-0">
                                                Bản vẽ chính thức
                                            </span>
                                            <span class="text-[11px] text-slate-400 hidden sm:inline truncate" x-text="(selectedFloorplan.block ? 'Tòa ' + selectedFloorplan.block + ' • ' : '') + (selectedFloorplan.floor_range ? 'Phạm vi: ' + selectedFloorplan.floor_range : 'Bản vẽ kiến trúc')"></span>
                                        </div>

                                        <!-- Các nút xem phóng to & tải ảnh -->
                                        <div class="flex items-center gap-1 shrink-0">
                                            <button 
                                                type="button" 
                                                @click="openLightbox(selectedFloorplan.image_url, selectedFloorplan.name)" 
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white text-[11px] font-bold transition"
                                                title="Xem phóng to toàn màn hình"
                                            >
                                                <i class="fa-solid fa-expand text-[10px]"></i>
                                                <span>Phóng to</span>
                                            </button>
                                            <a 
                                                :href="selectedFloorplan.image_url" 
                                                target="_blank" 
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 text-[11px] font-semibold transition"
                                                title="Mở ảnh gốc trong tab mới"
                                            >
                                                <i class="fa-solid fa-up-right-from-square text-[10px]"></i>
                                                <span class="hidden sm:inline">Tab mới</span>
                                            </a>
                                            <a 
                                                :href="selectedFloorplan.image_url" 
                                                download 
                                                class="inline-flex items-center gap-1 px-2 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 text-[11px] font-semibold transition"
                                                title="Tải ảnh"
                                            >
                                                <i class="fa-solid fa-download text-[10px]"></i>
                                                <span class="hidden sm:inline">Tải về</span>
                                            </a>
                                        </div>
                                    </div>

                                    <!-- Khung hiển thị ảnh: Dùng relative + absolute inset-0 để bảo đảm 100% VỪA MÀN HÌNH - KHÔNG CUỘN -->
                                    <div class="flex-1 min-h-0 relative w-full bg-slate-900 rounded-lg overflow-hidden flex items-center justify-center group">
                                        <img 
                                            :src="selectedFloorplan.image_url" 
                                            :alt="selectedFloorplan.name" 
                                            class="absolute inset-0 w-full h-full object-contain p-2 select-none cursor-zoom-in"
                                            @click="openLightbox(selectedFloorplan.image_url, selectedFloorplan.name)"
                                            loading="lazy"
                                        >
                                        <!-- Nút xem nhanh toàn màn hình trên ảnh -->
                                        <button 
                                            type="button" 
                                            @click="openLightbox(selectedFloorplan.image_url, selectedFloorplan.name)" 
                                            class="absolute bottom-2 right-2 px-2.5 py-1 rounded-lg bg-black/75 hover:bg-black text-white text-[10px] font-bold backdrop-blur-sm transition flex items-center gap-1 shadow opacity-0 group-hover:opacity-100"
                                        >
                                            <i class="fa-solid fa-magnifying-glass-plus text-[9px]"></i>
                                            <span>Click để phóng to</span>
                                        </button>
                                    </div>

                                </div>
                            </template>

                            <!-- Trạng thái trống nếu chưa có mặt bằng -->
                            <template x-if="!selectedFloorplan">
                                <div class="h-full flex flex-col items-center justify-center p-8 text-center text-slate-400">
                                    <i class="fa-regular fa-map text-3xl mb-2 text-slate-300"></i>
                                    <p class="text-xs font-bold text-slate-600">Chưa có mặt bằng</p>
                                    <p class="text-[11px] text-slate-400 mt-0.5">Vui lòng chọn một mặt bằng ở cột bên trái để hiển thị bản vẽ.</p>
                                </div>
                            </template>
                        </div>

                    </div>

                </div>

                </div>

                {{-- TẠM ẨN: MENU 3 - BẢNG HÀNG & TRA CỨU CĂN HỘ
                <div x-show="activeMenu === 'inventory'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                    
                    <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                        <div>
                            <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">Bảng Hàng Tổng Hợp & Tra Cứu</h2>
                            <p class="text-xs text-slate-500">Tra cứu nhanh toàn bộ danh sách căn hộ, diện tích, giá bán và tình trạng giao dịch.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <button 
                                type="button" 
                                @click="exportInventoryCsv()" 
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition"
                            >
                                <i class="fa-solid fa-file-excel text-emerald-600"></i>
                                <span>Xuất Excel / CSV</span>
                            </button>
                        </div>
                    </div>

                    <!-- Bảng dữ liệu bảng hàng -->
                    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                    <tr class="bg-slate-50 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                                        <th class="py-3.5 px-4">Mã Căn</th>
                                        <th class="py-3.5 px-4">Tòa / Tầng</th>
                                        <th class="py-3.5 px-4">Loại Căn</th>
                                        <th class="py-3.5 px-4">Diện Tích (m²)</th>
                                        <th class="py-3.5 px-4">Hướng Ban Công</th>
                                        <th class="py-3.5 px-4">Giá Niêm Yết</th>
                                        <th class="py-3.5 px-4">Trạng Thái</th>
                                        <th class="py-3.5 px-4 text-right">Thao Tác</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-100">
                                    <template x-for="unit in filteredUnits" :key="unit.id">
                                        <tr class="hover:bg-blue-50/40 transition">
                                            <td class="py-3 px-4 font-bold text-slate-900 text-sm" x-text="unit.unit_code"></td>
                                            <td class="py-3 px-4 text-slate-700" x-text="(unit.block ? 'Tòa ' + unit.block + ' - ' : '') + 'Tầng ' + (unit.floor || '-')"></td>
                                            <td class="py-3 px-4">
                                                <span class="px-2 py-0.5 rounded-md font-semibold bg-slate-100 text-slate-700" x-text="unit.unit_type || '2PN'"></span>
                                            </td>
                                            <td class="py-3 px-4 font-bold text-slate-800" x-text="unit.area ? unit.area + ' m²' : '-'"></td>
                                            <td class="py-3 px-4 text-slate-600" x-text="unit.direction || 'Đông Nam'"></td>
                                            <td class="py-3 px-4 font-extrabold text-blue-600 text-sm" x-text="unit.price_text || unit.display_price || 'Thỏa thuận'"></td>
                                            <td class="py-3 px-4">
                                                <span 
                                                    class="px-2 py-0.5 rounded-full text-[10px] font-bold border"
                                                    :class="{
                                                        'bg-emerald-50 text-emerald-700 border-emerald-200': unit.status === 'available',
                                                        'bg-amber-50 text-amber-700 border-amber-200': unit.status === 'reserved' || unit.status === 'deposited',
                                                        'bg-rose-50 text-rose-700 border-rose-200': unit.status === 'sold',
                                                        'bg-slate-100 text-slate-700 border-slate-300': unit.status === 'locked'
                                                    }"
                                                    x-text="unit.status === 'available' ? 'Còn trống' : (unit.status === 'sold' ? 'Đã bán' : 'Đang giữ chỗ')"
                                                ></span>
                                            </td>
                                            <td class="py-3 px-4 text-right">
                                                <div class="flex items-center justify-end gap-1.5">
                                                    <button 
                                                        type="button" 
                                                        @click="openUnitDetail(unit)" 
                                                        class="px-2.5 py-1 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white font-semibold transition"
                                                    >
                                                        Xem chỉ căn
                                                    </button>
                                                    <button 
                                                        type="button" 
                                                        @click="calculateLoanForUnit(unit)" 
                                                        class="px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold transition"
                                                        title="Tính bảng vay trả góp"
                                                    >
                                                        <i class="fa-solid fa-calculator"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                --}}

                <!-- ============================================================== -->
                <!-- MENU 4: BẢNG TÍNH VAY MUA NHÀ & LÃI SUẤT (LOAN CALCULATOR)      -->
                <!-- ============================================================== -->
                <div x-show="activeMenu === 'calculator'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="h-full flex flex-col min-h-0 overflow-hidden space-y-2">
                    
                    <div class="bg-white px-3.5 py-2 rounded-xl border border-slate-200/80 shadow-sm shrink-0">
                        <h2 class="text-xs sm:text-sm font-extrabold text-slate-900">Bảng Tính Lãi Suất & Phương Án Vay Ngân Hàng</h2>
                        <p class="text-[10px] text-slate-500">Dự toán kế hoạch tài chính, số tiền trả hàng tháng và hạn mức vay phù hợp.</p>
                    </div>

                    <div class="flex-1 min-h-0 overflow-y-auto custom-scroll pr-1">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
                        <!-- Cột nhập thông số tính toán -->
                        <div class="lg:col-span-5 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-5">
                            
                            <!-- Giá trị BĐS -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Giá trị Bất động sản (VNĐ)</label>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        x-model.number="calc.price" 
                                        @input="recalculateLoan()" 
                                        step="100000000"
                                        class="w-full pl-4 pr-12 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-base font-extrabold text-slate-900 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    >
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">VNĐ</span>
                                </div>
                                <div class="flex flex-wrap gap-1.5 mt-2">
                                    <button type="button" @click="calc.price = 3000000000; recalculateLoan()" class="px-2 py-0.5 rounded text-[11px] bg-slate-100 hover:bg-blue-50 hover:text-blue-600 font-semibold text-slate-600">3 Tỷ</button>
                                    <button type="button" @click="calc.price = 5000000000; recalculateLoan()" class="px-2 py-0.5 rounded text-[11px] bg-slate-100 hover:bg-blue-50 hover:text-blue-600 font-semibold text-slate-600">5 Tỷ</button>
                                    <button type="button" @click="calc.price = 6288000000; recalculateLoan()" class="px-2 py-0.5 rounded text-[11px] bg-blue-100 text-blue-700 font-bold">6.28 Tỷ (Imperia)</button>
                                    <button type="button" @click="calc.price = 8000000000; recalculateLoan()" class="px-2 py-0.5 rounded text-[11px] bg-slate-100 hover:bg-blue-50 hover:text-blue-600 font-semibold text-slate-600">8 Tỷ</button>
                                </div>
                            </div>

                            <!-- Tỷ lệ vay -->
                            <div>
                                <div class="flex justify-between text-xs font-bold text-slate-700 mb-1.5">
                                    <span>Tỷ lệ vay ngân hàng:</span>
                                    <span class="text-blue-600 font-extrabold" x-text="calc.ratio + '%'"></span>
                                </div>
                                <input 
                                    type="range" 
                                    min="30" 
                                    max="80" 
                                    step="5" 
                                    x-model.number="calc.ratio" 
                                    @input="recalculateLoan()" 
                                    class="w-full accent-blue-600 cursor-pointer"
                                >
                                <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                                    <span>30%</span>
                                    <span>50%</span>
                                    <span>70% (Phổ biến)</span>
                                    <span>80% (Tối đa)</span>
                                </div>
                            </div>

                            <!-- Thời hạn vay -->
                            <div>
                                <div class="flex justify-between text-xs font-bold text-slate-700 mb-1.5">
                                    <span>Thời gian vay:</span>
                                    <span class="text-blue-600 font-extrabold" x-text="calc.years + ' Năm (' + (calc.years * 12) + ' tháng)'"></span>
                                </div>
                                <input 
                                    type="range" 
                                    min="5" 
                                    max="35" 
                                    step="5" 
                                    x-model.number="calc.years" 
                                    @input="recalculateLoan()" 
                                    class="w-full accent-blue-600 cursor-pointer"
                                >
                                <div class="flex justify-between text-[10px] text-slate-400 mt-1">
                                    <span>5 năm</span>
                                    <span>15 năm</span>
                                    <span>20 năm</span>
                                    <span>35 năm</span>
                                </div>
                            </div>

                            <!-- Lãi suất vay -->
                            <div>
                                <label class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Lãi suất vay ưu đãi (% / năm)</label>
                                <div class="relative">
                                    <input 
                                        type="number" 
                                        x-model.number="calc.interestRate" 
                                        @input="recalculateLoan()" 
                                        step="0.1" 
                                        min="0" 
                                        max="20"
                                        class="w-full pl-4 pr-10 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-extrabold text-slate-900 focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    >
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-bold text-slate-400">%</span>
                                </div>
                                <p class="text-[11px] text-slate-500 mt-1">Lãi suất sau thời gian ưu đãi thường dao động khoảng 8.5% - 10.5%.</p>
                            </div>

                        </div>

                        <!-- Cột kết quả phân tích tài chính -->
                        <div class="lg:col-span-7 space-y-4">
                            
                            <!-- Thẻ tóm tắt kết quả chính -->
                            <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white p-6 rounded-3xl shadow-lg space-y-4">
                                <span class="text-xs uppercase font-bold tracking-wider text-blue-300">Dự toán số tiền phải trả tháng đầu tiên</span>
                                <div class="text-3xl sm:text-4xl font-extrabold text-amber-400" x-text="formatCurrency(calc.firstMonthTotal)"></div>

                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 pt-4 border-t border-slate-800 text-xs">
                                    <div>
                                        <span class="text-slate-400 block">Vốn tự có cần có (30%):</span>
                                        <span class="text-sm font-bold text-white" x-text="formatCurrency(calc.downPayment)"></span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block">Số tiền vay ngân hàng:</span>
                                        <span class="text-sm font-bold text-white" x-text="formatCurrency(calc.loanAmount)"></span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block">Tiền gốc trả / tháng:</span>
                                        <span class="text-sm font-bold text-white" x-text="formatCurrency(calc.monthlyPrincipal)"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Thẻ tư vấn & Ưu đãi ngân hàng đối tác -->
                            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-3">
                                <div class="flex items-center gap-2 text-sm font-bold text-slate-900">
                                    <i class="fa-solid fa-hand-holding-dollar text-emerald-600"></i>
                                    <span>Gói Hỗ Trợ Lãi Suất Đặc Biệt 0% Từ Chủ Đầu Tư</span>
                                </div>
                                <p class="text-xs text-slate-600 leading-relaxed">
                                    Khách hàng sở hữu căn hộ tại <strong>Imperia Sky Park</strong> được hưởng chính sách hỗ trợ lãi suất <strong>0% lên tới 24 tháng</strong>, ân hạn nợ gốc và miễn phí trả nợ trước hạn trong thời gian hỗ trợ lãi suất.
                                </p>
                                <div class="pt-2 flex flex-wrap gap-2">
                                    <button 
                                        type="button" 
                                        @click="openConsultationModal('Imperia Sky Park', 'Tư vấn gói vay 0%')"
                                        class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition shadow"
                                    >
                                        Đăng ký nhận bảng tính chi tiết từ ngân hàng
                                    </button>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- TẠM ẨN: MENU 5 - TIN TỨC & CẨM NANG BẤT ĐỘNG SẢN
                <div x-show="activeMenu === 'news'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="space-y-6">
                    
                    <div class="bg-white p-5 rounded-3xl border border-slate-200/80 shadow-sm">
                        <h2 class="text-lg sm:text-xl font-extrabold text-slate-900">Tin Tức Thị Trường & Cẩm Nang Bất Động Sản</h2>
                        <p class="text-xs text-slate-500">Cập nhật thông tin quy hoạch, biến động thị trường và kinh nghiệm mua bán căn hộ.</p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        @foreach($articles as $art)
                            <div class="bg-white rounded-3xl border border-slate-200 overflow-hidden shadow-sm hover:shadow-lg transition flex flex-col group">
                                <div class="h-48 overflow-hidden bg-slate-800 relative">
                                    <img 
                                        src="{{ $art['image'] }}" 
                                        alt="{{ $art['title'] }}" 
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                                        loading="lazy"
                                    >
                                    <span class="absolute top-3 left-3 px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-600 text-white">
                                        {{ $art['category'] }}
                                    </span>
                                </div>
                                <div class="p-5 flex-1 flex flex-col justify-between">
                                    <div>
                                        <div class="flex items-center gap-3 text-[11px] text-slate-400 mb-2">
                                            <span><i class="fa-regular fa-calendar mr-1"></i> {{ $art['date'] }}</span>
                                            <span>•</span>
                                            <span><i class="fa-regular fa-clock mr-1"></i> {{ $art['read_time'] }}</span>
                                        </div>
                                        <h3 class="font-bold text-base text-slate-900 group-hover:text-blue-600 transition line-clamp-2">
                                            {{ $art['title'] }}
                                        </h3>
                                        <p class="text-xs text-slate-600 mt-2 line-clamp-3 leading-relaxed">
                                            {{ $art['summary'] }}
                                        </p>
                                    </div>
                                    <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                                        <span class="text-[11px] font-bold text-slate-500">#{{ $art['tag'] }}</span>
                                        <button 
                                            type="button" 
                                            @click="openArticleModal({{ json_encode($art) }})" 
                                            class="text-xs font-bold text-blue-600 hover:text-blue-800 transition"
                                        >
                                            Đọc tiếp <i class="fa-solid fa-arrow-right text-[10px] ml-1"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
                --}}

                <!-- ============================================================== -->
                <!-- MENU 6: LIÊN HỆ & ĐĂNG KÝ TƯ VẤN                                -->
                <!-- ============================================================== -->
                <div x-show="activeMenu === 'contact'" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="h-full flex flex-col min-h-0 overflow-hidden space-y-2">
                    
                    <div class="bg-white px-3.5 py-2 rounded-xl border border-slate-200/80 shadow-sm shrink-0">
                        <h2 class="text-xs sm:text-sm font-extrabold text-slate-900">Liên Hệ & Đăng Ký Tư Vấn Chuyên Sâu</h2>
                        <p class="text-[10px] text-slate-500">Đội ngũ chuyên viên IDC sẵn sàng hỗ trợ chọn căn, phân tích dòng tiền và thủ tục hợp đồng.</p>
                    </div>

                    <div class="flex-1 min-h-0 overflow-y-auto custom-scroll pr-1">
                        <div class="grid grid-cols-1 lg:grid-cols-12 gap-3">
                        <!-- Form đăng ký nhận tư vấn -->
                        <div class="lg:col-span-7 bg-white p-6 sm:p-8 rounded-3xl border border-slate-200 shadow-sm">
                            <h3 class="text-base font-extrabold text-slate-900 mb-4">Gửi Yêu Cầu Tư Vấn Trực Tiếp</h3>
                            
                            <form @submit.prevent="submitConsultationForm()" class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Họ và tên của bạn <span class="text-rose-500">*</span></label>
                                    <input 
                                        type="text" 
                                        x-model="consultForm.name" 
                                        required 
                                        placeholder="Ví dụ: Nguyễn Văn An" 
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    >
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Số điện thoại liên hệ <span class="text-rose-500">*</span></label>
                                        <input 
                                            type="tel" 
                                            x-model="consultForm.phone" 
                                            required 
                                            placeholder="Ví dụ: 0989 123 456" 
                                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Email (nếu có)</label>
                                        <input 
                                            type="email" 
                                            x-model="consultForm.email" 
                                            placeholder="email@example.com" 
                                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                        >
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Dự án quan tâm</label>
                                        <input 
                                            type="text" 
                                            x-model="consultForm.projectName" 
                                            placeholder="Imperia Sky Park, The Diamond Residence..." 
                                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                        >
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Mã căn hộ muốn xem (nếu có)</label>
                                        <input 
                                            type="text" 
                                            x-model="consultForm.unitCode" 
                                            placeholder="Ví dụ: E1.0305, E1.2805..." 
                                            class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                        >
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Nhu cầu & Ghi chú thêm</label>
                                    <textarea 
                                        x-model="consultForm.note" 
                                        rows="3" 
                                        placeholder="Ví dụ: Tôi muốn nhận bảng giá chiết khấu thanh toán sớm, và xem chính sách hỗ trợ lãi suất ngân hàng..."
                                        class="w-full px-4 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs sm:text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                    ></textarea>
                                </div>

                                <button 
                                    type="submit" 
                                    :disabled="consultForm.submitting"
                                    class="w-full py-3 px-6 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs sm:text-sm shadow-lg shadow-blue-500/25 transition flex items-center justify-center gap-2"
                                >
                                    <i class="fa-solid fa-paper-plane" x-show="!consultForm.submitting"></i>
                                    <i class="fa-solid fa-circle-notch fa-spin" x-show="consultForm.submitting" x-cloak></i>
                                    <span x-text="consultForm.submitting ? 'Đang gửi thông tin...' : 'Gửi Yêu Cầu Tư Vấn Ngay'"></span>
                                </button>
                            </form>
                        </div>

                        <!-- Thông tin văn phòng & Hỗ trợ -->
                        <div class="lg:col-span-5 space-y-4">
                            <div class="bg-gradient-to-br from-slate-900 to-indigo-950 text-white p-6 sm:p-7 rounded-3xl shadow-lg space-y-4">
                                <h3 class="text-base font-bold text-blue-300">Văn Phòng Giao Dịch IDC Real Estate</h3>
                                <div class="space-y-3 text-xs text-slate-300">
                                    <div class="flex items-start gap-3">
                                        <i class="fa-solid fa-location-dot text-rose-400 mt-0.5 text-sm"></i>
                                        <div>
                                            <p class="font-bold text-white">Trụ sở chính:</p>
                                            <p>Tòa nhà IDC Building, Khu đô thị Nam An Khánh, Hoài Đức, Hà Nội</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <i class="fa-solid fa-phone text-emerald-400 mt-0.5 text-sm"></i>
                                        <div>
                                            <p class="font-bold text-white">Tổng đài CSKH & Phản ánh:</p>
                                            <p>1900 8686 - 0989.123.456 (24/7)</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <i class="fa-solid fa-envelope text-blue-400 mt-0.5 text-sm"></i>
                                        <div>
                                            <p class="font-bold text-white">Email tiếp nhận thông tin:</p>
                                            <p>hotro@idc.vn / contact@idc.vn</p>
                                        </div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <i class="fa-regular fa-clock text-amber-400 mt-0.5 text-sm"></i>
                                        <div>
                                            <p class="font-bold text-white">Thời gian làm việc:</p>
                                            <p>Thứ 2 - Chủ Nhật: 08:00 - 18:30 (Cả ngày lễ)</p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- FAQ nhanh -->
                            <div class="bg-white p-6 rounded-3xl border border-slate-200/80 shadow-sm space-y-3">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-slate-400">Câu hỏi thường gặp</h4>
                                <div class="space-y-2 text-xs">
                                    <details class="p-3 bg-slate-50 rounded-xl cursor-pointer">
                                        <summary class="font-bold text-slate-800">Quy trình đặt cọc giữ chỗ căn hộ như thế nào?</summary>
                                        <p class="mt-2 text-slate-600 leading-relaxed">Khách hàng chọn mã căn trên sơ đồ chỉ căn, liên hệ chuyên viên để khóa căn trên bảng hàng và nộp tiền cọc (thường là 50 triệu - 100 triệu) vào tài khoản chủ đầu tư.</p>
                                    </details>
                                    <details class="p-3 bg-slate-50 rounded-xl cursor-pointer">
                                        <summary class="font-bold text-slate-800">Ảnh chỉ căn 2D/3D có tính pháp lý hay không?</summary>
                                        <p class="mt-2 text-slate-600 leading-relaxed">Hình ảnh chỉ căn được kết xuất từ hồ sơ thiết kế cơ sở và bản vẽ mặt bằng tầng đã được cơ quan có thẩm quyền phê duyệt.</p>
                                    </details>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            </div>

        </main>

    </div>

    <!-- ============================================================== -->
    <!-- MODAL 1: THÔNG TIN CHI TIẾT NGƯỜI DÙNG (USER PROFILE MODAL)     -->
    <!-- Mở khi nhấn nút "Thông tin" ở góc dưới bên trái               -->
    <!-- ============================================================== -->
    <div 
        x-show="userInfoModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title" 
        role="dialog" 
        aria-modal="true"
    >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Backdrop -->
            <div 
                x-show="userInfoModalOpen" 
                @click="userInfoModalOpen = false" 
                x-transition:enter="ease-out duration-300" 
                x-transition:enter-start="opacity-0" 
                x-transition:enter-end="opacity-100" 
                x-transition:leave="ease-in duration-200" 
                x-transition:leave-start="opacity-100" 
                x-transition:leave-end="opacity-0" 
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
            ></div>

            <!-- Modal Panel -->
            <div 
                x-show="userInfoModalOpen" 
                x-transition:enter="ease-out duration-300" 
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" 
                x-transition:leave="ease-in duration-200" 
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" 
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" 
                class="inline-block w-full max-w-lg my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-slate-100 z-10"
            >
                @auth
                    <!-- Header Modal với Gradient đẹp mắt -->
                    <div class="relative bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 p-6 text-white">
                        <div class="flex items-center justify-between">
                            <span class="text-xs font-bold uppercase tracking-wider text-blue-300">Hồ sơ người dùng</span>
                            <button @click="userInfoModalOpen = false" class="text-slate-400 hover:text-white transition">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>
                        <div class="flex items-center gap-4 mt-4">
                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-tr from-blue-600 to-indigo-500 border-2 border-white/20 flex items-center justify-center font-extrabold text-2xl text-white shadow-xl">
                                {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white">{{ auth()->user()->name }}</h3>
                                <p class="text-xs text-slate-300">{{ auth()->user()->email }}</p>
                                <div class="mt-1 flex items-center gap-2">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                                        ● Đang hoạt động
                                    </span>
                                    @if(auth()->user()->isAdmin())
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/30">
                                            Quản trị viên
                                        </span>
                                    @else
                                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-blue-500/20 text-blue-300 border border-blue-500/30">
                                            Khách hàng / Thành viên
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabs bên trong Modal Hồ sơ -->
                    <div class="flex border-b border-slate-200 px-6 pt-3 text-xs font-bold">
                        <button 
                            type="button" 
                            @click="userProfileTab = 'info'" 
                            :class="userProfileTab === 'info' ? 'border-b-2 border-blue-600 text-blue-600 pb-2.5' : 'text-slate-500 hover:text-slate-800 pb-2.5'"
                            class="mr-4 transition"
                        >
                            Thông tin chi tiết
                        </button>
                        <button 
                            type="button" 
                            @click="userProfileTab = 'password'" 
                            :class="userProfileTab === 'password' ? 'border-b-2 border-blue-600 text-blue-600 pb-2.5' : 'text-slate-500 hover:text-slate-800 pb-2.5'"
                            class="transition"
                        >
                            Đổi mật khẩu
                        </button>
                    </div>

                    <!-- TAB 1: THÔNG TIN CHI TIẾT -->
                    <div x-show="userProfileTab === 'info'" class="p-6 space-y-4 text-xs">
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="text-slate-400 block mb-0.5">ID Tài khoản</span>
                                <span class="font-bold text-slate-800">#{{ auth()->user()->id }}</span>
                            </div>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="text-slate-400 block mb-0.5">Vai trò hệ thống</span>
                                <span class="font-bold text-slate-800">{{ auth()->user()->role === 'admin' ? 'Administrator' : 'User / Client' }}</span>
                            </div>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="text-slate-400 block mb-0.5">Trạng thái</span>
                                <span class="font-bold text-emerald-600">Đã kích hoạt (Active)</span>
                            </div>
                            <div class="p-3 bg-slate-50 rounded-xl border border-slate-100">
                                <span class="text-slate-400 block mb-0.5">Ngày tham gia</span>
                                <span class="font-bold text-slate-800">{{ auth()->user()->created_at ? auth()->user()->created_at->format('d/m/Y') : '14/11/2026' }}</span>
                            </div>
                        </div>

                        <!-- Form cập nhật tên và email -->
                        <form @submit.prevent="updateUserProfile()" class="pt-3 border-t border-slate-100 space-y-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Tên hiển thị</label>
                                <input 
                                    type="text" 
                                    x-model="profileForm.name" 
                                    required 
                                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Địa chỉ Email</label>
                                <input 
                                    type="email" 
                                    x-model="profileForm.email" 
                                    required 
                                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                            </div>
                            <div class="flex justify-end pt-2">
                                <button 
                                    type="submit" 
                                    :disabled="profileForm.submitting"
                                    class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow transition flex items-center gap-1.5"
                                >
                                    <i class="fa-solid fa-circle-notch fa-spin" x-show="profileForm.submitting" x-cloak></i>
                                    <span x-text="profileForm.submitting ? 'Đang lưu...' : 'Lưu thay đổi'"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 2: ĐỔI MẬT KHẨU -->
                    <div x-show="userProfileTab === 'password'" class="p-6 space-y-3 text-xs">
                        <form @submit.prevent="changeUserPassword()" class="space-y-3">
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Mật khẩu hiện tại</label>
                                <input 
                                    type="password" 
                                    x-model="passwordForm.currentPassword" 
                                    required 
                                    placeholder="••••••••" 
                                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Mật khẩu mới</label>
                                <input 
                                    type="password" 
                                    x-model="passwordForm.newPassword" 
                                    required 
                                    placeholder="Tối thiểu 6 ký tự" 
                                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Xác nhận mật khẩu mới</label>
                                <input 
                                    type="password" 
                                    x-model="passwordForm.confirmPassword" 
                                    required 
                                    placeholder="Nhập lại mật khẩu mới" 
                                    class="w-full px-3.5 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
                                >
                            </div>
                            <div class="flex justify-end pt-2">
                                <button 
                                    type="submit" 
                                    :disabled="passwordForm.submitting"
                                    class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow transition flex items-center gap-1.5"
                                >
                                    <i class="fa-solid fa-circle-notch fa-spin" x-show="passwordForm.submitting" x-cloak></i>
                                    <span x-text="passwordForm.submitting ? 'Đang đổi...' : 'Cập nhật mật khẩu'"></span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Footer Modal -->
                    <div class="p-4 bg-slate-50 border-t border-slate-100 flex items-center justify-between text-xs">
                        <span class="text-slate-400">IDC Real Estate Customer Portal</span>
                        <button 
                            type="button" 
                            @click="userInfoModalOpen = false" 
                            class="px-3.5 py-1.5 rounded-xl bg-slate-200 hover:bg-slate-300 text-slate-700 font-semibold transition"
                        >
                            Đóng
                        </button>
                    </div>
                @endauth
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 2: ĐĂNG NHẬP NHANH (QUICK LOGIN MODAL)                    -->
    <!-- Mở khi khách nhấn nút "Đăng nhập" ở góc dưới bên trái          -->
    <!-- ============================================================== -->
    <div 
        x-show="loginModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-login" 
        role="dialog" 
        aria-modal="true"
    >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <!-- Backdrop -->
            <div 
                x-show="loginModalOpen" 
                @click="loginModalOpen = false" 
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"
            ></div>

            <!-- Panel -->
            <div class="inline-block w-full max-w-md my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-slate-100 z-10 p-6 sm:p-8">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-9 h-9 rounded-xl bg-gradient-to-tr from-blue-600 to-indigo-600 text-white flex items-center justify-center shadow-md">
                            <i class="fa-solid fa-lock text-sm"></i>
                        </div>
                        <div>
                            <h3 class="text-base font-extrabold text-slate-900">Đăng Nhập Hệ Thống</h3>
                            <p class="text-xs text-slate-400">Cổng thông tin IDC Portal</p>
                        </div>
                    </div>
                    <button @click="loginModalOpen = false" class="text-slate-400 hover:text-slate-600">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <!-- Form đăng nhập nhanh -->
                <form @submit.prevent="submitQuickLogin()" class="space-y-4 text-xs">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Địa chỉ Email</label>
                        <input 
                            type="email" 
                            x-model="loginForm.email" 
                            required 
                            placeholder="admin@idc.vn hoặc email cá nhân" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        >
                    </div>

                    <div>
                        <label class="block font-bold text-slate-700 mb-1">Mật khẩu</label>
                        <input 
                            type="password" 
                            x-model="loginForm.password" 
                            required 
                            placeholder="••••••••" 
                            class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none"
                        >
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" x-model="loginForm.remember" class="rounded text-blue-600">
                            <span class="text-slate-600 font-medium">Ghi nhớ đăng nhập</span>
                        </label>
                        <a href="{{ route('login') }}" class="text-blue-600 hover:underline">Quên mật khẩu?</a>
                    </div>

                    <button 
                        type="submit" 
                        :disabled="loginForm.submitting"
                        class="w-full py-2.5 px-4 rounded-xl bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-bold text-xs shadow-lg shadow-blue-500/25 transition flex items-center justify-center gap-2"
                    >
                        <i class="fa-solid fa-circle-notch fa-spin" x-show="loginForm.submitting" x-cloak></i>
                        <span x-text="loginForm.submitting ? 'Đang xác thực...' : 'Đăng nhập ngay'"></span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 3: CHI TIẾT CĂN HỘ (UNIT DETAIL MODAL - RỘNG RÃI & TINH GỌN) -->
    <!-- Hiển thị ảnh chỉ căn cỡ lớn, 4 thông số cốt lõi & đăng ký tư vấn -->
    <!-- ============================================================== -->
    <div 
        x-show="unitDetailModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-unit" 
        role="dialog" 
        aria-modal="true"
    >
        <div class="flex items-center justify-center min-h-screen px-3 sm:px-6 py-4 text-center">
            <!-- Backdrop -->
            <div 
                x-show="unitDetailModalOpen" 
                @click="unitDetailModalOpen = false" 
                class="fixed inset-0 bg-slate-950/80 backdrop-blur-md transition-opacity"
            ></div>

            <!-- Panel to rộng, thoải mái: max-w-5xl xl:max-w-6xl max-h-[90vh] -->
            <div 
                x-show="unitDetailModalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 scale-95"
                x-transition:enter-end="opacity-100 scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 scale-100"
                x-transition:leave-end="opacity-0 scale-95"
                class="inline-block w-full max-w-5xl xl:max-w-6xl my-auto text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-slate-100 z-10 max-h-[90vh] flex flex-col overflow-hidden"
            >
                <template x-if="selectedUnit">
                    <div class="flex flex-col h-full min-h-0">
                        <!-- 1. Header modal tinh gọn 1 hàng -->
                        <div class="px-5 sm:px-6 py-3.5 border-b border-slate-100 flex items-center justify-between shrink-0 bg-white">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-xl bg-blue-600 text-white flex items-center justify-center text-sm shadow shrink-0">
                                    <i class="fa-solid fa-building"></i>
                                </div>
                                <div class="flex items-center gap-2.5 truncate">
                                    <h3 class="text-base sm:text-lg font-extrabold text-slate-900 truncate" x-text="'Căn hộ ' + selectedUnit.unit_code"></h3>
                                    
                                    <span 
                                        class="px-2.5 py-0.5 rounded-full text-[11px] font-bold border shrink-0"
                                        :class="{
                                            'bg-emerald-50 text-emerald-700 border-emerald-200': selectedUnit.status === 'available',
                                            'bg-amber-50 text-amber-700 border-amber-200': selectedUnit.status === 'reserved' || selectedUnit.status === 'deposited',
                                            'bg-rose-50 text-rose-700 border-rose-200': selectedUnit.status === 'sold'
                                        }"
                                        x-text="selectedUnit.status === 'available' ? '🟢 Còn trống' : (selectedUnit.status === 'sold' ? '🔴 Đã bán' : '🟡 Đang giữ chỗ')"
                                    ></span>

                                    <span class="hidden sm:inline-block px-2 py-0.5 rounded-lg text-xs font-semibold bg-slate-100 text-slate-600 shrink-0" x-text="'Tòa ' + (selectedUnit.block || 'E1') + ' • Tầng ' + (selectedUnit.floor || '-')"></span>
                                </div>
                            </div>
                            
                            <!-- Nút đóng -->
                            <button @click="unitDetailModalOpen = false" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition shrink-0" title="Đóng popup">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>

                        <!-- 2. Thân modal: Cột trái ảnh lớn, Cột phải thông tin đã tinh gọn -->
                        <div class="flex-1 min-h-0 overflow-y-auto p-5 sm:p-6 grid grid-cols-1 lg:grid-cols-12 gap-5 sm:gap-6">
                            
                            <!-- CỘT TRÁI (LỚN): ẢNH POSTER & MẶT BẰNG CHỈ CĂN RỘNG RÃI -->
                            <div class="lg:col-span-7 xl:col-span-8 flex flex-col h-full min-h-[420px] max-h-[66vh]">
                                <div class="flex-1 min-h-0 bg-slate-950 rounded-2xl p-2.5 flex items-center justify-center relative overflow-hidden group border border-slate-800 shadow-inner">
                                    <template x-if="getSelectedPosterLayout()">
                                        <div class="relative h-full max-w-full overflow-hidden rounded shadow-lg" :style="'aspect-ratio:' + getSelectedPosterLayout().width + '/' + getSelectedPosterLayout().height + ';container-type:inline-size;background:linear-gradient(145deg,#3f1703,#7c2d05,#270e02)'">
                                            <img x-show="getSelectedPosterLayout().background" :src="getSelectedPosterLayout().background" class="absolute inset-0 w-full h-full object-fill pointer-events-none" :alt="selectedUnit.unit_code + ' poster background'">
                                            <template x-for="image in getSelectedPosterLayout().images" :key="image.id">
                                                <img x-show="image.source" :src="image.source" :style="posterImageStyle(image)" class="absolute select-none pointer-events-none" :alt="image.id">
                                            </template>
                                            <template x-for="text in getSelectedPosterLayout().texts" :key="text.key">
                                                <div :style="posterTextStyle(text)" class="absolute leading-none whitespace-nowrap" :class="text.badge ? 'inline-flex items-center justify-center px-[0.45cqw] py-[0.25cqw]' : ''" x-text="text.value"></div>
                                            </template>
                                        </div>
                                    </template>
                                    <template x-if="!getSelectedPosterLayout() && getSelectedPosterSource()">
                                        <img :src="getSelectedPosterSource()" :alt="selectedUnit.unit_code" class="w-auto h-auto max-w-full max-h-full object-contain rounded select-none cursor-zoom-in" @click="openLightbox(getSelectedPosterSource(), 'Sơ đồ chi tiết Căn hộ ' + selectedUnit.unit_code)" loading="lazy">
                                    </template>
                                    <template x-if="!getSelectedPosterLayout() && !getSelectedPosterSource()">
                                        <div class="text-center text-slate-400 text-xs p-8">
                                            <i class="fa-regular fa-image text-4xl mb-2 block"></i>
                                            <span>Chưa có ảnh phôi chỉ căn cho mã căn này</span>
                                        </div>
                                    </template>

                                    <!-- Nút thao tác nhanh trên ảnh -->
                                    <template x-if="getSelectedPosterSource()">
                                        <div class="absolute bottom-3 right-3 flex items-center gap-2">
                                            <button 
                                                type="button" 
                                                @click="openLightbox(getSelectedPosterSource(), 'Sơ đồ chi tiết Căn hộ ' + selectedUnit.unit_code)" 
                                                class="px-3 py-1.5 rounded-xl bg-black/75 hover:bg-black text-white text-xs font-semibold backdrop-blur-sm shadow transition flex items-center gap-1.5"
                                                title="Phóng to toàn màn hình"
                                            >
                                                <i class="fa-solid fa-expand text-[11px]"></i>
                                                <span>Phóng to</span>
                                            </button>
                                            <button 
                                                type="button" @click="downloadSelectedPoster()"
                                                class="px-3 py-1.5 rounded-xl bg-white/95 hover:bg-white text-slate-900 text-xs font-bold shadow transition backdrop-blur-sm flex items-center gap-1.5"
                                                title="Tải ảnh Poster chất lượng cao"
                                            >
                                                <i class="fa-solid fa-download text-[11px] text-blue-600"></i>
                                                <span>Tải ảnh</span>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- CỘT PHẢI: THÔNG TIN TỐI ƯU (GIẢM LƯỢC TRÙNG LẶP) -->
                            <div class="lg:col-span-5 xl:col-span-4 flex flex-col justify-between space-y-4">
                                <div class="space-y-3.5">
                                    
                                    <!-- Khung Giá bán niêm yết nổi bật -->
                                    <div class="p-4 rounded-2xl bg-gradient-to-br from-blue-50/90 via-indigo-50/50 to-slate-50 border border-blue-100">
                                        <div class="flex items-center justify-between text-xs text-slate-500 font-semibold mb-1">
                                            <span>Giá bán niêm yết</span>
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-blue-100 text-blue-700">Giá trực tiếp CĐT</span>
                                        </div>
                                        <div class="flex items-baseline gap-2">
                                            <span class="text-2xl sm:text-3xl font-extrabold text-blue-600 tracking-tight" x-text="selectedUnit.price_text || selectedUnit.display_price || 'Liên hệ'"></span>
                                            <span class="text-xs text-slate-400 font-medium" x-text="selectedUnit.price_note || '(Chưa VAT)'"></span>
                                        </div>
                                    </div>

                                    <!-- Bộ 4 thông số cốt lõi (Grid 2x2 trực quan, loại bỏ bảng lặp lại) -->
                                    <div class="grid grid-cols-2 gap-2.5">
                                        <!-- 1. Diện tích -->
                                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                            <div class="flex items-center gap-1.5 text-slate-400 text-xs mb-1">
                                                <i class="fa-solid fa-ruler-combined text-blue-500 text-xs"></i>
                                                <span>Diện tích</span>
                                            </div>
                                            <p class="text-sm font-bold text-slate-800" x-text="(selectedUnit.area || '-') + ' m²'"></p>
                                            <span class="text-[10px] text-slate-400">Thông thủy</span>
                                        </div>

                                        <!-- 2. Loại căn & Phòng ngủ -->
                                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                            <div class="flex items-center gap-1.5 text-slate-400 text-xs mb-1">
                                                <i class="fa-solid fa-bed text-indigo-500 text-xs"></i>
                                                <span>Loại căn</span>
                                            </div>
                                            <p class="text-sm font-bold text-slate-800" x-text="selectedUnit.unit_type || '2 Phòng ngủ'"></p>
                                            <span class="text-[10px] text-slate-400" x-text="(selectedUnit.bedroom ? selectedUnit.bedroom + ' PN' : '') + (selectedUnit.bathroom ? ' • ' + selectedUnit.bathroom + ' WC' : '')"></span>
                                        </div>

                                        <!-- 3. Vị trí Tòa & Tầng -->
                                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                            <div class="flex items-center gap-1.5 text-slate-400 text-xs mb-1">
                                                <i class="fa-solid fa-layer-group text-emerald-500 text-xs"></i>
                                                <span>Vị trí</span>
                                            </div>
                                            <p class="text-sm font-bold text-slate-800" x-text="'Tòa ' + (selectedUnit.block || 'E1')"></p>
                                            <span class="text-[10px] text-slate-400" x-text="'Tầng ' + (selectedUnit.floor || '-')"></span>
                                        </div>

                                        <!-- 4. Hướng ban công -->
                                        <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                                            <div class="flex items-center gap-1.5 text-slate-400 text-xs mb-1">
                                                <i class="fa-solid fa-compass text-amber-500 text-xs"></i>
                                                <span>Hướng ban công</span>
                                            </div>
                                            <p class="text-sm font-bold text-slate-800 truncate" x-text="selectedUnit.direction || 'Đông Nam'"></p>
                                            <span class="text-[10px] text-slate-400" x-text="selectedUnit.view ? 'View: ' + selectedUnit.view : 'Thoáng mát'"></span>
                                        </div>
                                    </div>

                                    <!-- Dự toán phương án tài chính nhanh -->
                                    <div class="p-3 rounded-xl bg-slate-50/80 border border-slate-200/80 text-xs space-y-1.5">
                                        <div class="flex items-center justify-between text-slate-500 text-[11px] font-semibold">
                                            <span>Dự toán tài chính gợi ý</span>
                                            <span class="text-blue-600 font-bold">Hỗ trợ LS 0%</span>
                                        </div>
                                        <div class="grid grid-cols-2 gap-2 pt-1.5 border-t border-slate-200/60">
                                            <div>
                                                <span class="text-[10px] text-slate-400 block">Vốn tự có (30%):</span>
                                                <span class="font-extrabold text-slate-800 text-xs" x-text="formatShortPrice(getUnitNumericPrice(selectedUnit) * 0.3)"></span>
                                            </div>
                                            <div>
                                                <span class="text-[10px] text-slate-400 block">Vay ngân hàng (70%):</span>
                                                <span class="font-extrabold text-blue-600 text-xs" x-text="formatShortPrice(getUnitNumericPrice(selectedUnit) * 0.7)"></span>
                                            </div>
                                        </div>
                                    </div>

                                </div>

                                <!-- Các nút hành động chính -->
                                <div class="space-y-2 pt-2 border-t border-slate-100">
                                    <!-- Nút 1: Đăng ký giữ chỗ & Tư vấn -->
                                    <button 
                                        type="button" 
                                        @click="unitDetailModalOpen = false; openConsultationModal(currentProject.name, selectedUnit.unit_code)" 
                                        class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-sm shadow-md shadow-blue-500/25 transition flex items-center justify-center gap-2"
                                    >
                                        <i class="fa-solid fa-paper-plane text-xs"></i>
                                        <span>Đăng ký giữ chỗ & Nhận tư vấn</span>
                                    </button>

                                    <!-- Nút 2: Tính bảng vay mua nhà -->
                                    <button 
                                        type="button" 
                                        @click="calculateLoanForUnit(selectedUnit)" 
                                        class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition flex items-center justify-center gap-2"
                                    >
                                        <i class="fa-solid fa-calculator text-cyan-600 text-xs"></i>
                                        <span>Tính lịch trả góp mua căn này</span>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 4: XEM BÀI VIẾT TIN TỨC (ARTICLE MODAL)                   -->
    <!-- ============================================================== -->
    <div 
        x-show="articleModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-article" 
        role="dialog" 
        aria-modal="true"
    >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:p-0">
            <div x-show="articleModalOpen" @click="articleModalOpen = false" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm transition-opacity"></div>
            <div class="inline-block w-full max-w-2xl my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-2xl rounded-3xl border border-slate-100 z-10 p-6 sm:p-8">
                <template x-if="selectedArticle">
                    <div>
                        <div class="flex items-center justify-between mb-3">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-blue-100 text-blue-700" x-text="selectedArticle.category"></span>
                            <button @click="articleModalOpen = false" class="text-slate-400 hover:text-slate-600">
                                <i class="fa-solid fa-xmark text-lg"></i>
                            </button>
                        </div>
                        <h3 class="text-lg sm:text-xl font-extrabold text-slate-900 leading-snug" x-text="selectedArticle.title"></h3>
                        <p class="text-xs text-slate-400 mt-1 mb-4" x-text="'Đăng ngày ' + selectedArticle.date + ' • ' + selectedArticle.read_time"></p>
                        
                        <div class="h-64 rounded-2xl overflow-hidden mb-4 bg-slate-800">
                            <img :src="selectedArticle.image" :alt="selectedArticle.title" class="w-full h-full object-cover">
                        </div>

                        <div class="text-xs text-slate-600 leading-relaxed space-y-3">
                            <p class="font-semibold text-slate-800 text-sm" x-text="selectedArticle.summary"></p>
                            <p>
                                Thị trường bất động sản thời điểm hiện tại đang mở ra nhiều cơ hội hấp dẫn cho cả người mua ở thực và nhà đầu tư dài hạn. Để tối ưu hóa nguồn vốn, khách hàng nên tìm hiểu kỹ mặt bằng chỉ căn, so sánh đơn giá thông thủy và tận dụng các gói vay ưu đãi từ ngân hàng đối tác.
                            </p>
                            <p>
                                Đội ngũ chuyên gia IDC Real Estate luôn sẵn sàng đồng hành, thẩm định pháp lý và cung cấp giải pháp an cư hoàn hảo nhất cho quý khách hàng.
                            </p>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end">
                            <button 
                                type="button" 
                                @click="articleModalOpen = false; openConsultationModal()" 
                                class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition"
                            >
                                Tư vấn thêm về thông tin này
                            </button>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- MODAL 5: PHÓNG TO ẢNH MẶT BẰNG / BẢN VẼ (LIGHTBOX FULLSCREEN)  -->
    <!-- ============================================================== -->
    <div 
        x-show="lightboxOpen" 
        x-cloak 
        @keydown.escape.window="lightboxOpen = false"
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/90 backdrop-blur-md p-4 select-none"
    >
        <button 
            type="button" 
            @click="lightboxOpen = false" 
            class="absolute top-4 right-4 z-50 text-white/80 hover:text-white bg-black/50 hover:bg-black/80 rounded-full w-10 h-10 flex items-center justify-center transition"
            title="Đóng (Esc)"
        >
            <i class="fa-solid fa-xmark text-lg"></i>
        </button>

        <div class="max-w-6xl max-h-full flex flex-col items-center">
            <div class="text-white text-xs sm:text-sm font-bold mb-2 truncate max-w-xl text-center" x-text="lightboxTitle"></div>
            <div class="relative bg-slate-950 rounded-xl overflow-hidden shadow-2xl p-2 border border-slate-800 flex items-center justify-center">
                <img 
                    :src="lightboxImage" 
                    :alt="lightboxTitle" 
                    class="max-w-full max-h-[85vh] object-contain rounded"
                >
            </div>
        </div>
    </div>

    <!-- Hidden logout form to handle standard POST submit if needed -->
    <form id="logoutFormStandard" action="{{ route('logout') }}" method="POST" class="hidden">
        @csrf
    </form>

    <!-- ============================================================== -->
    <!-- ALPINE.JS APPLICATION SCRIPT                                    -->
    <!-- Quản lý trạng thái menu, lọc chỉ căn, tính lãi suất, modal     -->
    <!-- ============================================================== -->
    <script>
        function frontendApp() {
            return {
                // Trạng thái menu hiện tại
                activeMenu: '{{ $activeMenu ?? "projects" }}',
                mobileMenuOpen: false,
                userInfoModalOpen: false,
                loginModalOpen: false,
                unitDetailModalOpen: false,
                articleModalOpen: false,
                lightboxOpen: false,
                lightboxImage: '',
                lightboxTitle: '',
                userProfileTab: 'info',
                locatorSubTab: 'units',

                // Tiêu đề & mô tả cho từng menu
                menuTitles: {
                    projects: {
                        title: 'Khám Phá Dự Án Bất Động Sản',
                        desc: 'Danh mục các dự án chung cư, biệt thự và khu đô thị cao cấp'
                    },
                    locator: {
                        title: 'Sơ Đồ & Chỉ Căn Bất Động Sản',
                        desc: 'Tương tác trực quan 2D/3D, tra cứu chi tiết từng căn hộ'
                    },
                    inventory: {
                        title: 'Bảng Hàng & Tra Cứu Trực Tuyến',
                        desc: 'Bảng tổng hợp tình trạng giao dịch, diện tích và giá niêm yết'
                    },
                    calculator: {
                        title: 'Bảng Tính Lãi Suất Vay Mua Nhà',
                        desc: 'Dự toán dòng tiền trả góp, phương án vốn tự có và hỗ trợ 0%'
                    },
                    news: {
                        title: 'Tin Tức Thị Trường & Cẩm Nang BĐS',
                        desc: 'Phân tích quy hoạch, xu hướng đầu tư và kinh nghiệm an cư'
                    },
                    contact: {
                        title: 'Liên Hệ & Đăng Ký Tư Vấn',
                        desc: 'Tổng đài hỗ trợ 24/7 và gửi yêu cầu tư vấn trực tiếp'
                    }
                },

                // Dữ liệu dự án đang chọn
                currentProject: {
                    id: {{ $activeProject->id ?? 826 }},
                    name: '{{ addslashes($activeProject->name ?? "IMPERIA SKY PARK") }}',
                    address: '{{ addslashes($activeProject->address ?? "Nam An Khánh, Hoài Đức, Hà Nội") }}'
                },

                // Danh sách căn hộ của dự án đang chọn
                unitsList: {!! json_encode($units) !!},
                templatesList: {!! json_encode($templates) !!},
                selectedPosterUrl: '',
                posterRenderSequence: 0,

                // Danh sách mặt bằng tổng thể của dự án
                floorplansList: {!! json_encode($floorplans) !!},
                selectedFloorplan: {!! json_encode($floorplans->first() ?? null) !!},

                // Thống kê căn hộ
                stats: {
                    total: {{ $unitStats['total'] ?? 0 }},
                    available: {{ $unitStats['available'] ?? 0 }},
                    reserved: {{ $unitStats['reserved'] ?? 0 }},
                    sold: {{ $unitStats['sold'] ?? 0 }}
                },

                // Bộ lọc chỉ căn
                availableBlocks: {!! json_encode($blocks) !!},
                availableFloors: {!! json_encode($floors) !!},
                availableTypes: {!! json_encode($unitTypes) !!},
                availableAxes: {!! json_encode($axes ?? []) !!},
                unitFilter: {
                    search: '',
                    block: '',
                    floor: '',
                    axis: '',
                    type: '',
                    status: ''
                },

                // Bộ lọc danh mục dự án
                projectSearch: '',
                projectFilterType: 'all',

                // Căn hộ & Bài viết đang mở modal / chi tiết
                selectedUnit: {!! json_encode($units->first() ?? null) !!},
                selectedArticle: null,

                // Bảng tính vay
                calc: {
                    price: 5704000000,
                    ratio: 70,
                    years: 20,
                    interestRate: 7.5,
                    downPayment: 0,
                    loanAmount: 0,
                    monthlyPrincipal: 0,
                    firstMonthInterest: 0,
                    firstMonthTotal: 0
                },

                // Form tư vấn
                consultForm: {
                    name: '',
                    phone: '',
                    email: '',
                    projectName: '',
                    unitCode: '',
                    note: '',
                    submitting: false
                },

                // Form đăng nhập nhanh
                loginForm: {
                    email: '',
                    password: '',
                    remember: true,
                    submitting: false
                },

                // Form hồ sơ
                profileForm: {
                    name: '{{ auth()->user()->name ?? "" }}',
                    email: '{{ auth()->user()->email ?? "" }}',
                    submitting: false
                },

                // Form mật khẩu
                passwordForm: {
                    currentPassword: '',
                    newPassword: '',
                    confirmPassword: '',
                    submitting: false
                },

                // Khởi tạo
                initApp() {
                    // Kiểm tra URL hash hoặc param
                    const urlParams = new URLSearchParams(window.location.search);
                    const requestedMenu = urlParams.get('menu');
                    if (requestedMenu && this.menuTitles[requestedMenu]) {
                        this.activeMenu = requestedMenu;
                    }

                    // Nếu menu đang chọn thuộc các mục tạm ẩn, chuyển về projects
                    if (this.activeMenu === 'inventory' || this.activeMenu === 'news') {
                        this.activeMenu = 'projects';
                    }

                    // Khởi tạo mặt bằng đang chọn mặc định
                    if (this.floorplansList && this.floorplansList.length > 0 && !this.selectedFloorplan) {
                        this.selectedFloorplan = this.floorplansList[0];
                    }

                    // Khởi tạo căn hộ đang chọn mặc định
                    if (this.unitsList && this.unitsList.length > 0 && !this.selectedUnit) {
                        this.selectedUnit = this.unitsList[0];
                    }

                    // Tính bảng vay mặc định
                    this.recalculateLoan();

                    // Pre-fill form tư vấn nếu đã đăng nhập
                    @auth
                        this.consultForm.name = '{{ addslashes(auth()->user()->name) }}';
                        this.consultForm.email = '{{ addslashes(auth()->user()->email) }}';
                    @endauth
                },

                // Chuyển đổi Menu bên trái
                switchMenu(menuKey) {
                    if (this.menuTitles[menuKey]) {
                        this.activeMenu = menuKey;
                        this.mobileMenuOpen = false;

                        if (menuKey === 'locator') {
                            this.locatorSubTab = 'units';
                            if (this.unitsList && this.unitsList.length > 0 && !this.selectedUnit) {
                                this.selectedUnit = this.unitsList[0];
                            }
                            if (this.floorplansList && this.floorplansList.length > 0 && !this.selectedFloorplan) {
                                this.selectedFloorplan = this.floorplansList[0];
                            }
                        }

                        // Cập nhật URL sạch không làm reload trang
                        const url = new URL(window.location);
                        url.searchParams.set('menu', menuKey);
                        window.history.replaceState({}, '', url);

                        // Cuộn lên đầu trang
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },

                // Chọn dự án và nhảy ngay tới mục Sơ đồ chỉ căn / mặt bằng
                async selectProjectAndSwitchLocator(projectId, projectName) {
                    console.log('DEBUG: Selecting Project ID:', projectId, 'Name:', projectName);
                    this.currentProject.id = projectId;
                    this.currentProject.name = projectName;
                    
                    // Gọi API lấy dữ liệu dự án mới
                    try {
                        const response = await fetch('/idc/public/index.php/api/projects/' + projectId + '/units');
                        
                        if (!response.ok) throw new Error('Network response was not ok');
                        
                        const data = await response.json();
                        
                        console.log('DEBUG: API Data received:', data);
                        if (data.success) {
                            this.unitsList = data.units;
                            this.floorplansList = data.floorplans;
                            this.templatesList = data.templates || [];
                            this.stats = data.stats;
                            this.availableBlocks = data.blocks;
                            this.availableFloors = data.floors;
                            this.availableTypes = data.unitTypes;
                            this.availableAxes = data.axes;
                            // Cập nhật thông tin dự án đang chọn
                            if(data.project) {
                                this.currentProject.name = data.project.name;
                            }
                        }
                    } catch (error) {
                        console.error('Error fetching project units:', error);
                    }

                    this.switchMenu('locator');
                    this.locatorSubTab = 'units';
                    this.selectedUnit = (this.unitsList && this.unitsList.length > 0) ? this.unitsList[0] : null;
                    this.selectedFloorplan = (this.floorplansList && this.floorplansList.length > 0) ? this.floorplansList[0] : null;
                    this.resetUnitFilters();
                },

                // Lọc dự án (chỉ hiển thị dự án có chỉ căn)
                filterProject(name, address, hasLocators) {
                    if (!hasLocators) {
                        return false;
                    }
                    if (!this.projectSearch) return true;
                    const q = this.projectSearch.toLowerCase().trim();
                    return name.toLowerCase().includes(q) || address.toLowerCase().includes(q);
                },

                // Danh sách căn sau khi lọc (Computed)
                get filteredUnits() {
                    return this.unitsList.filter(u => {
                        // Lọc từ khóa mã căn hoặc trục căn
                        if (this.unitFilter.search) {
                            const q = this.unitFilter.search.toLowerCase().trim();
                            const code = (u.unit_code || '').toLowerCase();
                            const axis = (u.unit_axis || '').toLowerCase();
                            const axisWithPrefix = 'trục ' + axis;
                            const axisWithPrefixNoAccent = 'truc ' + axis;
                            if (!code.includes(q) && !axis.includes(q) && !axisWithPrefix.includes(q) && !axisWithPrefixNoAccent.includes(q)) {
                                return false;
                            }
                        }
                        // Lọc Tòa / Block
                        if (this.unitFilter.block && u.block !== this.unitFilter.block) {
                            return false;
                        }
                        // Lọc Tầng
                        if (this.unitFilter.floor && String(u.floor) !== String(this.unitFilter.floor)) {
                            return false;
                        }
                        // Lọc Trục căn
                        if (this.unitFilter.axis && String(u.unit_axis) !== String(this.unitFilter.axis)) {
                            return false;
                        }
                        // Lọc Loại căn
                        if (this.unitFilter.type && u.unit_type !== this.unitFilter.type) {
                            return false;
                        }
                        // Lọc Trạng thái
                        if (this.unitFilter.status) {
                            if (this.unitFilter.status === 'reserved') {
                                if (u.status !== 'reserved' && u.status !== 'deposited') return false;
                            } else if (u.status !== this.unitFilter.status) {
                                return false;
                            }
                        }
                        return true;
                    });
                },

                resetUnitFilters() {
                    this.unitFilter.search = '';
                    this.unitFilter.block = '';
                    this.unitFilter.floor = '';
                    this.unitFilter.axis = '';
                    this.unitFilter.type = '';
                    this.unitFilter.status = '';
                },

                // Mở Modal Chi tiết Căn hộ
                openUnitDetail(unit) {
                    this.selectedUnit = unit;
                    this.unitDetailModalOpen = true;
                },

                getSelectedPosterSource() {
                    return this.selectedUnit?.image_url || '';
                },

                getSelectedPosterLayout() {
                    const unit = this.selectedUnit;
                    if (!unit) return null;
                    let recipe = unit.composer_data;
                    if (typeof recipe === 'string') { try { recipe = JSON.parse(recipe); } catch (_) { recipe = null; } }
                    if (!recipe || !recipe.template_id) return null;
                    let template = (this.templatesList || []).find(item => String(item.id) === String(recipe.template_id));
                    if (!template && String(recipe.template_id) === 'default') {
                        template = { id:'default', image_url:'{{ asset('images/default-poster-phoi.jpg') }}', width:1000, height:1400, slots_config:{
                            canvas:{width:1000,height:1400}, image_slots:[
                                {id:'slot_1',key:'boc_mai',name:'Mặt bằng bóc mái 3D',x:650,y:160,width:280,height:340,radius:12},
                                {id:'slot_2',key:'mat_bang_tang',name:'Mặt bằng tầng chỉ căn',x:630,y:525,width:330,height:240,radius:12},
                                {id:'slot_3',key:'phoi_canh_toa',name:'Phối cảnh tổng thể',x:60,y:380,width:540,height:210,radius:12},
                                {id:'slot_4',key:'tien_ich_1',name:'Phối cảnh tiện ích',x:60,y:610,width:260,height:230,radius:12},
                                {id:'slot_5',key:'phan_khu',name:'Mặt bằng phân khu',x:340,y:610,width:260,height:230,radius:12},
                                {id:'slot_6',key:'phoi_canh_dem',name:'Phối cảnh ban đêm',x:200,y:860,width:760,height:240,radius:12}
                            ], text_slots:{
                                unit_code:{x:60,y:45,fontSize:26,color:'#fff'},unit_type:{x:395,y:45,fontSize:24,color:'#78350f'},
                                direction:{x:60,y:120,fontSize:20,color:'#fff'},view:{x:60,y:155,fontSize:20,color:'#fff'},
                                area:{x:60,y:190,fontSize:20,color:'#fff'},price:{x:280,y:285,fontSize:44,color:'#fff'}
                            }
                        }};
                    }
                    if (!template) return null;
                    let cfg = template.slots_config || {};
                    if (typeof cfg === 'string') { try { cfg = JSON.parse(cfg); } catch (_) { cfg = {}; } }
                    const width = Number(cfg.canvas?.width || template.width || 1000);
                    const height = Number(cfg.canvas?.height || template.height || 1400);
                    const slotImages = recipe.slotImages || {};
                    const identity = slot => [slot.id,slot.name,slot.key,slot.type].join(' ');
                    const images = (cfg.image_slots || []).map(slot => {
                        const slotIdentity = identity(slot);
                        const isRoof = /boc.?mai|roof.?plan|layout.?3d/i.test(slotIdentity);
                        const isFloorplan = /floor.?plan|mat.?bang.?tang/i.test(slotIdentity);
                        const source = (isRoof && (unit.layout_3d_image_url || unit.layout_3d_image))
                            || (isFloorplan && (unit.floorplan_image_url || unit.floorplan_image))
                            || slotImages[slot.id] || '';
                        return { id:slot.id, source:this.normalizePosterAssetUrl(source), x:Number(slot.x||0)/width*100, y:Number(slot.y||0)/height*100,
                            width:Number(slot.width||0)/width*100, height:Number(slot.height||0)/height*100, radius:Number(slot.radius||0)/width*100,
                            fit:/boc.?mai|floor.?plan|mat.?bang|layout.?3d/i.test(slotIdentity) ? 'contain' : 'cover' };
                    });
                    const form = { ...(recipe.posterForm || {}), unit_code:unit.unit_code || recipe.posterForm?.unit_code, unit_type:unit.unit_type || recipe.posterForm?.unit_type,
                        direction:unit.direction || recipe.posterForm?.direction, view:unit.view || recipe.posterForm?.view, area:unit.area || recipe.posterForm?.area,
                        price_text:unit.price_text || recipe.posterForm?.price_text, price_note:unit.price_note || recipe.posterForm?.price_note,
                        block:unit.block || recipe.posterForm?.block, floor:unit.floor || recipe.posterForm?.floor };
                    const valueMap={unit_code:form.unit_code,unit_type:form.unit_type,direction:form.direction,view:form.view,area:form.area,price:form.price_text,price_note:form.price_note,block:form.block,floor:form.floor};
                    const texts=Object.entries(cfg.text_slots || {}).map(([key,pos]) => {
                        if (!pos) return null;
                        const raw=valueMap[key] !== undefined ? valueMap[key] : (pos.sampleText || pos.label || '');
                        const value=`${pos.prefix && pos.prefix !== 'null' ? pos.prefix : ''}${raw || ''}${pos.suffix && pos.suffix !== 'null' ? pos.suffix : ''}`;
                        if (!value) return null;
                        return { key,value,x:Number(pos.x||0)/width*100,y:Number(pos.y||0)/height*100,
                            fontSize:Number(pos.fontSize||22)/width*100, color:pos.color||'#fff', weight:pos.fontWeight||'bold',
                            family:pos.fontFamily==='serif'?'Georgia,serif':'sans-serif', align:pos.align||'left',
                            badge:!!(pos.isBadge && pos.bgColor && pos.bgColor!=='transparent'), bg:pos.bgColor||'transparent',
                            border:pos.borderColor||'transparent', borderWidth:Number(pos.borderWidth||0)/width*100,
                            badgeWidth:pos.width ? Number(pos.width)/width*100 : null, badgeHeight:pos.height ? Number(pos.height)/height*100 : null,
                            radius:Number(pos.radius||8)/width*100 };
                    }).filter(Boolean);
                    return { width,height,ratio:width/height, background:this.normalizePosterAssetUrl(template.image_url || template.image || ''), images,texts };
                },

                posterImageStyle(image) {
                    return `left:${image.x}%;top:${image.y}%;width:${image.width}%;height:${image.height}%;border-radius:${image.radius}cqw;object-fit:${image.fit};`;
                },

                posterTextStyle(text) {
                    const transform = text.align === 'center' ? 'translateX(-50%)' : (text.align === 'right' ? 'translateX(-100%)' : 'none');
                    const badge = text.badge ? `background:${text.bg};border:${text.borderWidth}cqw solid ${text.border};border-radius:${text.radius}cqw;` : '';
                    const dimensions = text.badgeWidth ? `width:${text.badgeWidth}%;` : 'width:max-content;';
                    const badgeHeight = text.badgeHeight ? `height:${text.badgeHeight}%;` : '';
                    return `left:${text.x}%;top:${text.y}%;transform:${transform};font-size:${text.fontSize}cqw;color:${text.color};font-weight:${text.weight};font-family:${text.family};text-align:${text.align};white-space:nowrap;${badge}${dimensions}${badgeHeight}`;
                },

                normalizePosterAssetUrl(url) {
                    if (!url || typeof url !== 'string') return '';
                    if (/^(https?:|data:|blob:)/i.test(url)) {
                        const mediaPath = url.match(/\/unit-locators\/media\/(.+)$/);
                        return mediaPath ? '{{ url('unit-locators/media') }}/' + mediaPath[1] : url;
                    }
                    const path = url.replace(/^\/+/, '').replace(/^storage\//, '');
                    return '{{ url('unit-locators/media') }}/' + path;
                },

                async loadPosterAsset(url) {
                    return new Promise(resolve => {
                        if (!url) return resolve(null);
                        const image = new Image();
                        image.crossOrigin = 'anonymous';
                        image.onload = () => resolve(image);
                        image.onerror = () => resolve(null);
                        image.src = this.normalizePosterAssetUrl(url);
                    });
                },

                async renderSelectedPoster() {
                    const unit = this.selectedUnit;
                    const sequence = ++this.posterRenderSequence;
                    this.selectedPosterUrl = '';
                    if (!unit) return;
                    let recipe = unit.composer_data;
                    if (typeof recipe === 'string') { try { recipe = JSON.parse(recipe); } catch (_) { recipe = null; } }
                    if (!recipe || !recipe.template_id) return;
                    let template = (this.templatesList || []).find(item => String(item.id) === String(recipe.template_id));
                    if (!template && String(recipe.template_id) === 'default') {
                        template = {
                            image_url: '{{ asset('images/default-poster-phoi.jpg') }}', width: 1000, height: 1400,
                            slots_config: {
                                canvas: { width: 1000, height: 1400 },
                                image_slots: [
                                    { id:'slot_1',key:'boc_mai',name:'Mặt bằng bóc mái 3D',x:650,y:160,width:280,height:340,radius:12 },
                                    { id:'slot_2',key:'mat_bang_tang',name:'Mặt bằng tầng chỉ căn',x:630,y:525,width:330,height:240,radius:12 },
                                    { id:'slot_3',key:'phoi_canh_toa',name:'Phối cảnh tổng thể',x:60,y:380,width:540,height:210,radius:12 },
                                    { id:'slot_4',key:'tien_ich_1',name:'Phối cảnh tiện ích',x:60,y:610,width:260,height:230,radius:12 },
                                    { id:'slot_5',key:'phan_khu',name:'Mặt bằng phân khu',x:340,y:610,width:260,height:230,radius:12 },
                                    { id:'slot_6',key:'phoi_canh_dem',name:'Phối cảnh ban đêm',x:200,y:860,width:760,height:240,radius:12 }
                                ],
                                text_slots: {
                                    unit_code:{x:60,y:45,fontSize:26,color:'#fff'}, unit_type:{x:395,y:45,fontSize:24,color:'#78350f'},
                                    direction:{x:60,y:120,fontSize:20,color:'#fff'}, view:{x:60,y:155,fontSize:20,color:'#fff'},
                                    area:{x:60,y:190,fontSize:20,color:'#fff'}, price:{x:280,y:285,fontSize:44,color:'#fff'}
                                }
                            }
                        };
                    }
                    if (!template) return;
                    const cfg = typeof template.slots_config === 'string' ? JSON.parse(template.slots_config || '{}') : (template.slots_config || {});
                    const width = Number(cfg.canvas?.width || template.width || 1000);
                    const height = Number(cfg.canvas?.height || template.height || 1400);
                    const canvas = document.createElement('canvas');
                    canvas.width = width; canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    const background = await this.loadPosterAsset(template.image_url || template.image || '');
                    if (background) ctx.drawImage(background, 0, 0, width, height);
                    else { const gradient = ctx.createLinearGradient(0, 0, width, height); gradient.addColorStop(0, '#3f1703'); gradient.addColorStop(.55, '#7c2d05'); gradient.addColorStop(1, '#270e02'); ctx.fillStyle = gradient; ctx.fillRect(0, 0, width, height); }
                    const slots = cfg.image_slots || [];
                    for (const slot of slots) {
                        const x=Number(slot.x||0), y=Number(slot.y||0), w=Number(slot.width||0), h=Number(slot.height||0);
                        const slotIdentity = [slot.id,slot.name,slot.key,slot.type].join(' ');
                        const roofPlanSlot = /boc.?mai|roof.?plan|layout.?3d/i.test(slotIdentity);
                        const floorplanSlot = /floor.?plan|mat.?bang.?tang/i.test(slotIdentity);
                        // Luôn lấy ảnh bóc mái mới nhất của căn; fallback về ảnh gán trong recipe cũ.
                        const path = (roofPlanSlot && (unit.layout_3d_image_url || unit.layout_3d_image))
                            || (floorplanSlot && (unit.floorplan_image_url || unit.floorplan_image))
                            || recipe.slotImages?.[slot.id];
                        const image = await this.loadPosterAsset(path);
                        if (sequence !== this.posterRenderSequence) return;
                        if (!image) continue;
                        ctx.save();
                        const radius = Number(slot.radius||0);
                        ctx.beginPath(); if (ctx.roundRect) ctx.roundRect(x,y,w,h,radius); else ctx.rect(x,y,w,h); ctx.clip();
                        const full = /boc.?mai|floor.?plan|mat.?bang|layout.?3d/i.test(slotIdentity);
                        const scale = full ? Math.min(w/image.naturalWidth,h/image.naturalHeight) : Math.max(w/image.naturalWidth,h/image.naturalHeight);
                        const dw=image.naturalWidth*scale, dh=image.naturalHeight*scale;
                        const imageX=x+(w-dw)/2, imageY=y+(h-dh)/2;
                        ctx.drawImage(image, imageX, imageY, dw, dh);
                        const sourceUrl = String(path || '');
                        if (floorplanSlot && !/unit_canvas_|locator_/i.test(sourceUrl) && unit.highlight_data) {
                            let highlight = unit.highlight_data;
                            if (typeof highlight === 'string') { try { highlight = JSON.parse(highlight); } catch (_) { highlight = null; } }
                            if (highlight) {
                                const color = highlight.pinColor || '#ef4444';
                                const box = highlight.locatorBox || highlight.locator_box;
                                if ((highlight.locatorStyle === 'box' || highlight.locatorStyle === 'box_arrow' || !highlight.locatorStyle) && box) {
                                    const bx=imageX+Number(box.x||0)*scale, by=imageY+Number(box.y||0)*scale;
                                    const bw=Number(box.w||0)*scale, bh=Number(box.h||0)*scale;
                                    ctx.save();
                                    if (highlight.boxHasFill !== false) { ctx.fillStyle='rgba(239,68,68,0.20)'; ctx.fillRect(bx,by,bw,bh); }
                                    ctx.strokeStyle=color; ctx.lineWidth=Math.max(2,Number(highlight.boxBorderWidth||6)*scale);
                                    ctx.strokeRect(bx,by,bw,bh);
                                    if (highlight.boxHasLabel !== false) {
                                        const label=unit.unit_code || '';
                                        ctx.font=`bold ${Math.max(10,Math.round(14*scale))}px sans-serif`;
                                        const labelWidth=ctx.measureText(label).width+12, labelHeight=Math.max(18,Math.round(20*scale));
                                        ctx.fillStyle=color; ctx.fillRect(bx,Math.max(imageY,by-labelHeight),labelWidth,labelHeight);
                                        ctx.fillStyle='#fff'; ctx.textAlign='left'; ctx.textBaseline='middle';
                                        ctx.fillText(label,bx+6,Math.max(imageY,by-labelHeight)+labelHeight/2);
                                    }
                                    if (highlight.hasArrow !== false && highlight.arrowStart && highlight.arrowHead) {
                                        ctx.beginPath(); ctx.moveTo(imageX+Number(highlight.arrowStart.x||0)*scale,imageY+Number(highlight.arrowStart.y||0)*scale);
                                        ctx.lineTo(imageX+Number(highlight.arrowHead.x||0)*scale,imageY+Number(highlight.arrowHead.y||0)*scale);
                                        ctx.strokeStyle=color; ctx.lineWidth=Math.max(2,5*scale); ctx.stroke();
                                        const ax=imageX+Number(highlight.arrowHead.x||0)*scale, ay=imageY+Number(highlight.arrowHead.y||0)*scale;
                                        ctx.beginPath(); ctx.moveTo(ax,ay); ctx.lineTo(ax-10*scale,ay-5*scale); ctx.lineTo(ax-8*scale,ay+7*scale); ctx.closePath(); ctx.fillStyle=color; ctx.fill();
                                    }
                                    ctx.restore();
                                } else if (highlight.marker) {
                                    const mx=imageX+Number(highlight.marker.x||0)*scale, my=imageY+Number(highlight.marker.y||0)*scale;
                                    ctx.save(); ctx.beginPath(); ctx.arc(mx,my,Math.max(6,20*scale),0,Math.PI*2);
                                    ctx.fillStyle=color; ctx.fill(); ctx.lineWidth=Math.max(2,3*scale); ctx.strokeStyle='#fff'; ctx.stroke(); ctx.restore();
                                }
                            }
                        }
                        ctx.restore();
                        if (slot.border && slot.border !== 'none' && Number(slot.borderWidth)>0) { ctx.strokeStyle=slot.border; ctx.lineWidth=Number(slot.borderWidth); ctx.strokeRect(x,y,w,h); }
                    }
                    const form = { ...(recipe.posterForm || {}), unit_code: unit.unit_code || recipe.posterForm?.unit_code, unit_type: unit.unit_type || recipe.posterForm?.unit_type, direction: unit.direction || recipe.posterForm?.direction, view: unit.view || recipe.posterForm?.view, area: unit.area || recipe.posterForm?.area, price_text: unit.price_text || recipe.posterForm?.price_text, price_note: unit.price_note || recipe.posterForm?.price_note, block: unit.block || recipe.posterForm?.block, floor: unit.floor || recipe.posterForm?.floor };
                    const values = { unit_code:form.unit_code, unit_type:form.unit_type, direction:form.direction, view:form.view, area:form.area, price:form.price_text, price_note:form.price_note, block:form.block, floor:form.floor };
                    for (const [key,pos] of Object.entries(cfg.text_slots || {})) {
                        if (!pos || values[key] === undefined) continue;
                        const text = `${pos.prefix && pos.prefix !== 'null' ? pos.prefix : ''}${values[key] || ''}${pos.suffix && pos.suffix !== 'null' ? pos.suffix : ''}`;
                        if (!text) continue;
                        ctx.save(); ctx.font = `${pos.fontWeight || 'bold'} ${Number(pos.fontSize||22)}px ${pos.fontFamily === 'serif' ? 'Georgia, serif' : 'sans-serif'}`;
                        ctx.fillStyle=pos.color||'#fff'; ctx.textAlign=pos.align||'left'; ctx.textBaseline='top';
                        if (pos.isBadge && pos.bgColor && pos.bgColor !== 'transparent') { const tw=ctx.measureText(text).width, bw=Number(pos.width||tw+20), bh=Number(pos.height||Number(pos.fontSize||22)+12), bx=pos.align==='center'?Number(pos.x)-bw/2:(pos.align==='right'?Number(pos.x)-bw:Number(pos.x)); ctx.fillStyle=pos.bgColor; ctx.fillRect(bx,Number(pos.y),bw,bh); ctx.fillStyle=pos.color||'#fff'; ctx.textAlign='center'; ctx.textBaseline='middle'; ctx.fillText(text,bx+bw/2,Number(pos.y)+bh/2); }
                        else ctx.fillText(text,Number(pos.x||0),Number(pos.y||0));
                        ctx.restore();
                    }
                    if (sequence !== this.posterRenderSequence) return;
                    try { this.selectedPosterUrl = canvas.toDataURL('image/png'); } catch (error) { console.warn('Không thể dựng poster từ cấu hình:', error); }
                },

                async downloadSelectedPoster() {
                    this.selectedPosterUrl = '';
                    await this.renderSelectedPoster();
                    const source = this.selectedPosterUrl || this.selectedUnit?.image_url || '';
                    if (!source) return;
                    const link = document.createElement('a'); link.href = source; link.download = `Poster-${(this.selectedUnit?.unit_code || 'can-ho').replace(/[^a-z0-9_-]/gi, '-')}.png`; document.body.appendChild(link); link.click(); link.remove();
                },

                // Mở Modal Bài viết
                openArticleModal(art) {
                    this.selectedArticle = art;
                    this.articleModalOpen = true;
                },

                // Mở Modal Tư vấn
                openConsultationModal(projectName = '', unitCode = '') {
                    this.consultForm.projectName = projectName || this.currentProject.name;
                    this.consultForm.unitCode = unitCode || '';
                    this.switchMenu('contact');
                },

                // Mở Lightbox xem ảnh lớn
                openLightbox(imageUrl, title = '') {
                    if (!imageUrl) return;
                    this.lightboxImage = imageUrl;
                    this.lightboxTitle = title;
                    this.lightboxOpen = true;
                },

                // Chuyển giá căn vào Bảng tính vay
                calculateLoanForUnit(unit) {
                    this.unitDetailModalOpen = false;
                    const numPrice = this.getUnitNumericPrice(unit);
                    if (numPrice && numPrice > 0) {
                        this.calc.price = numPrice;
                    }
                    this.recalculateLoan();
                    this.switchMenu('calculator');
                },

                // Lấy giá trị số của căn hộ
                getUnitNumericPrice(unit) {
                    if (!unit) return 0;
                    let numPrice = Number(unit.price) || 0;
                    if (numPrice <= 0 && unit.price_text && unit.price_text.includes('tỷ')) {
                        const val = parseFloat(unit.price_text.replace(/[^\d.]/g, ''));
                        if (!isNaN(val)) numPrice = val * 1000000000;
                    }
                    return numPrice;
                },

                // Format giá ngắn gọn (Tỷ / Triệu)
                formatShortPrice(num) {
                    if (!num || isNaN(num) || num <= 0) return 'Đang cập nhật';
                    if (num >= 1000000000) {
                        return (num / 1000000000).toFixed(2).replace(/\.?0+$/, '') + ' Tỷ';
                    }
                    if (num >= 1000000) {
                        return (num / 1000000).toFixed(0) + ' Triệu';
                    }
                    return new Intl.NumberFormat('vi-VN').format(num) + ' đ';
                },

                // Tính toán bảng vay
                recalculateLoan() {
                    const price = Number(this.calc.price) || 0;
                    const ratio = Number(this.calc.ratio) || 70;
                    const years = Number(this.calc.years) || 20;
                    const interestRate = Number(this.calc.interestRate) || 7.5;

                    this.calc.loanAmount = Math.round(price * (ratio / 100));
                    this.calc.downPayment = Math.round(price - this.calc.loanAmount);

                    const totalMonths = Math.max(1, years * 12);
                    this.calc.monthlyPrincipal = Math.round(this.calc.loanAmount / totalMonths);

                    const monthlyInterestRate = (interestRate / 100) / 12;
                    this.calc.firstMonthInterest = Math.round(this.calc.loanAmount * monthlyInterestRate);

                    this.calc.firstMonthTotal = this.calc.monthlyPrincipal + this.calc.firstMonthInterest;
                },

                formatCurrency(val) {
                    if (!val || isNaN(val)) return '0 VNĐ';
                    return new Intl.NumberFormat('vi-VN', { style: 'currency', currency: 'VND' }).format(val);
                },

                // Xử lý gửi Form tư vấn
                async submitConsultationForm() {
                    this.consultForm.submitting = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch('{{ route("frontend.consultation") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                name: this.consultForm.name,
                                phone: this.consultForm.phone,
                                email: this.consultForm.email,
                                project_name: this.consultForm.projectName,
                                unit_code: this.consultForm.unitCode,
                                note: this.consultForm.note
                            })
                        });

                        const res = await response.json();
                        if (res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Tiếp nhận thành công!',
                                text: res.message,
                                confirmButtonColor: '#2563eb'
                            });
                            this.consultForm.note = '';
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Không thể gửi yêu cầu',
                                text: res.message || 'Vui lòng kiểm tra lại thông tin.',
                                confirmButtonColor: '#2563eb'
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi kết nối',
                            text: 'Đã có lỗi xảy ra. Quý khách vui lòng gọi hotline 1900 8686 để được hỗ trợ ngay.',
                            confirmButtonColor: '#2563eb'
                        });
                    } finally {
                        this.consultForm.submitting = false;
                    }
                },

                // Xử lý Đăng nhập nhanh bằng AJAX
                async submitQuickLogin() {
                    this.loginForm.submitting = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch('{{ route("login.post") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                email: this.loginForm.email,
                                password: this.loginForm.password,
                                remember: this.loginForm.remember
                            })
                        });

                        const res = await response.json();
                        if (response.ok && res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Đăng nhập thành công!',
                                text: res.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            setTimeout(() => {
                                window.location.href = res.redirect || '{{ route("home") }}';
                            }, 1000);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Đăng nhập thất bại',
                                text: res.message || 'Thông tin tài khoản hoặc mật khẩu không chính xác.',
                                confirmButtonColor: '#2563eb'
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi máy chủ',
                            text: 'Không thể kết nối đến máy chủ đăng nhập.',
                            confirmButtonColor: '#2563eb'
                        });
                    } finally {
                        this.loginForm.submitting = false;
                    }
                },

                // Xử lý Đăng xuất
                handleLogout() {
                    Swal.fire({
                        title: 'Xác nhận đăng xuất?',
                        text: 'Bạn sẽ quay trở lại chế độ khách thăm quan.',
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonColor: '#e11d48',
                        cancelButtonColor: '#64748b',
                        confirmButtonText: 'Đăng xuất ngay',
                        cancelButtonText: 'Ở lại'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('logoutFormStandard').submit();
                        }
                    });
                },

                // Cập nhật thông tin tài khoản
                async updateUserProfile() {
                    this.profileForm.submitting = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch('{{ route("profile.update") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                name: this.profileForm.name,
                                email: this.profileForm.email
                            })
                        });

                        const res = await response.json();
                        if (response.ok && res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công',
                                text: res.message,
                                confirmButtonColor: '#2563eb'
                            });
                            setTimeout(() => window.location.reload(), 1200);
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: res.message || 'Không thể cập nhật hồ sơ.',
                                confirmButtonColor: '#2563eb'
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi kết nối',
                            text: 'Đã xảy ra lỗi máy chủ.',
                            confirmButtonColor: '#2563eb'
                        });
                    } finally {
                        this.profileForm.submitting = false;
                    }
                },

                // Đổi mật khẩu
                async changeUserPassword() {
                    if (this.passwordForm.newPassword !== this.passwordForm.confirmPassword) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Mật khẩu chưa khớp',
                            text: 'Xác nhận mật khẩu mới không trùng khớp.',
                            confirmButtonColor: '#2563eb'
                        });
                        return;
                    }

                    this.passwordForm.submitting = true;
                    try {
                        const token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        const response = await fetch('{{ route("profile.password") }}', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': token
                            },
                            body: JSON.stringify({
                                current_password: this.passwordForm.currentPassword,
                                new_password: this.passwordForm.newPassword,
                                new_password_confirmation: this.passwordForm.confirmPassword
                            })
                        });

                        const res = await response.json();
                        if (response.ok && res.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công',
                                text: res.message,
                                confirmButtonColor: '#2563eb'
                            });
                            this.passwordForm.currentPassword = '';
                            this.passwordForm.newPassword = '';
                            this.passwordForm.confirmPassword = '';
                            this.userInfoModalOpen = false;
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Lỗi',
                                text: res.message || 'Mật khẩu hiện tại không chính xác.',
                                confirmButtonColor: '#2563eb'
                            });
                        }
                    } catch (e) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Lỗi kết nối',
                            text: 'Đã xảy ra lỗi máy chủ.',
                            confirmButtonColor: '#2563eb'
                        });
                    } finally {
                        this.passwordForm.submitting = false;
                    }
                },

                // Xuất file CSV bảng hàng
                exportInventoryCsv() {
                    const headers = ['Mã căn', 'Tòa', 'Tầng', 'Loại căn', 'Diện tích (m2)', 'Hướng', 'Giá niêm yết', 'Trạng thái'];
                    const rows = this.filteredUnits.map(u => [
                        u.unit_code,
                        u.block || '',
                        u.floor || '',
                        u.unit_type || '2PN',
                        u.area || '',
                        u.direction || '',
                        u.price_text || u.display_price || '',
                        u.status === 'available' ? 'Còn trống' : (u.status === 'sold' ? 'Đã bán' : 'Đang giữ chỗ')
                    ]);

                    let csvContent = "data:text/csv;charset=utf-8,\uFEFF" 
                        + [headers.join(','), ...rows.map(e => e.map(item => `"${item}"`).join(','))].join('\n');

                    const encodedUri = encodeURI(csvContent);
                    const link = document.createElement("a");
                    link.setAttribute("href", encodedUri);
                    link.setAttribute("download", `Bang_hang_${this.currentProject.name}_${new Date().toISOString().slice(0,10)}.csv`);
                    document.body.appendChild(link);
                    link.click();
                    document.body.removeChild(link);
                }
            }
        }
    </script>
</body>
</html>
