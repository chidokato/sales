@extends('layouts.admin')

@section('title', 'Chỉ căn - ' . ($project->name ?? 'Dự án BĐS'))
@section('page-title', 'Chỉ căn: ' . ($project->name ?? 'Dự án'))

@section('header-actions')
    <a 
        href="{{ route('admin.unit-locators.index') }}" 
        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-sm transition"
        title="Quay lại danh sách dự án"
    >
        <i class="fa-solid fa-arrow-left text-xs"></i>
        <span>Danh sách dự án</span>
    </a>

    <a 
        href="{{ route('admin.unit-locators.create', ['projectId' => $project->id, 'mode' => 'studio']) }}"
        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-rose-200 bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-bold shadow-xs transition"
        title="Định vị vị trí căn trên mặt bằng theo từng trục căn (đồng bộ cho toàn bộ căn cùng trục)"
    >
        <i class="fa-solid fa-arrows-split-up-and-left text-xs"></i>
        <span>Định vị theo trục căn</span>
    </a>

    <a 
        href="{{ route('admin.unit-locators.create', $project->id) }}"
        onclick="if (window.__selectedUnitIds && window.__selectedUnitIds.length > 0) { window.location.href = '{{ route('admin.unit-locators.create', $project->id) }}?units=' + window.__selectedUnitIds.join(','); return false; }"
        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl border border-indigo-200 bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold shadow-xs transition"
        title="Vào Studio ghép & sửa ảnh chỉ căn hàng loạt"
    >
        <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
        <span>Sửa ảnh hàng loạt</span>
    </a>

    <a 
        href="{{ route('admin.unit-locators.create', ['projectId' => $project->id, 'import' => 'excel']) }}" 
        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold shadow-sm transition"
        title="Nhập danh sách căn từ file Excel và tạo ảnh chỉ căn"
    >
        <i class="fa-solid fa-file-excel text-xs"></i>
        <span>Nhập từ Excel</span>
    </a>

    <a 
        href="{{ route('admin.unit-locators.create', $project->id) }}" 
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition"
        title="Tạo chỉ căn mới"
    >
        <i class="fa-solid fa-plus text-xs"></i>
        <span>+ Tạo chỉ căn mới</span>
    </a>
@endsection

@section('content')
<div class="space-y-3.5" x-data="unitProjectHub()" 
    @open-floorplan-tab.window="activeTab = 'floorplans'" 
    @open-template-tab.window="activeTab = 'templates'" 
    @open-perspective-tab.window="activeTab = 'perspectives'" 
    @open-floorplan-modal.window="floorplanModalOpen = true"
    @open-template-modal.window="uploadTemplateModalOpen = true"
    @open-perspective-modal.window="perspectiveModalOpen = true"
