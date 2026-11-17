@extends('layouts.admin')

@section('title', 'Tổng quan hệ thống')
@section('page-title', 'Bảng điều khiển Tổng quan')

@section('content')

    <!-- Welcome Banner -->
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-r from-slate-900 via-indigo-950 to-blue-900 text-white p-6 sm:p-8 shadow-xl">
        <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-blue-500/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div>
                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 text-blue-300 text-xs font-semibold mb-2">
                    <span class="w-2 h-2 rounded-full bg-blue-400"></span> Hệ thống đang vận hành ổn định
                </span>
                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
                    Xin chào, {{ auth()->user()->name }}! 👋
                </h1>
                <p class="text-slate-300 text-xs sm:text-sm mt-1 max-w-xl leading-relaxed">
                    Chào mừng bạn đến với trung tâm điều hành IDC Portal. Tại đây bạn có thể kiểm soát người dùng, hiệu suất hệ thống và dữ liệu thời gian thực.
                </p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('admin.users') }}" class="px-4 py-2.5 rounded-xl bg-white text-slate-900 hover:bg-slate-100 font-bold text-xs shadow-md transition flex items-center gap-2">
                    <i class="fa-solid fa-user-plus text-blue-600"></i>
                    <span>Quản lý thành viên</span>
                </a>
                <a href="{{ route('admin.settings') }}" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/20 border border-white/10 text-white font-bold text-xs transition flex items-center gap-2">
                    <i class="fa-solid fa-gear"></i>
                    <span>Cấu hình</span>
                </a>
            </div>
        </div>
    </div>

    <!-- 4 Stats Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Total Users -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Tổng người dùng</p>
                    <h3 class="text-3xl font-extrabold text-slate-900 mt-2">{{ $totalUsers }}</h3>
                    <p class="text-xs text-emerald-600 font-semibold mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-arrow-trend-up"></i>
                        <span>+12.5% so với tháng trước</span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-blue-50 text-blue-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-users"></i>
                </div>
            </div>
        </div>

        <!-- Active Users -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Đang hoạt động</p>
                    <h3 class="text-3xl font-extrabold text-emerald-600 mt-2">{{ $activeUsers }}</h3>
                    <p class="text-xs text-slate-500 font-medium mt-1">
                        Tỷ lệ kích hoạt: <span class="font-bold text-slate-700">{{ $totalUsers > 0 ? round(($activeUsers / $totalUsers) * 100) : 0 }}%</span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-user-check"></i>
                </div>
            </div>
        </div>

        <!-- Admin Count -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Quản trị viên</p>
                    <h3 class="text-3xl font-extrabold text-indigo-600 mt-2">{{ $adminCount }}</h3>
                    <p class="text-xs text-indigo-500 font-semibold mt-1 flex items-center gap-1">
                        <i class="fa-solid fa-shield-halved"></i>
                        <span>Quyền hạn tối cao</span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-user-shield"></i>
                </div>
            </div>
        </div>

        <!-- Database & Health -->
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-wider">Hệ thống IDC</p>
                    <h3 class="text-lg font-extrabold text-slate-900 mt-2">idc_tool</h3>
                    <p class="text-xs text-slate-500 font-medium mt-1">
                        MySQL: <span class="font-semibold text-slate-700">Online</span>
                    </p>
                </div>
                <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl shadow-inner">
                    <i class="fa-solid fa-database"></i>
                </div>
            </div>
        </div>

    </div>

    <!-- Charts & System Section -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Chart (8 cols) -->
        <div class="lg:col-span-8 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h3 class="text-base font-bold text-slate-900">Biểu đồ Lượt truy cập & Tăng trưởng</h3>
                    <p class="text-xs text-slate-500">Số liệu tương tác và người dùng mới trong 6 tháng qua</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-blue-600">
                        <span class="w-2.5 h-2.5 rounded-full bg-blue-600"></span> Truy cập
                    </span>
                    <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-600 ml-3">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Người dùng mới
                    </span>
                </div>
            </div>
            <div class="h-72">
                <canvas id="growthChart"></canvas>
            </div>
        </div>

        <!-- System Quick Specs (4 cols) -->
        <div class="lg:col-span-4 bg-white p-6 rounded-2xl border border-slate-100 shadow-sm flex flex-col justify-between">
            <div>
                <h3 class="text-base font-bold text-slate-900 mb-1">Môi trường máy chủ</h3>
                <p class="text-xs text-slate-500 mb-5">Thông số phần mềm nền tảng XAMPP</p>

                <div class="space-y-4">
                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50">
                        <div class="flex items-center gap-3">
                            <i class="fa-brands fa-php text-indigo-600 text-lg"></i>
                            <span class="text-xs font-semibold text-slate-700">PHP Version</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 text-xs font-bold">{{ $phpVersion }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50">
                        <div class="flex items-center gap-3">
                            <i class="fa-brands fa-laravel text-rose-600 text-lg"></i>
                            <span class="text-xs font-semibold text-slate-700">Laravel Version</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-md bg-rose-100 text-rose-700 text-xs font-bold">{{ $laravelVersion }}</span>
                    </div>

                    <div class="flex items-center justify-between p-3 rounded-xl bg-slate-50">
                        <div class="flex items-center gap-3">
                            <i class="fa-solid fa-server text-emerald-600 text-lg"></i>
                            <span class="text-xs font-semibold text-slate-700">Cơ sở dữ liệu</span>
                        </div>
                        <span class="px-2 py-0.5 rounded-md bg-emerald-100 text-emerald-700 text-xs font-bold truncate max-w-[120px]">idc_tool</span>
                    </div>
                </div>
            </div>

            <!-- Quick Action Box -->
            <div class="pt-6 border-t border-slate-100 mt-6">
                <form id="dashboardClearCacheForm" action="{{ route('admin.clear-cache') }}" method="POST">
                    @csrf
                    <button type="button" onclick="handleClearCache('dashboardClearCacheForm')" class="w-full py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold flex items-center justify-center gap-2 transition">
                        <i class="fa-solid fa-broom text-amber-500"></i>
                        <span>Dọn dẹp Bộ nhớ Cache</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

    <!-- Recent Users Table Section -->
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h3 class="text-base font-bold text-slate-900">Danh sách Người dùng mới nhất</h3>
                <p class="text-xs text-slate-500">Người dùng được tạo hoặc cập nhật gần đây</p>
            </div>
            <a href="{{ route('admin.users') }}" class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1.5 self-start sm:self-auto">
                <span>Xem tất cả người dùng</span>
                <i class="fa-solid fa-arrow-right text-[10px]"></i>
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 uppercase tracking-wider font-bold border-b border-slate-100">
                        <th class="py-3.5 px-6">Thành viên</th>
                        <th class="py-3.5 px-6">Email</th>
                        <th class="py-3.5 px-6">Vai trò</th>
                        <th class="py-3.5 px-6">Trạng thái</th>
                        <th class="py-3.5 px-6">Ngày tham gia</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 text-slate-700 font-medium">
                    @forelse ($recentUsers as $user)
                        <tr class="hover:bg-slate-50/60 transition">
                            <td class="py-4 px-6 flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-blue-600 to-indigo-600 text-white font-bold flex items-center justify-center text-xs shadow-sm">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="font-bold text-slate-900">{{ $user->name }}</span>
                            </td>
                            <td class="py-4 px-6 text-slate-600 font-mono">{{ $user->email }}</td>
                            <td class="py-4 px-6">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[11px] font-bold border {{ $user->role_badge }}">
                                    <i class="fa-solid {{ $user->role_icon }} text-[10px]"></i> {{ $user->role_name }}
                                </span>
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
                                {{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : 'Mới tạo' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-400">Chưa có người dùng nào.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const ctx = document.getElementById('growthChart').getContext('2d');
        
        // Gradient fill for visits
        const visitGradient = ctx.createLinearGradient(0, 0, 0, 300);
        visitGradient.addColorStop(0, 'rgba(59, 130, 246, 0.35)');
        visitGradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [
                    {
                        label: 'Lượt truy cập',
                        data: {!! json_encode($chartData['visits']) !!},
                        borderColor: '#2563eb',
                        backgroundColor: visitGradient,
                        borderWidth: 2.5,
                        fill: true,
                        tension: 0.4,
                        pointBackgroundColor: '#2563eb',
                        pointBorderColor: '#ffffff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Người dùng mới',
                        data: {!! json_encode($chartData['users']) !!},
                        borderColor: '#10b981',
                        borderWidth: 2,
                        borderDash: [5, 5],
                        fill: false,
                        tension: 0.4,
                        pointBackgroundColor: '#10b981',
                        pointRadius: 4,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: '#0f172a',
                        titleFont: { family: 'Plus Jakarta Sans', size: 12 },
                        bodyFont: { family: 'Plus Jakarta Sans', size: 12 },
                        padding: 12,
                        cornerRadius: 8,
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 11 }, color: '#64748b' }
                    },
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        grid: { color: '#f1f5f9' },
                        ticks: { font: { family: 'Plus Jakarta Sans', size: 11 }, color: '#64748b' }
                    },
                    y1: {
                        type: 'linear',
                        display: false,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                    }
                }
            }
        });
    });
</script>
@endpush
