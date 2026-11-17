@extends('layouts.admin')

@section('title', 'Tạo & Quản lý Chỉ căn Dự án')
@section('page-title', 'Tạo & Quản lý Chỉ căn Dự án')

@section('header-actions')
    <a 
        href="{{ route('admin.databases.index') }}" 
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-sm transition"
        title="Cấu hình Database Dùng chung"
    >
        <i class="fa-solid fa-database text-blue-500 text-xs"></i>
        <span>Cấu hình DB Dùng chung</span>
    </a>
@endsection

@section('content')
<div class="space-y-6" x-data="{ viewMode: 'table' }">

    <!-- Error Alert if Shared Database is Unreachable -->
    @if ($dbError)
    <div class="p-4 rounded-2xl bg-amber-50 border border-amber-200 text-amber-800 flex items-start gap-3 shadow-sm">
        <i class="fa-solid fa-triangle-exclamation text-amber-600 text-xl mt-0.5"></i>
        <div class="flex-1 text-sm">
            <h4 class="font-bold text-amber-900">Cảnh báo kết nối Database Dùng chung</h4>
            <p class="mt-0.5 text-amber-700">{{ $dbError }}</p>
            <p class="mt-2 text-xs text-amber-600">Vui lòng kiểm tra lại cấu hình Database và đảm bảo bảng <strong>posts</strong> đã được chọn làm bảng dùng chung.</p>
            <a href="{{ route('admin.databases.index') }}" class="inline-block mt-2 font-semibold underline text-amber-900 hover:text-amber-950">
                Đi đến trang Cấu hình Database &rarr;
            </a>
        </div>
    </div>
    @endif

    <!-- KPI Summary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Stat 1: Tổng số dự án -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-city text-xl"></i>
            </div>
            <div class="overflow-hidden">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Dự án BĐS</span>
                <span class="text-2xl font-black text-slate-800 tracking-tight">{{ number_format($totalProjectsCount) }}</span>
                <span class="text-[11px] text-slate-500 block truncate">Từ bảng posts DB dùng chung</span>
            </div>
        </div>

        <!-- Stat 2: Tổng số chỉ căn -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-layer-group text-xl"></i>
            </div>
            <div class="overflow-hidden">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Tổng ảnh chỉ căn</span>
                <span class="text-2xl font-black text-slate-800 tracking-tight">{{ number_format($totalUnitsInSystem) }}</span>
                <span class="text-[11px] text-indigo-600 font-semibold block truncate">Đã tạo trong hệ thống</span>
            </div>
        </div>

        <!-- Stat 3: Dự án đã cấu hình chỉ căn -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-circle-check text-xl"></i>
            </div>
            <div class="overflow-hidden">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Dự án đã có chỉ căn</span>
                <span class="text-2xl font-black text-slate-800 tracking-tight">{{ number_format($projectsWithUnitsCount) }}</span>
                <span class="text-[11px] text-emerald-600 font-semibold block truncate">Dự án sẵn sàng bán hàng</span>
            </div>
        </div>

        <!-- Stat 4: Căn hộ còn trống -->
        <div class="bg-white p-5 rounded-2xl border border-slate-100 shadow-sm flex items-center gap-4 hover:shadow-md transition">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                <i class="fa-solid fa-door-open text-xl"></i>
            </div>
            <div class="overflow-hidden">
                <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider block">Căn còn trống</span>
                <span class="text-2xl font-black text-slate-800 tracking-tight">{{ number_format($availableUnitsCount) }}</span>
                <span class="text-[11px] text-amber-600 font-semibold block truncate">Sẵn sàng giao dịch</span>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex flex-col md:flex-row items-center justify-between gap-4">
        <!-- Search Input -->
        <form method="GET" action="{{ route('admin.unit-locators.index') }}" class="w-full md:w-96 flex items-center gap-2">
            <div class="relative w-full">
                <i class="fa-solid fa-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input 
                    type="text" 
                    name="search" 
                    value="{{ $search }}" 
                    placeholder="Tìm theo tên dự án, địa chỉ..." 
                    class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition bg-slate-50/50"
                >
                @if(!empty($search))
                <a href="{{ route('admin.unit-locators.index', ['filter' => $filter]) }}" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600">
                    <i class="fa-solid fa-circle-xmark text-sm"></i>
                </a>
                @endif
            </div>
            @if($filter !== 'all')
                <input type="hidden" name="filter" value="{{ $filter }}">
            @endif
            <button type="submit" class="px-4 py-2.5 rounded-xl bg-blue-600 text-white font-semibold text-sm hover:bg-blue-700 transition shrink-0 shadow-sm">
                Tìm
            </button>
        </form>

        <!-- Right: Filter Tabs + View Mode Switcher -->
        <div class="flex items-center gap-3 w-full md:w-auto justify-between md:justify-end">
            <!-- Filter Tabs -->
            <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl">
                <a 
                    href="{{ route('admin.unit-locators.index', array_merge(request()->query(), ['filter' => 'all', 'page' => 1])) }}" 
                    class="text-center px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $filter === 'all' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Tất cả dự án
                </a>
                <a 
                    href="{{ route('admin.unit-locators.index', array_merge(request()->query(), ['filter' => 'has_units', 'page' => 1])) }}" 
                    class="text-center px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $filter === 'has_units' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Đã có chỉ căn
                </a>
                <a 
                    href="{{ route('admin.unit-locators.index', array_merge(request()->query(), ['filter' => 'no_units', 'page' => 1])) }}" 
                    class="text-center px-3 py-1.5 rounded-lg text-xs font-bold transition {{ $filter === 'no_units' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-600 hover:text-slate-900' }}"
                >
                    Chưa có chỉ căn
                </a>
            </div>

            <!-- View Mode Toggle: Table (default) vs Grid -->
            <div class="flex items-center gap-1 p-1 bg-slate-100 rounded-xl shrink-0">
                <button 
                    type="button" 
                    @click="viewMode = 'table'" 
                    :class="viewMode === 'table' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                    class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5"
                    title="Hiển thị dạng Bảng (Table - mỗi căn 1 dòng)"
                >
                    <i class="fa-solid fa-table-list"></i>
                    <span class="hidden sm:inline">Bảng</span>
                </button>
                <button 
                    type="button" 
                    @click="viewMode = 'grid'" 
                    :class="viewMode === 'grid' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-800'"
                    class="px-2.5 py-1.5 rounded-lg text-xs font-bold transition flex items-center gap-1.5"
                    title="Hiển thị dạng Thẻ (Grid)"
                >
                    <i class="fa-solid fa-border-all"></i>
                    <span class="hidden sm:inline">Lưới</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Empty State -->
    @if ($projects->isEmpty())
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
        <div class="w-16 h-16 rounded-2xl bg-blue-50 text-blue-500 mx-auto flex items-center justify-center text-2xl mb-4">
            <i class="fa-solid fa-folder-open"></i>
        </div>
        <h3 class="text-lg font-bold text-slate-800">Không tìm thấy dự án bất động sản nào</h3>
        <p class="text-sm text-slate-500 max-w-md mx-auto mt-1">
            Không có dữ liệu dự án phù hợp với từ khóa hoặc bộ lọc của bạn trong bảng <code>posts</code>.
        </p>
        <div class="mt-5">
            <a href="{{ route('admin.unit-locators.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 text-slate-700 text-sm font-semibold hover:bg-slate-200 transition">
                <i class="fa-solid fa-rotate-left"></i>
                <span>Đặt lại bộ lọc</span>
            </a>
        </div>
    </div>
    @else

    <!-- ==================== VIEW 1: TABLE VIEW (MỖI CĂN 1 DÒNG, TỐI ƯU KHÔNG XUỐNG DÒNG) ==================== -->
    <div x-show="viewMode === 'table'" class="bg-white rounded-2xl border border-slate-200/80 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                <thead>
                    <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-4 w-16 text-center">ID</th>
                        <th class="py-3.5 px-4 w-16 text-center">Ảnh</th>
                        <th class="py-3.5 px-4">Tên Dự án Bất động sản</th>
                        <th class="py-3.5 px-4">Địa chỉ / Vị trí</th>
                        <th class="py-3.5 px-4">Khoảng giá</th>
                        <th class="py-3.5 px-4">Diện tích</th>
                        <th class="py-3.5 px-4 text-center">Số ảnh chỉ căn</th>
                        <th class="py-3.5 px-4 text-center">Trạng thái</th>
                        <th class="py-3.5 px-4 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($projects as $item)
                    <tr class="hover:bg-blue-50/30 transition duration-150">
                        <!-- ID -->
                        <td class="py-3 px-4 text-center">
                            <span class="font-mono text-xs font-bold text-slate-500 bg-slate-100 px-2 py-0.5 rounded-md">#{{ $item->id }}</span>
                        </td>

                        <!-- Thumbnail -->
                        <td class="py-3 px-4 text-center">
                            <div class="w-10 h-10 rounded-xl overflow-hidden bg-slate-900 border border-slate-200 mx-auto shrink-0 shadow-sm">
                                <img 
                                    src="{{ !empty($item->img) ? route('admin.unit-locators.project-image', $item->img) : asset('images/placeholder-floorplan.svg') }}" 
                                    alt="{{ $item->name }}"
                                    class="w-full h-full object-cover"
                                    onerror="this.onerror=null; this.src='{{ route('admin.unit-locators.project-image', 'default.jpg') }}';"
                                >
                            </div>
                        </td>

                        <!-- Tên dự án -->
                        <td class="py-3 px-4">
                            <a 
                                href="{{ route('admin.unit-locators.project', $item->id) }}" 
                                class="font-bold text-slate-900 hover:text-blue-600 transition text-sm flex items-center gap-1.5"
                                title="{{ $item->name }}"
                            >
                                <span>{{ $item->name }}</span>
                            </a>
                        </td>

                        <!-- Địa chỉ -->
                        <td class="py-3 px-4">
                            @if (!empty($item->address))
                            <span class="text-slate-600 flex items-center gap-1.5" title="{{ $item->address }}">
                                <i class="fa-solid fa-location-dot text-rose-500 text-[11px]"></i>
                                <span>{{ $item->address }}</span>
                            </span>
                            @else
                            <span class="text-slate-400 italic">Đang cập nhật</span>
                            @endif
                        </td>

                        <!-- Khoảng giá -->
                        <td class="py-3 px-4">
                            @if(!empty($item->price) && $item->price > 0)
                            <span class="font-extrabold text-blue-600">
                                {{ $item->price >= 1000000000 ? number_format($item->price / 1000000000, 2) . ' Tỷ' : number_format($item->price) . ' ' . ($item->unit ?? 'đ') }}
                            </span>
                            @else
                            <span class="text-slate-400 italic">Đang cập nhật</span>
                            @endif
                        </td>

                        <!-- Diện tích -->
                        <td class="py-3 px-4">
                            @if(!empty($item->acreage))
                            <span class="font-semibold text-slate-700">{{ $item->acreage }} m²</span>
                            @else
                            <span class="text-slate-400">—</span>
                            @endif
                        </td>

                        <!-- Số lượng chỉ căn -->
                        <td class="py-3 px-4 text-center">
                            @if ($item->total_units > 0)
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                <span>{{ $item->total_units }} chỉ căn</span>
                            </span>
                            @else
                            <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                <span>Chưa có</span>
                            </span>
                            @endif
                        </td>

                        <!-- Trạng thái dự án -->
                        <td class="py-3 px-4 text-center">
                            @if ($item->status == 'true' || $item->status == '1')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                Đang mở bán
                            </span>
                            @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold bg-slate-50 text-slate-500 border border-slate-200">
                                Tạm dừng
                            </span>
                            @endif
                        </td>

                        <!-- Thao tác -->
                        <td class="py-3 px-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <a 
                                    href="{{ route('admin.unit-locators.project', $item->id) }}" 
                                    class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white font-bold text-xs transition duration-150"
                                    title="Xem danh sách ảnh chỉ căn của dự án"
                                >
                                    <i class="fa-solid fa-eye text-xs"></i>
                                    <span>Xem ({{ $item->total_units }})</span>
                                </a>
                                <a 
                                    href="{{ route('admin.unit-locators.create', $item->id) }}" 
                                    class="inline-flex items-center gap-1.5 py-1.5 px-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-bold text-xs transition duration-150 shadow-sm"
                                    title="Tạo chỉ căn mới cho dự án này"
                                >
                                    <i class="fa-solid fa-plus text-xs"></i>
                                    <span>Tạo chỉ căn</span>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- ==================== VIEW 2: GRID VIEW (THẺ LƯỚI) ==================== -->
    <div x-show="viewMode === 'grid'" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach ($projects as $item)
        <div class="group bg-white rounded-2xl border border-slate-200/80 hover:border-blue-400 overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
            <div>
                <!-- Project Image & Badge -->
                <div class="relative h-44 w-full bg-slate-900 overflow-hidden">
                    <img 
                        src="{{ !empty($item->img) ? route('admin.unit-locators.project-image', $item->img) : asset('images/placeholder-floorplan.svg') }}" 
                        alt="{{ $item->name }}"
                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                        onerror="this.onerror=null; this.src='{{ route('admin.unit-locators.project-image', 'default.jpg') }}';"
                    >
                    <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-black/20"></div>

                    <!-- Unit Count Badge -->
                    <div class="absolute top-3 right-3">
                        @if ($item->total_units > 0)
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-emerald-500 text-white shadow-lg shadow-emerald-500/30">
                            <i class="fa-solid fa-check"></i>
                            <span>{{ $item->total_units }} chỉ căn</span>
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-800/80 text-slate-300 backdrop-blur-sm border border-slate-700">
                            <span>Chưa có chỉ căn</span>
                        </span>
                        @endif
                    </div>

                    <!-- Project ID Badge -->
                    <div class="absolute top-3 left-3">
                        <span class="px-2.5 py-0.5 rounded-lg text-[11px] font-bold bg-black/60 text-white backdrop-blur-md border border-white/10">
                            #{{ $item->id }}
                        </span>
                    </div>

                    <!-- Project Title on Image Overlay -->
                    <div class="absolute bottom-3 left-3 right-3 text-white">
                        <h3 class="font-bold text-base leading-snug line-clamp-1 drop-shadow-md text-white group-hover:text-blue-200 transition">
                            {{ $item->name }}
                        </h3>
                        @if (!empty($item->address))
                        <p class="text-xs text-slate-300 line-clamp-1 flex items-center gap-1 mt-0.5">
                            <i class="fa-solid fa-location-dot text-[11px] text-rose-400"></i>
                            <span>{{ $item->address }}</span>
                        </p>
                        @endif
                    </div>
                </div>

                <!-- Info Body -->
                <div class="p-4 space-y-3">
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Khoảng giá</span>
                            <span class="font-bold text-slate-800">
                                @if(!empty($item->price) && $item->price > 0)
                                    {{ $item->price >= 1000000000 ? number_format($item->price / 1000000000, 1) . ' Tỷ' : number_format($item->price) . ' ' . ($item->unit ?? 'đ') }}
                                @else
                                    Đang cập nhật
                                @endif
                            </span>
                        </div>
                        <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                            <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Diện tích</span>
                            <span class="font-bold text-slate-800">
                                {{ !empty($item->acreage) ? $item->acreage . ' m²' : 'Đang cập nhật' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Actions -->
            <div class="p-4 pt-0 grid grid-cols-2 gap-2">
                <a 
                    href="{{ route('admin.unit-locators.project', $item->id) }}" 
                    class="w-full flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white font-bold text-xs transition duration-200"
                >
                    <i class="fa-solid fa-eye text-xs"></i>
                    <span>Xem ({{ $item->total_units }})</span>
                </a>
                <a 
                    href="{{ route('admin.unit-locators.create', $item->id) }}" 
                    class="w-full flex items-center justify-center gap-1.5 py-2.5 px-3 rounded-xl bg-blue-600 text-white hover:bg-blue-700 font-bold text-xs transition duration-200 shadow-md shadow-blue-500/20"
                >
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Tạo chỉ căn</span>
                </a>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="pt-4">
        {{ $projects->links() }}
    </div>
    @endif

</div>
@endsection