>

    <!-- KPI Stats Mini Cards (Hàng ngang nhỏ gọn) -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2.5">
        <div class="bg-white px-4 py-2.5 rounded-xl border border-slate-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider block">Tổng số chỉ căn</span>
                <span class="text-lg font-black text-slate-800 leading-tight" x-text="unitCount">{{ $stats['total'] }}</span>
            </div>
            <div class="w-8 h-8 rounded-lg bg-slate-50 text-slate-400 flex items-center justify-center text-xs">
                <i class="fa-solid fa-layer-group"></i>
            </div>
        </div>
        <div class="bg-white px-4 py-2.5 rounded-xl border border-emerald-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-emerald-600 uppercase tracking-wider block">Còn trống</span>
                <span class="text-lg font-black text-emerald-700 leading-tight">{{ $stats['available'] }}</span>
            </div>
            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center text-xs">
                <i class="fa-solid fa-check"></i>
            </div>
        </div>
        <div class="bg-white px-4 py-2.5 rounded-xl border border-purple-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-purple-600 uppercase tracking-wider block">Đã đặt cọc</span>
                <span class="text-lg font-black text-purple-700 leading-tight">{{ $stats['deposited'] }}</span>
            </div>
            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center text-xs">
                <i class="fa-solid fa-handshake"></i>
            </div>
        </div>
        <div class="bg-white px-4 py-2.5 rounded-xl border border-rose-200/80 shadow-xs flex items-center justify-between">
            <div>
                <span class="text-[10px] font-bold text-rose-600 uppercase tracking-wider block">Đã bán</span>
                <span class="text-lg font-black text-rose-700 leading-tight">{{ $stats['sold'] }}</span>
            </div>
            <div class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 flex items-center justify-center text-xs">
                <i class="fa-solid fa-lock"></i>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs: Chỉ căn hộ, Phôi mẫu, Kho phối cảnh, Mặt bằng tầng -->
    <div class="flex items-center justify-between border-b border-slate-200">
        <div class="flex items-center gap-1 overflow-x-auto">
            <!-- Tab 1: Căn hộ -->
            <button 
                type="button" 
                @click="activeTab = 'units'"
                :class="activeTab === 'units' ? 'border-blue-600 text-blue-600 font-bold bg-white' : 'border-transparent text-slate-500 hover:text-slate-800 font-semibold'"
                class="py-2.5 px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition rounded-t-xl shrink-0"
            >
                <i class="fa-solid fa-layer-group text-xs"></i>
                <span>Danh sách Chỉ căn Hộ</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold" :class="activeTab === 'units' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'" x-text="unitCount">{{ $units->total() }}</span>
            </button>

            <!-- Tab 2: Cấu hình Phôi Chỉ Căn -->
            <button 
                type="button" 
                @click="activeTab = 'templates'"
                :class="activeTab === 'templates' ? 'border-amber-600 text-amber-700 font-bold bg-white' : 'border-transparent text-slate-500 hover:text-slate-800 font-semibold'"
                class="py-2.5 px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition rounded-t-xl shrink-0"
            >
                <i class="fa-solid fa-palette text-xs text-amber-500"></i>
                <span>Cấu hình Phôi Chỉ Căn</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold" :class="activeTab === 'templates' ? 'bg-amber-100 text-amber-800' : 'bg-slate-100 text-slate-600'" x-text="templateCount">{{ $templates->count() }}</span>
            </button>

            <!-- Tab 3: Kho Ảnh Phối Cảnh & Tiện Ích -->
            <button 
                type="button" 
                @click="activeTab = 'perspectives'"
                :class="activeTab === 'perspectives' ? 'border-indigo-600 text-indigo-700 font-bold bg-white' : 'border-transparent text-slate-500 hover:text-slate-800 font-semibold'"
                class="py-2.5 px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition rounded-t-xl shrink-0"
            >
                <i class="fa-solid fa-images text-xs text-indigo-500"></i>
                <span>Kho Phối Cảnh & Tiện Ích</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold" :class="activeTab === 'perspectives' ? 'bg-indigo-100 text-indigo-800' : 'bg-slate-100 text-slate-600'" x-text="perspectiveCount">{{ $perspectives->count() }}</span>
            </button>

            <!-- Tab 4: Mặt bằng tầng -->
            <button 
                type="button" 
                @click="activeTab = 'floorplans'"
                :class="activeTab === 'floorplans' ? 'border-blue-600 text-blue-600 font-bold bg-white' : 'border-transparent text-slate-500 hover:text-slate-800 font-semibold'"
                class="py-2.5 px-4 border-b-2 text-xs sm:text-sm flex items-center gap-2 transition rounded-t-xl shrink-0"
            >
                <i class="fa-solid fa-map text-xs"></i>
                <span>Mặt bằng Tầng</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-bold" :class="activeTab === 'floorplans' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-600'" x-text="floorplanCount">{{ $floorplans->count() }}</span>
            </button>
        </div>

        <div class="pb-1.5 shrink-0 flex items-center gap-2">
            <!-- Nút Tab Phôi Mẫu -->
            <button 
                type="button" 
                x-show="activeTab === 'templates'"
                @click="openNewTemplateModal()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold shadow-xs transition"
            >
                <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                <span>+ Tải lên Phôi Mới & Cài Đặt Vị Trí</span>
            </button>

            <!-- Nút Tab Phối Cảnh -->
            <button 
                type="button" 
                x-show="activeTab === 'perspectives'"
                @click="perspectiveModalOpen = true"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition"
            >
                <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                <span>+ Tải lên Ảnh Phối Cảnh</span>
            </button>

            <!-- Nút Tab Mặt Bằng -->
            <button 
                type="button" 
                x-show="activeTab === 'floorplans'"
                @click="floorplanModalOpen = true"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-xs transition"
            >
                <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                <span>+ Tải lên mặt bằng mới</span>
            </button>
        </div>
    </div>

    <!-- ==================== TAB 1: DANH SÁCH CHỈ CĂN ==================== -->
    <div x-show="activeTab === 'units'" class="space-y-3.5">

        <!-- Filter & Search Toolbar (Thu gọn nhỏ gọn - Tự động Lọc & Tìm kiếm qua AJAX) -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-3">
            <form 
                x-ref="filterForm" 
                @submit.prevent="applyFilters()" 
                method="GET" 
                action="{{ route('admin.unit-locators.project', $project->id) }}"
            >
                <input type="hidden" name="per_page" value="100">
                <input type="hidden" name="tab" value="units">

                <div x-data="{ showAdvanced: false }" class="flex flex-wrap items-center gap-2">
                    <!-- Search Keyword -->
                    <div class="relative w-full sm:w-64">
                        <i class="fa-solid fa-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                        <input 
                            type="text" 
                            name="search" 
                            value="{{ request('search') }}" 
                            @input.debounce.350ms="applyFilters()"
                            placeholder="Mã căn..." 
                            class="w-full pl-9 pr-3 py-1.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50"
                        >
                    </div>

                    <!-- Toggle Advanced Filters -->
                    <button 
                        type="button" 
                        @click="showAdvanced = !showAdvanced" 
                        class="py-1.5 px-3 rounded-lg border border-slate-200 bg-white text-slate-600 font-semibold text-xs hover:bg-slate-50 transition"
                    >
                        Bộ lọc <i class="fa-solid" :class="showAdvanced ? 'fa-chevron-up' : 'fa-chevron-down'"></i>
                    </button>

                    <!-- Advanced Filters (Collapsible) -->
                    <div x-show="showAdvanced" class="flex flex-wrap gap-2 items-center" x-cloak>
                        <!-- Unit type filter -->
                        <div>
                            <select name="unit_type_group" @change="applyFilters()" class="py-1.5 px-2.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50 font-semibold">
                                <option value="">-- Loại căn --</option>
                                <option value="studio" {{ request('unit_type_group') === 'studio' ? 'selected' : '' }}>Studio</option>
                                @foreach([1, 2, 3, 4, 5] as $bedroomCount)
                                <option value="{{ $bedroomCount }}" {{ request('unit_type_group') === (string) $bedroomCount ? 'selected' : '' }}>{{ $bedroomCount }}N</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Direction Filter -->
                        <div>
                            <select name="direction" @change="applyFilters()" class="py-1.5 px-2.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50">
                                <option value="">-- Tất cả Hướng --</option>
                                @foreach(['Đông Nam', 'Đông Bắc', 'Tây Nam', 'Tây Bắc', 'Đông', 'Tây', 'Nam', 'Bắc'] as $dir)
                                <option value="{{ $dir }}" {{ request('direction') === $dir ? 'selected' : '' }}>{{ $dir }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <select name="status" @change="applyFilters()" class="py-1.5 px-2.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50">
                                <option value="">-- Trạng thái --</option>
                                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Còn trống</option>
                                <option value="reserved" {{ request('status') === 'reserved' ? 'selected' : '' }}>Đang giữ chỗ</option>
                                <option value="deposited" {{ request('status') === 'deposited' ? 'selected' : '' }}>Đã cọc</option>
                                <option value="sold" {{ request('status') === 'sold' ? 'selected' : '' }}>Đã bán</option>
                                <option value="locked" {{ request('status') === 'locked' ? 'selected' : '' }}>Tạm khóa</option>
                            </select>
                        </div>

                        <!-- Block Filter -->
                        @if(!empty($existingBlocks) && count($existingBlocks) > 0)
                        <div>
                            <select name="block" @change="applyFilters()" class="py-1.5 px-2.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50 font-semibold">
                                <option value="">-- Tòa --</option>
                                @foreach($existingBlocks as $bl)
                                <option value="{{ $bl }}" {{ request('block') === $bl ? 'selected' : '' }}>{{ $bl }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Floor Filter -->
                        @if(!empty($existingFloors) && count($existingFloors) > 0)
                        <div>
                            <select name="floor" @change="applyFilters()" class="py-1.5 px-2.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50 font-semibold">
                                <option value="">-- Tầng --</option>
                                @foreach($existingFloors as $fl)
                                <option value="{{ $fl }}" {{ request('floor') === $fl ? 'selected' : '' }}>Tầng {{ $fl }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Axis Filter -->
                        @if(!empty($existingAxes) && count($existingAxes) > 0)
                        <div>
                            <select name="unit_axis" @change="applyFilters()" class="py-1.5 px-2.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50 font-semibold">
                                <option value="">-- Trục căn --</option>
                                @foreach($existingAxes as $ax)
                                <option value="{{ $ax }}" {{ request('unit_axis') === $ax ? 'selected' : '' }}>Trục {{ $ax }}</option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        <!-- Sort Order -->
                        <div>
                            <select name="sort" @change="applyFilters()" class="py-1.5 px-2.5 rounded-lg border border-slate-200 text-xs focus:outline-none focus:ring-2 focus:ring-blue-500 bg-slate-50/50">
                                <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Mới nhất</option>
                                <option value="code_asc" {{ request('sort') === 'code_asc' ? 'selected' : '' }}>Mã căn A-Z</option>
                                <option value="axis_asc" {{ request('sort') === 'axis_asc' ? 'selected' : '' }}>Trục căn (Tăng dần)</option>
                                <option value="axis_desc" {{ request('sort') === 'axis_desc' ? 'selected' : '' }}>Trục căn (Giảm dần)</option>
                                <option value="area_asc" {{ request('sort') === 'area_asc' ? 'selected' : '' }}>Diện tích tăng dần</option>
                                <option value="area_desc" {{ request('sort') === 'area_desc' ? 'selected' : '' }}>Diện tích giảm dần</option>
                                <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Giá tăng dần</option>
                                <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Giá giảm dần</option>
                            </select>
                        </div>
                    </div>

                    <!-- Action Submit & View Toggle -->
                    <div class="flex items-center gap-1.5">
                        <button type="submit" class="flex-1 py-1.5 px-3 rounded-lg bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition shadow-xs flex items-center justify-center gap-1.5">
                            <template x-if="isLoadingUnits">
                                <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                            </template>
                            <span>Lọc</span>
                        </button>
                        <button 
                            type="button" 
                            @click="resetFilters()" 
                            class="p-1.5 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition" 
                            title="Đặt lại bộ lọc"
                        >
                            <i class="fa-solid fa-rotate-left text-xs" :class="isLoadingUnits ? 'fa-spin' : ''"></i>
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Units Container (Cập nhật mượt mà qua AJAX) -->
        <div id="units-list-container" class="relative transition-opacity duration-200" :class="isLoadingUnits ? 'opacity-40 pointer-events-none' : 'opacity-100'">
            @include('admin.unit-locators.partials.units-list')
        </div>

    </div> <!-- End Tab 1: Units -->

    <!-- ==================== TAB 2: CẤU HÌNH PHÔI CHỈ CĂN ==================== -->
    <div x-show="activeTab === 'templates'" class="space-y-4">
        @if ($templates->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
            <div class="w-20 h-20 rounded-3xl bg-amber-50 text-amber-500 mx-auto flex items-center justify-center text-3xl mb-4">
                <i class="fa-solid fa-palette"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800">Dự án này chưa có Phôi Chỉ Căn riêng</h3>
            <p class="text-sm text-slate-500 max-w-lg mx-auto mt-2">
                Mỗi dự án có thể sở hữu nhiều phôi thiết kế riêng. Hãy tải lên ảnh phôi nền và dùng <strong>Trình Cài Đặt Vị Trí</strong> để kéo thả các ô ảnh (bóc mái 3D, mặt bằng tầng, tiện ích) & khối chữ thông số!
            </p>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                <button 
                    type="button" 
                    @click="openNewTemplateModal()" 
                    class="px-5 py-2.5 rounded-xl bg-amber-600 text-white font-bold text-sm hover:bg-amber-700 transition shadow-lg shadow-amber-500/25 flex items-center gap-2"
                >
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Tải lên Phôi Mới & Cài Đặt Vị Trí</span>
                </button>
                <button 
                    type="button" 
                    @click="createDefaultPresetTemplate()" 
                    class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-sm hover:bg-slate-200 transition flex items-center gap-2"
                >
                    <i class="fa-solid fa-wand-magic-sparkles text-amber-600"></i>
                    <span>Kích hoạt Phôi Mẫu 6 Ô Chuẩn (Indochine)</span>
                </button>
            </div>
        </div>
        @else
        <!-- Templates List Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($templates as $tmpl)
            <div id="template-card-{{ $tmpl->id }}" data-template-id="{{ $tmpl->id }}" class="bg-white rounded-2xl border border-slate-200/80 hover:border-amber-400 overflow-hidden shadow-xs hover:shadow-xl transition-all duration-300 flex flex-col justify-between group">
                <div>
                    <!-- Template Image Preview with overlay slot badges -->
                    <div class="relative h-64 bg-slate-900 overflow-hidden cursor-pointer" @click="openTemplateStudio({{ json_encode($tmpl) }})">
                        <img 
                            src="{{ $tmpl->image_url }}" 
                            alt="{{ $tmpl->name }}" 
                            class="w-full h-full object-cover group-hover:scale-105 transition duration-500"
                            onerror="this.onerror=null; this.src='{{ route('admin.unit-locators.project-image', 'default.jpg') }}';"
                        >
                        <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-black/20"></div>

                        @if ($tmpl->is_default)
                        <div class="absolute top-3 right-3">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-black bg-amber-500 text-white shadow-md">
                                <i class="fa-solid fa-star text-[10px]"></i>
                                <span>Phôi Mặc Định</span>
                            </span>
                        </div>
                        @endif

                        <div class="absolute bottom-3 left-3 right-3 text-white">
                            <span class="text-[11px] text-amber-300 font-bold block uppercase tracking-wider">
                                Kích thước: {{ $tmpl->width }} x {{ $tmpl->height }}px
                            </span>
                            <h4 class="text-lg font-black text-white drop-shadow-md truncate">
                                {{ $tmpl->name }}
                            </h4>
                        </div>
                    </div>

                    <!-- Slots Summary Info -->
                    <div class="p-4 space-y-2.5">
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span class="font-semibold flex items-center gap-1.5">
                                <i class="fa-regular fa-image text-amber-500"></i>
                                <span>Số khung ảnh đã chia:</span>
                            </span>
                            <strong class="text-slate-800 bg-slate-100 px-2 py-0.5 rounded-md font-bold">
                                {{ count($tmpl->slots_config['image_slots'] ?? []) }} khung ảnh
                            </strong>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-600">
                            <span class="font-semibold flex items-center gap-1.5">
                                <i class="fa-solid fa-font text-blue-500"></i>
                                <span>Khối thông số chữ:</span>
                            </span>
                            <strong class="text-slate-800 bg-slate-100 px-2 py-0.5 rounded-md font-bold">
                                {{ count($tmpl->slots_config['text_slots'] ?? []) }} vị trí text
                            </strong>
                        </div>
                    </div>
                </div>

                <!-- Template Bottom Toolbar -->
                <div class="p-4 pt-0 border-t border-slate-100 mt-2">
                    <div class="grid grid-cols-2 gap-2 pt-3">
                        <button 
                            type="button" 
                            @click="openTemplateStudio({{ json_encode($tmpl) }})"
                            class="py-2 px-3 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-xs transition flex items-center justify-center gap-1.5 border border-amber-200/70"
                        >
                            <i class="fa-solid fa-sliders text-xs"></i>
                            <span>Cài đặt vị trí</span>
                        </button>

                        <a 
                            href="{{ route('admin.unit-locators.create', ['projectId' => $project->id, 'template_id' => $tmpl->id]) }}"
                            class="py-2 px-3 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-xs"
                        >
                            <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                            <span>Tạo chỉ căn</span>
                        </a>
                    </div>

                    <div class="flex items-center justify-between mt-2 pt-2 border-t border-slate-50 text-[11px]">
                        @if (!$tmpl->is_default)
                        <form action="{{ route('admin.unit-locators.templates.update', $tmpl->id) }}" method="POST">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="is_default" value="1">
                            <input type="hidden" name="slots_config" value="{{ json_encode($tmpl->slots_config) }}">
                            <button type="submit" class="text-slate-400 hover:text-amber-600 transition">
                                Đặt làm mặc định
                            </button>
                        </form>
                        @else
                        <span class="text-amber-600 font-semibold flex items-center gap-1">
                            <i class="fa-solid fa-check text-[10px]"></i> Đang dùng làm mặc định
                        </span>
                        @endif

                        <button 
                            type="button" 
                            @click="deleteTemplate({{ $tmpl->id }}, '{{ addslashes($tmpl->name) }}')"
                            class="text-rose-400 hover:text-rose-600 transition"
                        >
                            Xóa phôi
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- ==================== TAB 3: KHO ẢNH PHỐI CẢNH & TIỆN ÍCH ==================== -->
    <div x-show="activeTab === 'perspectives'" class="space-y-4">
        <!-- Category Filter Pills & Upload Action -->
        <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-3 flex flex-wrap items-center justify-between gap-3">
            <div class="flex flex-wrap items-center gap-1.5">
                <button 
                    type="button" 
                    @click="perspectiveCategory = 'all'"
                    :class="perspectiveCategory === 'all' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-lg text-xs transition"
                >
                    Tất cả ({{ $perspectives->count() }})
                </button>
                <button 
                    type="button" 
                    @click="perspectiveCategory = 'layout_3d'"
                    :class="perspectiveCategory === 'layout_3d' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-lg text-xs transition"
                >
                    Bóc mái 3D ({{ $perspectives->where('category', 'layout_3d')->count() }})
                </button>
                <button 
                    type="button" 
                    @click="perspectiveCategory = 'perspective'"
                    :class="perspectiveCategory === 'perspective' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-lg text-xs transition"
                >
                    Phối cảnh tòa & tổng thể ({{ $perspectives->where('category', 'perspective')->count() }})
                </button>
                <button 
                    type="button" 
                    @click="perspectiveCategory = 'amenity'"
                    :class="perspectiveCategory === 'amenity' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-lg text-xs transition"
                >
                    Tiện ích nội khu ({{ $perspectives->where('category', 'amenity')->count() }})
                </button>
                <button 
                    type="button" 
                    @click="perspectiveCategory = 'other'"
                    :class="perspectiveCategory === 'other' ? 'bg-indigo-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200'"
                    class="px-3 py-1.5 rounded-lg text-xs transition"
                >
                    Khác ({{ $perspectives->where('category', 'other')->count() }})
                </button>
            </div>

            <button 
                type="button" 
                @click="perspectiveModalOpen = true"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold shadow-xs transition"
            >
                <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                <span>+ Tải lên ảnh mới</span>
            </button>
        </div>

        @if ($perspectives->isEmpty())
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-12 text-center">
            <div class="w-20 h-20 rounded-3xl bg-indigo-50 text-indigo-500 mx-auto flex items-center justify-center text-3xl mb-4">
                <i class="fa-solid fa-images"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800">Kho ảnh phối cảnh đang trống</h3>
            <p class="text-sm text-slate-500 max-w-md mx-auto mt-2">
                Hãy tải lên các ảnh phối cảnh (bóc mái 3D, phối cảnh tòa nhà, công viên, hồ bơi) để khi tạo chỉ căn có thể kéo thả nhanh vào phôi!
            </p>
            <div class="mt-6">
                <button 
                    type="button" 
                    @click="perspectiveModalOpen = true" 
                    class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-bold text-sm hover:bg-indigo-700 transition shadow-lg shadow-indigo-500/25 flex items-center gap-2 mx-auto"
                >
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Tải lên ảnh phối cảnh ngay</span>
                </button>
            </div>
        </div>
        @else
        <!-- Perspectives Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3.5">
            @foreach ($perspectives as $p)
            <div 
                id="perspective-card-{{ $p->id }}"
                data-perspective-id="{{ $p->id }}"
                x-show="perspectiveCategory === 'all' || perspectiveCategory === '{{ $p->category }}'"
                class="bg-white rounded-xl border border-slate-200/80 hover:border-indigo-400 overflow-hidden shadow-xs hover:shadow-md transition group flex flex-col justify-between"
            >
                <div class="relative aspect-video bg-slate-900 overflow-hidden cursor-pointer" @click="openLightbox('{{ $p->image_url }}', '{{ $p->name }}', null)">
                    <img 
                        src="{{ $p->image_url }}" 
                        alt="{{ $p->name }}" 
                        class="w-full h-full object-cover group-hover:scale-105 transition duration-300"
                        onerror="this.onerror=null; this.src='{{ route('admin.unit-locators.project-image', 'default.jpg') }}';"
                    >
                    <div class="absolute top-1.5 left-1.5">
                        <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-black/60 text-white backdrop-blur-xs">
                            {{ $p->category_label }}
                        </span>
                    </div>
                </div>
                <div class="p-2.5 flex items-center justify-between gap-2 border-t border-slate-100">
                    <span class="text-xs font-bold text-slate-800 truncate" title="{{ $p->name }}">
                        {{ $p->name }}
                    </span>
                    <button 
                        type="button" 
                        @click="deletePerspective({{ $p->id }}, '{{ addslashes($p->name) }}')"
                        class="text-slate-400 hover:text-rose-600 p-1 transition" 
                        title="Xóa ảnh"
                    >
                        <i class="fa-regular fa-trash-can text-xs"></i>
                    </button>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    <!-- ==================== TAB 4: DANH SÁCH MẶT BẰNG TẦNG ==================== -->
    <div x-show="activeTab === 'floorplans'" class="space-y-4">

        <!-- Empty state khi không còn mặt bằng nào -->
        <div x-show="floorplanCount === 0" class="bg-white rounded-2xl border border-slate-100 shadow-sm p-16 text-center" style="{{ $floorplans->isEmpty() ? '' : 'display: none;' }}">
            <div class="w-20 h-20 rounded-3xl bg-blue-50 text-blue-500 mx-auto flex items-center justify-center text-3xl mb-4">
                <i class="fa-solid fa-map"></i>
            </div>
            <h3 class="text-xl font-bold text-slate-800">Chưa có sơ đồ mặt bằng nào</h3>
            <p class="text-sm text-slate-500 max-w-md mx-auto mt-2">
                Hãy tải lên ảnh mặt bằng tầng điển hình (layout tổng thể / tầng) để bắt đầu đánh dấu chỉ căn!
            </p>
            <div class="mt-6">
                <button type="button" @click="floorplanModalOpen = true" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-bold text-sm hover:bg-blue-700 transition shadow-lg shadow-blue-500/25 flex items-center gap-2 mx-auto">
                    <i class="fa-solid fa-cloud-arrow-up"></i>
                    <span>Tải lên mặt bằng ngay</span>
                </button>
            </div>
        </div>

        <!-- Floorplans Table View (Mỗi mặt bằng 1 dòng, tối ưu không xuống dòng) -->
        <div x-show="floorplanCount > 0" class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden" style="{{ $floorplans->isEmpty() ? 'display: none;' : '' }}">
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                    <thead>
                        <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                            <th class="py-2.5 px-3.5 w-12 text-center">STT</th>
                            <th class="py-2.5 px-3.5 w-14 text-center">Ảnh mặt bằng</th>
                            <th class="py-2.5 px-3.5">Tên mặt bằng</th>
                            <th class="py-2.5 px-3.5">Tòa / Phân khu</th>
                            <th class="py-2.5 px-3.5">Khoảng tầng</th>
                            <th class="py-2.5 px-3.5">Ngày tải lên</th>
                            <th class="py-2.5 px-3.5 text-right">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody id="floorplanTableBody" class="divide-y divide-slate-100">
                        @foreach ($floorplans as $index => $fp)
                        <tr id="floorplan-row-{{ $fp->id }}" data-floorplan-id="{{ $fp->id }}" class="hover:bg-blue-50/30 transition duration-150">
                            <!-- STT -->
                            <td class="floorplan-stt py-2 px-3.5 text-center font-bold text-slate-400">
                                {{ $index + 1 }}
                            </td>

                            <!-- Ảnh Thumbnail -->
                            <td class="py-2 px-3.5 text-center">
                                <div 
                                    class="w-10 h-10 rounded-lg overflow-hidden bg-slate-900 border border-slate-200 mx-auto shrink-0 shadow-xs cursor-pointer relative group/fp"
                                    @click="openLightbox('{{ $fp->image_url }}', '{{ $fp->name }}', null)"
                                    title="Xem ảnh mặt bằng lớn"
                                >
                                    <img 
                                        src="{{ $fp->image_url }}" 
                                        alt="{{ $fp->name }}" 
                                        class="w-full h-full object-cover group-hover/fp:scale-110 transition duration-300"
                                        onerror="this.onerror=null; this.src='{{ route('admin.unit-locators.project-image', 'default.jpg') }}';"
                                    >
                                    <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover/fp:opacity-100 transition">
                                        <i class="fa-solid fa-magnifying-glass text-white text-[10px]"></i>
                                    </div>
                                </div>
                            </td>

                            <!-- Tên mặt bằng -->
                            <td class="py-2 px-3.5">
                                <button 
                                    type="button"
                                    @click="openLightbox('{{ $fp->image_url }}', '{{ $fp->name }}', null)"
                                    class="font-black text-xs text-slate-900 hover:text-blue-600 transition text-left block"
                                >
                                    {{ $fp->name }}
                                </button>
                                @if($fp->description)
                                <span class="text-[10px] text-slate-400 block truncate max-w-xs">{{ $fp->description }}</span>
                                @endif
                            </td>

                            <!-- Tòa / Phân khu -->
                            <td class="py-2 px-3.5">
                                <span class="font-semibold text-slate-700">{{ $fp->block ?: 'Toàn dự án' }}</span>
                            </td>

                            <!-- Khoảng tầng -->
                            <td class="py-2 px-3.5">
                                @if($fp->floor_range)
                                <span class="px-2 py-0.5 rounded font-bold text-xs bg-slate-100 text-slate-700 border border-slate-200">
                                    {{ $fp->floor_range }}
                                </span>
                                @else
                                <span class="text-slate-400">—</span>
                                @endif
                            </td>

                            <!-- Ngày tải lên -->
                            <td class="py-2 px-3.5 text-slate-500 font-medium">
                                {{ $fp->created_at ? $fp->created_at->format('d/m/Y H:i') : '—' }}
                            </td>

                            <!-- Thao tác -->
                            <td class="py-2 px-3.5 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <!-- Nút Tạo chỉ căn từ mặt bằng này -->
                                    <a 
                                        href="{{ route('admin.unit-locators.create', ['projectId' => $project->id, 'floorplan_url' => $fp->image_url]) }}" 
                                        class="inline-flex items-center gap-1 py-1 px-2.5 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition shadow-xs"
                                        title="Mở Studio vẽ chỉ căn trên mặt bằng này"
                                    >
                                        <i class="fa-solid fa-wand-magic-sparkles text-[10px]"></i>
                                        <span>Tạo chỉ căn</span>
                                    </a>

                                    <!-- Nút Xem lớn -->
                                    <button 
                                        type="button" 
                                        @click="openLightbox('{{ $fp->image_url }}', '{{ $fp->name }}', null)"
                                        class="p-1.5 rounded-lg bg-slate-50 hover:bg-blue-50 text-slate-600 hover:text-blue-600 transition"
                                        title="Xem ảnh lớn"
                                    >
                                        <i class="fa-solid fa-eye text-xs"></i>
                                    </button>

                                    <!-- Nút Tải ảnh -->
                                    <a 
                                        href="{{ $fp->image_url }}" 
                                        download="{{ Str::slug($fp->name) }}.png"
                                        class="p-1.5 rounded-lg bg-slate-50 hover:bg-emerald-50 text-slate-600 hover:text-emerald-600 transition"
                                        title="Tải ảnh mặt bằng gốc"
                                    >
                                        <i class="fa-solid fa-download text-xs"></i>
                                    </a>

                                    <!-- Nút Xóa (Ajax) -->
                                    <button 
                                        type="button" 
                                        @click="deleteFloorplan({{ $fp->id }}, '{{ addslashes($fp->name) }}')"
                                        class="p-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-600 transition" 
                                        title="Xóa mặt bằng này"
                                    >
                                        <i class="fa-regular fa-trash-can text-xs"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div> <!-- End Tab 2: Floorplans -->

    <!-- LIGHTBOX MODAL: Xem ảnh chỉ căn kích thước lớn (Giao diện sáng, mờ tinh tế) -->
    <div 
        x-show="lightboxOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4 sm:p-6 transition-all"
        @keydown.escape.window="lightboxOpen = false"
        @click.self="lightboxOpen = false"
    >
        <div class="relative max-w-5xl w-full max-h-[92vh] flex flex-col bg-white rounded-3xl overflow-hidden border border-slate-200/90 shadow-2xl">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-white">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center font-bold text-sm shrink-0">
                        <i :class="currentLightbox.unit ? 'fa-solid fa-map-pin' : 'fa-solid fa-map'"></i>
                    </div>
                    <div>
                        <h4 class="text-slate-900 font-extrabold text-base" x-text="currentLightbox.unit ? 'Chỉ căn ' + currentLightbox.code : currentLightbox.code"></h4>
                        <span class="text-xs text-slate-400">{{ $project->name }}</span>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <a 
                        :href="currentLightbox.image" 
                        :download="'Chi-can-' + currentLightbox.code + '.png'"
                        class="px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold transition flex items-center gap-1.5 shadow-sm"
                        title="Tải ảnh về máy"
                    >
                        <i class="fa-solid fa-download"></i>
                        <span>Tải ảnh gốc</span>
                    </a>
                    <button 
                        @click="lightboxOpen = false" 
                        class="w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition"
                        title="Đóng (Esc)"
                    >
                        <i class="fa-solid fa-xmark text-base"></i>
                    </button>
                </div>
            </div>

            <!-- Image Viewport -->
            <div class="flex-1 overflow-auto p-4 flex items-center justify-center bg-slate-50/70 min-h-[350px]">
                <img 
                    :src="currentLightbox.image" 
                    :alt="currentLightbox.code" 
                    class="max-w-full max-h-[72vh] object-contain rounded-2xl shadow-sm border border-slate-200/60 bg-white"
                >
            </div>

            <!-- Unit Info Footer Bar in Lightbox -->
            <div class="px-6 py-3.5 bg-white border-t border-slate-100 flex flex-wrap items-center justify-between text-xs text-slate-600 gap-3">
                <div class="flex flex-wrap items-center gap-2.5">
                    <span x-show="currentLightbox.unit?.area" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-50 text-amber-800 border border-amber-200/60 font-semibold">
                        <span class="text-slate-400">Diện tích:</span> 
                        <strong class="text-amber-700" x-text="currentLightbox.unit?.area + ' m²'"></strong>
                    </span>
                    <span x-show="currentLightbox.unit?.direction" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 border border-blue-200/60 font-semibold">
                        <span class="text-slate-400">Hướng:</span> 
                        <strong class="text-blue-700" x-text="currentLightbox.unit?.direction"></strong>
                    </span>
                    <span x-show="currentLightbox.unit?.block" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 font-semibold">
                        <span class="text-slate-400">Tòa:</span> 
                        <strong class="text-slate-800" x-text="currentLightbox.unit?.block"></strong>
                    </span>
                    <span x-show="currentLightbox.unit?.floor" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 border border-slate-200 font-semibold">
                        <span class="text-slate-400">Tầng:</span> 
                        <strong class="text-slate-800" x-text="currentLightbox.unit?.floor"></strong>
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- EDIT MODAL: Sửa thông tin chỉ căn -->
    <div 
        x-show="editModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4"
        @keydown.escape.window="editModalOpen = false"
        @click.self="editModalOpen = false"
    >
        <div class="relative max-w-xl w-full bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-2xl">
            <form :action="'{{ url('admin/unit-locators') }}/' + editingUnit.id" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')

                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                            <i class="fa-solid fa-pen-to-square"></i>
                        </div>
                        <h4 class="font-bold text-slate-800 text-base" x-text="'Chỉnh sửa chỉ căn: ' + editingUnit.unit_code"></h4>
                    </div>
                    <div class="flex items-center gap-2">
                        <a 
                            :href="'{{ route('admin.unit-locators.create', $project->id) }}?units=' + editingUnit.id"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-bold transition border border-indigo-200 shadow-2xs"
                            title="Mở Studio để chỉnh sửa ảnh chỉ căn này"
                        >
                            <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                            <span>Vào Studio sửa ảnh</span>
                        </a>
                        <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                            <i class="fa-solid fa-xmark text-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Mã căn <span class="text-rose-500">*</span></label>
                            <input type="text" name="unit_code" x-model="editingUnit.unit_code" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Trạng thái</label>
                            <select name="status" x-model="editingUnit.status" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="available">Còn trống</option>
                                <option value="reserved">Đang giữ chỗ</option>
                                <option value="deposited">Đã đặt cọc</option>
                                <option value="sold">Đã bán</option>
                                <option value="locked">Tạm khóa</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Diện tích thông thủy (m²)</label>
                            <input type="number" step="0.01" name="area" x-model="editingUnit.area" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Diện tích tim tường (m²)</label>
                            <input type="number" step="0.01" name="area_gross" x-model="editingUnit.area_gross" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Hướng ban công / chính</label>
                            <select name="direction" x-model="editingUnit.direction" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Chọn hướng --</option>
                                <option value="Đông Nam">Đông Nam</option>
                                <option value="Đông Bắc">Đông Bắc</option>
                                <option value="Tây Nam">Tây Nam</option>
                                <option value="Tây Bắc">Tây Bắc</option>
                                <option value="Đông">Đông</option>
                                <option value="Tây">Tây</option>
                                <option value="Nam">Nam</option>
                                <option value="Bắc">Bắc</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Hướng cửa chính</label>
                            <input type="text" name="door_direction" x-model="editingUnit.door_direction" placeholder="Ví dụ: Tây Nam" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tòa / Block</label>
                            <input type="text" name="block" x-model="editingUnit.block" placeholder="Tòa A" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tầng</label>
                            <input type="text" name="floor" x-model="editingUnit.floor" placeholder="12" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Trục căn</label>
                            <input type="text" name="unit_axis" x-model="editingUnit.unit_axis" placeholder="05, 08A..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Số phòng ngủ</label>
                            <input type="number" name="bedroom" x-model="editingUnit.bedroom" min="0" max="10" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Giá hiển thị</label>
                            <input type="text" name="price_text" x-model="editingUnit.price_text" placeholder="Ví dụ: 3.5 Tỷ" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Số WC</label>
                            <input type="number" name="bathroom" x-model="editingUnit.bathroom" min="0" max="10" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Thay đổi ảnh chỉ căn (Tùy chọn)</label>
                        <input type="file" name="image_file" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                    </div>

                    <!-- Ảnh bóc mái 3D -->
                    <div class="p-3.5 rounded-2xl bg-indigo-50/50 border border-indigo-100 space-y-2.5">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-extrabold text-indigo-900">
                                <i class="fa-solid fa-cube text-indigo-500 mr-1"></i>
                                Ảnh bóc mái 3D
                            </label>
                            <template x-if="editingUnit.layout_3d_image_url">
                                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                    Đã có ảnh
                                </span>
                            </template>
                        </div>

                        <!-- Current 3D preview if any -->
                        <template x-if="editingUnit.layout_3d_image_url">
                            <div class="flex items-center gap-3 p-2 bg-white rounded-xl border border-indigo-100">
                                <img :src="editingUnit.layout_3d_image_url" alt="Bóc mái" class="w-12 h-12 object-cover rounded-lg border border-slate-200 shrink-0">
                                <div class="flex-1 min-w-0">
                                    <div class="text-xs font-bold text-slate-800 truncate" x-text="'Bóc mái căn ' + editingUnit.unit_code"></div>
                                    <button 
                                        type="button" 
                                        @click="openLightbox(editingUnit.layout_3d_image_url, 'Bóc mái ' + editingUnit.unit_code, editingUnit)"
                                        class="text-[11px] text-blue-600 hover:underline font-semibold"
                                    >
                                        Xem ảnh phóng to
                                    </button>
                                </div>
                                <label class="inline-flex items-center gap-1.5 text-xs text-rose-600 cursor-pointer">
                                    <input type="checkbox" name="remove_layout_3d" value="1" class="rounded text-rose-600">
                                    <span>Xóa ảnh</span>
                                </label>
                            </div>
                        </template>

                        <div class="space-y-1">
                            <span class="text-[11px] text-slate-500 font-medium block">Tải ảnh bóc mái mới từ máy tính:</span>
                            <input type="file" name="layout_3d_file" accept="image/*" class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-100 file:text-indigo-700 hover:file:bg-indigo-200">
                        </div>

                        <!-- Sync axis option -->
                        <div class="pt-1 border-t border-indigo-100">
                            <label class="inline-flex items-start gap-2 cursor-pointer">
                                <input type="checkbox" name="sync_axis_layout_3d" value="1" checked class="mt-0.5 w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                                <div class="text-xs">
                                    <span class="font-bold text-slate-800">Đồng bộ cho tất cả căn cùng trục</span>
                                    <template x-if="editingUnit.unit_axis">
                                        <span class="font-black text-indigo-700" x-text="' (' + editingUnit.unit_axis + ')'"></span>
                                    </template>
                                    <p class="text-[11px] text-slate-500">Tự động áp dụng ảnh bóc mái này cho các căn hộ cùng trục căn.</p>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Ghi chú / Điểm nổi bật</label>
                        <textarea name="note" x-model="editingUnit.note" rows="2" placeholder="Ví dụ: Căn góc 2 mặt thoáng, view hồ điều hòa..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500"></textarea>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-100">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-blue-600 text-white text-sm font-bold hover:bg-blue-700 shadow-md">
                        Cập nhật chỉ căn
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== QUICK BÓC MÁI 3D MODAL (AJAX, KHÔNG LOAD TRANG) ==================== -->
    <div 
        x-show="quickBocMaiModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4"
        @keydown.escape.window="closeQuickBocMaiModal()"
        @click.self="closeQuickBocMaiModal()"
    >
        <div class="relative max-w-lg w-full bg-white rounded-3xl overflow-hidden border border-slate-200/90 shadow-2xl transition-all">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-indigo-50/60 to-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-indigo-600 text-white flex items-center justify-center shadow-md shadow-indigo-500/20">
                        <i class="fa-solid fa-cube text-base"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                            <span>Ảnh bóc mái 3D</span>
                            <template x-if="activeBocMaiUnit">
                                <span class="text-xs px-2 py-0.5 rounded-md bg-indigo-100 text-indigo-700 font-black" x-text="activeBocMaiUnit.unit_code"></span>
                            </template>
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Căn hộ: <span class="font-bold text-slate-700" x-text="activeBocMaiUnit?.unit_code || '—'"></span>
                            • Trục căn: <span class="font-black text-indigo-600" x-text="activeBocMaiUnit?.unit_axis || 'Chưa đặt'"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="closeQuickBocMaiModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                <!-- Preview current image if existing -->
                <template x-if="activeBocMaiUnit && activeBocMaiUnit.layout_3d_image_url">
                    <div class="p-3 bg-indigo-50/40 rounded-2xl border border-indigo-100 flex items-center gap-3.5">
                        <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-900 border border-indigo-200 shrink-0 shadow-xs cursor-pointer" @click="openLightbox(activeBocMaiUnit.layout_3d_image_url, 'Bóc mái căn ' + activeBocMaiUnit.unit_code, activeBocMaiUnit)">
                            <img :src="activeBocMaiUnit.layout_3d_image_url" alt="Bóc mái hiện tại" class="w-full h-full object-cover hover:scale-105 transition">
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-indigo-100 text-indigo-800 mb-1">
                                Ảnh bóc mái hiện tại
                            </span>
                            <div class="text-xs text-slate-600 truncate font-medium">Bấm ảnh để xem kích thước lớn</div>
                        </div>
                        <button 
                            type="button" 
                            @click="deleteCurrentBocMai()" 
                            class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs transition border border-rose-200/80"
                            title="Xóa ảnh bóc mái của căn này"
                        >
                            <i class="fa-regular fa-trash-can mr-1"></i> Xóa ảnh
                        </button>
                    </div>
                </template>

                <!-- Source Selector Tabs -->
                <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                    <button 
                        type="button" 
                        @click="bocMaiSourceTab = 'upload'" 
                        :class="bocMaiSourceTab === 'upload' ? 'bg-indigo-600 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'"
                        class="px-3.5 py-1.5 rounded-xl text-xs flex items-center gap-1.5 transition"
                    >
                        <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                        <span>Tải ảnh từ máy tính</span>
                    </button>
                    <button 
                        type="button" 
                        @click="bocMaiSourceTab = 'perspectives'" 
                        :class="bocMaiSourceTab === 'perspectives' ? 'bg-indigo-600 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'"
                        class="px-3.5 py-1.5 rounded-xl text-xs flex items-center gap-1.5 transition"
                    >
                        <i class="fa-solid fa-images text-xs"></i>
                        <span>Kho Phối Cảnh ({{ $perspectives->count() }})</span>
                    </button>
                </div>

                <!-- Tab 1: Upload File -->
                <div x-show="bocMaiSourceTab === 'upload'" class="space-y-3">
                    <div 
                        class="border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition"
                        :class="bocMaiFilePreview ? 'border-indigo-400 bg-indigo-50/20' : 'border-slate-300 hover:border-indigo-400 bg-slate-50/50 hover:bg-indigo-50/30'"
                        @click="$refs.quickBocMaiFileInput.click()"
                    >
                        <input 
                            x-ref="quickBocMaiFileInput" 
                            type="file" 
                            accept="image/png,image/jpeg,image/webp" 
                            class="hidden" 
                            @change="handleQuickBocMaiFile($event)"
                        >
                        <template x-if="!bocMaiFilePreview">
                            <div class="space-y-2">
                                <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 mx-auto flex items-center justify-center text-xl shadow-2xs">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <div class="text-xs font-bold text-slate-700">Bấm để chọn file ảnh bóc mái 3D từ máy tính</div>
                                <p class="text-[11px] text-slate-400">Hỗ trợ PNG, JPG, WEBP (tối đa 15MB)</p>
                            </div>
                        </template>
                        <template x-if="bocMaiFilePreview">
                            <div class="space-y-2">
                                <img :src="bocMaiFilePreview" class="max-h-48 mx-auto rounded-xl border border-indigo-200 shadow-sm object-contain bg-white">
                                <p class="text-xs font-bold text-indigo-700 flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span x-text="selectedBocMaiFileName"></span>
                                </p>
                                <span class="text-[11px] text-slate-400">Bấm để chọn ảnh khác</span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Tab 2: Choose from Kho Phối Cảnh & Tiện Ích -->
                <div x-show="bocMaiSourceTab === 'perspectives'" class="space-y-2">
                    @if ($perspectives->isEmpty())
                    <div class="p-6 text-center bg-slate-50 rounded-2xl border border-slate-200">
                        <i class="fa-solid fa-image text-slate-300 text-3xl mb-2"></i>
                        <p class="text-xs text-slate-500">Dự án này chưa có ảnh nào trong Kho Phối Cảnh.</p>
                        <p class="text-[11px] text-slate-400 mt-1">Hãy chuyển sang tab "Tải ảnh từ máy tính" ở trên.</p>
                    </div>
                    @else
                    <div class="text-xs text-slate-500 font-medium">Bấm vào 1 ảnh dưới đây để chọn làm ảnh bóc mái:</div>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5 max-h-56 overflow-y-auto p-1">
                        @foreach ($perspectives as $persp)
                        <div 
                            @click="selectPerspectiveForBocMai('{{ $persp->image_path }}', '{{ $persp->image_url }}')"
                            :class="selectedPerspectivePath === '{{ $persp->image_path }}' ? 'ring-2 ring-indigo-600 border-indigo-500 shadow-md' : 'border-slate-200 hover:border-indigo-300'"
                            class="group relative rounded-xl border bg-slate-900 overflow-hidden cursor-pointer aspect-4/3 transition-all"
                            title="{{ $persp->name }}"
                        >
                            <img src="{{ $persp->image_url }}" alt="{{ $persp->name }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex flex-col justify-end p-1.5">
                                <span class="text-[10px] font-bold text-white truncate drop-shadow-sm">{{ $persp->name }}</span>
                                @if ($persp->category === 'layout_3d')
                                <span class="text-[8px] font-black text-amber-300">Bóc mái 3D</span>
                                @endif
                            </div>
                            <template x-if="selectedPerspectivePath === '{{ $persp->image_path }}'">
                                <div class="absolute top-1 right-1 w-5 h-5 rounded-full bg-indigo-600 text-white flex items-center justify-center text-[10px] shadow-sm">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                            </template>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- Sync Axis Option Checkbox -->
                <div class="p-3.5 bg-gradient-to-r from-amber-50/60 to-indigo-50/60 rounded-2xl border border-indigo-100/80">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input 
                            type="checkbox" 
                            x-model="syncAxisBocMai" 
                            class="mt-0.5 w-4 h-4 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer"
                        >
                        <div class="text-xs">
                            <div class="font-extrabold text-slate-800 flex items-center gap-1.5">
                                <i class="fa-solid fa-arrows-rotate text-indigo-600 text-xs"></i>
                                <span>Đồng bộ cho tất cả các căn cùng trục</span>
                                <template x-if="activeBocMaiUnit && activeBocMaiUnit.unit_axis">
                                    <span class="px-1.5 py-0.2 rounded bg-indigo-600 text-white font-black text-[11px]" x-text="'Trục ' + activeBocMaiUnit.unit_axis"></span>
                                </template>
                            </div>
                            <p class="text-slate-500 text-[11px] mt-0.5 leading-relaxed">
                                Thường các căn cùng trục căn ở các tầng khác nhau có thiết kế bóc mái giống hệt nhau. Bật tùy chọn này sẽ tự động cập nhật ảnh cho toàn bộ các căn cùng trục.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between px-6 py-4 bg-slate-50 border-t border-slate-100">
                <button type="button" @click="closeQuickBocMaiModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-100 transition">
                    Đóng
                </button>
                <button 
                    type="button" 
                    @click="saveQuickBocMai()"
                    :disabled="isSavingBocMai || (!selectedBocMaiFile && !selectedPerspectivePath)"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-extrabold shadow-md shadow-indigo-500/20 transition cursor-pointer"
                >
                    <template x-if="isSavingBocMai">
                        <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                    </template>
                    <template x-if="!isSavingBocMai">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                    </template>
                    <span>Lưu ảnh bóc mái</span>
                </button>
            </div>
        </div>
    </div>

    <!-- ==================== QUICK ĐỊNH VỊ MẶT BẰNG MODAL (AJAX, KHÔNG LOAD TRANG) ==================== -->
    <div 
        x-show="quickLocatorModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4"
        @keydown.escape.window="closeQuickLocatorModal()"
        @click.self="closeQuickLocatorModal()"
    >
        <div class="relative max-w-lg w-full bg-white rounded-3xl overflow-hidden border border-slate-200/90 shadow-2xl transition-all">
            <!-- Modal Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-teal-50/60 to-white">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-teal-600 text-white flex items-center justify-center shadow-md shadow-teal-500/20">
                        <i class="fa-solid fa-map-location-dot text-base"></i>
                    </div>
                    <div>
                        <h4 class="font-extrabold text-slate-800 text-base flex items-center gap-2">
                            <span>Ảnh định vị trên mặt bằng</span>
                            <template x-if="activeLocatorUnit">
                                <span class="text-xs px-2 py-0.5 rounded-md bg-teal-100 text-teal-800 font-black" x-text="activeLocatorUnit.unit_code"></span>
                            </template>
                        </h4>
                        <p class="text-xs text-slate-500 mt-0.5">
                            Căn hộ: <span class="font-bold text-slate-700" x-text="activeLocatorUnit?.unit_code || '—'"></span>
                            • Trục căn: <span class="font-black text-teal-600" x-text="activeLocatorUnit?.unit_axis || 'Chưa đặt'"></span>
                        </p>
                    </div>
                </div>
                <button type="button" @click="closeQuickLocatorModal()" class="w-8 h-8 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-500 hover:text-slate-800 flex items-center justify-center transition">
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto">
                <!-- Preview current image if existing -->
                <template x-if="activeLocatorUnit && activeLocatorUnit.floorplan_image_url">
                    <div class="p-3 bg-teal-50/40 rounded-2xl border border-teal-100 flex items-center gap-3.5">
                        <div class="w-14 h-14 rounded-xl overflow-hidden bg-slate-900 border border-teal-200 shrink-0 shadow-xs cursor-pointer" @click="openLightbox(activeLocatorUnit.floorplan_image_url, 'Định vị MB căn ' + activeLocatorUnit.unit_code, activeLocatorUnit)">
                            <img :src="activeLocatorUnit.floorplan_image_url" alt="Định vị MB hiện tại" class="w-full h-full object-cover hover:scale-105 transition">
                        </div>
                        <div class="flex-1 min-w-0">
                            <span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold bg-teal-100 text-teal-800 mb-1">
                                Ảnh định vị MB hiện tại
                            </span>
                            <div class="text-xs text-slate-600 truncate font-medium">Bấm ảnh để xem kích thước lớn</div>
                        </div>
                        <button 
                            type="button" 
                            @click="deleteCurrentLocator()" 
                            class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 font-bold text-xs transition border border-rose-200/80"
                            title="Xóa ảnh định vị của căn này"
                        >
                            <i class="fa-regular fa-trash-can mr-1"></i> Xóa ảnh
                        </button>
                    </div>
                </template>

                <!-- Source Selector Tabs -->
                <div class="flex items-center gap-2 border-b border-slate-100 pb-2">
                    <button 
                        type="button" 
                        @click="locatorSourceTab = 'upload'" 
                        :class="locatorSourceTab === 'upload' ? 'bg-teal-600 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'"
                        class="px-3.5 py-1.5 rounded-xl text-xs flex items-center gap-1.5 transition"
                    >
                        <i class="fa-solid fa-cloud-arrow-up text-xs"></i>
                        <span>Tải ảnh từ máy tính</span>
                    </button>
                    <button 
                        type="button" 
                        @click="locatorSourceTab = 'floorplans'" 
                        :class="locatorSourceTab === 'floorplans' ? 'bg-teal-600 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'"
                        class="px-3.5 py-1.5 rounded-xl text-xs flex items-center gap-1.5 transition"
                    >
                        <i class="fa-solid fa-layer-group text-xs"></i>
                        <span>Mặt bằng Tầng ({{ $floorplans->count() }})</span>
                    </button>
                    @if ($perspectives->isNotEmpty())
                    <button 
                        type="button" 
                        @click="locatorSourceTab = 'perspectives'" 
                        :class="locatorSourceTab === 'perspectives' ? 'bg-teal-600 text-white shadow-xs font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-semibold'"
                        class="px-3.5 py-1.5 rounded-xl text-xs flex items-center gap-1.5 transition"
                    >
                        <i class="fa-solid fa-images text-xs"></i>
                        <span>Kho Phối Cảnh ({{ $perspectives->count() }})</span>
                    </button>
                    @endif
                </div>

                <!-- Tab 1: Upload File -->
                <div x-show="locatorSourceTab === 'upload'" class="space-y-3">
                    <div 
                        class="border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition"
                        :class="locatorFilePreview ? 'border-teal-400 bg-teal-50/20' : 'border-slate-300 hover:border-teal-400 bg-slate-50/50 hover:bg-teal-50/30'"
                        @click="$refs.quickLocatorFileInput.click()"
                    >
                        <input 
                            x-ref="quickLocatorFileInput" 
                            type="file" 
                            accept="image/png,image/jpeg,image/webp" 
                            class="hidden" 
                            @change="handleQuickLocatorFile($event)"
                        >
                        <template x-if="!locatorFilePreview">
                            <div class="space-y-2">
                                <div class="w-12 h-12 rounded-2xl bg-teal-50 text-teal-600 mx-auto flex items-center justify-center text-xl shadow-2xs">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <div class="text-xs font-bold text-slate-700">Bấm để chọn file ảnh định vị mặt bằng từ máy tính</div>
                                <p class="text-[11px] text-slate-400">Hỗ trợ PNG, JPG, WEBP (tối đa 15MB)</p>
                            </div>
                        </template>
                        <template x-if="locatorFilePreview">
                            <div class="space-y-2">
                                <img :src="locatorFilePreview" class="max-h-48 mx-auto rounded-xl border border-teal-200 shadow-sm object-contain bg-white">
                                <p class="text-xs font-bold text-teal-700 flex items-center justify-center gap-1.5">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span x-text="selectedLocatorFileName"></span>
                                </p>
                                <span class="text-[11px] text-slate-400">Bấm để chọn ảnh khác</span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Tab 2: Choose from Mặt bằng Tầng -->
                <div x-show="locatorSourceTab === 'floorplans'" class="space-y-2">
                    @if ($floorplans->isEmpty())
                    <div class="p-6 text-center bg-slate-50 rounded-2xl border border-slate-200">
                        <i class="fa-solid fa-layer-group text-slate-300 text-3xl mb-2"></i>
                        <p class="text-xs text-slate-500">Dự án này chưa có mặt bằng tầng nào.</p>
                        <p class="text-[11px] text-slate-400 mt-1">Hãy chuyển sang tab "Tải ảnh từ máy tính" ở trên.</p>
                    </div>
                    @else
                    <div class="text-xs text-slate-500 font-medium">Bấm vào 1 mặt bằng tầng dưới đây để chọn làm ảnh định vị:</div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-2.5 max-h-56 overflow-y-auto p-1">
                        @foreach ($floorplans as $fp)
                        <div 
                            @click="selectFloorplanForLocator('{{ $fp->image_path }}', '{{ $fp->image_url }}')"
                            :class="selectedFloorplanPath === '{{ $fp->image_path }}' ? 'ring-2 ring-teal-600 border-teal-500 shadow-md' : 'border-slate-200 hover:border-teal-300'"
                            class="group relative rounded-xl border bg-slate-900 overflow-hidden cursor-pointer aspect-4/3 transition-all"
                            title="{{ $fp->name }}"
                        >
                            <img src="{{ $fp->image_url }}" alt="{{ $fp->name }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/20 to-transparent flex flex-col justify-end p-2">
                                <span class="text-[11px] font-bold text-white truncate drop-shadow-sm">{{ $fp->name }}</span>
                                @if ($fp->floor_range)
                                <span class="text-[9px] font-semibold text-teal-300">{{ $fp->floor_range }}</span>
                                @endif
                            </div>
                            <template x-if="selectedFloorplanPath === '{{ $fp->image_path }}'">
                                <div class="absolute top-1 right-1 w-5 h-5 rounded-full bg-teal-600 text-white flex items-center justify-center text-[10px] shadow-sm">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                            </template>
                        </div>
                        @endforeach
                    </div>
                    @endif
                </div>

                <!-- Tab 3: Choose from Kho Phối Cảnh & Tiện Ích -->
                @if ($perspectives->isNotEmpty())
                <div x-show="locatorSourceTab === 'perspectives'" class="space-y-2">
                    <div class="text-xs text-slate-500 font-medium">Bấm vào 1 ảnh dưới đây để chọn:</div>
                    <div class="grid grid-cols-3 sm:grid-cols-4 gap-2.5 max-h-56 overflow-y-auto p-1">
                        @foreach ($perspectives as $persp)
                        <div 
                            @click="selectFloorplanForLocator('{{ $persp->image_path }}', '{{ $persp->image_url }}')"
                            :class="selectedFloorplanPath === '{{ $persp->image_path }}' ? 'ring-2 ring-teal-600 border-teal-500 shadow-md' : 'border-slate-200 hover:border-teal-300'"
                            class="group relative rounded-xl border bg-slate-900 overflow-hidden cursor-pointer aspect-4/3 transition-all"
                            title="{{ $persp->name }}"
                        >
                            <img src="{{ $persp->image_url }}" alt="{{ $persp->name }}" class="w-full h-full object-cover group-hover:scale-105 transition">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent flex flex-col justify-end p-1.5">
                                <span class="text-[10px] font-bold text-white truncate drop-shadow-sm">{{ $persp->name }}</span>
                            </div>
                            <template x-if="selectedFloorplanPath === '{{ $persp->image_path }}'">
                                <div class="absolute top-1 right-1 w-5 h-5 rounded-full bg-teal-600 text-white flex items-center justify-center text-[10px] shadow-sm">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                            </template>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Sync Axis Option Checkbox -->
                <div class="p-3.5 bg-gradient-to-r from-teal-50/60 to-emerald-50/60 rounded-2xl border border-teal-100/80">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input 
                            type="checkbox" 
                            x-model="syncAxisLocator" 
                            class="mt-0.5 w-4 h-4 rounded border-slate-300 text-teal-600 focus:ring-teal-500 cursor-pointer"
                        >
                        <div class="text-xs">
                            <div class="font-extrabold text-slate-800 flex items-center gap-1.5">
                                <i class="fa-solid fa-arrows-rotate text-teal-600 text-xs"></i>
                                <span>Đồng bộ cho tất cả các căn cùng trục</span>
                                <template x-if="activeLocatorUnit && activeLocatorUnit.unit_axis">
                                    <span class="px-1.5 py-0.2 rounded bg-teal-600 text-white font-black text-[11px]" x-text="'Trục ' + activeLocatorUnit.unit_axis"></span>
                                </template>
                            </div>
                            <p class="text-slate-500 text-[11px] mt-0.5 leading-relaxed">
                                Thường các căn cùng trục căn ở các tầng khác nhau có vị trí định vị trên mặt bằng giống nhau. Bật tùy chọn này sẽ tự động cập nhật ảnh định vị cho toàn bộ các căn cùng trục.
                            </p>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex items-center justify-between px-6 py-4 bg-slate-50 border-t border-slate-100">
                <button type="button" @click="closeQuickLocatorModal()" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-xs font-semibold hover:bg-slate-100 transition">
                    Đóng
                </button>
                <button 
                    type="button" 
                    @click="saveQuickLocator()"
                    :disabled="isSavingLocator || (!selectedLocatorFile && !selectedFloorplanPath)"
                    class="inline-flex items-center gap-2 px-5 py-2 rounded-xl bg-teal-600 hover:bg-teal-700 disabled:opacity-50 text-white text-xs font-extrabold shadow-md shadow-teal-500/20 transition cursor-pointer"
                >
                    <template x-if="isSavingLocator">
                        <i class="fa-solid fa-spinner fa-spin text-xs"></i>
                    </template>
                    <template x-if="!isSavingLocator">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                    </template>
                    <span>Lưu ảnh định vị</span>
                </button>
            </div>
        </div>
    </div>

    <!-- FLOATING ACTION BAR: Khi có căn được chọn -->
    <div 
        x-show="selectedUnitIds.length > 0" 
        x-cloak
        x-transition:enter="transition ease-out duration-300"
        x-transition:enter-start="opacity-0 translate-y-8"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-8"
        class="fixed bottom-6 left-1/2 -translate-x-1/2 z-40 max-w-3xl w-[94%] sm:w-auto bg-slate-900/95 backdrop-blur-md text-white px-4 py-3 rounded-2xl shadow-2xl border border-slate-700/80 flex flex-wrap items-center justify-between sm:justify-start gap-3"
    >
        <div class="flex items-center gap-2 pr-2 border-r border-slate-700">
            <span class="w-6 h-6 rounded-full bg-indigo-600 text-white text-xs font-black flex items-center justify-center shadow-xs" x-text="selectedUnitIds.length"></span>
            <span class="text-xs font-bold text-slate-200">căn đã chọn</span>
            <template x-if="selectedUnitIds.length < allProjectUnitIds.length">
                <button 
                    type="button" 
                    @click="selectAllInProject()" 
                    class="text-[11px] text-indigo-400 hover:text-indigo-300 underline font-semibold ml-1"
                >
                    Chọn cả dự án (<span x-text="allProjectUnitIds.length"></span>)
                </button>
            </template>
        </div>

        <div class="flex items-center gap-2">
            <input type="file" x-ref="bulkBocMaiFile" accept="image/*" class="hidden" @change="bulkReplaceLayout3d($event)">
            <button
                type="button"
                @click="$refs.bulkBocMaiFile.click()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-violet-600 hover:bg-violet-500 text-white text-xs font-bold shadow-md transition"
                title="Dùng một ảnh bóc mái mới cho tất cả căn đang chọn"
            >
                <i class="fa-solid fa-cubes-stacked text-xs"></i>
                <span>Thay ảnh bóc mái</span>
            </button>

            <!-- Nút Chuyển sang Studio Sửa Ảnh Hàng Loạt -->
            <button 
                type="button" 
                @click="goToBulkStudio()"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-500 text-white text-xs font-bold shadow-md transition"
                title="Vào Studio để ghép & sửa ảnh chỉ căn cho các căn đã chọn"
            >
                <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                <span>Sửa ảnh các căn đã chọn (<span x-text="selectedUnitIds.length"></span>)</span>
            </button>

            <!-- Dropdown Đổi nhanh trạng thái -->
            <div class="relative" x-data="{ statusDropOpen: false }" @click.outside="statusDropOpen = false">
                <button 
                    type="button" 
                    @click="statusDropOpen = !statusDropOpen"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold border border-slate-700 transition"
                >
                    <i class="fa-solid fa-tag text-xs text-amber-400"></i>
                    <span>Đổi trạng thái</span>
                    <i class="fa-solid fa-chevron-down text-[10px]"></i>
                </button>
                <div 
                    x-show="statusDropOpen" 
                    x-cloak 
                    class="absolute bottom-full mb-2 left-0 w-44 bg-slate-900 border border-slate-700 rounded-xl shadow-xl overflow-hidden py-1 z-50 text-xs"
                >
                    <button type="button" @click="quickBulkStatus('available'); statusDropOpen = false" class="w-full text-left px-3 py-2 hover:bg-slate-800 text-emerald-400 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Còn trống
                    </button>
                    <button type="button" @click="quickBulkStatus('reserved'); statusDropOpen = false" class="w-full text-left px-3 py-2 hover:bg-slate-800 text-amber-400 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Đang giữ chỗ
                    </button>
                    <button type="button" @click="quickBulkStatus('deposited'); statusDropOpen = false" class="w-full text-left px-3 py-2 hover:bg-slate-800 text-purple-400 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-purple-500"></span> Đã đặt cọc
                    </button>
                    <button type="button" @click="quickBulkStatus('sold'); statusDropOpen = false" class="w-full text-left px-3 py-2 hover:bg-slate-800 text-rose-400 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Đã bán
                    </button>
                    <button type="button" @click="quickBulkStatus('locked'); statusDropOpen = false" class="w-full text-left px-3 py-2 hover:bg-slate-800 text-slate-400 flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-slate-500"></span> Tạm khóa
                    </button>
                </div>
            </div>

            <!-- Nút Xóa Hàng Loạt -->
            <button 
                type="button" 
                @click="confirmBulkDelete()"
                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-600/80 hover:bg-rose-600 text-white text-xs font-bold transition"
                title="Xóa các căn đã chọn"
            >
                <i class="fa-regular fa-trash-can text-xs"></i>
                <span class="hidden sm:inline">Xóa</span>
            </button>

            <!-- Nút Bỏ Chọn -->
            <button 
                type="button" 
                @click="clearSelection()"
                class="p-1.5 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800 transition"
                title="Bỏ chọn tất cả"
            >
                <i class="fa-solid fa-xmark text-sm"></i>
            </button>
        </div>
    </div>

    <!-- FLOORPLAN MODAL: Quản lý mặt bằng tầng của dự án -->
    <div 
        x-show="floorplanModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4"
        @keydown.escape.window="floorplanModalOpen = false"
        @click.self="floorplanModalOpen = false"
    >
        <div class="relative max-w-2xl w-full bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fa-solid fa-map"></i>
                    </div>
                    <div>
                        <h4 class="font-bold text-slate-800 text-base">Mặt bằng tầng dự án</h4>
                        <span class="text-xs text-slate-500">{{ $project->name }}</span>
                    </div>
                </div>
                <button type="button" @click="floorplanModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <div class="p-6 space-y-6 max-h-[75vh] overflow-y-auto">
                <!-- Upload New Floorplan Form -->
                <form action="{{ route('admin.unit-locators.floorplan.store') }}" method="POST" enctype="multipart/form-data" class="p-4 rounded-xl bg-blue-50/50 border border-blue-100 space-y-3">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ $project->id }}">
                    <h5 class="text-xs font-bold text-blue-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-cloud-arrow-up text-blue-600"></i>
                        <span>Tải lên mặt bằng mới</span>
                    </h5>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tên mặt bằng <span class="text-rose-500">*</span></label>
                            <input type="text" name="name" placeholder="Ví dụ: Mặt bằng Tầng 3-15 Tòa A" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-700 mb-1">Tòa / Khoảng tầng</label>
                            <input type="text" name="floor_range" placeholder="Tòa A - Tầng 3-15" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs bg-white focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">File ảnh mặt bằng <span class="text-rose-500">*</span></label>
                        <input type="file" name="floorplan_file" accept="image/*" required class="w-full text-xs text-slate-500 file:mr-4 file:py-1.5 file:px-3 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                    </div>

                    <button type="submit" class="w-full py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition shadow-sm">
                        Lưu mặt bằng
                    </button>
                </form>

                <!-- List of existing floorplans -->
                <div>
                    <h5 class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-3">
                        Danh sách mặt bằng đã có (<span x-text="floorplanCount">{{ $floorplans->count() }}</span>)
                    </h5>

                    <p x-show="floorplanCount === 0" class="text-xs text-slate-400 italic text-center py-4" style="{{ $floorplans->isEmpty() ? '' : 'display: none;' }}">Chưa có ảnh mặt bằng nào. Hãy tải lên ảnh mặt bằng đầu tiên ở trên!</p>
                    <div x-show="floorplanCount > 0" class="grid grid-cols-1 sm:grid-cols-2 gap-3" style="{{ $floorplans->isEmpty() ? 'display: none;' : '' }}">
                        @foreach ($floorplans as $fp)
                        <div id="modal-floorplan-{{ $fp->id }}" data-modal-floorplan-id="{{ $fp->id }}" class="p-3 rounded-xl border border-slate-200 flex items-center justify-between gap-3 bg-white hover:border-blue-400 transition">
                            <div class="flex items-center gap-3 overflow-hidden">
                                <img 
                                    src="{{ $fp->image_url }}" 
                                    alt="{{ $fp->name }}" 
                                    class="w-14 h-14 object-cover rounded-lg border border-slate-100 shrink-0"
                                    onerror="this.onerror=null; this.src='{{ route('admin.unit-locators.project-image', 'default.jpg') }}';"
                                >
                                <div class="overflow-hidden">
                                    <h6 class="font-bold text-xs text-slate-800 truncate">{{ $fp->name }}</h6>
                                    @if($fp->floor_range)
                                    <span class="text-[11px] text-slate-500 block truncate">{{ $fp->floor_range }}</span>
                                    @endif
                                    <a href="{{ $fp->image_url }}" target="_blank" class="text-[11px] text-blue-600 hover:underline">Xem ảnh &rarr;</a>
                                </div>
                            </div>

                            <button 
                                type="button" 
                                @click="deleteFloorplan({{ $fp->id }}, '{{ addslashes($fp->name) }}')"
                                class="p-2 text-slate-400 hover:text-rose-600 transition" 
                                title="Xóa mặt bằng"
                            >
                                <i class="fa-regular fa-trash-can text-sm"></i>
                            </button>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 text-right">
                <button type="button" @click="floorplanModalOpen = false" class="px-4 py-2 rounded-xl bg-slate-200 text-slate-700 text-xs font-bold hover:bg-slate-300">
                    Đóng
                </button>
            </div>
        </div>
    </div>

    <!-- MODAL 1: TẢI LÊN PHÔI ẢNH CHỈ CĂN MỚI -->
    <div 
        x-show="uploadTemplateModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4"
        @keydown.escape.window="uploadTemplateModalOpen = false"
        @click.self="uploadTemplateModalOpen = false"
    >
        <div class="relative max-w-lg w-full bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-2xl">
            <form action="{{ route('admin.unit-locators.templates.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">

                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-amber-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center">
                            <i class="fa-solid fa-palette"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-base">Tải lên Phôi ảnh chỉ căn</h4>
                            <span class="text-xs text-slate-500">{{ $project->name }}</span>
                        </div>
                    </div>
                    <button type="button" @click="uploadTemplateModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tên phôi thiết kế <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" placeholder="Ví dụ: Phôi Luxury Gold 6 ô, Phôi Căn 2PN..." required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-amber-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">File ảnh phôi nền (Khung trống chưa điền chữ & ảnh) <span class="text-rose-500">*</span></label>
                        <input type="file" name="template_file" accept="image/*" required class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-amber-600 file:text-white hover:file:bg-amber-700">
                        <p class="text-[11px] text-slate-400 mt-1">Định dạng PNG/JPG độ phân giải cao. Sau khi tải lên, bạn sẽ mở Studio để kéo thả định vị các ô ảnh và khối chữ!</p>
                    </div>

                    <div class="flex items-center gap-2 pt-1">
                        <input type="checkbox" id="tmpl_is_default" name="is_default" value="1" class="rounded text-amber-600 focus:ring-amber-500">
                        <label for="tmpl_is_default" class="text-xs font-semibold text-slate-700">Đặt làm phôi mặc định cho dự án này</label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button type="button" @click="uploadTemplateModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-100">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-amber-600 hover:bg-amber-700 text-white text-sm font-bold shadow-md shadow-amber-500/20">
                        Tải lên phôi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: TẢI LÊN ẢNH PHỐI CẢNH & TIỆN ÍCH DỰ ÁN -->
    <div 
        x-show="perspectiveModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm p-4"
        @keydown.escape.window="perspectiveModalOpen = false"
        @click.self="perspectiveModalOpen = false"
    >
        <div class="relative max-w-lg w-full bg-white rounded-2xl overflow-hidden border border-slate-200 shadow-2xl">
            <form action="{{ route('admin.unit-locators.perspectives.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">

                <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-indigo-50/50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center">
                            <i class="fa-solid fa-images"></i>
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-base">Tải lên Ảnh Phối Cảnh & Tiện Ích</h4>
                            <span class="text-xs text-slate-500">{{ $project->name }}</span>
                        </div>
                    </div>
                    <button type="button" @click="perspectiveModalOpen = false" class="text-slate-400 hover:text-slate-600 p-1">
                        <i class="fa-solid fa-xmark text-lg"></i>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Phân loại ảnh <span class="text-rose-500">*</span></label>
                        <select name="category" required class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500 bg-white">
                            <option value="layout_3d">1. Mặt bằng bóc mái 3D căn hộ</option>
                            <option value="perspective">2. Phối cảnh tòa / tổng thể dự án</option>
                            <option value="amenity">3. Tiện ích nội khu (công viên, bể bơi, sân chơi...)</option>
                            <option value="other">4. Ảnh phối cảnh khác</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tên ảnh (Tùy chọn)</label>
                        <input type="text" name="name" placeholder="Ví dụ: Bóc mái 2PN Tòa A, Phối cảnh hoàng hôn..." class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-indigo-500">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Chọn một hoặc nhiều file ảnh <span class="text-rose-500">*</span></label>
                        <input type="file" name="image_files[]" multiple accept="image/*" required class="w-full text-xs text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-xs file:font-semibold file:bg-indigo-600 file:text-white hover:file:bg-indigo-700">
                        <p class="text-[11px] text-slate-400 mt-1">Hỗ trợ chọn nhiều ảnh cùng lúc để tải lên thư viện nhanh chóng.</p>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 px-6 py-4 bg-slate-50 border-t border-slate-100">
                    <button type="button" @click="perspectiveModalOpen = false" class="px-4 py-2 rounded-xl border border-slate-200 text-slate-600 text-sm font-semibold hover:bg-slate-100">
                        Hủy
                    </button>
                    <button type="submit" class="px-5 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-md shadow-indigo-500/20">
                        Tải lên ảnh
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- BACKDROP NỀN MỜ CHO STUDIO TRƯỢT PHẢI -->
    <div 
        x-show="templateStudioOpen" 
        x-cloak 
        x-transition:enter="transition-opacity ease-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition-opacity ease-in duration-200"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-xs"
        @click="templateStudioOpen = false"
    ></div>

    <!-- DRAWER TRƯỢT TỪ BÊN PHẢI SANG (SLIDE-OVER PANEL TỰ CO GIÃN THEO NỘI DUNG) -->
    <div 
        x-show="templateStudioOpen" 
        x-cloak 
        x-transition:enter="transform transition ease-out duration-300"
        x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transform transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="translate-x-full"
        class="fixed inset-y-0 right-0 z-50 h-screen flex flex-col bg-slate-900 border-l border-slate-700/80 shadow-2xl overflow-hidden w-fit max-w-[100vw]"
        @keydown.escape.window="templateStudioOpen = false"
        @keydown.window="onStudioKeyDown($event)"
        @mousemove="onStudioMouseMove($event)"
        @mouseup="onStudioMouseUp($event)"
    >
        <!-- Top Toolbar -->
        <div class="flex items-center justify-between px-5 py-2.5 border-b border-slate-800 bg-slate-950 text-white shrink-0 gap-4">
            <div class="flex items-center gap-3 min-w-0">
                <div class="w-8 h-8 rounded-xl bg-amber-500/20 text-amber-400 border border-amber-500/30 flex items-center justify-center text-sm font-black shrink-0">
                    <i class="fa-solid fa-sliders"></i>
                </div>
                <div class="min-w-0">
                    <div class="flex items-center gap-2">
                        <h3 class="font-black text-sm text-white truncate" x-text="'Cài đặt vị trí: ' + (studioTemplate.name || 'Phôi mẫu')"></h3>
                        <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-amber-500/20 text-amber-300 border border-amber-500/40 shrink-0" x-text="(studioTemplate.slots_config?.canvas?.width || 1000) + ' × ' + (studioTemplate.slots_config?.canvas?.height || 1400) + ' px'"></span>
                    </div>
                    <p class="text-[11px] text-slate-400 truncate">Kéo thả trực tiếp các ô khung ảnh & khối chữ để định vị trên phôi</p>
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0">
                <button 
                    type="button" 
                    @click="addSlot()"
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition flex items-center gap-1.5 border border-slate-700"
                    title="Thêm 1 ô khung ảnh mới vào phôi"
                >
                    <i class="fa-solid fa-plus text-amber-400"></i>
                    <span>Thêm khung ảnh</span>
                </button>

                <button 
                    type="button" 
                    @click="addTextSlot('custom')"
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-200 text-xs font-bold transition flex items-center gap-1.5 border border-slate-700"
                    title="Thêm 1 khối chữ mới vào phôi"
                >
                    <i class="fa-solid fa-font text-amber-400"></i>
                    <span>Thêm khối chữ</span>
                </button>

                <button 
                    type="button" 
                    @click="resetToPreset()"
                    class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-700 text-amber-400 text-xs font-bold transition flex items-center gap-1.5 border border-slate-700"
                    title="Khôi phục lại bộ tọa độ mẫu 6 ô chuẩn"
                >
                    <i class="fa-solid fa-wand-magic-sparkles"></i>
                    <span>Nạp mẫu 6 ô chuẩn</span>
                </button>

                <button 
                    type="button" 
                    @click="saveTemplateConfig()"
                    :disabled="isSavingTemplate"
                    class="px-4 py-1.5 rounded-xl bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-slate-950 text-xs font-black transition flex items-center gap-1.5 shadow-md shadow-amber-500/20"
                >
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span x-text="isSavingTemplate ? 'Đang lưu...' : 'Lưu cấu hình vị trí'"></span>
                </button>

                <button 
                    type="button" 
                    @click="templateStudioOpen = false" 
                    class="w-8 h-8 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white flex items-center justify-center transition shrink-0 ml-1"
                    title="Đóng (Esc)"
                >
                    <i class="fa-solid fa-xmark text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Workspace Body -->
        <div class="flex-1 flex overflow-hidden min-h-0">
            <!-- Center: Interactive Visual Template Viewport (Tự co giãn chuẩn kích thước phôi) -->
            <div class="p-3.5 flex items-center justify-center bg-slate-950/70 overflow-hidden shrink-0" id="mapperViewportContainer">
                    <div 
                        class="relative bg-slate-900 border-2 border-slate-700/80 rounded-2xl shadow-2xl overflow-hidden select-none"
                        id="mapperBoard"
                        :style="'width: ' + boardW + 'px; height: ' + boardH + 'px;'"
                    >
                        <!-- Phôi Background Image (hoặc nền gradient nếu chưa up ảnh) -->
                        <template x-if="studioTemplate.image_url && studioTemplate.image_url.indexOf('placeholder') === -1">
                            <img :src="studioTemplate.image_url" class="absolute inset-0 w-full h-full object-cover pointer-events-none">
                        </template>
                        <template x-if="!studioTemplate.image_url || studioTemplate.image_url.indexOf('placeholder') !== -1">
                            <div class="absolute inset-0 w-full h-full bg-gradient-to-br from-amber-950 via-yellow-950 to-stone-900 flex items-center justify-center pointer-events-none">
                                <span class="text-amber-500/30 text-2xl font-black uppercase tracking-widest pointer-events-none">Phôi Nền Dự Án</span>
                            </div>
                        </template>

                        <!-- Render Draggable Image Slots (Nền trong suốt 100%, nét viền mảnh 1px chuẩn pixel để căn phôi) -->
                        <template x-for="(slot, sIndex) in (studioTemplate.slots_config?.image_slots || [])" :key="slot.id || sIndex">
                            <div 
                                @mousedown="startDragSlot(slot, sIndex, $event)"
                                @click.stop="activeSlotIndex = sIndex; activeTextKey = null; studioTab = 'slots'"
                                :class="activeSlotIndex === sIndex ? 'border border-amber-400 z-20 bg-amber-500/[0.04]' : 'border border-blue-400/40 hover:border-blue-400/80 bg-transparent z-10'"
                                class="absolute cursor-move transition-colors group select-none box-border"
                                :style="'left: ' + (slot.x * mapperScale) + 'px; top: ' + (slot.y * mapperScale) + 'px; width: ' + (slot.width * mapperScale) + 'px; height: ' + (slot.height * mapperScale) + 'px; border-radius: ' + ((slot.radius || 0) * mapperScale) + 'px;'"
                            >
                                <!-- Nhãn tên góc trên bên trái nhỏ gọn (không che khuất phôi bên dưới) -->
                                <div class="absolute top-1 left-1.5 flex items-center gap-1 max-w-[95%] pointer-events-none select-none z-10">
                                    <span class="text-[9px] font-black px-1.5 py-0.5 rounded bg-black/85 text-amber-300 border border-amber-400/40 shadow-xs truncate" x-text="slot.name"></span>
                                    <span class="text-[8px] font-bold px-1 py-0.5 rounded bg-slate-900/85 text-slate-300 font-mono shadow-xs" x-text="slot.width + '×' + slot.height"></span>
                                </div>

                                <!-- 4 Góc Ke Định Vị (L-Corners) chỉ hiện khi chọn để căn chuẩn pixel mép phôi -->
                                <template x-if="activeSlotIndex === sIndex">
                                    <div class="pointer-events-none absolute inset-0">
                                        <div class="absolute -top-px -left-px w-2 h-2 border-t-2 border-l-2 border-amber-400"></div>
                                        <div class="absolute -top-px -right-px w-2 h-2 border-t-2 border-r-2 border-amber-400"></div>
                                        <div class="absolute -bottom-px -left-px w-2 h-2 border-b-2 border-l-2 border-amber-400"></div>
                                        <div class="absolute -bottom-px -right-px w-2 h-2 border-b-2 border-r-2 border-amber-400"></div>
                                    </div>
                                </template>

                                <!-- Resize handle bottom-right corner -->
                                <div 
                                    @mousedown.stop="startResizeSlot(slot, sIndex, $event)"
                                    class="absolute -bottom-1 -right-1 w-3.5 h-3.5 bg-amber-400 border border-slate-950 cursor-nwse-resize shadow-md hover:scale-125 transition-transform z-30"
                                    title="Kéo để chỉnh kích thước ô"
                                ></div>
                            </div>
                        </template>

                        <!-- Render Dynamic Draggable Text Slots -->
                        <template x-for="(tSlot, tKey) in (studioTemplate.slots_config?.text_slots || {})" :key="tKey">
                            <div 
                                @mousedown="startDragText(tKey, $event)"
                                @click.stop="activeTextKey = tKey; activeSlotIndex = null; studioTab = 'text'"
                                :class="[
                                    activeTextKey === tKey ? 'ring-2 ring-amber-400 z-40 shadow-lg' : 'z-30 hover:ring-1 hover:ring-amber-400/60',
                                    (tSlot.isBadge && tSlot.bgColor && tSlot.bgColor !== 'transparent') ? 'px-2.5 py-1 rounded-lg shadow-md' : 'drop-shadow-md'
                                ]"
                                class="absolute cursor-move select-none whitespace-nowrap transition-transform"
                                :style="'left: ' + ((tSlot.x || 0) * mapperScale) + 'px; top: ' + ((tSlot.y || 0) * mapperScale) + 'px; font-size: ' + ((tSlot.fontSize || 20) * mapperScale) + 'px; color: ' + (tSlot.color || '#ffffff') + '; font-weight: ' + (tSlot.fontWeight || (tKey === 'price' ? '900' : 'bold')) + '; font-family: ' + (tSlot.fontFamily === 'serif' || tKey === 'price' ? 'serif' : 'sans-serif') + '; background-color: ' + ((tSlot.isBadge && tSlot.bgColor) ? tSlot.bgColor : 'transparent') + '; border: ' + ((tSlot.isBadge && tSlot.borderColor && tSlot.borderColor !== 'transparent') ? '1.5px solid ' + tSlot.borderColor : 'none') + '; line-height: 1; text-align: ' + (tSlot.align || 'left') + '; transform: ' + (tSlot.align === 'center' ? 'translateX(-50%)' : (tSlot.align === 'right' ? 'translateX(-100%)' : 'none')) + ';'"
                            >
                                <span x-text="getTextSlotDisplaySample(tKey, tSlot)"></span>
                            </div>
                        </template>
                    </div>
                </div>

                <!-- Right: Properties & Slot Settings Panel (2 Columns Layout: Cột 1 Danh Sách, Cột 2 Thông Số Tùy Chỉnh) -->
                <div class="w-[660px] bg-slate-900 border-l border-slate-800 flex flex-col text-white shrink-0">
                    <!-- Panel Tabs -->
                    <div class="flex border-b border-slate-800 text-xs font-bold shrink-0">
                        <button 
                            type="button" 
                            @click="studioTab = 'slots'"
                            :class="studioTab === 'slots' ? 'border-amber-400 text-amber-400 bg-slate-800/80 border-b-2' : 'text-slate-400 hover:text-white'"
                            class="flex-1 py-3 px-3 text-center transition"
                        >
                            <i class="fa-regular fa-image mr-1"></i> Khung ảnh (<span x-text="(studioTemplate.slots_config?.image_slots || []).length"></span>)
                        </button>
                        <button 
                            type="button" 
                            @click="studioTab = 'text'"
                            :class="studioTab === 'text' ? 'border-amber-400 text-amber-400 bg-slate-800/80 border-b-2' : 'text-slate-400 hover:text-white'"
                            class="flex-1 py-3 px-3 text-center transition"
                        >
                            <i class="fa-solid fa-font mr-1"></i> Khối chữ (<span x-text="Object.keys(studioTemplate.slots_config?.text_slots || {}).length"></span>)
                        </button>
                    </div>

                    <!-- Panel 2-Column Content Body -->
                    <div class="flex-1 flex overflow-hidden min-h-0">
                        <!-- TAB A: KHUNG ẢNH (2 CỘT: CỘT 1 DANH SÁCH KHUNG, CỘT 2 THÔNG SỐ TÙY CHỈNH) -->
                        <div x-show="studioTab === 'slots'" class="flex-1 flex overflow-hidden min-h-0">
                            <!-- Cột 1: Danh sách các khung ảnh -->
                            <div class="w-[270px] shrink-0 border-r border-slate-800 p-3.5 flex flex-col overflow-hidden bg-slate-900/60">
                                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-800 shrink-0">
                                    <span class="font-bold text-slate-300 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                                        <i class="fa-regular fa-images text-amber-400"></i>
                                        <span>Các khung ảnh (<span x-text="(studioTemplate.slots_config?.image_slots || []).length"></span>)</span>
                                    </span>
                                    <button 
                                        type="button" 
                                        @click="addSlot()"
                                        class="px-2 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-[10px] flex items-center gap-1 shadow transition"
                                        title="Thêm khung ảnh mới"
                                    >
                                        <i class="fa-solid fa-plus text-[9px]"></i>
                                        <span>Thêm khung</span>
                                    </button>
                                </div>

                                <div class="flex-1 overflow-y-auto space-y-1.5 pr-1 text-xs">
                                    <template x-for="(slot, sIdx) in (studioTemplate.slots_config?.image_slots || [])" :key="slot.id || sIdx">
                                        <div 
                                            @click="activeSlotIndex = sIdx; activeTextKey = null"
                                            :class="activeSlotIndex === sIdx ? 'bg-amber-500/20 border-amber-400 text-amber-200 ring-1 ring-amber-400/40 shadow-xs' : 'bg-slate-800/80 border-slate-700/80 text-slate-300 hover:bg-slate-800'"
                                            class="p-2 rounded-xl border flex items-center justify-between cursor-pointer transition group"
                                        >
                                            <div class="overflow-hidden min-w-0 pr-1">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 shrink-0"></span>
                                                    <h6 class="font-bold truncate text-xs" x-text="slot.name"></h6>
                                                </div>
                                                <span class="text-[10px] text-slate-400 font-mono block pl-3" x-text="slot.width + ' × ' + slot.height + ' px'"></span>
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0">
                                                <button 
                                                    type="button"
                                                    @click.stop="removeSlot(sIdx)"
                                                    class="w-6 h-6 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 flex items-center justify-center transition"
                                                    title="Xóa khung ảnh này"
                                                >
                                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                                </button>
                                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-500 group-hover:text-slate-300 transition"></i>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="(studioTemplate.slots_config?.image_slots || []).length === 0">
                                        <div class="text-center py-8 text-slate-500 border border-dashed border-slate-800 rounded-xl text-xs">
                                            Chưa có khung ảnh nào.<br>Bấm "+ Thêm khung" để tạo.
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Cột 2: Thông số tùy chỉnh khung ảnh đang chọn -->
                            <div class="flex-1 p-4 overflow-y-auto space-y-3.5 text-xs">
                                <template x-if="activeSlotIndex !== null && studioTemplate.slots_config?.image_slots[activeSlotIndex]">
                                    <div class="p-3.5 rounded-2xl bg-slate-800/90 border border-amber-500/40 space-y-3">
                                        <div class="flex items-center justify-between pb-2 border-b border-slate-700">
                                            <div>
                                                <span class="font-black text-amber-400 uppercase tracking-wider text-[11px] block">Thông số tùy chỉnh khung ảnh</span>
                                                <span class="text-[9px] text-slate-400" x-text="'Đang chọn: ' + studioTemplate.slots_config.image_slots[activeSlotIndex].name"></span>
                                            </div>
                                            <button type="button" @click="removeSlot(activeSlotIndex)" class="text-rose-400 hover:text-rose-300 font-bold text-xs flex items-center gap-1 px-2 py-1 rounded bg-rose-500/10 hover:bg-rose-500/20 transition" title="Xóa khung này">
                                                <i class="fa-regular fa-trash-can"></i> Xóa
                                            </button>
                                        </div>

                                        <div>
                                            <label class="block text-slate-400 mb-1 font-bold text-[11px]">Tên khung</label>
                                            <input 
                                                type="text" 
                                                x-model="studioTemplate.slots_config.image_slots[activeSlotIndex].name"
                                                class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white font-bold text-xs focus:ring-1 focus:ring-amber-400 focus:outline-hidden"
                                            >
                                        </div>

                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-slate-400 mb-0.5 text-[11px]">Tọa độ X</label>
                                                <input type="number" x-model.number="studioTemplate.slots_config.image_slots[activeSlotIndex].x" class="w-full px-2 py-1 rounded bg-slate-900 border border-slate-700 text-white text-xs font-mono">
                                            </div>
                                            <div>
                                                <label class="block text-slate-400 mb-0.5 text-[11px]">Tọa độ Y</label>
                                                <input type="number" x-model.number="studioTemplate.slots_config.image_slots[activeSlotIndex].y" class="w-full px-2 py-1 rounded bg-slate-900 border border-slate-700 text-white text-xs font-mono">
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-slate-400 mb-0.5 text-[11px]">Rộng (Width)</label>
                                                <input type="number" x-model.number="studioTemplate.slots_config.image_slots[activeSlotIndex].width" class="w-full px-2 py-1 rounded bg-slate-900 border border-slate-700 text-white text-xs font-mono">
                                            </div>
                                            <div>
                                                <label class="block text-slate-400 mb-0.5 text-[11px]">Cao (Height)</label>
                                                <input type="number" x-model.number="studioTemplate.slots_config.image_slots[activeSlotIndex].height" class="w-full px-2 py-1 rounded bg-slate-900 border border-slate-700 text-white text-xs font-mono">
                                            </div>
                                        </div>

                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between">
                                                <label class="block text-slate-400 text-[11px] font-semibold">Bo góc khung (Radius)</label>
                                                <div class="flex items-center gap-1">
                                                    <input 
                                                        type="number" 
                                                        min="0" 
                                                        max="200" 
                                                        step="1"
                                                        x-model.number="studioTemplate.slots_config.image_slots[activeSlotIndex].radius" 
                                                        class="w-16 px-1.5 py-0.5 rounded bg-slate-900 border border-slate-700 text-amber-300 text-xs font-bold text-center"
                                                    >
                                                    <span class="text-xs text-slate-400 font-mono">px</span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input 
                                                    type="range" 
                                                    min="0" 
                                                    max="150" 
                                                    step="1"
                                                    x-model.number="studioTemplate.slots_config.image_slots[activeSlotIndex].radius" 
                                                    class="flex-1 accent-amber-400 cursor-pointer"
                                                >
                                            </div>
                                            <!-- Gợi ý bo góc nhanh -->
                                            <div class="flex items-center gap-1.5 pt-0.5 flex-wrap">
                                                <span class="text-[10px] text-slate-500 font-semibold">Gợi ý:</span>
                                                <button 
                                                    type="button" 
                                                    @click="studioTemplate.slots_config.image_slots[activeSlotIndex].radius = 0" 
                                                    :class="(studioTemplate.slots_config.image_slots[activeSlotIndex].radius || 0) === 0 ? 'bg-amber-400 text-slate-950 font-black' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-700'"
                                                    class="px-1.5 py-0.5 text-[10px] rounded transition"
                                                >0px</button>
                                                <button 
                                                    type="button" 
                                                    @click="studioTemplate.slots_config.image_slots[activeSlotIndex].radius = 20" 
                                                    :class="studioTemplate.slots_config.image_slots[activeSlotIndex].radius === 20 ? 'bg-amber-400 text-slate-950 font-black' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-700'"
                                                    class="px-1.5 py-0.5 text-[10px] rounded transition"
                                                >20px</button>
                                                <button 
                                                    type="button" 
                                                    @click="studioTemplate.slots_config.image_slots[activeSlotIndex].radius = 37" 
                                                    :class="studioTemplate.slots_config.image_slots[activeSlotIndex].radius === 37 ? 'bg-amber-400 text-slate-950 font-black' : 'bg-amber-500/20 text-amber-300 hover:bg-amber-500/30 border border-amber-500/40 font-bold'"
                                                    class="px-1.5 py-0.5 text-[10px] rounded transition shadow-xs"
                                                    title="Bo góc chuẩn 37px khớp 100% phôi Imperia Sky Park"
                                                >★ 37px (Chuẩn phôi)</button>
                                                <button 
                                                    type="button" 
                                                    @click="studioTemplate.slots_config.image_slots[activeSlotIndex].radius = 50" 
                                                    :class="studioTemplate.slots_config.image_slots[activeSlotIndex].radius === 50 ? 'bg-amber-400 text-slate-950 font-black' : 'bg-slate-900 text-slate-400 hover:text-white border border-slate-700'"
                                                    class="px-1.5 py-0.5 text-[10px] rounded transition"
                                                >50px</button>
                                            </div>
                                        </div>

                                        <!-- Cài đặt Viền Khung (Border) -->
                                        <div>
                                            <div class="flex items-center justify-between mb-1">
                                                <label class="text-slate-400 font-bold text-[11px]">Viền khung ảnh (Border)</label>
                                                <span 
                                                    class="text-[10px] font-bold px-1.5 py-0.5 rounded"
                                                    :class="(!studioTemplate.slots_config.image_slots[activeSlotIndex].border || studioTemplate.slots_config.image_slots[activeSlotIndex].border === 'none' || studioTemplate.slots_config.image_slots[activeSlotIndex].borderWidth == 0) ? 'bg-emerald-500/20 text-emerald-300' : 'bg-amber-500/20 text-amber-300'"
                                                    x-text="(!studioTemplate.slots_config.image_slots[activeSlotIndex].border || studioTemplate.slots_config.image_slots[activeSlotIndex].border === 'none' || studioTemplate.slots_config.image_slots[activeSlotIndex].borderWidth == 0) ? 'Không viền' : (studioTemplate.slots_config.image_slots[activeSlotIndex].borderWidth + 'px')"
                                                ></span>
                                            </div>
                                            <div class="grid grid-cols-2 gap-1.5">
                                                <button 
                                                    type="button" 
                                                    @click="studioTemplate.slots_config.image_slots[activeSlotIndex].border = 'none'; studioTemplate.slots_config.image_slots[activeSlotIndex].borderWidth = 0"
                                                    :class="(!studioTemplate.slots_config.image_slots[activeSlotIndex].border || studioTemplate.slots_config.image_slots[activeSlotIndex].border === 'none' || studioTemplate.slots_config.image_slots[activeSlotIndex].borderWidth == 0) ? 'bg-amber-500 text-slate-950 font-black' : 'bg-slate-900 text-slate-300 hover:bg-slate-750 border border-slate-700'"
                                                    class="py-1 px-2 rounded text-[11px] transition text-center flex items-center justify-center gap-1"
                                                >
                                                    <i class="fa-solid fa-ban text-[9px]"></i>
                                                    <span>Không viền (0px)</span>
                                                </button>

                                                <button 
                                                    type="button" 
                                                    @click="studioTemplate.slots_config.image_slots[activeSlotIndex].border = '#ffffff'; studioTemplate.slots_config.image_slots[activeSlotIndex].borderWidth = 1"
                                                    :class="(studioTemplate.slots_config.image_slots[activeSlotIndex].border === '#ffffff' && studioTemplate.slots_config.image_slots[activeSlotIndex].borderWidth == 1) ? 'bg-amber-500 text-slate-950 font-black' : 'bg-slate-900 text-slate-300 hover:bg-slate-750 border border-slate-700'"
                                                    class="py-1 px-2 rounded text-[11px] transition text-center"
                                                >
                                                    Viền trắng 1px
                                                </button>

                                                <button 
                                                    type="button" 
                                                    @click="studioTemplate.slots_config.image_slots[activeSlotIndex].border = '#ffffff'; studioTemplate.slots_config.image_slots[activeSlotIndex].borderWidth = 2"
                                                    :class="(studioTemplate.slots_config.image_slots[activeSlotIndex].border === '#ffffff' && studioTemplate.slots_config.image_slots[activeSlotIndex].borderWidth == 2) ? 'bg-amber-500 text-slate-950 font-black' : 'bg-slate-900 text-slate-300 hover:bg-slate-750 border border-slate-700'"
                                                    class="py-1 px-2 rounded text-[11px] transition text-center"
                                                >
                                                    Viền trắng 2px
                                                </button>

                                                <button 
                                                    type="button" 
                                                    @click="studioTemplate.slots_config.image_slots[activeSlotIndex].border = '#fbbf24'; studioTemplate.slots_config.image_slots[activeSlotIndex].borderWidth = 2"
                                                    :class="(studioTemplate.slots_config.image_slots[activeSlotIndex].border === '#fbbf24') ? 'bg-amber-500 text-slate-950 font-black' : 'bg-slate-900 text-slate-300 hover:bg-slate-750 border border-slate-700'"
                                                    class="py-1 px-2 rounded text-[11px] transition text-center"
                                                >
                                                    Viền vàng 2px
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="activeSlotIndex === null || !studioTemplate.slots_config?.image_slots[activeSlotIndex]">
                                    <div class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-500 border border-dashed border-slate-800 rounded-2xl">
                                        <i class="fa-regular fa-hand-pointer text-2xl text-slate-600 mb-2"></i>
                                        <p class="font-bold text-slate-400">Chưa chọn khung ảnh nào</p>
                                        <p class="text-[11px] text-slate-500 mt-1">Bấm chọn một khung ảnh ở danh sách bên trái hoặc bấm trực tiếp lên bảng phôi để tùy chỉnh.</p>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <!-- TAB B: KHỐI CHỮ (2 CỘT: CỘT 1 DANH SÁCH CHỮ, CỘT 2 THÔNG SỐ TÙY CHỈNH) -->
                        <div x-show="studioTab === 'text'" class="flex-1 flex overflow-hidden min-h-0">
                            <!-- Cột 1: Danh sách các khối chữ -->
                            <div class="w-[270px] shrink-0 border-r border-slate-800 p-3.5 flex flex-col overflow-hidden bg-slate-900/60">
                                <div class="flex items-center justify-between pb-2 mb-2 border-b border-slate-800 shrink-0">
                                    <span class="font-bold text-slate-300 uppercase tracking-wider text-[11px] flex items-center gap-1.5">
                                        <i class="fa-solid fa-font text-amber-400"></i>
                                        <span>Các khối chữ (<span x-text="Object.keys(studioTemplate.slots_config?.text_slots || {}).length"></span>)</span>
                                    </span>
                                    <div class="relative" x-data="{ openMenu: false }">
                                        <button 
                                            type="button" 
                                            @click="openMenu = !openMenu"
                                            class="px-2 py-1 rounded-lg bg-amber-500 hover:bg-amber-400 text-slate-950 font-bold text-[10px] flex items-center gap-1 shadow transition"
                                        >
                                            <i class="fa-solid fa-plus text-[9px]"></i>
                                            <span>Thêm chữ</span>
                                            <i class="fa-solid fa-chevron-down text-[8px] ml-0.5"></i>
                                        </button>

                                        <!-- Dropdown menu for adding text slot -->
                                        <div 
                                            x-show="openMenu" 
                                            @click.outside="openMenu = false"
                                            x-transition
                                            class="absolute right-0 mt-1 w-56 bg-slate-900 border border-slate-700 rounded-xl shadow-2xl py-1 z-50 text-xs divide-y divide-slate-800"
                                            style="display: none;"
                                        >
                                            <div class="py-1">
                                                <span class="px-3 py-1 block text-[10px] font-bold text-slate-400 uppercase">Trường thông tin căn:</span>
                                                <template x-for="field in [
                                                    { key: 'unit_code', label: 'Mã căn' },
                                                    { key: 'unit_type', label: 'Loại căn (2PN)' },
                                                    { key: 'direction', label: 'Hướng' },
                                                    { key: 'view', label: 'View' },
                                                    { key: 'area', label: 'Diện tích' },
                                                    { key: 'price', label: 'Giá bán' },
                                                    { key: 'price_note', label: 'Ghi chú giá' },
                                                    { key: 'block', label: 'Tòa / Block' },
                                                    { key: 'floor', label: 'Tầng' }
                                                ]" :key="field.key">
                                                    <button 
                                                        type="button"
                                                        @click="addTextSlot(field.key); openMenu = false"
                                                        class="w-full text-left px-3 py-1.5 hover:bg-slate-800 flex items-center justify-between transition text-slate-200"
                                                    >
                                                        <span x-text="field.label"></span>
                                                        <span 
                                                            x-show="studioTemplate.slots_config?.text_slots?.[field.key]" 
                                                            class="text-[9px] text-amber-400 font-bold px-1 rounded bg-amber-400/10"
                                                        >Đã có</span>
                                                    </button>
                                                </template>
                                            </div>
                                            <div class="py-1">
                                                <button 
                                                    type="button"
                                                    @click="addTextSlot('custom'); openMenu = false"
                                                    class="w-full text-left px-3 py-1.5 hover:bg-amber-500/10 text-amber-400 font-bold flex items-center gap-1.5 transition"
                                                >
                                                    <i class="fa-solid fa-font text-[10px]"></i>
                                                    <span>+ Thêm chữ tùy chỉnh...</span>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="flex-1 overflow-y-auto space-y-1.5 pr-1 text-xs">
                                    <template x-for="(tSlot, tKey) in (studioTemplate.slots_config?.text_slots || {})" :key="tKey">
                                        <div 
                                            @click="activeTextKey = tKey; activeSlotIndex = null"
                                            :class="activeTextKey === tKey ? 'bg-amber-500/20 border-amber-400 text-amber-200 ring-1 ring-amber-400/40 shadow-xs' : 'bg-slate-800/80 border-slate-700/80 text-slate-300 hover:bg-slate-800'"
                                            class="p-2 rounded-xl border flex items-center justify-between cursor-pointer transition group"
                                        >
                                            <div class="flex items-center gap-2 min-w-0 pr-1">
                                                <span 
                                                    class="w-2.5 h-2.5 rounded-full shrink-0 border border-slate-600"
                                                    :style="'background-color: ' + (tSlot.color || '#ffffff')"
                                                ></span>
                                                <div class="truncate">
                                                    <span class="font-bold text-xs block truncate" x-text="getTextSlotLabel(tKey)"></span>
                                                    <span class="text-[10px] text-slate-400 font-mono" x-text="'X: ' + (tSlot.x || 0) + ' | Y: ' + (tSlot.y || 0) + ' | ' + (tSlot.fontSize || 20) + 'px'"></span>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-1 shrink-0">
                                                <button 
                                                    type="button"
                                                    @click.stop="deleteTextSlot(tKey)"
                                                    class="w-6 h-6 rounded-lg text-slate-500 hover:text-rose-400 hover:bg-rose-500/10 flex items-center justify-center transition"
                                                    title="Xóa khối chữ này"
                                                >
                                                    <i class="fa-regular fa-trash-can text-xs"></i>
                                                </button>
                                                <i class="fa-solid fa-chevron-right text-[10px] text-slate-500 group-hover:text-slate-300 transition"></i>
                                            </div>
                                        </div>
                                    </template>
                                    <template x-if="Object.keys(studioTemplate.slots_config?.text_slots || {}).length === 0">
                                        <div class="text-center py-8 text-slate-500 border border-dashed border-slate-800 rounded-xl text-xs">
                                            Chưa có khối chữ nào.<br>Bấm "+ Thêm chữ" ở trên để tạo.
                                        </div>
                                    </template>
                                </div>
                            </div>

                            <!-- Cột 2: Thông số tùy chỉnh khối chữ đang chọn -->
                            <div class="flex-1 p-4 overflow-y-auto space-y-3.5 text-xs">
                                <template x-if="activeTextKey && studioTemplate.slots_config?.text_slots?.[activeTextKey]">
                                    <div class="p-3.5 rounded-2xl bg-slate-800/90 border border-amber-500/40 space-y-3">
                                        <div class="flex items-center justify-between pb-2 border-b border-slate-700">
                                            <div class="min-w-0">
                                                <span class="font-black text-amber-400 uppercase tracking-wider text-[11px] block truncate">Thông số tùy chỉnh khối chữ</span>
                                                <span class="text-[9px] text-slate-400 font-mono" x-text="'Khóa: ' + activeTextKey + ' (' + getTextSlotLabel(activeTextKey) + ')'"></span>
                                            </div>
                                            <button 
                                                type="button" 
                                                @click="deleteTextSlot(activeTextKey)" 
                                                class="text-rose-400 hover:text-rose-300 font-bold text-xs flex items-center gap-1 px-2 py-1 rounded bg-rose-500/10 hover:bg-rose-500/20 transition"
                                                title="Xóa khối chữ này khỏi phôi"
                                            >
                                                <i class="fa-regular fa-trash-can"></i>
                                                <span>Xóa</span>
                                            </button>
                                        </div>

                                        <!-- Tiêu đề / Nhãn tùy biến -->
                                        <div>
                                            <label class="block text-slate-400 mb-0.5 text-[11px] font-bold">Tên / Nhãn hiển thị</label>
                                            <input 
                                                type="text" 
                                                x-model="studioTemplate.slots_config.text_slots[activeTextKey].label" 
                                                placeholder="VD: Tiêu đề, Hotline, Ghi chú..."
                                                class="w-full px-2.5 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white font-bold text-xs focus:border-amber-400 focus:outline-hidden"
                                            >
                                        </div>

                                        <!-- Tiền tố / Nội dung mẫu -->
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-slate-400 mb-0.5 text-[11px]">Tiền tố (Prefix)</label>
                                                <input 
                                                    type="text" 
                                                    x-model="studioTemplate.slots_config.text_slots[activeTextKey].prefix" 
                                                    placeholder="VD: Hướng: "
                                                    class="w-full px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs focus:border-amber-400 focus:outline-hidden"
                                                >
                                            </div>
                                            <div>
                                                <label class="block text-slate-400 mb-0.5 text-[11px]">Chữ mẫu (Preview)</label>
                                                <input 
                                                    type="text" 
                                                    x-model="studioTemplate.slots_config.text_slots[activeTextKey].sampleText" 
                                                    placeholder="Chữ hiển thị mẫu"
                                                    class="w-full px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs focus:border-amber-400 focus:outline-hidden"
                                                >
                                            </div>
                                        </div>

                                        <!-- Coordinates X & Y -->
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-slate-400 mb-0.5 text-[11px]">Tọa độ X</label>
                                                <input 
                                                    type="number" 
                                                    x-model.number="studioTemplate.slots_config.text_slots[activeTextKey].x" 
                                                    class="w-full px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:border-amber-400 focus:outline-hidden"
                                                >
                                            </div>
                                            <div>
                                                <label class="block text-slate-400 mb-0.5 text-[11px]">Tọa độ Y</label>
                                                <input 
                                                    type="number" 
                                                    x-model.number="studioTemplate.slots_config.text_slots[activeTextKey].y" 
                                                    class="w-full px-2.5 py-1 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs font-mono focus:border-amber-400 focus:outline-hidden"
                                                >
                                            </div>
                                        </div>

                                        <!-- Font Size -->
                                        <div>
                                            <div class="flex items-center justify-between mb-0.5 text-[11px]">
                                                <label class="text-slate-400">Cỡ chữ (Font Size)</label>
                                                <span class="font-bold text-amber-300 font-mono" x-text="(studioTemplate.slots_config.text_slots[activeTextKey].fontSize || 20) + 'px'"></span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <input 
                                                    type="range" 
                                                    min="12" 
                                                    max="120" 
                                                    x-model.number="studioTemplate.slots_config.text_slots[activeTextKey].fontSize" 
                                                    class="flex-1 accent-amber-400 cursor-pointer"
                                                >
                                                <input 
                                                    type="number" 
                                                    min="12" 
                                                    max="200" 
                                                    x-model.number="studioTemplate.slots_config.text_slots[activeTextKey].fontSize" 
                                                    class="w-16 px-1.5 py-0.5 rounded bg-slate-900 border border-slate-700 text-white text-xs text-center font-mono"
                                                >
                                            </div>
                                        </div>

                                        <!-- Color & Weight -->
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-slate-400 mb-0.5 text-[11px]">Màu chữ</label>
                                                <div class="flex items-center gap-1.5">
                                                    <input 
                                                        type="color" 
                                                        x-model="studioTemplate.slots_config.text_slots[activeTextKey].color" 
                                                        class="w-7 h-7 rounded border border-slate-700 bg-transparent cursor-pointer p-0"
                                                    >
                                                    <input 
                                                        type="text" 
                                                        x-model="studioTemplate.slots_config.text_slots[activeTextKey].color" 
                                                        class="w-full px-2 py-1 rounded bg-slate-900 border border-slate-700 text-white text-xs font-mono uppercase"
                                                    >
                                                </div>
                                            </div>
                                            <div>
                                                <label class="block text-slate-400 mb-0.5 text-[11px]">Độ đậm</label>
                                                <select 
                                                    x-model="studioTemplate.slots_config.text_slots[activeTextKey].fontWeight" 
                                                    class="w-full px-2 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs focus:border-amber-400 focus:outline-hidden"
                                                >
                                                    <option value="normal">Bình thường (Normal)</option>
                                                    <option value="bold">Đậm (Bold)</option>
                                                    <option value="900">Rất đậm (Black 900)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Kiểu chữ Serif / Sans-serif & Align -->
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <label class="block text-slate-400 mb-0.5 text-[11px]">Kiểu Font</label>
                                                <select 
                                                    x-model="studioTemplate.slots_config.text_slots[activeTextKey].fontFamily" 
                                                    class="w-full px-2 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs focus:border-amber-400 focus:outline-hidden"
                                                >
                                                    <option value="sans-serif">Sans-serif (Hiện đại)</option>
                                                    <option value="serif">Serif (Có chân / Sang trọng)</option>
                                                </select>
                                            </div>
                                            <div>
                                                <label class="block text-slate-400 mb-0.5 text-[11px]">Căn lề (Align)</label>
                                                <select 
                                                    x-model="studioTemplate.slots_config.text_slots[activeTextKey].align" 
                                                    class="w-full px-2 py-1.5 rounded-lg bg-slate-900 border border-slate-700 text-white text-xs focus:border-amber-400 focus:outline-hidden"
                                                >
                                                    <option value="left">Căn trái (Left)</option>
                                                    <option value="center">Căn giữa (Center)</option>
                                                    <option value="right">Căn phải (Right)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <!-- Tùy chọn Dạng thẻ bo góc (Badge) -->
                                        <div class="pt-2.5 border-t border-slate-700/60">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <span class="text-slate-300 font-bold text-[11px] block">Dạng thẻ bo góc (Badge)</span>
                                                    <span class="text-[9px] text-slate-400">Tạo nền bo góc & viền quanh khối chữ</span>
                                                </div>
                                                <input 
                                                    type="checkbox" 
                                                    x-model="studioTemplate.slots_config.text_slots[activeTextKey].isBadge" 
                                                    class="rounded bg-slate-900 border-slate-700 text-amber-500 focus:ring-amber-400 w-4 h-4 cursor-pointer"
                                                >
                                            </div>
                                            <template x-if="studioTemplate.slots_config.text_slots[activeTextKey].isBadge">
                                                <div class="grid grid-cols-2 gap-2 mt-2 pt-2 border-t border-slate-800">
                                                    <div>
                                                        <label class="block text-slate-400 mb-0.5 text-[10px]">Màu nền thẻ</label>
                                                        <div class="flex items-center gap-1.5">
                                                            <input type="color" x-model="studioTemplate.slots_config.text_slots[activeTextKey].bgColor" class="w-7 h-7 rounded border border-slate-700 bg-transparent cursor-pointer p-0">
                                                            <input type="text" x-model="studioTemplate.slots_config.text_slots[activeTextKey].bgColor" class="w-full px-2 py-1 rounded bg-slate-900 border border-slate-700 text-white text-xs font-mono">
                                                        </div>
                                                    </div>
                                                    <div>
                                                        <label class="block text-slate-400 mb-0.5 text-[10px]">Màu viền thẻ</label>
                                                        <div class="flex items-center gap-1.5">
                                                            <input type="color" x-model="studioTemplate.slots_config.text_slots[activeTextKey].borderColor" class="w-7 h-7 rounded border border-slate-700 bg-transparent cursor-pointer p-0">
                                                            <input type="text" x-model="studioTemplate.slots_config.text_slots[activeTextKey].borderColor" class="w-full px-2 py-1 rounded bg-slate-900 border border-slate-700 text-white text-xs font-mono">
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </template>

                                <template x-if="!activeTextKey || !studioTemplate.slots_config?.text_slots?.[activeTextKey]">
                                    <div class="h-full flex flex-col items-center justify-center text-center p-6 text-slate-500 border border-dashed border-slate-800 rounded-2xl">
                                        <i class="fa-solid fa-i-cursor text-2xl text-slate-600 mb-2"></i>
                                        <p class="font-bold text-slate-400">Chưa chọn khối chữ nào</p>
                                        <p class="text-[11px] text-slate-500 mt-1">Bấm chọn một khối chữ ở danh sách bên trái hoặc bấm trực tiếp lên bảng phôi để tùy chỉnh.</p>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

