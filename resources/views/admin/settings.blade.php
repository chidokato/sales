@extends('layouts.admin')

@section('title', 'Cấu hình hệ thống')
@section('page-title', 'Cấu hình & Trạng thái')

@section('content')

    <div class="space-y-6">

        <!-- System Overview Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

            <!-- App Information Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-laptop-code"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Ứng dụng Laravel</h4>
                        <p class="text-xs text-slate-500">Cấu hình cơ bản từ file .env</p>
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Tên ứng dụng:</span>
                        <span class="font-bold text-slate-800">{{ config('app.name') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Môi trường:</span>
                        <span class="px-2 py-0.5 rounded-md bg-blue-50 text-blue-600 font-bold font-mono">{{ config('app.env') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Chế độ Debug:</span>
                        <span class="px-2 py-0.5 rounded-md {{ config('app.debug') ? 'bg-amber-50 text-amber-600' : 'bg-emerald-50 text-emerald-600' }} font-bold">
                            {{ config('app.debug') ? 'Bật (Debug ON)' : 'Tắt (Production)' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Đường dẫn URL:</span>
                        <span class="font-mono text-slate-700 truncate max-w-[160px]">{{ config('app.url') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Phiên bản Laravel:</span>
                        <span class="font-bold text-rose-600 font-mono">v{{ app()->version() }}</span>
                    </div>
                </div>
            </div>

            <!-- Database Information Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-database"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Cơ sở dữ liệu</h4>
                        <p class="text-xs text-slate-500">Kết nối MySQL / MariaDB</p>
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Driver:</span>
                        <span class="font-bold text-slate-800 font-mono">{{ config('database.default') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Tên Database:</span>
                        <span class="px-2 py-0.5 rounded-md bg-emerald-50 text-emerald-700 font-bold font-mono">idc_tool</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Máy chủ & Cổng:</span>
                        <span class="font-mono text-slate-700">127.0.0.1:3306</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Tài khoản DB:</span>
                        <span class="font-mono text-slate-700">root</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Trạng thái kết nối:</span>
                        <span class="inline-flex items-center gap-1 text-emerald-600 font-bold">
                            <i class="fa-solid fa-circle-check"></i> Đang hoạt động
                        </span>
                    </div>
                    <div class="pt-2 border-t border-slate-100">
                        <a href="{{ route('admin.databases.index') }}" class="w-full py-2 px-3 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-bold flex items-center justify-center gap-2 transition">
                            <i class="fa-solid fa-network-wired text-blue-600"></i>
                            <span>Quản lý Database Dùng chung</span>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Server & PHP Card -->
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm space-y-4">
                <div class="flex items-center gap-3 pb-3 border-b border-slate-100">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg">
                        <i class="fa-solid fa-server"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-slate-900">Máy chủ & PHP</h4>
                        <p class="text-xs text-slate-500">Môi trường thực thi cục bộ</p>
                    </div>
                </div>

                <div class="space-y-3 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Phiên bản PHP:</span>
                        <span class="font-bold text-indigo-600 font-mono">v{{ PHP_VERSION }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Hệ điều hành:</span>
                        <span class="font-medium text-slate-700">{{ PHP_OS_FAMILY }} (Windows)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Web Server:</span>
                        <span class="font-medium text-slate-700">Apache (XAMPP)</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Bộ nhớ giới hạn:</span>
                        <span class="font-mono text-slate-700">{{ ini_get('memory_limit') }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium">Thời gian tối đa:</span>
                        <span class="font-mono text-slate-700">{{ ini_get('max_execution_time') }}s</span>
                    </div>
                </div>
            </div>

        </div>

        <!-- Maintenance & Quick Actions Section -->
        <div class="bg-white p-6 sm:p-8 rounded-2xl border border-slate-100 shadow-sm space-y-6">
            <div>
                <h3 class="text-base font-bold text-slate-900">Bảo trì & Tối ưu hóa hệ thống</h3>
                <p class="text-xs text-slate-500">Thực hiện xóa cache cấu hình, views và route khi cập nhật mã nguồn</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                
                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2.5 text-amber-600 font-bold text-xs mb-1">
                            <i class="fa-solid fa-broom"></i>
                            <span>Dọn dẹp Bộ nhớ Cache & View Compiled</span>
                        </div>
                        <p class="text-slate-500 text-xs leading-relaxed">
                            Xóa toàn bộ các tệp cấu hình đã cache, view blade biên dịch và route cache để hệ thống tải lại mã mới nhất.
                        </p>
                    </div>
                    <div class="mt-4">
                        <form id="settingsClearCacheForm" action="{{ route('admin.clear-cache') }}" method="POST">
                            @csrf
                            <button type="button" onclick="handleClearCache('settingsClearCacheForm')" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs shadow-sm transition">
                                Xóa toàn bộ Cache ngay
                            </button>
                        </form>
                    </div>
                </div>

                <div class="p-5 rounded-2xl bg-slate-50 border border-slate-200/80 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-2.5 text-blue-600 font-bold text-xs mb-1">
                            <i class="fa-solid fa-shield-halved"></i>
                            <span>Bảo mật & Phân quyền</span>
                        </div>
                        <p class="text-slate-500 text-xs leading-relaxed">
                            Mọi route quản trị đều được bảo vệ bởi middleware xác thực <code class="bg-slate-200 px-1 py-0.5 rounded text-[11px]">auth</code> và kiểm tra quyền <code class="bg-slate-200 px-1 py-0.5 rounded text-[11px]">admin</code>.
                        </p>
                    </div>
                    <div class="mt-4 flex items-center gap-2">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            <i class="fa-solid fa-check text-xs"></i> Đã kích hoạt bảo vệ
                        </span>
                    </div>
                </div>

            </div>
        </div>

    </div>

@endsection