</div>

@push('scripts')
<script>
function unitProjectHub() {
    return {
        activeTab: '{{ request('tab', 'units') }}',
        viewMode: 'table',
        lightboxOpen: false,
        editModalOpen: false,
        floorplanModalOpen: false,
        uploadTemplateModalOpen: false,
        perspectiveModalOpen: false,
        templateStudioOpen: false,
        perspectiveCategory: 'all',
        isSavingTemplate: false,

        // Reactive Counts
        floorplanCount: {{ $floorplans->count() }},
        templateCount: {{ $templates->count() }},
        perspectiveCount: {{ $perspectives->count() }},
        unitCount: {{ $units->total() }},

        // Selection State
        selectedUnitIds: [],
        currentPageUnitIds: {{ json_encode($units->pluck('id')) }},
        allProjectUnitIds: {{ json_encode(\App\Models\ProjectUnitLocator::where('project_id', $project->id)->pluck('id')) }},

        // Template Mapper Studio State
        studioTemplate: {
            id: null,
            name: '',
            image_url: '',
            width: 1000,
            height: 1400,
            slots_config: null
        },
        activeSlotIndex: null,
        activeTextKey: null,
        studioTab: 'slots',
        boardW: 550,
        boardH: 770,
        mapperScale: 0.55,
        isDragging: false,
        isResizing: false,
        dragMode: null, // 'slot', 'resize', 'text'
        dragItemIndex: null,
        dragTextKey: null,
        dragStartMouse: { x: 0, y: 0 },
        dragStartItem: { x: 0, y: 0, w: 0, h: 0 },

        currentLightbox: {
            image: '',
            code: '',
            unit: null
        },
        editingUnit: {
            id: null,
            unit_code: '',
            block: '',
            floor: '',
            unit_axis: '',
            area: '',
            area_gross: '',
            direction: '',
            door_direction: '',
            bedroom: '',
            bathroom: '',
            price_text: '',
            status: 'available',
            note: '',
            layout_3d_image_url: null
        },

        // Quick Bóc Mái Modal States
        quickBocMaiModalOpen: false,
        activeBocMaiUnit: null,
        bocMaiSourceTab: 'upload',
        selectedBocMaiFile: null,
        selectedBocMaiFileName: '',
        bocMaiFilePreview: null,
        selectedPerspectivePath: '',
        syncAxisBocMai: true,
        isSavingBocMai: false,

        // Quick Định Vị Mặt Bằng Modal States
        quickLocatorModalOpen: false,
        activeLocatorUnit: null,
        locatorSourceTab: 'upload',
        selectedLocatorFile: null,
        selectedLocatorFileName: '',
        locatorFilePreview: null,
        selectedFloorplanPath: '',
        syncAxisLocator: true,
        isSavingLocator: false,

        // AJAX Search & Filter State
        isLoadingUnits: false,

        init() {
            window.__selectedUnitIds = this.selectedUnitIds || [];
            this.$watch('selectedUnitIds', (val) => {
                window.__selectedUnitIds = val || [];
            });
            this.calcBoardDimensions();
            this.$nextTick(() => {
                this.bindPaginationLinks(document.getElementById('units-list-container'));
            });
            window.addEventListener('resize', () => {
                if (this.templateStudioOpen) this.calcBoardDimensions();
            });
            window.addEventListener('popstate', () => {
                this.applyFilters(window.location.href, false);
            });
        },

        calcBoardDimensions() {
            if (!this.studioTemplate) return;
            const nativeW = this.studioTemplate.slots_config?.canvas?.width || 1000;
            const nativeH = this.studioTemplate.slots_config?.canvas?.height || 1400;
            const ratio = nativeW / nativeH;

            // Chiều cao tối đa khả dụng = chiều cao toàn màn hình trừ toolbar (54px) và padding (28px)
            const availableH = Math.max(300, window.innerHeight - 82);
            // Chiều rộng tối đa khả dụng = màn hình trừ sidebar công cụ 2 cột (660px) và lề an toàn (40px)
            const maxW = Math.max(300, window.innerWidth - 700);

            // Tự động tính kích thước phôi vừa vặn chiều cao màn hình (không sinh thanh cuộn dọc)
            let h = availableH;
            let w = h * ratio;

            if (w > maxW) {
                w = maxW;
                h = w / ratio;
            }

            this.boardW = Math.round(w);
            this.boardH = Math.round(h);
            this.mapperScale = this.boardW / nativeW;
        },

        openNewTemplateModal() {
            this.uploadTemplateModalOpen = true;
        },

        createDefaultPresetTemplate() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('admin.unit-locators.templates.store') }}';
            form.innerHTML = `
                @csrf
                <input type="hidden" name="project_id" value="{{ $project->id }}">
                <input type="hidden" name="name" value="Phôi Luxury Gold 6 Ô (Chuẩn)">
                <input type="hidden" name="is_default" value="1">
            `;
            document.body.appendChild(form);
            form.submit();
        },

        openTemplateStudio(template) {
            this.studioTemplate = JSON.parse(JSON.stringify(template));
            if (!this.studioTemplate.slots_config) {
                this.resetToPreset();
            }
            if (!this.studioTemplate.slots_config.text_slots) {
                this.studioTemplate.slots_config.text_slots = {};
            }
            if (this.studioTemplate.slots_config?.image_slots) {
                this.studioTemplate.slots_config.image_slots.forEach(s => {
                    if (s.border === undefined || s.border === '#ffffff') s.border = 'none';
                    if (s.borderWidth === undefined || s.border === 'none') s.borderWidth = 0;
                    if (s.radius === undefined) s.radius = 0;
                });
            }
            this.activeSlotIndex = (this.studioTemplate.slots_config?.image_slots?.length > 0) ? 0 : null;
            this.activeTextKey = null;
            this.studioTab = 'slots';
            this.calcBoardDimensions();
            this.templateStudioOpen = true;
            this.$nextTick(() => {
                this.calcBoardDimensions();
            });
        },

        addSlot() {
            if (!this.studioTemplate.slots_config) this.resetToPreset();
            if (!this.studioTemplate.slots_config.image_slots) {
                this.studioTemplate.slots_config.image_slots = [];
            }
            const count = this.studioTemplate.slots_config.image_slots.length + 1;
            const newSlot = {
                id: 'slot_' + Date.now(),
                key: 'slot_' + count,
                name: count + '. Khung ảnh mới',
                x: 100 + (count * 20),
                y: 200 + (count * 20),
                width: 280,
                height: 220,
                radius: 0,
                border: 'none',
                borderWidth: 0
            };
            this.studioTemplate.slots_config.image_slots.push(newSlot);
            this.activeSlotIndex = this.studioTemplate.slots_config.image_slots.length - 1;
            this.activeTextKey = null;
            this.studioTab = 'slots';
        },

        removeSlot(index) {
            this.studioTemplate.slots_config.image_slots.splice(index, 1);
            this.activeSlotIndex = this.studioTemplate.slots_config.image_slots.length > 0 ? 0 : null;
        },

        addTextSlot(key) {
            if (!this.studioTemplate.slots_config) this.resetToPreset();
            if (!this.studioTemplate.slots_config.text_slots) {
                this.studioTemplate.slots_config.text_slots = {};
            }

            let finalKey = key;
            if (key === 'custom') {
                const count = Object.keys(this.studioTemplate.slots_config.text_slots).filter(k => k.startsWith('custom_')).length + 1;
                finalKey = 'custom_' + Date.now();
            }

            const presets = {
                unit_code: {
                    label: 'Mã căn',
                    sampleText: 'C2.3-27-08',
                    x: 60,
                    y: 45,
                    fontSize: 26,
                    color: '#ffffff',
                    bgColor: 'rgba(0,0,0,0.55)',
                    borderColor: '#d97706',
                    isBadge: true,
                    fontWeight: '900',
                    fontFamily: 'sans-serif',
                    align: 'center'
                },
                unit_type: {
                    label: 'Loại căn (2PN)',
                    sampleText: '2PN',
                    x: 395,
                    y: 45,
                    fontSize: 24,
                    color: '#78350f',
                    bgColor: '#fbbf24',
                    borderColor: 'transparent',
                    isBadge: true,
                    fontWeight: '900',
                    fontFamily: 'sans-serif',
                    align: 'center'
                },
                direction: {
                    label: 'Hướng',
                    prefix: 'Hướng: ',
                    sampleText: 'NAM',
                    x: 60,
                    y: 120,
                    fontSize: 20,
                    color: '#ffffff',
                    fontWeight: 'bold',
                    fontFamily: 'sans-serif',
                    align: 'left'
                },
                view: {
                    label: 'View',
                    prefix: 'View: ',
                    sampleText: 'Nội khu',
                    x: 60,
                    y: 155,
                    fontSize: 20,
                    color: '#ffffff',
                    fontWeight: 'bold',
                    fontFamily: 'sans-serif',
                    align: 'left'
                },
                area: {
                    label: 'Diện tích',
                    prefix: 'Diện tích thông thủy: ',
                    suffix: ' m²',
                    sampleText: '59.8',
                    x: 60,
                    y: 190,
                    fontSize: 20,
                    color: '#ffffff',
                    fontWeight: 'bold',
                    fontFamily: 'sans-serif',
                    align: 'left'
                },
                price: {
                    label: 'Giá bán',
                    sampleText: '2.039 tỷ',
                    x: 280,
                    y: 285,
                    fontSize: 44,
                    color: '#ffffff',
                    fontWeight: '900',
                    fontFamily: 'serif',
                    align: 'center'
                },
                price_note: {
                    label: 'Ghi chú giá',
                    sampleText: '(Chưa VAT và Kinh phí bảo trì)',
                    x: 280,
                    y: 335,
                    fontSize: 16,
                    color: '#e2e8f0',
                    fontWeight: 'normal',
                    fontFamily: 'sans-serif',
                    align: 'center'
                },
                block: {
                    label: 'Tòa / Block',
                    prefix: 'Tòa: ',
                    sampleText: 'Sky 2',
                    x: 60,
                    y: 225,
                    fontSize: 20,
                    color: '#ffffff',
                    fontWeight: 'bold',
                    fontFamily: 'sans-serif',
                    align: 'left'
                },
                floor: {
                    label: 'Tầng',
                    prefix: 'Tầng: ',
                    sampleText: '27',
                    x: 60,
                    y: 255,
                    fontSize: 20,
                    color: '#ffffff',
                    fontWeight: 'bold',
                    fontFamily: 'sans-serif',
                    align: 'left'
                }
            };

            const newSlot = presets[key] || {
                label: 'Khối chữ mới',
                sampleText: 'Nội dung văn bản',
                x: 100,
                y: 250,
                fontSize: 24,
                color: '#ffffff',
                fontWeight: 'bold',
                fontFamily: 'sans-serif',
                align: 'left'
            };

            this.studioTemplate.slots_config.text_slots[finalKey] = JSON.parse(JSON.stringify(newSlot));
            this.studioTemplate.slots_config.text_slots = { ...this.studioTemplate.slots_config.text_slots };
            this.activeTextKey = finalKey;
            this.activeSlotIndex = null;
            this.studioTab = 'text';
        },

        deleteTextSlot(key) {
            if (!this.studioTemplate.slots_config?.text_slots) return;
            delete this.studioTemplate.slots_config.text_slots[key];
            this.studioTemplate.slots_config.text_slots = { ...this.studioTemplate.slots_config.text_slots };
            const remaining = Object.keys(this.studioTemplate.slots_config.text_slots);
            this.activeTextKey = remaining.length > 0 ? remaining[0] : null;
        },

        getTextSlotLabel(key) {
            const slot = this.studioTemplate.slots_config?.text_slots?.[key];
            if (slot?.label) return slot.label;
            const map = {
                unit_code: 'Mã căn',
                unit_type: 'Loại căn (2PN)',
                direction: 'Hướng',
                view: 'View',
                area: 'Diện tích',
                price: 'Giá bán',
                price_note: 'Ghi chú giá',
                block: 'Tòa / Block',
                floor: 'Tầng'
            };
            return map[key] || ('Khối chữ (' + key + ')');
        },

        getTextSlotDisplaySample(key, slot) {
            if (!slot) return '';
            const prefix = (slot.prefix !== undefined && slot.prefix !== null && slot.prefix !== 'null') ? String(slot.prefix) : '';
            const suffix = (slot.suffix !== undefined && slot.suffix !== null && slot.suffix !== 'null') ? String(slot.suffix) : '';
            const sample = (slot.sampleText !== undefined && slot.sampleText !== null && slot.sampleText !== '') ? slot.sampleText : (
                key === 'unit_code' ? 'C2.3-27-08' :
                key === 'unit_type' ? '2PN' :
                key === 'direction' ? 'NAM' :
                key === 'view' ? 'Nội khu' :
                key === 'area' ? '59.8' :
                key === 'price' ? '2.039 tỷ' :
                key === 'price_note' ? '(Chưa VAT và KPBT)' :
                key === 'block' ? 'Sky 2' :
                key === 'floor' ? '27' :
                (slot.label || 'Văn bản')
            );
            return prefix + sample + suffix;
        },

        resetToPreset() {
            this.studioTemplate.slots_config = {
                canvas: { width: 1000, height: 1400, bgColor: '#451a03' },
                image_slots: [
                    { id: 'slot_1', key: 'boc_mai', name: '1. Mặt bằng bóc mái 3D', x: 650, y: 160, width: 280, height: 340, radius: 0, border: 'none', borderWidth: 0 },
                    { id: 'slot_2', key: 'mat_bang_tang', name: '2. Mặt bằng tầng chỉ căn', x: 630, y: 525, width: 330, height: 240, radius: 0, border: 'none', borderWidth: 0 },
                    { id: 'slot_3', key: 'phoi_canh_toa', name: '3. Phối cảnh tổng thể & tòa', x: 60, y: 380, width: 540, height: 210, radius: 0, border: 'none', borderWidth: 0 },
                    { id: 'slot_4', key: 'tien_ich_1', name: '4. Phối cảnh tiện ích', x: 60, y: 610, width: 260, height: 230, radius: 0, border: 'none', borderWidth: 0 },
                    { id: 'slot_5', key: 'phan_khu', name: '5. Mặt bằng phân khu / tiện ích', x: 340, y: 610, width: 260, height: 230, radius: 0, border: 'none', borderWidth: 0 },
                    { id: 'slot_6', key: 'phoi_canh_dem', name: '6. Phối cảnh tòa đêm lung linh', x: 200, y: 860, width: 760, height: 240, radius: 0, border: 'none', borderWidth: 0 }
                ],
                text_slots: {
                    unit_code: { label: 'Mã căn', sampleText: 'C2.3-27-08', x: 60, y: 45, fontSize: 26, color: '#ffffff', bgColor: 'rgba(0,0,0,0.55)', borderColor: '#d97706', isBadge: true, fontWeight: '900', fontFamily: 'sans-serif', align: 'center' },
                    unit_type: { label: 'Loại căn (2PN)', sampleText: '2PN', x: 395, y: 45, fontSize: 24, color: '#78350f', bgColor: '#fbbf24', borderColor: 'transparent', isBadge: true, fontWeight: '900', fontFamily: 'sans-serif', align: 'center' },
                    direction: { label: 'Hướng', prefix: 'Hướng: ', sampleText: 'NAM', x: 60, y: 120, fontSize: 20, color: '#ffffff', fontWeight: 'bold', fontFamily: 'sans-serif', align: 'left' },
                    view: { label: 'View', prefix: 'View: ', sampleText: 'Nội khu', x: 60, y: 155, fontSize: 20, color: '#ffffff', fontWeight: 'bold', fontFamily: 'sans-serif', align: 'left' },
                    area: { label: 'Diện tích', prefix: 'Diện tích thông thủy: ', suffix: ' m²', sampleText: '59.8', x: 60, y: 190, fontSize: 20, color: '#ffffff', fontWeight: 'bold', fontFamily: 'sans-serif', align: 'left' },
                    price: { label: 'Giá bán', sampleText: '2.039 tỷ', x: 280, y: 285, fontSize: 44, color: '#ffffff', fontWeight: '900', fontFamily: 'serif', align: 'center' }
                }
            };
        },

        startDragSlot(slot, index, e) {
            e.preventDefault();
            this.isDragging = true;
            this.dragMode = 'slot';
            this.dragItemIndex = index;
            this.activeSlotIndex = index;
            this.activeTextKey = null;
            this.dragStartMouse = { x: e.clientX, y: e.clientY };
            this.dragStartItem = { x: slot.x, y: slot.y, w: slot.width, h: slot.height };
        },

        startResizeSlot(slot, index, e) {
            e.preventDefault();
            this.isResizing = true;
            this.dragMode = 'resize';
            this.dragItemIndex = index;
            this.activeSlotIndex = index;
            this.dragStartMouse = { x: e.clientX, y: e.clientY };
            this.dragStartItem = { x: slot.x, y: slot.y, w: slot.width, h: slot.height };
        },

        startDragText(key, e) {
            e.preventDefault();
            this.isDragging = true;
            this.dragMode = 'text';
            this.dragTextKey = key;
            this.activeTextKey = key;
            this.activeSlotIndex = null;
            const tObj = this.studioTemplate.slots_config?.text_slots?.[key];
            this.dragStartMouse = { x: e.clientX, y: e.clientY };
            this.dragStartItem = { x: tObj?.x || 0, y: tObj?.y || 0 };
        },

        onStudioMouseMove(e) {
            if (!this.isDragging && !this.isResizing) return;
            const deltaX = (e.clientX - this.dragStartMouse.x) / this.mapperScale;
            const deltaY = (e.clientY - this.dragStartMouse.y) / this.mapperScale;

            if (this.dragMode === 'slot') {
                const slot = this.studioTemplate.slots_config.image_slots[this.dragItemIndex];
                if (slot) {
                    slot.x = Math.max(0, Math.round(this.dragStartItem.x + deltaX));
                    slot.y = Math.max(0, Math.round(this.dragStartItem.y + deltaY));
                }
            } else if (this.dragMode === 'resize') {
                const slot = this.studioTemplate.slots_config.image_slots[this.dragItemIndex];
                if (slot) {
                    slot.width = Math.max(60, Math.round(this.dragStartItem.w + deltaX));
                    slot.height = Math.max(40, Math.round(this.dragStartItem.h + deltaY));
                }
            } else if (this.dragMode === 'text') {
                const tObj = this.studioTemplate.slots_config.text_slots[this.dragTextKey];
                if (tObj) {
                    tObj.x = Math.max(0, Math.round(this.dragStartItem.x + deltaX));
                    tObj.y = Math.max(0, Math.round(this.dragStartItem.y + deltaY));
                }
            }
        },

        onStudioMouseUp(e) {
            this.isDragging = false;
            this.isResizing = false;
            this.dragMode = null;
        },

        onStudioKeyDown(e) {
            if (!this.templateStudioOpen) return;
            const tag = document.activeElement ? document.activeElement.tagName.toLowerCase() : '';
            if (tag === 'input' || tag === 'textarea' || tag === 'select') return;

            const step = e.shiftKey ? 5 : 1;

            if (this.activeSlotIndex !== null && this.studioTemplate?.slots_config?.image_slots) {
                const slot = this.studioTemplate.slots_config.image_slots[this.activeSlotIndex];
                if (!slot) return;

                if (e.altKey) {
                    if (e.key === 'ArrowRight') { slot.width += step; e.preventDefault(); }
                    else if (e.key === 'ArrowLeft') { slot.width = Math.max(20, slot.width - step); e.preventDefault(); }
                    else if (e.key === 'ArrowDown') { slot.height += step; e.preventDefault(); }
                    else if (e.key === 'ArrowUp') { slot.height = Math.max(20, slot.height - step); e.preventDefault(); }
                } else {
                    if (e.key === 'ArrowLeft') { slot.x = Math.max(0, slot.x - step); e.preventDefault(); }
                    else if (e.key === 'ArrowRight') { slot.x += step; e.preventDefault(); }
                    else if (e.key === 'ArrowUp') { slot.y = Math.max(0, slot.y - step); e.preventDefault(); }
                    else if (e.key === 'ArrowDown') { slot.y += step; e.preventDefault(); }
                }
            } else if (this.activeTextKey && this.studioTemplate?.slots_config?.text_slots) {
                const tObj = this.studioTemplate.slots_config.text_slots[this.activeTextKey];
                if (!tObj) return;

                if (e.key === 'ArrowLeft') { tObj.x = Math.max(0, tObj.x - step); e.preventDefault(); }
                else if (e.key === 'ArrowRight') { tObj.x += step; e.preventDefault(); }
                else if (e.key === 'ArrowUp') { tObj.y = Math.max(0, tObj.y - step); e.preventDefault(); }
                else if (e.key === 'ArrowDown') { tObj.y += step; e.preventDefault(); }
            }
        },

        saveTemplateConfig() {
            this.isSavingTemplate = true;
            if (this.studioTemplate.slots_config?.text_slots) {
                Object.values(this.studioTemplate.slots_config.text_slots).forEach(slot => {
                    if (slot.prefix === null || slot.prefix === undefined || slot.prefix === 'null') slot.prefix = '';
                    if (slot.suffix === null || slot.suffix === undefined || slot.suffix === 'null') slot.suffix = '';
                });
            }
            fetch(`{{ url('admin/unit-locators/templates') }}/${this.studioTemplate.id}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    _method: 'PUT',
                    name: this.studioTemplate.name,
                    slots_config: this.studioTemplate.slots_config
                })
            })
            .then(res => res.json())
            .then(data => {
                this.isSavingTemplate = false;
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'bottom-start',
                        icon: 'success',
                        title: 'Đã lưu cấu hình vị trí thành công!',
                        showConfirmButton: false,
                        timer: 2500,
                        timerProgressBar: true,
                        background: '#0f172a',
                        color: '#ffffff',
                        iconColor: '#f59e0b',
                        customClass: {
                            popup: 'border border-amber-500/40 rounded-xl shadow-2xl text-xs'
                        }
                    });
                } else {
                    Swal.fire({
                        toast: true,
                        position: 'bottom-start',
                        icon: 'error',
                        title: 'Lỗi: ' + (data.message || 'Không thể lưu'),
                        showConfirmButton: false,
                        timer: 3000,
                        background: '#0f172a',
                        color: '#ffffff',
                        customClass: {
                            popup: 'border border-rose-500/40 rounded-xl shadow-2xl text-xs'
                        }
                    });
                }
            })
            .catch(err => {
                this.isSavingTemplate = false;
                alert('Có lỗi xảy ra khi lưu: ' + err.message);
            });
        },

        openLightbox(imageUrl, code, unit) {
            this.currentLightbox = {
                image: imageUrl,
                code: code,
                unit: unit
            };
            this.lightboxOpen = true;
        },

        openEditModal(unit) {
            this.editingUnit = {
                id: unit.id,
                unit_code: unit.unit_code || '',
                block: unit.block || '',
                floor: unit.floor || '',
                unit_axis: unit.unit_axis || '',
                area: unit.area || '',
                area_gross: unit.area_gross || '',
                direction: unit.direction || '',
                door_direction: unit.door_direction || '',
                bedroom: unit.bedroom || '',
                bathroom: unit.bathroom || '',
                price_text: unit.price_text || '',
                status: unit.status || 'available',
                note: unit.note || '',
                layout_3d_image_url: unit.layout_3d_image_url || null
            };
            this.editModalOpen = true;
        },

        openQuickBocMaiModal(unit) {
            this.activeBocMaiUnit = unit;
            this.bocMaiSourceTab = 'upload';
            this.selectedBocMaiFile = null;
            this.selectedBocMaiFileName = '';
            this.bocMaiFilePreview = null;
            this.selectedPerspectivePath = '';
            this.syncAxisBocMai = true;
            this.isSavingBocMai = false;
            this.quickBocMaiModalOpen = true;
        },

        closeQuickBocMaiModal() {
            this.quickBocMaiModalOpen = false;
            this.activeBocMaiUnit = null;
            this.selectedBocMaiFile = null;
            this.selectedBocMaiFileName = '';
            this.bocMaiFilePreview = null;
            this.selectedPerspectivePath = '';
            this.isSavingBocMai = false;
        },

        handleQuickBocMaiFile(event) {
            const file = event.target.files[0];
            if (file) {
                this.selectedBocMaiFile = file;
                this.selectedBocMaiFileName = file.name;
                this.selectedPerspectivePath = '';
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.bocMaiFilePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        selectPerspectiveForBocMai(path, url) {
            this.selectedPerspectivePath = path;
            this.selectedBocMaiFile = null;
            this.selectedBocMaiFileName = '';
            this.bocMaiFilePreview = url;
        },

        saveQuickBocMai() {
            if (!this.activeBocMaiUnit) return;
            if (!this.selectedBocMaiFile && !this.selectedPerspectivePath) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa chọn ảnh',
                    text: 'Vui lòng chọn 1 file ảnh từ máy tính hoặc từ Kho Phối Cảnh!',
                    confirmButtonText: 'Đã hiểu'
                });
                return;
            }

            this.isSavingBocMai = true;
            const formData = new FormData();
            if (this.selectedBocMaiFile) {
                formData.append('layout_3d_file', this.selectedBocMaiFile);
            } else if (this.selectedPerspectivePath) {
                formData.append('layout_3d_image', this.selectedPerspectivePath);
            }
            formData.append('sync_axis', this.syncAxisBocMai ? '1' : '0');

            fetch(`{{ url('admin/unit-locators') }}/${this.activeBocMaiUnit.id}/layout-3d`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.isSavingBocMai = false;
                if (data.success) {
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Đã cập nhật ảnh bóc mái!'
                    });

                    const newUrl = data.layout_3d_image_url;
                    const unitId = this.activeBocMaiUnit.id;
                    const unitAxis = this.activeBocMaiUnit.unit_axis;

                    if (this.syncAxisBocMai && unitAxis) {
                        document.querySelectorAll(`[data-unit-axis="${unitAxis}"]`).forEach(el => {
                            const id = el.getAttribute('data-unit-id');
                            if (id) {
                                this.updateUnitBocMaiDOM(id, newUrl, unitAxis);
                            }
                        });
                    } else {
                        this.updateUnitBocMaiDOM(unitId, newUrl, unitAxis);
                    }

                    this.closeQuickBocMaiModal();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: data.message || 'Không thể cập nhật ảnh bóc mái.'
                    });
                }
            })
            .catch(err => {
                this.isSavingBocMai = false;
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi kết nối',
                    text: 'Có lỗi xảy ra khi lưu ảnh. Vui lòng thử lại.'
                });
            });
        },

        deleteCurrentBocMai() {
            if (!this.activeBocMaiUnit) return;
            confirmAction({
                title: `Xóa ảnh bóc mái căn ${this.activeBocMaiUnit.unit_code}?`,
                text: this.syncAxisBocMai && this.activeBocMaiUnit.unit_axis 
                    ? `Ảnh sẽ bị xóa khỏi tất cả căn cùng trục ${this.activeBocMaiUnit.unit_axis}.`
                    : 'Ảnh bóc mái của căn này sẽ bị xóa khỏi hệ thống.',
                icon: 'warning',
                confirmButtonText: 'Đồng ý xóa',
                confirmButtonColor: '#ef4444'
            }).then(result => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('remove_image', '1');
                    formData.append('sync_axis', this.syncAxisBocMai ? '1' : '0');

                    fetch(`{{ url('admin/unit-locators') }}/${this.activeBocMaiUnit.id}/layout-3d`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Toast.fire({
                                icon: 'success',
                                title: 'Đã xóa ảnh bóc mái!'
                            });

                            const unitId = this.activeBocMaiUnit.id;
                            const unitAxis = this.activeBocMaiUnit.unit_axis;
                            if (this.syncAxisBocMai && unitAxis) {
                                document.querySelectorAll(`[data-unit-axis="${unitAxis}"]`).forEach(el => {
                                    const id = el.getAttribute('data-unit-id');
                                    if (id) {
                                        this.updateUnitBocMaiDOM(id, null, unitAxis);
                                    }
                                });
                            } else {
                                this.updateUnitBocMaiDOM(unitId, null, unitAxis);
                            }

                            this.closeQuickBocMaiModal();
                        }
                    });
                }
            });
        },

        updateUnitBocMaiDOM(id, url, axis) {
            // Update Table View cell
            const cell = document.getElementById(`unit-bocmai-cell-${id}`);
            if (cell) {
                if (url) {
                    cell.innerHTML = `
                        <div class="inline-flex items-center gap-1.5">
                            <div class="w-9 h-9 rounded-lg overflow-hidden bg-slate-900 border border-indigo-200 shrink-0 shadow-2xs cursor-pointer relative group/bocmai">
                                <img src="${url}" alt="Bóc mái" class="w-full h-full object-cover group-hover/bocmai:scale-110 transition duration-300">
                                <div class="absolute inset-0 bg-black/35 flex items-center justify-center opacity-0 group-hover/bocmai:opacity-100 transition">
                                    <i class="fa-solid fa-magnifying-glass-plus text-white text-[9px]"></i>
                                </div>
                            </div>
                            <button type="button" class="w-6 h-6 rounded-md bg-slate-50 hover:bg-indigo-50 text-slate-400 hover:text-indigo-600 transition flex items-center justify-center border border-slate-200 hover:border-indigo-300" title="Đổi ảnh hoặc đồng bộ trục">
                                <i class="fa-solid fa-pen text-[10px]"></i>
                            </button>
                        </div>
                    `;
                    const imgDiv = cell.querySelector('.group\\/bocmai');
                    if (imgDiv) {
                        imgDiv.onclick = () => this.openLightbox(url, 'Bóc mái căn', null);
                    }
                    const editBtn = cell.querySelector('button');
                    if (editBtn) {
                        editBtn.onclick = () => this.openQuickBocMaiModal({ id: id, unit_code: '', unit_axis: axis, layout_3d_image_url: url });
                    }
                } else {
                    cell.innerHTML = `
                        <button type="button" class="inline-flex items-center gap-1 px-2 py-1 rounded-md text-[11px] font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50/70 hover:bg-indigo-100/80 border border-dashed border-indigo-300 transition" title="Thêm ảnh bóc mái 3D">
                            <i class="fa-solid fa-cube text-[10px]"></i>
                            <span>+ Bóc mái</span>
                        </button>
                    `;
                    const addBtn = cell.querySelector('button');
                    if (addBtn) {
                        addBtn.onclick = () => this.openQuickBocMaiModal({ id: id, unit_code: '', unit_axis: axis, layout_3d_image_url: null });
                    }
                }
            }

            // Update Grid View card
            const cardEl = document.getElementById(`unit-card-bocmai-${id}`);
            if (cardEl) {
                if (url) {
                    cardEl.innerHTML = `
                        <div class="flex items-center gap-1.5 text-slate-600">
                            <i class="fa-solid fa-cube text-indigo-500 text-xs"></i>
                            <span class="font-bold">Ảnh bóc mái 3D:</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800" title="Xem ảnh bóc mái lớn">
                                <span>Xem 3D</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                            </button>
                            <button type="button" class="w-5 h-5 rounded bg-white hover:bg-indigo-50 text-slate-400 hover:text-indigo-600 flex items-center justify-center border border-slate-200 transition" title="Đổi ảnh hoặc đồng bộ theo trục">
                                <i class="fa-solid fa-pen text-[9px]"></i>
                            </button>
                        </div>
                    `;
                    const viewBtn = cardEl.querySelector('button');
                    if (viewBtn) {
                        viewBtn.onclick = () => this.openLightbox(url, 'Bóc mái căn', null);
                    }
                    const editBtn = cardEl.querySelectorAll('button')[1];
                    if (editBtn) {
                        editBtn.onclick = () => this.openQuickBocMaiModal({ id: id, unit_code: '', unit_axis: axis, layout_3d_image_url: url });
                    }
                } else {
                    cardEl.innerHTML = `
                        <div class="flex items-center gap-1.5 text-slate-600">
                            <i class="fa-solid fa-cube text-indigo-500 text-xs"></i>
                            <span class="font-bold">Ảnh bóc mái 3D:</span>
                        </div>
                        <button type="button" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2 py-0.5 rounded border border-dashed border-indigo-300 transition">
                            <i class="fa-solid fa-plus text-[9px]"></i>
                            <span>Thêm ảnh</span>
                        </button>
                    `;
                    const addBtn = cardEl.querySelector('button');
                    if (addBtn) {
                        addBtn.onclick = () => this.openQuickBocMaiModal({ id: id, unit_code: '', unit_axis: axis, layout_3d_image_url: null });
                    }
                }
            }
        },

        openQuickLocatorModal(unit) {
            this.activeLocatorUnit = unit;
            this.locatorSourceTab = 'upload';
            this.selectedLocatorFile = null;
            this.selectedLocatorFileName = '';
            this.locatorFilePreview = null;
            this.selectedFloorplanPath = '';
            this.syncAxisLocator = true;
            this.isSavingLocator = false;
            this.quickLocatorModalOpen = true;
        },

        closeQuickLocatorModal() {
            this.quickLocatorModalOpen = false;
            this.activeLocatorUnit = null;
            this.selectedLocatorFile = null;
            this.selectedLocatorFileName = '';
            this.locatorFilePreview = null;
            this.selectedFloorplanPath = '';
            this.isSavingLocator = false;
        },

        handleQuickLocatorFile(event) {
            const file = event.target.files[0];
            if (file) {
                this.selectedLocatorFile = file;
                this.selectedLocatorFileName = file.name;
                this.selectedFloorplanPath = '';
                const reader = new FileReader();
                reader.onload = (e) => {
                    this.locatorFilePreview = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        },

        selectFloorplanForLocator(path, url) {
            this.selectedFloorplanPath = path;
            this.selectedLocatorFile = null;
            this.selectedLocatorFileName = '';
            this.locatorFilePreview = url;
        },

        saveQuickLocator() {
            if (!this.activeLocatorUnit) return;
            if (!this.selectedLocatorFile && !this.selectedFloorplanPath) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Chưa chọn ảnh',
                    text: 'Vui lòng chọn 1 file ảnh từ máy tính hoặc từ Mặt bằng tầng/Kho phối cảnh!',
                    confirmButtonText: 'Đã hiểu'
                });
                return;
            }

            this.isSavingLocator = true;
            const formData = new FormData();
            if (this.selectedLocatorFile) {
                formData.append('floorplan_file', this.selectedLocatorFile);
            } else if (this.selectedFloorplanPath) {
                formData.append('floorplan_image', this.selectedFloorplanPath);
            }
            formData.append('sync_axis', this.syncAxisLocator ? '1' : '0');

            fetch(`{{ url('admin/unit-locators') }}/${this.activeLocatorUnit.id}/floorplan-image`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                this.isSavingLocator = false;
                if (data.success) {
                    Toast.fire({
                        icon: 'success',
                        title: data.message || 'Đã cập nhật ảnh định vị mặt bằng!'
                    });

                    const newUrl = data.floorplan_image_url;
                    const unitId = this.activeLocatorUnit.id;
                    const unitAxis = this.activeLocatorUnit.unit_axis;

                    if (this.syncAxisLocator && unitAxis) {
                        document.querySelectorAll(`[data-unit-axis="${unitAxis}"]`).forEach(el => {
                            const id = el.getAttribute('data-unit-id');
                            if (id) {
                                this.updateUnitLocatorDOM(id, newUrl, unitAxis);
                            }
                        });
                    } else {
                        this.updateUnitLocatorDOM(unitId, newUrl, unitAxis);
                    }

                    this.closeQuickLocatorModal();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Lỗi',
                        text: data.message || 'Không thể cập nhật ảnh định vị mặt bằng.'
                    });
                }
            })
            .catch(err => {
                this.isSavingLocator = false;
                console.error(err);
                Swal.fire({
                    icon: 'error',
                    title: 'Lỗi kết nối',
                    text: 'Có lỗi xảy ra khi lưu ảnh. Vui lòng thử lại.'
                });
            });
        },

        deleteCurrentLocator() {
            if (!this.activeLocatorUnit) return;
            confirmAction({
                title: `Xóa ảnh định vị căn ${this.activeLocatorUnit.unit_code}?`,
                text: this.syncAxisLocator && this.activeLocatorUnit.unit_axis 
                    ? `Ảnh sẽ bị xóa khỏi tất cả căn cùng trục ${this.activeLocatorUnit.unit_axis}.`
                    : 'Ảnh định vị của căn này sẽ bị xóa.',
                icon: 'warning',
                confirmButtonText: 'Đồng ý xóa',
                confirmButtonColor: '#ef4444'
            }).then(result => {
                if (result.isConfirmed) {
                    const formData = new FormData();
                    formData.append('remove_image', '1');
                    formData.append('sync_axis', this.syncAxisLocator ? '1' : '0');

                    fetch(`{{ url('admin/unit-locators') }}/${this.activeLocatorUnit.id}/floorplan-image`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: formData
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Toast.fire({
                                icon: 'success',
                                title: 'Đã xóa ảnh định vị!'
                            });

                            const unitId = this.activeLocatorUnit.id;
                            const unitAxis = this.activeLocatorUnit.unit_axis;
                            if (this.syncAxisLocator && unitAxis) {
                                document.querySelectorAll(`[data-unit-axis="${unitAxis}"]`).forEach(el => {
                                    const id = el.getAttribute('data-unit-id');
                                    if (id) {
                                        this.updateUnitLocatorDOM(id, null, unitAxis);
                                    }
                                });
                            } else {
                                this.updateUnitLocatorDOM(unitId, null, unitAxis);
                            }

                            this.closeQuickLocatorModal();
                        }
                    });
                }
            });
        },

        updateUnitLocatorDOM(id, url, axis) {
            // Update Table View cell
            const cell = document.getElementById(`unit-locator-cell-${id}`);
            if (cell) {
                if (url) {
                    cell.innerHTML = `
                        <div class="inline-flex items-center gap-1">
                            <div class="w-8 h-8 rounded-lg overflow-hidden bg-slate-900 border border-teal-200 shrink-0 shadow-2xs cursor-pointer relative group/locator">
                                <img src="${url}" alt="Định vị MB" class="w-full h-full object-cover group-hover/locator:scale-110 transition duration-300">
                                <div class="absolute inset-0 bg-black/35 flex items-center justify-center opacity-0 group-hover/locator:opacity-100 transition">
                                    <i class="fa-solid fa-magnifying-glass-plus text-white text-[8px]"></i>
                                </div>
                            </div>
                            <button type="button" class="w-5 h-5 rounded bg-slate-50 hover:bg-teal-50 text-slate-400 hover:text-teal-600 transition flex items-center justify-center border border-slate-200 hover:border-teal-300" title="Đổi ảnh định vị mặt bằng hoặc đồng bộ theo trục">
                                <i class="fa-solid fa-pen text-[9px]"></i>
                            </button>
                        </div>
                    `;
                    const imgBox = cell.querySelector('.group\\/locator');
                    if (imgBox) {
                        imgBox.onclick = () => this.openLightbox(url, `Định vị MB`, { id: id, unit_axis: axis, floorplan_image_url: url });
                    }
                    const editBtn = cell.querySelector('button');
                    if (editBtn) {
                        editBtn.onclick = () => this.openQuickLocatorModal({ id: id, unit_code: '', unit_axis: axis, floorplan_image_url: url });
                    }
                } else {
                    cell.innerHTML = `
                        <button type="button" class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold text-teal-600 hover:text-teal-700 bg-teal-50/70 hover:bg-teal-100/80 border border-dashed border-teal-300 transition" title="Thêm ảnh định vị mặt bằng">
                            <i class="fa-solid fa-map text-[9px]"></i>
                            <span>+ Định vị</span>
                        </button>
                    `;
                    const addBtn = cell.querySelector('button');
                    if (addBtn) {
                        addBtn.onclick = () => this.openQuickLocatorModal({ id: id, unit_code: '', unit_axis: axis, floorplan_image_url: null });
                    }
                }
            }

            // Update Grid View card
            const cardEl = document.getElementById(`unit-card-locator-${id}`);
            if (cardEl) {
                if (url) {
                    cardEl.innerHTML = `
                        <div class="flex items-center gap-1.5 text-slate-600">
                            <i class="fa-solid fa-map text-teal-500 text-xs"></i>
                            <span class="font-bold">Định vị mặt bằng:</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <button type="button" class="inline-flex items-center gap-1 text-[11px] font-bold text-teal-600 hover:text-teal-800" title="Xem ảnh định vị mặt bằng lớn">
                                <span>Xem MB</span>
                                <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                            </button>
                            <button type="button" class="w-5 h-5 rounded bg-white hover:bg-teal-50 text-slate-400 hover:text-teal-600 flex items-center justify-center border border-slate-200 transition" title="Đổi ảnh hoặc đồng bộ theo trục">
                                <i class="fa-solid fa-pen text-[9px]"></i>
                            </button>
                        </div>
                    `;
                    const viewBtn = cardEl.querySelectorAll('button')[0];
                    if (viewBtn) {
                        viewBtn.onclick = () => this.openLightbox(url, `Định vị MB`, { id: id, unit_axis: axis, floorplan_image_url: url });
                    }
                    const editBtn = cardEl.querySelectorAll('button')[1];
                    if (editBtn) {
                        editBtn.onclick = () => this.openQuickLocatorModal({ id: id, unit_code: '', unit_axis: axis, floorplan_image_url: url });
                    }
                } else {
                    cardEl.innerHTML = `
                        <div class="flex items-center gap-1.5 text-slate-600">
                            <i class="fa-solid fa-map text-teal-500 text-xs"></i>
                            <span class="font-bold">Định vị mặt bằng:</span>
                        </div>
                        <button type="button" class="inline-flex items-center gap-1 text-[11px] font-bold text-teal-600 hover:text-teal-700 bg-teal-50 hover:bg-teal-100 px-2 py-0.5 rounded border border-dashed border-teal-300 transition">
                            <i class="fa-solid fa-plus text-[9px]"></i>
                            <span>Thêm ảnh</span>
                        </button>
                    `;
                    const addBtn = cardEl.querySelector('button');
                    if (addBtn) {
                        addBtn.onclick = () => this.openQuickLocatorModal({ id: id, unit_code: '', unit_axis: axis, floorplan_image_url: null });
                    }
                }
            }
        },

        copyUnitInfo(unit) {
            const lines = [
                `🏢 DỰ ÁN: {{ $project->name }}`,
                `🔑 MÃ CĂN: ${unit.unit_code}` + (unit.block ? ` (${unit.block}${unit.floor ? ' - ' + unit.floor : ''})` : ''),
                unit.area ? `📐 DIỆN TÍCH: ${unit.area} m²` : null,
                unit.direction ? `🧭 HƯỚNG: ${unit.direction}` : null,
                unit.door_direction ? `🚪 HƯỚNG CỬA: ${unit.door_direction}` : null,
                unit.bedroom ? `🛏 PHÒNG NGỦ: ${unit.bedroom} PN` + (unit.bathroom ? ` • ${unit.bathroom} WC` : '') : null,
                unit.price_text ? `💰 GIÁ BÁN: ${unit.price_text}` : null,
                unit.note ? `✨ ĐIỂM NỔI BẬT: ${unit.note}` : null,
                `📷 ẢNH CHỈ CĂN: ${unit.image_url || ''}`
            ].filter(Boolean).join('\n');

            navigator.clipboard.writeText(lines).then(() => {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Đã sao chép thông tin căn vào Clipboard!',
                    showConfirmButton: false,
                    timer: 2500
                });
            }).catch(err => {
                alert('Đã sao chép:\n' + lines);
            });
        },

        deleteUnit(id, code) {
            confirmAction({
                title: `Xóa chỉ căn ${code}?`,
                text: 'Hành động này sẽ xóa vĩnh viễn hình ảnh và dữ liệu chỉ căn này khỏi hệ thống.',
                icon: 'warning',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy bỏ',
                confirmButtonColor: '#ef4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ url('admin/unit-locators') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Toast.fire({
                                icon: 'success',
                                title: data.message || `Đã xóa chỉ căn ${code}!`
                            });
                            const elements = [
                                document.getElementById(`unit-row-${id}`),
                                document.getElementById(`unit-card-${id}`)
                            ];
                            elements.forEach(el => {
                                if (el) {
                                    el.style.transition = 'all 0.3s ease';
                                    el.style.opacity = '0';
                                    el.style.transform = 'scale(0.95)';
                                    setTimeout(() => el.remove(), 300);
                                }
                            });
                            this.unitCount = Math.max(0, this.unitCount - 1);
                            this.selectedUnitIds = this.selectedUnitIds.filter(item => item !== id);
                            this.currentPageUnitIds = this.currentPageUnitIds.filter(item => item !== id);
                            this.allProjectUnitIds = this.allProjectUnitIds.filter(item => item !== id);
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: data.message || 'Không thể xóa chỉ căn!'
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Delete unit error:', err);
                        Toast.fire({
                            icon: 'error',
                            title: 'Đã xảy ra lỗi khi xóa chỉ căn!'
                        });
                    });
                }
            });
        },

        applyFilters(customUrl = null, pushState = true) {
            this.isLoadingUnits = true;

            let url = customUrl;
            if (!url) {
                const form = this.$refs.filterForm;
                if (!form) {
                    this.isLoadingUnits = false;
                    return;
                }
                const formData = new FormData(form);
                const params = new URLSearchParams();
                for (const [key, value] of formData.entries()) {
                    if (value !== '' && value !== null && value !== undefined) {
                        params.append(key, value);
                    }
                }
                params.set('tab', 'units');
                if (!params.has('per_page')) {
                    params.set('per_page', '100');
                }
                url = '{{ route('admin.unit-locators.project', $project->id) }}?' + params.toString();
            }

            fetch(url, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                this.isLoadingUnits = false;
                if (data.success && data.html !== undefined) {
                    const container = document.getElementById('units-list-container');
                    if (container) {
                        container.innerHTML = data.html;
                        if (window.Alpine) {
                            window.Alpine.initTree(container);
                        }
                        this.bindPaginationLinks(container);
                    }
                    if (data.total !== undefined) {
                        this.unitCount = data.total;
                    }
                    if (data.page_unit_ids) {
                        this.currentPageUnitIds = data.page_unit_ids;
                    }
                    if (pushState) {
                        window.history.pushState({}, '', url);
                    }
                }
            })
            .catch(err => {
                this.isLoadingUnits = false;
                console.error('Lỗi tìm kiếm AJAX:', err);
            });
        },

        resetFilters() {
            const form = this.$refs.filterForm;
            if (form) {
                const searchInput = form.querySelector('input[name="search"]');
                if (searchInput) searchInput.value = '';

                const selects = form.querySelectorAll('select');
                selects.forEach(s => s.selectedIndex = 0);
            }
            this.applyFilters();
        },

        bindPaginationLinks(container) {
            if (!container) return;
            const links = container.querySelectorAll('nav a');
            links.forEach(link => {
                link.addEventListener('click', (e) => {
                    e.preventDefault();
                    const href = link.getAttribute('href');
                    if (href && href !== '#' && !href.startsWith('javascript:')) {
                        this.applyFilters(href);
                        container.scrollIntoView({ behavior: 'smooth', block: 'start' });
                    }
                });
            });
        },

        deleteFloorplan(id, name) {
            confirmAction({
                title: 'Xác nhận xóa mặt bằng?',
                text: `Bạn có chắc chắn muốn xóa mặt bằng "${name || 'này'}"? Thao tác này không thể hoàn tác!`,
                icon: 'warning',
                confirmButtonText: 'Có, xóa ngay',
                cancelButtonText: 'Hủy bỏ',
                confirmButtonColor: '#ef4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ url('admin/unit-locators/floorplans') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Toast.fire({
                                icon: 'success',
                                title: data.message || 'Đã xóa mặt bằng thành công!'
                            });

                            // Xóa trên bảng danh sách mặt bằng chính
                            const tableRow = document.getElementById(`floorplan-row-${id}`);
                            if (tableRow) {
                                tableRow.style.transition = 'all 0.3s ease';
                                tableRow.style.opacity = '0';
                                tableRow.style.transform = 'scale(0.97)';
                                setTimeout(() => {
                                    tableRow.remove();
                                    this.updateFloorplanIndices();
                                }, 300);
                            }

                            // Xóa trên modal quản lý mặt bằng
                            const modalItem = document.getElementById(`modal-floorplan-${id}`);
                            if (modalItem) {
                                modalItem.style.transition = 'all 0.3s ease';
                                modalItem.style.opacity = '0';
                                modalItem.style.transform = 'scale(0.97)';
                                setTimeout(() => {
                                    modalItem.remove();
                                }, 300);
                            }

                            this.floorplanCount = Math.max(0, this.floorplanCount - 1);
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: data.message || 'Không thể xóa mặt bằng!'
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Delete floorplan error:', err);
                        Toast.fire({
                            icon: 'error',
                            title: 'Đã xảy ra lỗi khi xóa mặt bằng!'
                        });
                    });
                }
            });
        },

        updateFloorplanIndices() {
            const sttCells = document.querySelectorAll('#floorplanTableBody .floorplan-stt');
            sttCells.forEach((cell, idx) => {
                cell.textContent = idx + 1;
            });
        },

        deleteTemplate(id, name) {
            confirmAction({
                title: 'Xác nhận xóa phôi mẫu?',
                text: `Bạn có chắc chắn muốn xóa phôi mẫu "${name || 'này'}"?`,
                icon: 'warning',
                confirmButtonText: 'Có, xóa ngay',
                cancelButtonText: 'Hủy bỏ',
                confirmButtonColor: '#ef4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ url('admin/unit-locators/templates') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Toast.fire({
                                icon: 'success',
                                title: data.message || 'Đã xóa phôi mẫu thành công!'
                            });
                            const el = document.getElementById(`template-card-${id}`);
                            if (el) {
                                el.style.transition = 'all 0.3s ease';
                                el.style.opacity = '0';
                                el.style.transform = 'scale(0.95)';
                                setTimeout(() => el.remove(), 300);
                            }
                            this.templateCount = Math.max(0, this.templateCount - 1);
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: data.message || 'Không thể xóa phôi mẫu!'
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Delete template error:', err);
                        Toast.fire({
                            icon: 'error',
                            title: 'Đã xảy ra lỗi khi xóa phôi mẫu!'
                        });
                    });
                }
            });
        },

        deletePerspective(id, name) {
            confirmAction({
                title: 'Xác nhận xóa ảnh?',
                text: `Bạn có chắc muốn xóa ảnh "${name || 'này'}"?`,
                icon: 'warning',
                confirmButtonText: 'Có, xóa ngay',
                cancelButtonText: 'Hủy bỏ',
                confirmButtonColor: '#ef4444'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`{{ url('admin/unit-locators/perspectives') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Toast.fire({
                                icon: 'success',
                                title: data.message || 'Đã xóa ảnh phối cảnh thành công!'
                            });
                            const el = document.getElementById(`perspective-card-${id}`);
                            if (el) {
                                el.style.transition = 'all 0.3s ease';
                                el.style.opacity = '0';
                                el.style.transform = 'scale(0.95)';
                                setTimeout(() => el.remove(), 300);
                            }
                            this.perspectiveCount = Math.max(0, this.perspectiveCount - 1);
                        } else {
                            Toast.fire({
                                icon: 'error',
                                title: data.message || 'Không thể xóa ảnh phối cảnh!'
                            });
                        }
                    })
                    .catch(err => {
                        console.error('Delete perspective error:', err);
                        Toast.fire({
                            icon: 'error',
                            title: 'Đã xảy ra lỗi khi xóa ảnh phối cảnh!'
                        });
                    });
                }
            });
        },

        get isAllSelected() {
            if (!this.currentPageUnitIds || this.currentPageUnitIds.length === 0) return false;
            return this.currentPageUnitIds.every(id => this.selectedUnitIds.includes(id));
        },

        toggleSelectAll(event) {
            const checked = event.target.checked;
            if (checked) {
                const set = new Set([...this.selectedUnitIds, ...this.currentPageUnitIds]);
                this.selectedUnitIds = Array.from(set);
            } else {
                this.selectedUnitIds = this.selectedUnitIds.filter(id => !this.currentPageUnitIds.includes(id));
            }
        },

        selectAllInProject() {
            this.selectedUnitIds = [...this.allProjectUnitIds];
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: `Đã chọn toàn bộ ${this.allProjectUnitIds.length} căn của dự án!`,
                showConfirmButton: false,
                timer: 2000
            });
        },

        clearSelection() {
            this.selectedUnitIds = [];
        },

        goToBulkStudio() {
            if (this.selectedUnitIds.length > 0) {
                window.location.href = `{{ route('admin.unit-locators.create', $project->id) }}?units=${this.selectedUnitIds.join(',')}`;
            } else {
                window.location.href = `{{ route('admin.unit-locators.create', $project->id) }}`;
            }
        },

        quickBulkStatus(newStatus) {
            const statusLabels = {
                available: 'Còn trống',
                reserved: 'Đang giữ chỗ',
                deposited: 'Đã đặt cọc',
                sold: 'Đã bán',
                locked: 'Tạm khóa'
            };
            const label = statusLabels[newStatus] || newStatus;

            Swal.fire({
                title: `Đổi trạng thái sang [${label}]?`,
                text: `Áp dụng cho ${this.selectedUnitIds.length} căn đang được chọn.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Đồng ý',
                cancelButtonText: 'Hủy'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('{{ route('admin.unit-locators.bulk-update', $project->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: this.selectedUnitIds,
                            apply_fields: ['status'],
                            status: newStatus
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Thành công!',
                                text: data.message,
                                timer: 1200,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Lỗi', data.message || 'Không thể đổi trạng thái', 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Lỗi', err.message, 'error');
                    });
                }
            });
        },

        async bulkReplaceLayout3d(event) {
            const file = event.target.files?.[0];
            event.target.value = '';
            if (!file || this.selectedUnitIds.length === 0) return;

            const result = await Swal.fire({
                title: 'Thay ảnh bóc mái hàng loạt?',
                text: `Ảnh “${file.name}” sẽ được gán cho ${this.selectedUnitIds.length} căn đang chọn.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Thay ảnh',
                cancelButtonText: 'Hủy',
                confirmButtonColor: '#7c3aed'
            });
            if (!result.isConfirmed) return;

            const formData = new FormData();
            this.selectedUnitIds.forEach(id => formData.append('ids[]', id));
            formData.append('layout_3d_file', file);

            Swal.fire({ title: 'Đang cập nhật ảnh...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
            try {
                const response = await fetch('{{ route('admin.unit-locators.bulk-layout-3d', $project->id) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message || 'Không thể cập nhật ảnh bóc mái.');

                await Swal.fire({ icon: 'success', title: 'Đã cập nhật', text: data.message, timer: 1600, showConfirmButton: false });
                window.location.reload();
            } catch (error) {
                Swal.fire('Lỗi', error.message, 'error');
            }
        },

        confirmBulkDelete() {
            Swal.fire({
                title: `Xóa vĩnh viễn ${this.selectedUnitIds.length} căn đã chọn?`,
                text: 'Các ảnh chỉ căn và dữ liệu liên quan sẽ bị xóa hoàn toàn khỏi hệ thống!',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Đồng ý xóa',
                cancelButtonText: 'Hủy bỏ'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch('{{ route('admin.unit-locators.bulk-delete', $project->id) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            ids: this.selectedUnitIds
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Đã xóa thành công!',
                                text: data.message,
                                timer: 1500,
                                showConfirmButton: false
                            }).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire('Lỗi', data.message || 'Không thể xóa các căn đã chọn', 'error');
                        }
                    })
                    .catch(err => {
                        Swal.fire('Lỗi', err.message, 'error');
                    });
                }
            });
        }
    };
}
</script>
@endpush
@endsection
