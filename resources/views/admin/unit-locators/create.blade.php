@extends('layouts.admin')

@section('title', 'Tạo Chỉ căn - ' . ($project->name ?? 'Dự án'))
@section('page-title', 'Tạo Chỉ Căn: ' . ($project->name ?? 'Dự án'))

@push('styles')
<style>
.custom-unit-scroll::-webkit-scrollbar {
    width: 4px;
}
.custom-unit-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.custom-unit-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.custom-unit-scroll::-webkit-scrollbar-thumb:hover {
    background: #94a3b8;
}
</style>
@endpush

@section('header-actions')
    <a 
        href="{{ route('admin.unit-locators.project', $project->id) }}" 
        class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 text-xs font-bold shadow-sm transition"
        title="Quay lại danh sách chỉ căn"
    >
        <i class="fa-solid fa-arrow-left text-xs"></i>
        <span>Các căn đã tạo</span>
    </a>
@endsection

@section('content')
<div class="-mt-4 sm:-mt-6 -mb-6 sm:-mb-8 flex flex-col h-[calc(100vh-5.5rem)] overflow-hidden space-y-2" x-data="unitCreatorStudio()">

    <!-- Mode Selector Bar (Nhỏ gọn 1 dòng) -->
    <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs px-3 py-1 flex items-center justify-between gap-2 shrink-0">
        <div class="flex items-center gap-1.5 overflow-x-auto">
            <button 
                type="button" 
                @click="mode = 'poster'"
                :class="mode === 'poster' ? 'bg-amber-600 text-white shadow-xs font-black' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold'"
                class="px-3 py-1 rounded-lg text-xs transition flex items-center gap-1.5 shrink-0"
            >
                <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                <span>1. Ghép Poster Chỉ Căn</span>
            </button>

            <!-- Nút Bật/Tắt Cột Danh sách căn -->
            <button 
                type="button" 
                x-show="mode === 'poster'"
                @click="showUnitsCol = !showUnitsCol"
                :class="showUnitsCol ? 'bg-amber-100 text-amber-900 border-amber-300 font-black' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold'"
                class="px-2.5 py-1 rounded-lg text-xs border border-transparent transition flex items-center gap-1.5 shrink-0"
                title="Bật/Tắt hiển thị cột danh sách các căn"
            >
                <i class="fa-solid fa-list-check text-xs text-amber-600"></i>
                <span>Danh sách căn</span>
                <template x-if="importedUnits.length > 0">
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-amber-200/80 text-amber-950 font-mono" x-text="importedUnits.length"></span>
                </template>
            </button>

            <button 
                type="button" 
                @click="mode = 'studio'"
                :class="mode === 'studio' ? 'bg-blue-600 text-white shadow-xs font-black' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold'"
                class="px-3 py-1 rounded-lg text-xs transition flex items-center gap-1.5 shrink-0"
            >
                <i class="fa-solid fa-crosshairs text-xs"></i>
                <span>2. Studio Chấm Ghim Mặt Bằng</span>
            </button>

            <!-- Nút Nhập từ Excel -->
            <button 
                type="button" 
                @click="openExcelModal()"
                class="px-3 py-1 rounded-lg text-xs font-bold transition flex items-center gap-1.5 shrink-0 bg-emerald-600 hover:bg-emerald-700 text-white shadow-xs"
                title="Tải lên danh sách căn hộ từ file Excel (.xlsx, .xls, .csv)"
            >
                <i class="fa-solid fa-file-excel text-xs"></i>
                <span>Nhập từ Excel</span>
                <template x-if="importedUnits.length > 0">
                    <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-emerald-800 text-emerald-100 font-mono" x-text="importedUnits.length"></span>
                </template>
            </button>
        </div>

        <div class="text-[11px] text-slate-500 font-semibold truncate shrink-0">
            Dự án: <strong class="text-slate-800">{{ $project->name }}</strong> (#{{ $project->id }})
        </div>
    </div>

    <!-- ==================== MODE 0: POSTER COMPOSER STUDIO (TỐI ƯU 1 MÀN HÌNH KHÔNG CUỘN) ==================== -->
    <div x-show="mode === 'poster'" class="flex-1 min-h-0 flex flex-col lg:flex-row gap-2.5 overflow-hidden">
        
        <!-- ==================== CỘT 1 (MỚI): DANH SÁCH CÁC CĂN & TICK CHỌN CĂN ĐANG SỬA ==================== -->
        <div 
            x-show="showUnitsCol"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 -translate-x-4"
            x-transition:enter-end="opacity-100 translate-x-0"
            class="w-full lg:w-56 xl:w-60 2xl:w-64 shrink-0 flex flex-col h-full min-h-0 bg-white rounded-xl border border-slate-200/80 shadow-xs p-2.5 space-y-2 overflow-hidden"
        >
            <!-- Header Cột 1 -->
            <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 shrink-0">
                <div class="flex items-center gap-1.5 min-w-0">
                    <span class="w-6 h-6 rounded-lg bg-amber-500/10 text-amber-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-list-check text-xs"></i>
                    </span>
                    <span class="text-xs font-black text-slate-800 uppercase tracking-tight truncate">Danh Sách Căn</span>
                </div>
                <div class="flex items-center gap-1">
                    <span 
                        class="text-[10px] font-extrabold px-1.5 py-0.5 rounded-md bg-amber-100 text-amber-900 font-mono shrink-0" 
                        x-text="filteredUnitList.length + (filteredUnitList.length !== importedUnits.length ? '/' + importedUnits.length : '') + ' căn'"
                        title="Số căn hiển thị"
                    ></span>
                    <button 
                        type="button" 
                        @click="showUnitsCol = false"
                        class="text-slate-400 hover:text-slate-600 p-0.5 rounded hover:bg-slate-100 transition text-xs hidden lg:inline-flex"
                        title="Thu gọn cột danh sách căn"
                    >
                        <i class="fa-solid fa-chevron-left text-[11px]"></i>
                    </button>
                </div>
            </div>

            <!-- Ô Tìm kiếm nhanh -->
            <div class="relative shrink-0">
                <i class="fa-solid fa-magnifying-glass absolute left-2.5 top-2.5 text-[10px] text-slate-400"></i>
                <input 
                    type="text" 
                    x-model="unitSearchText" 
                    placeholder="Tìm mã căn, loại căn..." 
                    class="w-full pl-7 pr-6 py-1 rounded-lg border border-slate-200 text-xs text-slate-800 placeholder:text-slate-400 focus:ring-1 focus:ring-amber-500 bg-slate-50 transition"
                >
                <button 
                    type="button" 
                    x-show="unitSearchText" 
                    @click="unitSearchText = ''" 
                    class="absolute right-2 top-1.5 text-slate-400 hover:text-slate-600 text-xs"
                    title="Xóa tìm kiếm"
                >
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>

            <!-- Bộ lọc trạng thái nhỏ gọn -->
            <div class="flex items-center gap-1 shrink-0 text-[10px]">
                <button 
                    type="button" 
                    @click="unitFilterStatus = 'all'" 
                    :class="unitFilterStatus === 'all' ? 'bg-slate-800 text-white font-bold shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                    class="px-1.5 py-0.5 rounded transition flex-1 text-center truncate"
                >
                    Tất cả
                </button>
                <template x-if="selectedUnitIds && selectedUnitIds.length > 0">
                    <button 
                        type="button" 
                        @click="unitFilterStatus = (unitFilterStatus === 'selected' ? 'all' : 'selected')" 
                        :class="unitFilterStatus === 'selected' ? 'bg-indigo-600 text-white font-bold shadow-2xs' : 'bg-indigo-50 text-indigo-700 hover:bg-indigo-100 font-medium'"
                        class="px-1.5 py-0.5 rounded transition flex-1 text-center truncate"
                        title="Lọc các căn được chọn từ bảng danh sách"
                    >
                        Đã chọn (<span x-text="selectedUnitIds.length"></span>)
                    </button>
                </template>
                <button 
                    type="button" 
                    @click="unitFilterStatus = (unitFilterStatus === 'has_image' ? 'all' : 'has_image')" 
                    :class="unitFilterStatus === 'has_image' ? 'bg-emerald-600 text-white font-bold shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                    class="px-1.5 py-0.5 rounded transition flex-1 text-center truncate"
                    title="Các căn đã có ảnh poster"
                >
                    Đã có ảnh
                </button>
                <button 
                    type="button" 
                    @click="unitFilterStatus = (unitFilterStatus === 'no_image' ? 'all' : 'no_image')" 
                    :class="unitFilterStatus === 'no_image' ? 'bg-amber-600 text-white font-bold shadow-2xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'"
                    class="px-1.5 py-0.5 rounded transition flex-1 text-center truncate"
                    title="Các căn chưa có ảnh poster"
                >
                    Chưa tạo
                </button>
            </div>

            <!-- Thanh công cụ Chọn nhiều / Chọn tất cả -->
            <div class="flex items-center justify-between px-2 py-1 bg-slate-50 rounded-lg border border-slate-200/80 text-[10px] shrink-0 select-none">
                <label class="flex items-center gap-1.5 cursor-pointer font-bold text-slate-700">
                    <input 
                        type="checkbox" 
                        :checked="isAllFilteredSelected" 
                        @change="toggleSelectAll()"
                        class="w-3.5 h-3.5 rounded border-slate-300 text-amber-600 focus:ring-amber-500 cursor-pointer"
                    >
                    <span>Chọn tất cả (<span x-text="filteredUnitList.length"></span>)</span>
                </label>

                <template x-if="selectedIndices.length > 1">
                    <div class="flex items-center gap-1">
                        <span class="font-black text-amber-800 bg-amber-100 px-1.5 py-0.2 rounded" x-text="'Đã chọn: ' + selectedIndices.length"></span>
                        <button 
                            type="button" 
                            @click="clearSelectionToCurrent()" 
                            class="text-slate-500 hover:text-rose-600 underline font-semibold ml-0.5"
                            title="Bỏ chọn các căn khác, chỉ giữ căn đang xem"
                        >
                            Bỏ chọn
                        </button>
                    </div>
                </template>
                <template x-if="selectedIndices.length <= 1">
                    <span class="text-slate-400 font-medium italic">Đang sửa 1 căn</span>
                </template>
            </div>

            <!-- Danh sách các căn (Cuộn nội bộ 1 màn hình) -->
            <div class="flex-1 overflow-y-auto space-y-1 pr-0.5 min-h-0 custom-unit-scroll" id="unitListScrollContainer">
                <template x-for="u in filteredUnitList" :key="u.id || u.originalIndex">
                    <div 
                        :id="'unit-item-' + u.originalIndex"
                        :class="selectedIndices.includes(u.originalIndex)
                            ? (currentUnitIndex === u.originalIndex 
                                ? 'bg-amber-50/90 border-amber-400 ring-1 ring-amber-400 shadow-xs' 
                                : 'bg-amber-50/40 border-amber-300/80 hover:bg-amber-50/70')
                            : 'bg-white hover:bg-slate-50 border-slate-200/80 hover:border-slate-300'"
                        class="p-2 rounded-lg border transition-all select-none group flex items-start gap-2"
                    >
                        <!-- Checkbox chọn 1 hoặc nhiều căn -->
                        <div 
                            @click.stop="toggleUnitSelection(u.originalIndex)" 
                            class="mt-0.5 shrink-0 cursor-pointer p-0.5 -m-0.5 rounded hover:bg-amber-100/80 transition"
                            :title="selectedIndices.includes(u.originalIndex) ? 'Bỏ chọn căn này' : 'Chọn căn này để sửa hàng loạt'"
                        >
                            <div 
                                :class="selectedIndices.includes(u.originalIndex) 
                                    ? 'bg-amber-500 border-amber-500 text-white shadow-2xs' 
                                    : 'bg-white border-slate-300 group-hover:border-slate-400'"
                                class="w-4 h-4 rounded border flex items-center justify-center transition"
                            >
                                <i x-show="selectedIndices.includes(u.originalIndex)" class="fa-solid fa-check text-[10px]"></i>
                            </div>
                        </div>

                        <!-- Thông tin căn (Click để xem trước / sửa riêng) -->
                        <div 
                            class="flex-1 min-w-0 cursor-pointer"
                            @click="onRowBodyClick(u.originalIndex)"
                        >
                            <div class="flex items-center justify-between gap-1">
                                <div class="flex items-center gap-1 min-w-0">
                                    <span class="text-[10px] font-mono text-slate-400 shrink-0" x-text="(u.originalIndex + 1) + '.'"></span>
                                    <span 
                                        :class="selectedIndices.includes(u.originalIndex) ? 'text-amber-950 font-black' : 'text-slate-800 font-bold'" 
                                        class="text-xs truncate" 
                                        x-text="u.unit_code"
                                    ></span>
                                </div>

                                <div class="flex items-center gap-1 shrink-0">
                                    <!-- Khi chọn 1 căn: nhãn ĐANG SỬA -->
                                    <template x-if="selectedIndices.length <= 1 && currentUnitIndex === u.originalIndex">
                                        <span class="text-[8px] font-black px-1 py-0.2 rounded bg-amber-500 text-white uppercase tracking-tighter shadow-2xs">
                                            ĐANG SỬA
                                        </span>
                                    </template>

                                    <!-- Khi chọn nhiều căn: nhãn ĐANG XEM cho căn đang preview trên canvas -->
                                    <template x-if="selectedIndices.length > 1 && currentUnitIndex === u.originalIndex">
                                        <span class="text-[8px] font-black px-1 py-0.2 rounded bg-indigo-600 text-white uppercase tracking-tighter shadow-2xs flex items-center gap-0.5" title="Đang hiển thị xem trước poster trên Canvas">
                                            <i class="fa-solid fa-eye text-[7px]"></i> ĐANG XEM
                                        </span>
                                    </template>

                                    <!-- Biểu tượng đã có ảnh poster -->
                                    <template x-if="u.has_image">
                                        <span class="text-[9px] text-emerald-600 font-bold" title="Đã có ảnh poster">
                                            <i class="fa-solid fa-circle-check"></i>
                                        </span>
                                    </template>
                                </div>
                            </div>

                            <!-- Dòng phụ: Loại căn, DT, Giá bán -->
                            <div class="flex items-center gap-1 text-[10px] text-slate-500 mt-0.5 truncate">
                                <span class="font-medium text-slate-700 truncate" x-text="u.unit_type || 'Căn hộ'"></span>
                                <template x-if="u.area">
                                    <span class="shrink-0" x-text="'• ' + u.area + 'm²'"></span>
                                </template>
                                <template x-if="u.price_text">
                                    <span class="text-blue-600 font-bold truncate" x-text="'• ' + u.price_text"></span>
                                </template>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Trạng thái tìm kiếm không thấy -->
                <template x-if="filteredUnitList.length === 0 && importedUnits.length > 0">
                    <div class="p-4 text-center text-slate-400 text-xs">
                        <i class="fa-solid fa-magnifying-glass text-slate-300 text-lg mb-1 block"></i>
                        <span>Không tìm thấy căn phù hợp</span>
                        <button type="button" @click="unitSearchText = ''; unitFilterStatus = 'all'" class="text-amber-600 font-bold block mt-1 hover:underline mx-auto">
                            Xóa bộ lọc
                        </button>
                    </div>
                </template>

                <!-- Trạng thái chưa có căn nào -->
                <template x-if="importedUnits.length === 0">
                    <div class="p-3 text-center text-slate-500 text-xs space-y-2">
                        <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center mx-auto text-slate-400">
                            <i class="fa-solid fa-inbox text-sm"></i>
                        </div>
                        <p class="text-[11px] text-slate-500">Chưa có danh sách căn nào trong dự án.</p>
                        <button 
                            type="button" 
                            @click="openExcelModal()" 
                            class="w-full py-1.5 px-2 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition flex items-center justify-center gap-1 shadow-xs"
                        >
                            <i class="fa-solid fa-file-excel text-xs"></i>
                            <span>Nhập từ Excel</span>
                        </button>
                    </div>
                </template>
            </div>

            <!-- Footer Cột 1 -->
            <div class="pt-1.5 border-t border-slate-100 flex items-center justify-between text-[10px] text-slate-500 shrink-0">
                <span class="flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block"></span>
                    <span>Đã xong: <strong class="text-emerald-700 font-bold" x-text="importedUnits.filter(u => u.has_image).length"></strong>/<span x-text="importedUnits.length"></span></span>
                </span>
                <button 
                    type="button" 
                    @click="openExcelModal()" 
                    class="text-emerald-700 hover:text-emerald-800 font-bold flex items-center gap-1 transition"
                    title="Nhập thêm hoặc nạp lại danh sách căn từ file Excel"
                >
                    <i class="fa-solid fa-file-arrow-up text-[10px]"></i>
                    <span x-text="importedUnits.length > 0 ? 'Nạp lại' : 'Nạp file'"></span>
                </button>
            </div>
        </div>

        <!-- Nút mở lại Cột 1 khi bị thu gọn -->
        <div x-show="!showUnitsCol" class="hidden lg:flex shrink-0">
            <button 
                type="button" 
                @click="showUnitsCol = true"
                class="h-full px-2 py-4 bg-white hover:bg-amber-50 border border-slate-200/80 rounded-xl shadow-xs text-slate-600 hover:text-amber-700 text-xs font-bold flex flex-col items-center justify-center gap-2 transition"
                title="Mở rộng danh sách căn"
            >
                <i class="fa-solid fa-list-check text-amber-500"></i>
                <span class="[writing-mode:vertical-rl] tracking-wider uppercase text-[10px] font-black">Danh sách căn</span>
                <span class="px-1.5 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[9px] font-mono" x-text="importedUnits.length"></span>
            </button>
        </div>

        <!-- Left: Template Selection, Unit Info Form & Assigned Slots (Pinned action buttons) -->
        <div class="w-full lg:w-72 xl:w-76 2xl:w-80 shrink-0 flex flex-col h-full min-h-0 justify-between gap-2 overflow-hidden">
            
            <!-- Card 1: Phôi & Thông tin căn hộ (Gọn gàng) -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-2.5 space-y-2 shrink-0">
                <!-- Phôi chọn -->
                <div class="flex items-center justify-between gap-1 pb-1 border-b border-slate-100">
                    <span class="text-[11px] font-black text-amber-900 uppercase flex items-center gap-1">
                        <i class="fa-solid fa-palette text-amber-500 text-xs"></i> Phôi
                    </span>
                    <select x-model="selectedTemplateId" @change="onTemplateChange()" class="py-0.5 px-2 rounded-lg border border-slate-200 text-xs font-bold text-slate-800 bg-slate-50 focus:ring-1 focus:ring-amber-500 max-w-[145px] truncate">
                        @foreach ($templates as $t)
                            <option value="{{ $t->id }}">{{ $t->name }}{{ $t->is_default ? ' (Mặc định)' : '' }}</option>
                        @endforeach
                        @if ($templates->isEmpty())
                            <option value="default">Phôi Luxury Gold 6 Ô</option>
                        @endif
                    </select>
                    <a href="{{ route('admin.unit-locators.project', ['projectId' => $project->id, 'tab' => 'templates']) }}" class="text-[10px] font-bold text-amber-600 hover:underline shrink-0" title="Quản lý & Cài đặt vị trí phôi">
                        Cài đặt &rarr;
                    </a>
                </div>

                <!-- Notice khi đang chọn nhiều căn: Không cho sửa thông tin, chỉ sửa ảnh -->
                <template x-if="selectedIndices.length > 1">
                    <div class="p-2 rounded-lg bg-amber-50/90 border border-amber-200 text-amber-900 text-xs flex items-start gap-2 shadow-2xs">
                        <i class="fa-solid fa-lock text-amber-600 mt-0.5 text-xs shrink-0"></i>
                        <div class="flex-1 min-w-0">
                            <div class="font-bold flex items-center justify-between gap-1">
                                <span>Đang chọn <span class="text-amber-800 font-black" x-text="selectedIndices.length"></span> căn</span>
                                <span class="text-[8px] font-black px-1.5 py-0.2 rounded bg-amber-200 text-amber-950 font-mono">CHỈ SỬA ẢNH</span>
                            </div>
                            <div class="text-[10px] text-amber-800/90 leading-tight mt-0.5">
                                Thông tin từng căn giữ riêng biệt. Bạn chỉ có thể sửa và gán <b>ảnh khung phôi</b> hàng loạt.
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Inputs thông số căn -->
                <div class="space-y-1.5 text-xs" :class="selectedIndices.length > 1 ? 'opacity-60 pointer-events-none select-none' : ''">
                    <!-- Row 1: Mã căn + Loại căn -->
                    <div class="grid grid-cols-2 gap-1.5">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Mã căn <span class="text-rose-500">*</span></label>
                            <input type="text" :disabled="selectedIndices.length > 1" x-model="posterForm.unit_code" @input="debouncedRender()" placeholder="C2.3-27-08" class="w-full px-2 py-1 rounded-md border border-slate-200 text-xs font-black text-slate-900 focus:ring-1 focus:ring-amber-500 disabled:bg-slate-100 disabled:text-slate-500 disabled:cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Loại căn</label>
                            <input type="text" :disabled="selectedIndices.length > 1" x-model="posterForm.unit_type" @input="debouncedRender()" placeholder="2PN" class="w-full px-2 py-1 rounded-md border border-slate-200 text-xs font-bold text-amber-700 focus:ring-1 focus:ring-amber-500 disabled:bg-slate-100 disabled:text-slate-500 disabled:cursor-not-allowed">
                        </div>
                    </div>

                    <!-- Row 2: Hướng + View -->
                    <div class="grid grid-cols-2 gap-1.5">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Hướng</label>
                            <select :disabled="selectedIndices.length > 1" x-model="posterForm.direction" @change="debouncedRender()" class="w-full px-1.5 py-1 rounded-md border border-slate-200 text-xs focus:ring-1 focus:ring-amber-500 bg-white font-bold disabled:bg-slate-100 disabled:text-slate-500 disabled:cursor-not-allowed">
                                <option value="">-- Hướng --</option>
                                <option value="NAM">NAM</option>
                                <option value="BẮC">BẮC</option>
                                <option value="ĐÔNG">ĐÔNG</option>
                                <option value="TÂY">TÂY</option>
                                <option value="ĐÔNG NAM">ĐÔNG NAM</option>
                                <option value="ĐÔNG BẮC">ĐÔNG BẮC</option>
                                <option value="TÂY NAM">TÂY NAM</option>
                                <option value="TÂY BẮC">TÂY BẮC</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">View</label>
                            <input type="text" :disabled="selectedIndices.length > 1" x-model="posterForm.view" @input="debouncedRender()" placeholder="Nội khu" class="w-full px-2 py-1 rounded-md border border-slate-200 text-xs focus:ring-1 focus:ring-amber-500 disabled:bg-slate-100 disabled:text-slate-500 disabled:cursor-not-allowed">
                        </div>
                    </div>

                    <!-- Row 3: Diện tích + Giá bán -->
                    <div class="grid grid-cols-2 gap-1.5">
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">DT thông thủy (m²)</label>
                            <input type="number" step="0.1" :disabled="selectedIndices.length > 1" x-model="posterForm.area" @input="debouncedRender()" placeholder="59.8" class="w-full px-2 py-1 rounded-md border border-slate-200 text-xs focus:ring-1 focus:ring-amber-500 font-bold text-slate-800 disabled:bg-slate-100 disabled:text-slate-500 disabled:cursor-not-allowed">
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Giá bán</label>
                            <input type="text" :disabled="selectedIndices.length > 1" x-model="posterForm.price_text" @input="debouncedRender()" placeholder="2.039 tỷ" class="w-full px-2 py-1 rounded-md border border-slate-200 text-xs font-bold text-blue-700 focus:ring-1 focus:ring-amber-500 disabled:bg-slate-100 disabled:text-slate-500 disabled:cursor-not-allowed">
                        </div>
                    </div>

                    <!-- Row 4: Ghi chú giá -->
                    <div>
                        <label class="block text-[10px] font-bold text-slate-600 mb-0.5">Ghi chú giá</label>
                        <input type="text" :disabled="selectedIndices.length > 1" x-model="posterForm.price_note" @input="debouncedRender()" placeholder="(Chưa VAT và KPBT)" class="w-full px-2 py-1 rounded-md border border-slate-200 text-xs focus:ring-1 focus:ring-amber-500 text-slate-600 disabled:bg-slate-100 disabled:text-slate-500 disabled:cursor-not-allowed">
                    </div>
                </div>
            </div>

            <!-- Card 2: Các khung ảnh trên phôi (Scroll nội bộ, hỗ trợ Kéo - Thả) -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-2.5 flex-1 min-h-0 flex flex-col overflow-hidden">
                <div class="flex items-center justify-between pb-1.5 border-b border-slate-100 shrink-0">
                    <span class="text-[11px] font-black text-slate-800 uppercase flex items-center gap-1.5">
                        <i class="fa-regular fa-images text-indigo-500 text-xs"></i>
                        <span>Khung ảnh trên phôi</span>
                    </span>
                    <span class="text-[10px] text-slate-400 font-bold" x-text="(currentTemplate?.slots_config?.image_slots || []).length + ' ô'"></span>
                </div>

                <div class="space-y-1.5 flex-1 overflow-y-auto pr-1 py-1 min-h-0">
                    <template x-for="(slot, sIndex) in (currentTemplate?.slots_config?.image_slots || [])" :key="slot.id || sIndex">
                        <div 
                            @dragover.prevent="$el.classList.add('border-indigo-500', 'bg-indigo-50')"
                            @dragleave="$el.classList.remove('border-indigo-500', 'bg-indigo-50')"
                            @drop.prevent="$el.classList.remove('border-indigo-500', 'bg-indigo-50'); const url = $event.dataTransfer.getData('text/plain'); if(url) assignPhotoToSlot(slot.id, url)"
                            class="p-1.5 rounded-lg border border-slate-200/80 bg-slate-50 hover:bg-slate-100/80 flex items-center justify-between gap-1.5 transition text-xs"
                        >
                            <div class="flex items-center gap-1.5 overflow-hidden">
                                <div class="w-7 h-7 rounded bg-slate-200 border border-slate-300 overflow-hidden shrink-0 flex items-center justify-center">
                                    <template x-if="slotImages[slot.id]">
                                        <img :src="slotImages[slot.id]" class="w-full h-full object-cover">
                                    </template>
                                    <template x-if="!slotImages[slot.id]">
                                        <i class="fa-regular fa-image text-slate-400 text-[10px]"></i>
                                    </template>
                                </div>
                                <div class="overflow-hidden">
                                    <h6 class="font-bold text-[11px] text-slate-800 truncate" x-text="slot.name"></h6>
                                    <span class="text-[9px] block truncate" 
                                          :class="slotImages[slot.id] ? 'text-emerald-600 font-semibold' : 'text-slate-400'" 
                                          x-text="slotImages[slot.id] ? (isFloorplanSlot(slot) ? '✓ Chỉ căn đúng tầng' : '✓ Đã gán ảnh') : 'Kéo thả ảnh vào đây'"></span>
                                </div>
                            </div>
                            <template x-if="slotImages[slot.id]">
                                <button type="button" @click="removeSlotImage(slot.id)" class="p-1 text-slate-400 hover:text-rose-600 shrink-0" title="Bỏ ảnh này">
                                    <i class="fa-solid fa-xmark text-xs"></i>
                                </button>
                            </template>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Card 3: Nút Lưu & Xuất (Luôn hiển thị cố định ở chân cột) -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs p-2 space-y-1.5 shrink-0">
                <!-- Khi chọn nhiều căn: Lưu & Áp dụng hàng loạt cho các căn đã chọn -->
                <template x-if="selectedIndices.length > 1">
                    <button 
                        type="button" 
                        @click="batchSaveSelectedUnits()"
                        :disabled="isBatchGenerating || isSavingPoster"
                        class="w-full py-2 px-3 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 disabled:opacity-50 text-white font-black text-xs transition flex items-center justify-center gap-1.5 shadow-md shadow-emerald-600/25"
                    >
                        <i class="fa-solid fa-bolt text-amber-300"></i>
                        <span x-text="isBatchGenerating ? 'Đang xuất và lưu...' : 'Lưu & Áp dụng cho ' + selectedIndices.length + ' căn đã chọn'"></span>
                    </button>
                </template>

                <!-- Khi chọn 1 căn: Lưu đơn lẻ -->
                <template x-if="selectedIndices.length <= 1">
                    <button 
                        type="button" 
                        @click="savePosterUnit(false)"
                        :disabled="isSavingPoster"
                        class="w-full py-2 px-3 rounded-lg bg-amber-500 hover:bg-amber-600 disabled:opacity-50 text-slate-950 font-black text-xs transition flex items-center justify-center gap-1.5 shadow-md shadow-amber-500/20"
                    >
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span x-text="isSavingPoster ? 'Đang xuất và lưu...' : 'Lưu & Hoàn tất Chỉ Căn'"></span>
                    </button>
                </template>

                <div class="grid grid-cols-2 gap-1.5">
                    <template x-if="selectedIndices.length <= 1">
                        <button 
                            type="button" 
                            @click="savePosterUnit(true)"
                            :disabled="isSavingPoster"
                            class="py-1.5 px-2 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-[11px] transition flex items-center justify-center gap-1 truncate"
                        >
                            <i class="fa-solid fa-arrows-rotate text-[10px]"></i>
                            <span>Lưu & Tạo tiếp</span>
                        </button>
                    </template>

                    <template x-if="selectedIndices.length > 1">
                        <button 
                            type="button" 
                            @click="clearSelectionToCurrent()"
                            class="py-1.5 px-2 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-800 font-bold text-[11px] transition flex items-center justify-center gap-1 border border-amber-200 truncate"
                            title="Bỏ chế độ chọn nhiều, chuyển về sửa riêng căn đang xem"
                        >
                            <i class="fa-solid fa-pen text-[10px]"></i>
                            <span>Sửa riêng căn này</span>
                        </button>
                    </template>

                    <button 
                        type="button" 
                        @click="downloadPosterCanvas()"
                        class="py-1.5 px-2 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 font-bold text-[11px] transition flex items-center justify-center gap-1 border border-blue-200 truncate"
                        title="Tải ảnh PNG của căn đang xem trước"
                    >
                        <i class="fa-solid fa-download text-[10px]"></i>
                        <span>Tải ảnh PNG</span>
                    </button>
                </div>

                <template x-if="importedUnits.length > 1 && selectedIndices.length <= 1">
                    <button 
                        type="button" 
                        @click="batchGenerateAll()"
                        :disabled="isBatchGenerating || isSavingPoster"
                        class="w-full py-1.5 px-2 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-[11px] transition flex items-center justify-center gap-1.5 shadow-sm shadow-emerald-600/20"
                        title="Tự động xuất và lưu ảnh poster chỉ căn cho tất cả các căn"
                    >
                        <i class="fa-solid fa-bolt text-amber-300"></i>
                        <span x-text="'Xuất toàn bộ ' + importedUnits.length + ' ảnh chỉ căn'"></span>
                    </button>
                </template>
            </div>
        </div>

        <!-- Center: Live Canvas Poster Preview (Tự động co giãn vừa khít 100% khung nhìn mà không cuộn) -->
        <div class="flex-1 min-w-[280px] flex flex-col h-full min-h-0 overflow-hidden">
            <div class="bg-slate-900/95 p-2 rounded-2xl border border-slate-700/80 shadow-xl relative w-full h-full flex flex-col items-center overflow-hidden">
                <div class="w-full flex items-center justify-between pb-1.5 px-2 text-white text-xs border-b border-slate-800 shrink-0">
                    <div class="flex items-center gap-1.5 min-w-0">
                        <span class="font-bold flex items-center gap-1 text-amber-400 shrink-0">
                            <i class="fa-solid fa-eye text-xs"></i>
                            <span>Xem trước:</span>
                        </span>
                        <span class="font-black text-white truncate text-xs" x-text="posterForm.unit_code"></span>
                        <template x-if="selectedIndices.length > 1">
                            <span class="text-[10px] font-bold text-amber-300 bg-amber-950/80 border border-amber-600/40 px-1.5 py-0.2 rounded-full shrink-0" x-text="'(1/' + selectedIndices.length + ' căn đang chọn)'"></span>
                        </template>
                    </div>
                    <span class="text-[10px] text-slate-400 font-mono shrink-0" x-text="'Độ nét: ' + (currentTemplate?.slots_config?.canvas?.width || 1000) + ' × ' + (currentTemplate?.slots_config?.canvas?.height || 1400) + ' px'"></span>
                </div>

                <!-- Canvas element (Co giãn chuẩn tỷ lệ 100% không tràn) -->
                <div class="p-1 w-full flex-1 min-h-0 flex items-center justify-center overflow-hidden" id="posterCanvasWrapper">
                    <canvas id="posterCanvas" class="rounded-lg shadow-2xl border border-slate-800 bg-slate-950" style="max-height: 100%; max-width: 100%; width: auto; height: auto; object-fit: contain;"></canvas>
                </div>
            </div>
        </div>

        <!-- Right: Project Perspectives Library Drawer (Ngăn kéo thư viện ảnh) -->
        <div class="w-full lg:w-60 xl:w-64 2xl:w-72 shrink-0 flex flex-col h-full min-h-0 overflow-hidden">
            <div class="bg-white rounded-2xl border border-slate-200/80 shadow-xs p-2.5 flex flex-col h-full min-h-0 overflow-hidden">
                <div class="flex items-center justify-between border-b border-slate-100 pb-1.5 shrink-0">
                    <div>
                        <h4 class="font-black text-xs text-slate-800 uppercase flex items-center gap-1.5">
                            <i class="fa-solid fa-images text-indigo-600"></i>
                            <span>Kho Ảnh Dự Án</span>
                        </h4>
                        <span class="text-[9px] text-slate-400">Kéo ảnh thả vào khung</span>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <button 
                            type="button" 
                            @click="openDirectUploadModal()"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-[11px] shadow-xs transition"
                            title="Tải ảnh mới trực tiếp vào kho ảnh dự án"
                        >
                            <i class="fa-solid fa-cloud-arrow-up text-[10px]"></i>
                            <span>+ Tải ảnh</span>
                        </button>
                        <a href="{{ route('admin.unit-locators.project', ['projectId' => $project->id, 'tab' => 'perspectives']) }}" class="text-[10px] text-slate-400 hover:text-indigo-600 font-bold" title="Quản lý toàn bộ kho ảnh">
                            Quản lý &rarr;
                        </a>
                    </div>
                </div>

                <!-- Category filters (Pills nhỏ gọn) -->
                <div class="flex flex-wrap gap-1 py-1.5 border-b border-slate-50 shrink-0">
                    <button type="button" @click="libCategory = 'all'" :class="libCategory === 'all' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded text-[10px] font-bold">Tất cả</button>
                    <button type="button" @click="libCategory = 'layout_3d'" :class="libCategory === 'layout_3d' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded text-[10px] font-bold">Bóc mái 3D</button>
                    <button type="button" @click="libCategory = 'perspective'" :class="libCategory === 'perspective' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded text-[10px] font-bold">Phối cảnh</button>
                    <button type="button" @click="libCategory = 'floorplan'" :class="libCategory === 'floorplan' ? 'bg-indigo-600 text-white' : 'bg-slate-100 text-slate-600'" class="px-2 py-0.5 rounded text-[10px] font-bold">Mặt bằng</button>
                </div>

                <!-- Photos list (Scroll mượt trong ngăn kéo) -->
                <div class="flex-1 overflow-y-auto space-y-1.5 py-1.5 pr-1 min-h-0">
                    <template x-for="photo in filteredPhotos" :key="photo.id + '_' + photo.type">
                        <div 
                            draggable="true"
                            @dragstart="$event.dataTransfer.setData('text/plain', photo.image_url)"
                            class="group bg-slate-50 rounded-lg border border-slate-200 p-1.5 space-y-1 hover:border-indigo-400 transition cursor-grab active:cursor-grabbing"
                            title="Kéo ảnh này thả vào khung bên trái hoặc bấm 'Gán ô'"
                        >
                            <div class="relative aspect-video rounded overflow-hidden bg-slate-900 pointer-events-none">
                                <img :src="photo.image_url" class="w-full h-full object-cover">
                                <span class="absolute top-0.5 left-0.5 px-1 py-0.2 rounded text-[8px] font-bold bg-black/60 text-white backdrop-blur-xs" x-text="photo.category_label || 'Ảnh'"></span>
                            </div>
                            <div class="flex items-center justify-between gap-1">
                                <span class="font-bold text-[10px] text-slate-700 truncate flex-1" x-text="photo.name"></span>
                                <!-- Quick Slot assign Popover -->
                                <div x-data="{ openMenu: false }" class="relative shrink-0">
                                    <button 
                                        type="button" 
                                        @click="openMenu = !openMenu" 
                                        class="text-[9px] font-bold py-0.5 px-1.5 rounded bg-indigo-50 hover:bg-indigo-100 text-indigo-700 border border-indigo-200 flex items-center gap-0.5 transition"
                                    >
                                        <span>Gán</span>
                                        <i class="fa-solid fa-chevron-down text-[7px]"></i>
                                    </button>
                                    <div 
                                        x-show="openMenu" 
                                        @click.outside="openMenu = false" 
                                        x-cloak
                                        class="absolute right-0 bottom-full mb-1 z-50 w-48 bg-white rounded-xl shadow-xl border border-slate-200 p-1 space-y-0.5"
                                    >
                                        <div class="text-[9px] font-bold text-slate-400 px-2 py-0.5 uppercase">Chọn ô trên phôi:</div>
                                        <template x-for="(slot, idx) in (currentTemplate?.slots_config?.image_slots || [])" :key="slot.id || idx">
                                            <button 
                                                type="button" 
                                                @click="assignPhotoToSlot(slot.id, photo.image_url); openMenu = false"
                                                class="w-full text-left px-2 py-1 rounded text-[11px] font-semibold text-slate-700 hover:bg-indigo-50 hover:text-indigo-700 flex items-center justify-between transition"
                                            >
                                                <span class="truncate" x-text="slot.name"></span>
                                                <i class="fa-solid fa-arrow-right text-[9px] text-slate-400"></i>
                                            </button>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== MODE 1: VISUAL CANVAS STUDIO ==================== -->
    <div x-show="mode === 'studio'" class="grid grid-cols-1 lg:grid-cols-12 gap-6">

        <!-- Left: Form Input Unit Info & Pin Customization -->
        <div class="lg:col-span-4 space-y-5">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i class="fa-solid fa-sliders text-blue-600"></i>
                    <span>Thông tin căn hộ</span>
                </h3>

                <!-- Mã căn -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">
                        Mã căn <span class="text-rose-500">*</span>
                    </label>
                    <input 
                        type="text" 
                        x-model="form.unit_code" 
                        @input="renderCanvas()"
                        placeholder="Ví dụ: CH-01, A12.05, Căn 08..." 
                        class="w-full px-3.5 py-2 rounded-xl border border-slate-200 text-sm font-bold text-slate-800 focus:ring-2 focus:ring-blue-500 focus:border-transparent bg-slate-50/50"
                        required
                    >
                </div>

                <!-- Diện tích & Hướng -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Diện tích (m²) <span class="text-rose-500">*</span>
                        </label>
                        <input 
                            type="number" 
                            step="0.1" 
                            x-model="form.area" 
                            @input="renderCanvas()"
                            placeholder="75.5" 
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 bg-slate-50/50"
                        >
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">
                            Hướng ban công
                        </label>
                        <select 
                            x-model="form.direction" 
                            @change="renderCanvas()"
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 bg-slate-50/50"
                        >
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
                </div>

                <!-- Tòa & Tầng -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tòa / Block</label>
                        <input 
                            type="text" 
                            x-model="form.block" 
                            @input="renderCanvas()"
                            placeholder="Tòa A" 
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 bg-slate-50/50"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Tầng</label>
                        <input 
                            type="text" 
                            x-model="form.floor" 
                            @input="renderCanvas()"
                            placeholder="Tầng 12" 
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 bg-slate-50/50"
                        >
                    </div>
                </div>

                <!-- Phòng ngủ & Giá bán -->
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Số phòng ngủ</label>
                        <input 
                            type="number" 
                            min="0" 
                            max="10" 
                            x-model="form.bedroom" 
                            @input="renderCanvas()"
                            placeholder="2" 
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 bg-slate-50/50"
                        >
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Giá hiển thị</label>
                        <input 
                            type="text" 
                            x-model="form.price_text" 
                            @input="renderCanvas()"
                            placeholder="3.5 Tỷ" 
                            class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 bg-slate-50/50"
                        >
                    </div>
                </div>

                <!-- Ghi chú / Điểm nổi bật -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Ghi chú / Điểm nổi bật</label>
                    <input 
                        type="text" 
                        x-model="form.note" 
                        placeholder="Căn góc 2 mặt thoáng view hồ điều hòa..." 
                        class="w-full px-3 py-2 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-blue-500 bg-slate-50/50"
                    >
                </div>
            </div>

            <!-- Pin Marker & Watermark Stying Controls -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-4">
                <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider flex items-center gap-2 border-b border-slate-100 pb-3">
                    <i class="fa-solid fa-paintbrush text-indigo-600"></i>
                    <span>Tùy chỉnh Điểm Ghim & Thẻ Thông Tin</span>
                </h3>

                <!-- Pin Style -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kiểu ghim đánh dấu</label>
                    <div class="grid grid-cols-3 gap-2">
                        <button 
                            type="button" 
                            @click="pinStyle = 'bullseye'; renderCanvas()"
                            :class="pinStyle === 'bullseye' ? 'border-blue-600 bg-blue-50 text-blue-700 font-bold' : 'border-slate-200 text-slate-600'"
                            class="p-2 rounded-xl border text-xs text-center transition flex flex-col items-center gap-1"
                        >
                            <span class="text-base">🎯</span>
                            <span>Mục tiêu</span>
                        </button>
                        <button 
                            type="button" 
                            @click="pinStyle = 'pin'; renderCanvas()"
                            :class="pinStyle === 'pin' ? 'border-blue-600 bg-blue-50 text-blue-700 font-bold' : 'border-slate-200 text-slate-600'"
                            class="p-2 rounded-xl border text-xs text-center transition flex flex-col items-center gap-1"
                        >
                            <span class="text-base">📍</span>
                            <span>Ghim vị trí</span>
                        </button>
                        <button 
                            type="button" 
                            @click="pinStyle = 'badge'; renderCanvas()"
                            :class="pinStyle === 'badge' ? 'border-blue-600 bg-blue-50 text-blue-700 font-bold' : 'border-slate-200 text-slate-600'"
                            class="p-2 rounded-xl border text-xs text-center transition flex flex-col items-center gap-1"
                        >
                            <span class="text-base">🏷️</span>
                            <span>Thẻ mã căn</span>
                        </button>
                    </div>
                </div>

                <!-- Pin Color Picker -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Màu sắc ghim</label>
                    <div class="flex items-center gap-2.5">
                        <template x-for="c in ['#ef4444', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899']" :key="c">
                            <button 
                                type="button" 
                                @click="pinColor = c; renderCanvas()"
                                :style="'background-color: ' + c"
                                :class="pinColor === c ? 'ring-4 ring-offset-2 ring-blue-400 scale-110' : 'hover:scale-105'"
                                class="w-8 h-8 rounded-full transition-transform shadow-sm"
                            ></button>
                        </template>
                    </div>
                </div>

                <!-- Tùy chỉnh Kích thước Thẻ Thông Tin Căn Hộ -->
                <div class="pt-3 border-t border-slate-100 space-y-2.5">
                    <div class="flex items-center justify-between">
                        <label class="text-xs font-bold text-slate-700 flex items-center gap-1.5">
                            <i class="fa-solid fa-up-right-and-down-left-from-center text-blue-600 text-xs"></i>
                            <span>Kích thước thẻ căn</span>
                        </label>
                        <div class="flex items-center gap-2">
                            <span class="px-2 py-0.5 rounded-full text-xs font-black bg-blue-100 text-blue-700 font-mono" x-text="cardScalePercent + '%'"></span>
                            <button 
                                type="button" 
                                @click="cardScalePercent = 100; renderCanvas()" 
                                x-show="cardScalePercent !== 100"
                                class="text-[11px] text-slate-400 hover:text-blue-600 underline font-semibold transition"
                                title="Đặt lại kích thước chuẩn 100%"
                            >Đặt lại</button>
                        </div>
                    </div>

                    <!-- Thanh kéo Slider to nhỏ tùy biến -->
                    <div class="flex items-center gap-2.5">
                        <button 
                            type="button" 
                            @click="cardScalePercent = Math.max(40, cardScalePercent - 10); renderCanvas()"
                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center shrink-0 transition text-xs"
                            title="Thu nhỏ 10%"
                        >
                            <i class="fa-solid fa-minus"></i>
                        </button>
                        <input 
                            type="range" 
                            min="40" 
                            max="250" 
                            step="5" 
                            x-model.number="cardScalePercent" 
                            @input="renderCanvas()"
                            class="w-full accent-blue-600 h-2 bg-slate-200 rounded-lg cursor-pointer"
                        >
                        <button 
                            type="button" 
                            @click="cardScalePercent = Math.min(250, cardScalePercent + 10); renderCanvas()"
                            class="w-7 h-7 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold flex items-center justify-center shrink-0 transition text-xs"
                            title="Phóng to 10%"
                        >
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>

                    <!-- 5 nút chọn nhanh tỉ lệ (Presets) -->
                    <div class="grid grid-cols-5 gap-1.5 text-center">
                        <template x-for="p in [
                            { label: 'Nhỏ', val: 70 },
                            { label: 'Vừa', val: 85 },
                            { label: 'Chuẩn', val: 100 },
                            { label: 'To', val: 130 },
                            { label: 'Lớn', val: 160 }
                        ]" :key="p.val">
                            <button 
                                type="button" 
                                @click="cardScalePercent = p.val; renderCanvas()"
                                :class="cardScalePercent === p.val ? 'bg-blue-600 text-white font-black shadow-xs' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-bold'"
                                class="py-1 px-1 rounded-lg text-[11px] transition"
                            >
                                <span x-text="p.label"></span>
                                <span class="block text-[9px] opacity-80" x-text="p.val + '%'"></span>
                            </button>
                        </template>
                    </div>

                    <p class="text-[11px] text-slate-400 italic flex items-center gap-1">
                        <i class="fa-solid fa-circle-info text-[10px] text-blue-500"></i>
                        <span>Mẹo: Kéo góc dưới thẻ trên ảnh để phóng to/nhỏ trực tiếp, kéo thân thẻ để đổi vị trí!</span>
                    </p>
                </div>

                <!-- Info Card Banner Position & Pin Size -->
                <div class="grid grid-cols-2 gap-3 pt-2 border-t border-slate-100">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Vị trí thẻ thông tin</label>
                        <select x-model="cardPosition" @change="renderCanvas()" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                            <option value="bottom-left">Góc dưới - Trái</option>
                            <option value="bottom-right">Góc dưới - Phải</option>
                            <option value="top-left">Góc trên - Trái</option>
                            <option value="top-right">Góc trên - Phải</option>
                            <option value="custom">Tự do (Kéo thả)</option>
                            <option value="none">Không hiển thị thẻ</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Kích thước ghim</label>
                        <select x-model="pinSize" @change="renderCanvas()" class="w-full px-3 py-2 rounded-xl border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500">
                            <option value="small">Nhỏ gọn</option>
                            <option value="medium">Vừa (Chuẩn)</option>
                            <option value="large">To nổi bật</option>
                            <option value="xlarge">Cực đại (XL)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Save Action Buttons -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 space-y-3">
                <button 
                    type="button" 
                    @click="saveUnit(false)"
                    :disabled="isSaving || !hasImage"
                    class="w-full py-3 px-4 rounded-xl bg-blue-600 hover:bg-blue-700 disabled:opacity-50 text-white font-extrabold text-sm transition shadow-lg shadow-blue-500/25 flex items-center justify-center gap-2"
                >
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span x-text="isSaving ? 'Đang xuất và lưu ảnh...' : 'Lưu & Hoàn tất'"></span>
                </button>

                <button 
                    type="button" 
                    @click="saveUnit(true)"
                    :disabled="isSaving || !hasImage"
                    class="w-full py-2.5 px-4 rounded-xl bg-indigo-50 hover:bg-indigo-100 text-indigo-700 font-bold text-xs transition border border-indigo-200 flex items-center justify-center gap-2"
                >
                    <i class="fa-solid fa-arrows-rotate"></i>
                    <span>Lưu & Tạo tiếp căn khác trên mặt bằng này</span>
                </button>

                <button 
                    type="button" 
                    @click="downloadCanvas()"
                    :disabled="!hasImage"
                    class="w-full py-2 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition flex items-center justify-center gap-2"
                >
                    <i class="fa-solid fa-download"></i>
                    <span>Tải ảnh chỉ căn về máy (PNG)</span>
                </button>
            </div>
        </div>

        <!-- Right: Interactive Canvas Floorplan Viewer -->
        <div class="lg:col-span-8 space-y-4">
            <!-- Floorplan Source Selector -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <div class="w-9 h-9 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-map"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-wider">Ảnh mặt bằng gốc</h4>
                        <p class="text-[11px] text-slate-400">Chọn mặt bằng có sẵn hoặc tải ảnh mới</p>
                    </div>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <!-- Select from saved project floorplans -->
                    @if ($floorplans->isNotEmpty())
                    <select @change="loadSavedFloorplan($event.target.value)" class="py-2 px-3 rounded-xl border border-slate-200 text-xs bg-slate-50 font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Chọn mặt bằng dự án --</option>
                        @foreach ($floorplans as $fp)
                        <option value="{{ $fp->image_url }}">{{ $fp->name }}</option>
                        @endforeach
                    </select>
                    @endif

                    <!-- Upload new floorplan on the fly -->
                    <label class="cursor-pointer px-4 py-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold transition flex items-center gap-1.5 shrink-0 border border-blue-200">
                        <i class="fa-solid fa-upload"></i>
                        <span>Tải ảnh mặt bằng</span>
                        <input type="file" accept="image/*" @change="handleFileUpload($event)" class="hidden">
                    </label>
                </div>
            </div>

            <!-- Canvas Container -->
            <div class="bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden relative min-h-[580px] flex flex-col">
                <!-- Canvas Top Bar Controls -->
                <div class="px-5 py-3 bg-slate-950/80 border-b border-slate-800 flex items-center justify-between text-xs text-slate-300 z-10">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1.5 text-emerald-400 font-semibold text-[11px] sm:text-xs">
                            <i class="fa-solid fa-circle-info"></i>
                            <span>Nhấp chuột để ghim vị trí • Kéo thẻ căn để đổi chỗ • Kéo góc dưới thẻ để chỉnh to nhỏ</span>
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 text-[11px]" x-show="hasImage" x-text="'Tọa độ: X: ' + Math.round(marker.x) + 'px, Y: ' + Math.round(marker.y) + 'px'"></span>
                        <button 
                            type="button" 
                            @click="resetMarker()"
                            x-show="hasImage"
                            class="px-2.5 py-1 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-semibold"
                        >
                            Đặt lại tâm
                        </button>
                    </div>
                </div>

                <!-- Empty State before floorplan selected -->
                <div 
                    x-show="!hasImage" 
                    class="flex-1 flex flex-col items-center justify-center p-12 text-center text-slate-400"
                >
                    <div class="w-20 h-20 rounded-3xl bg-slate-800/80 border border-slate-700 flex items-center justify-center text-3xl text-blue-400 mb-4">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <h3 class="text-lg font-bold text-white">Chưa chọn ảnh mặt bằng</h3>
                    <p class="text-xs text-slate-400 max-w-sm mt-1">
                        Hãy chọn một mặt bằng từ menu phía trên hoặc bấm <strong>"Tải ảnh mặt bằng"</strong> để bắt đầu Studio chỉ căn.
                    </p>
                    <label class="mt-5 cursor-pointer px-5 py-2.5 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition shadow-lg shadow-blue-500/25">
                        <i class="fa-solid fa-image mr-1"></i>
                        <span>Chọn ảnh từ máy tính</span>
                        <input type="file" accept="image/*" @change="handleFileUpload($event)" class="hidden">
                    </label>
                </div>

                <!-- Active Canvas Area -->
                <div 
                    x-show="hasImage" 
                    class="flex-1 overflow-auto p-4 flex items-center justify-center bg-slate-950 select-none"
                    id="canvasWrapper"
                >
                    <canvas 
                        id="studioCanvas" 
                        @mousedown="handleCanvasMouseDown($event)"
                        @mousemove="handleCanvasMouseMove($event)"
                        @mouseup="handleCanvasMouseUp($event)"
                        @mouseleave="handleCanvasMouseUp($event)"
                        @touchstart.passive="handleTouchStart($event)"
                        @touchmove="handleTouchMove($event)"
                        @touchend="handleTouchEnd($event)"
                        @click="handleCanvasClick($event)"
                        class="max-w-full max-h-[70vh] object-contain rounded-lg shadow-2xl border border-slate-800"
                    ></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== MODALS CHO TÍNH NĂNG IMPORT EXCEL & BATCH GENERATION ==================== -->
    
    <!-- MODAL 1: CHỌN / KÉO THẢ FILE EXCEL -->
    <div 
        x-show="excelModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/70 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div 
            @click.outside="excelModalOpen = false" 
            class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden flex flex-col"
        >
            <!-- Modal Header -->
            <div class="px-5 py-3.5 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-file-excel text-base"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-sm">Nhập Danh Sách Căn Hộ Từ Excel</h3>
                        <p class="text-[11px] text-emerald-100">Hỗ trợ file .xlsx, .xls, .csv</p>
                    </div>
                </div>
                <button type="button" @click="excelModalOpen = false" class="text-white/80 hover:text-white p-1 rounded-lg">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 space-y-4">
                <!-- Dropzone -->
                <label 
                    class="border-2 border-dashed border-emerald-300 hover:border-emerald-500 rounded-2xl p-6 bg-emerald-50/40 hover:bg-emerald-50 flex flex-col items-center justify-center cursor-pointer transition text-center group"
                    @dragover.prevent
                    @drop.prevent="handleExcelUpload($event)"
                >
                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center text-2xl mb-2 group-hover:scale-110 transition">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <span class="font-black text-sm text-slate-800">Chọn hoặc Kéo thả file Excel vào đây</span>
                    <span class="text-xs text-slate-500 mt-1">Hỗ trợ định dạng: .xlsx, .xls, .csv</span>
                    <input type="file" accept=".xlsx, .xls, .csv" class="hidden" @change="handleExcelUpload($event)">
                </label>

                <!-- Column format hint -->
                <div class="bg-slate-50 rounded-xl p-3 border border-slate-200 text-xs space-y-1.5 text-slate-600">
                    <div class="font-bold text-slate-800 flex items-center gap-1.5">
                        <i class="fa-solid fa-circle-info text-emerald-600"></i>
                        <span>Cột dữ liệu tự động nhận diện theo mẫu:</span>
                    </div>
                    <div class="flex flex-wrap gap-1 text-[11px]">
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">Trục/Tòa</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">Tầng</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">Căn số</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-emerald-400 font-mono font-bold text-emerald-700">Mã Căn *</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">Loại căn</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">Diện tích</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">Giá (chưa VAT)</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">Hướng</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">View</span>
                    </div>
                    <p class="text-[10px] text-slate-400 italic">Mỗi căn sẽ tự động liên kết với 1 ảnh poster chỉ căn tương ứng.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 2: XEM TRƯỚC DỮ LIỆU EXCEL ĐÃ ĐỌC -->
    <div 
        x-show="excelPreviewModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div 
            class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-4xl max-h-[85vh] flex flex-col overflow-hidden"
        >
            <!-- Modal Header -->
            <div class="px-5 py-3 bg-gradient-to-r from-emerald-600 to-teal-700 text-white flex items-center justify-between shrink-0">
                <div class="flex items-center gap-2">
                    <i class="fa-solid fa-file-excel text-lg"></i>
                    <div>
                        <h3 class="font-black text-sm">Xem trước dữ liệu Excel: <span class="font-mono text-amber-200" x-text="excelFileName"></span></h3>
                        <p class="text-[11px] text-emerald-100">Đã đọc được <strong class="text-white" x-text="previewExcelUnits.length"></strong> căn hộ hợp lệ</p>
                    </div>
                </div>
                <button type="button" @click="excelPreviewModalOpen = false" class="text-white/80 hover:text-white p-1 rounded-lg">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Table Preview -->
            <div class="p-4 flex-1 overflow-y-auto min-h-0">
                <div class="border border-slate-200 rounded-xl overflow-hidden shadow-xs">
                    <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
                        <thead class="bg-slate-100 border-b border-slate-200 text-slate-600 font-bold uppercase text-[10px] sticky top-0 z-10">
                            <tr>
                                <th class="py-2 px-3 text-center w-12">STT</th>
                                <th class="py-2 px-3">Mã Căn</th>
                                <th class="py-2 px-3">Tòa / Tháp</th>
                                <th class="py-2 px-3">Tầng</th>
                                <th class="py-2 px-3">Loại Căn</th>
                                <th class="py-2 px-3 text-right">Diện Tích</th>
                                <th class="py-2 px-3 text-right">Giá Bán</th>
                                <th class="py-2 px-3">Hướng</th>
                                <th class="py-2 px-3">View</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(u, idx) in previewExcelUnits" :key="idx">
                                <tr class="hover:bg-emerald-50/50 transition">
                                    <td class="py-1.5 px-3 text-center font-mono text-slate-400" x-text="idx + 1"></td>
                                    <td class="py-1.5 px-3 font-black text-slate-900" x-text="u.unit_code"></td>
                                    <td class="py-1.5 px-3 text-slate-600" x-text="u.block || '-'"></td>
                                    <td class="py-1.5 px-3 text-slate-600" x-text="u.floor || '-'"></td>
                                    <td class="py-1.5 px-3">
                                        <span class="px-1.5 py-0.5 rounded font-bold text-[10px] bg-amber-100 text-amber-800" x-text="u.unit_type || '2PN'"></span>
                                    </td>
                                    <td class="py-1.5 px-3 text-right font-bold text-slate-800" x-text="u.area ? u.area + ' m²' : '-'"></td>
                                    <td class="py-1.5 px-3 text-right font-black text-blue-700" x-text="u.price_text || '-'"></td>
                                    <td class="py-1.5 px-3 text-slate-600" x-text="u.direction || '-'"></td>
                                    <td class="py-1.5 px-3 text-slate-600" x-text="u.view || '-'"></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Footer Actions -->
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex flex-wrap items-center justify-between gap-2 shrink-0">
                <div class="text-xs text-slate-500 font-semibold">
                    Tổng cộng: <strong class="text-emerald-700 font-bold" x-text="previewExcelUnits.length + ' căn hộ'"></strong>
                </div>

                <div class="flex items-center gap-2">
                    <button 
                        type="button" 
                        @click="excelPreviewModalOpen = false" 
                        class="px-3.5 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold transition"
                    >
                        Hủy bỏ
                    </button>

                    <button 
                        type="button" 
                        @click="confirmImportExcel(false)"
                        :disabled="isImportingExcel"
                        class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-black transition flex items-center gap-1.5 shadow-sm"
                    >
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        <span x-text="isImportingExcel ? 'Đang lưu...' : 'Lưu Danh Sách Căn Vào Hệ Thống'"></span>
                    </button>

                    <button 
                        type="button" 
                        @click="confirmImportExcel(true)"
                        :disabled="isImportingExcel"
                        class="px-4 py-2 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-black transition flex items-center gap-1.5 shadow-md shadow-emerald-600/20"
                    >
                        <i class="fa-solid fa-bolt text-amber-300"></i>
                        <span>Lưu & Tạo Hàng Loạt Tất Cả Ảnh Chỉ Căn</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL 3: TIẾN ĐỘ TẠO ẢNH HÀNG LOẠT (BATCH GENERATION) -->
    <div 
        x-show="batchModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/80 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div 
            class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-md p-6 text-center space-y-4"
        >
            <div class="w-16 h-16 rounded-full mx-auto flex items-center justify-center text-3xl shadow-lg transition-all"
                :class="batchProgress.isDone ? 'bg-emerald-100 text-emerald-600 shadow-emerald-500/20' : 'bg-blue-100 text-blue-600 animate-pulse shadow-blue-500/20'"
            >
                <i :class="batchProgress.isDone ? 'fa-solid fa-circle-check' : 'fa-solid fa-wand-magic-sparkles'"></i>
            </div>

            <div>
                <h3 class="text-base font-black text-slate-900" x-text="batchProgress.isDone ? 'Đã hoàn tất tạo ảnh chỉ căn!' : 'Đang tạo hàng loạt ảnh chỉ căn...'"></h3>
                <p class="text-xs text-slate-500 mt-1" x-text="batchProgress.isDone ? ('Đã xuất thành công ' + batchProgress.successCount + '/' + batchProgress.total + ' ảnh poster chỉ căn!') : ('Đang kết xuất và lưu căn: ' + batchProgress.currentCode + ' (' + batchProgress.current + '/' + batchProgress.total + ')')"></p>
            </div>

            <!-- Progress bar -->
            <div class="space-y-1">
                <div class="w-full bg-slate-100 rounded-full h-3 overflow-hidden p-0.5 border border-slate-200">
                    <div 
                        class="bg-gradient-to-r from-emerald-500 to-teal-500 h-full rounded-full transition-all duration-200"
                        :style="'width: ' + batchProgress.percent + '%'"
                    ></div>
                </div>
                <div class="flex justify-between text-[11px] font-bold text-slate-400">
                    <span x-text="batchProgress.current + ' / ' + batchProgress.total + ' căn'"></span>
                    <span x-text="batchProgress.percent + '%'"></span>
                </div>
            </div>

            <!-- Action buttons when done -->
            <template x-if="batchProgress.isDone">
                <div class="flex items-center justify-center gap-2 pt-2">
                    <a 
                        href="{{ route('admin.unit-locators.project', $project->id) }}"
                        class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs transition flex items-center gap-1.5 shadow-md shadow-blue-500/20"
                    >
                        <i class="fa-solid fa-layer-group text-xs"></i>
                        <span>Xem danh sách chỉ căn</span>
                    </a>
                    <button 
                        type="button" 
                        @click="batchModalOpen = false"
                        class="px-4 py-2 rounded-xl border border-slate-200 bg-white hover:bg-slate-50 text-slate-700 font-bold text-xs transition"
                    >
                        Ở lại tạo tiếp
                    </button>
                </div>
            </template>
        </div>
    </div>

    <!-- MODAL 4: TẢI ẢNH TRỰC TIẾP VÀO KHO DỰ ÁN -->
    <div 
        x-show="directUploadModalOpen" 
        x-cloak 
        class="fixed inset-0 z-50 overflow-y-auto bg-slate-950/75 backdrop-blur-xs flex items-center justify-center p-4"
    >
        <div 
            @click.outside="directUploadModalOpen = false" 
            class="bg-white rounded-2xl shadow-2xl border border-slate-200 w-full max-w-lg overflow-hidden flex flex-col"
        >
            <!-- Modal Header -->
            <div class="px-5 py-3.5 bg-gradient-to-r from-indigo-600 to-blue-700 text-white flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-white/20 flex items-center justify-center">
                        <i class="fa-solid fa-cloud-arrow-up text-base"></i>
                    </div>
                    <div>
                        <h3 class="font-black text-sm">Tải Ảnh Lên Kho Dự Án</h3>
                        <p class="text-[11px] text-indigo-100">Mặt bằng tầng, phối cảnh, bóc mái 3D, tiện ích</p>
                    </div>
                </div>
                <button type="button" @click="directUploadModalOpen = false" class="text-white/80 hover:text-white p-1 rounded-lg">
                    <i class="fa-solid fa-xmark text-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-5 space-y-3.5 text-xs">
                <!-- 1. Loại ảnh -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Loại ảnh tải lên <span class="text-rose-500">*</span></label>
                    <div class="grid grid-cols-2 gap-2">
                        <label 
                            class="p-2.5 rounded-xl border flex items-center gap-2 cursor-pointer transition"
                            :class="directUploadType === 'floorplan' ? 'border-blue-500 bg-blue-50 text-blue-900 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                        >
                            <input type="radio" name="uploadType" value="floorplan" x-model="directUploadType" class="hidden">
                            <i class="fa-solid fa-map text-blue-600 text-lg"></i>
                            <div>
                                <span class="block font-black text-xs">Mặt Bằng Tầng</span>
                                <span class="text-[10px] text-slate-500 block">Sơ đồ tầng chỉ căn</span>
                            </div>
                        </label>

                        <label 
                            class="p-2.5 rounded-xl border flex items-center gap-2 cursor-pointer transition"
                            :class="directUploadType === 'perspective' ? 'border-indigo-500 bg-indigo-50 text-indigo-900 font-bold' : 'border-slate-200 hover:bg-slate-50 text-slate-700'"
                        >
                            <input type="radio" name="uploadType" value="perspective" x-model="directUploadType" class="hidden">
                            <i class="fa-solid fa-images text-indigo-600 text-lg"></i>
                            <div>
                                <span class="block font-black text-xs">Phối Cảnh / 3D</span>
                                <span class="text-[10px] text-slate-500 block">Bóc mái, tiện ích...</span>
                            </div>
                        </label>
                    </div>
                </div>

                <!-- Nếu chọn Phối cảnh: chọn danh mục con -->
                <template x-if="directUploadType === 'perspective'">
                    <div>
                        <label class="block text-xs font-bold text-slate-700 mb-1">Phân loại phối cảnh</label>
                        <select x-model="directUploadCategory" class="w-full py-1.5 px-2.5 rounded-lg border border-slate-200 text-xs font-bold bg-white focus:ring-1 focus:ring-indigo-500">
                            <option value="layout_3d">Mặt bằng bóc mái 3D</option>
                            <option value="perspective">Phối cảnh tòa / tổng thể</option>
                            <option value="amenity">Phối cảnh tiện ích</option>
                            <option value="other">Ảnh phối cảnh khác</option>
                        </select>
                    </div>
                </template>

                <!-- 2. Tên ảnh / Nhãn hiển thị -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Tên ảnh / Nhãn hiển thị</label>
                    <input 
                        type="text" 
                        x-model="directUploadName" 
                        placeholder="VD: Mặt bằng tầng 6-12, Phối cảnh đêm lung linh..." 
                        class="w-full px-3 py-1.5 rounded-lg border border-slate-200 text-xs font-bold text-slate-800 focus:ring-1 focus:ring-indigo-500"
                    >
                    <span class="text-[10px] text-slate-400 mt-0.5 block">Để trống sẽ tự động lấy theo tên file ảnh</span>
                </div>

                <!-- 3. Khu vực chọn / kéo thả ảnh -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">File hình ảnh <span class="text-rose-500">*</span></label>
                    <label 
                        class="border-2 border-dashed rounded-2xl p-5 flex flex-col items-center justify-center cursor-pointer transition text-center group"
                        :class="directUploadFiles.length > 0 ? 'border-emerald-400 bg-emerald-50/30' : 'border-indigo-300 hover:border-indigo-500 bg-indigo-50/30 hover:bg-indigo-50/60'"
                        @dragover.prevent
                        @drop.prevent="handleDirectFilesDrop($event)"
                    >
                        <template x-if="directUploadFiles.length === 0">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center text-xl mb-1.5 group-hover:scale-110 transition">
                                    <i class="fa-solid fa-cloud-arrow-up"></i>
                                </div>
                                <span class="font-black text-xs text-slate-800">Bấm để chọn ảnh từ máy tính</span>
                                <span class="text-[10px] text-slate-500 mt-0.5">JPG, PNG, WEBP (Tối đa 15MB/ảnh)</span>
                            </div>
                        </template>

                        <template x-if="directUploadFiles.length > 0">
                            <div class="flex flex-col items-center">
                                <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-xl mb-1.5">
                                    <i class="fa-solid fa-check"></i>
                                </div>
                                <span class="font-black text-xs text-emerald-800" x-text="'Đã chọn ' + directUploadFiles.length + ' file ảnh'"></span>
                                <span class="text-[10px] text-slate-500 mt-0.5 truncate max-w-xs" x-text="directUploadFiles[0].name"></span>
                            </div>
                        </template>

                        <input type="file" accept="image/*" multiple class="hidden" @change="handleDirectFilesSelect($event)">
                    </label>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="px-5 py-3 bg-slate-50 border-t border-slate-200 flex items-center justify-between shrink-0">
                <button 
                    type="button" 
                    @click="directUploadModalOpen = false" 
                    class="px-3 py-1.5 rounded-xl border border-slate-200 bg-white hover:bg-slate-100 text-slate-700 text-xs font-bold transition"
                >
                    Hủy bỏ
                </button>

                <button 
                    type="button" 
                    @click="submitDirectUpload()"
                    :disabled="isDirectUploading || directUploadFiles.length === 0"
                    class="px-4 py-1.5 rounded-xl bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white text-xs font-black transition flex items-center gap-1.5 shadow-sm shadow-indigo-500/20"
                >
                    <i class="fa-solid fa-arrow-up-from-bracket text-xs" :class="{'fa-spin': isDirectUploading}"></i>
                    <span x-text="isDirectUploading ? 'Đang tải lên...' : 'Tải Lên Kho Ảnh'"></span>
                </button>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script src="{{ asset('js/xlsx.full.min.js') }}"></script>
<script>
if (typeof XLSX === 'undefined') {
    document.write('<script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"><\/script>');
}
</script>
<script>
function unitCreatorStudio() {
    return {
        mode: '{{ request('mode', 'poster') }}',
        hasImage: false,
        isSaving: false,
        isSavingPoster: false,
        imgObj: null,
        marker: { x: 0, y: 0 },
        pinStyle: 'bullseye', // bullseye, pin, badge
        pinColor: '#ef4444',
        pinSize: 'medium',
        cardPosition: 'bottom-left',
        cardScalePercent: 100,
        cardCustomX: null,
        cardCustomY: null,
        cardRect: null,
        isDraggingCard: false,
        isResizingCard: false,
        dragCardOffset: { x: 0, y: 0 },
        resizeStart: { x: 0, y: 0, initialScale: 100 },

        // Imported Units State (Excel & CSDL)
        importedUnits: {!! json_encode($existingUnits->map(function($u) {
            return [
                'id' => $u->id,
                'unit_code' => $u->unit_code,
                'unit_type' => $u->unit_type,
                'block' => $u->block,
                'floor' => $u->floor,
                'area' => $u->area,
                'direction' => $u->direction,
                'view' => $u->view,
                'bedroom' => $u->bedroom,
                'price' => $u->price,
                'price_text' => $u->price_text,
                'price_note' => $u->price_note,
                'has_image' => !empty($u->image),
                'image_url' => $u->image_url,
                'composer_data' => $u->composer_data,
                'highlight_data' => $u->highlight_data,
            ];
        })) !!},
        currentUnitIndex: 0,
        showUnitsCol: true,
        unitSearchText: '',
        unitFilterStatus: 'all',
        selectedUnitIds: @json($selectedUnitIds ?? []),
        selectedIndices: [],
        excelModalOpen: false,
        excelPreviewModalOpen: false,
        previewExcelUnits: [],
        excelFileName: '',
        isImportingExcel: false,
        isBatchGenerating: false,
        batchModalOpen: false,
        batchProgress: {
            current: 0,
            total: 0,
            percent: 0,
            currentCode: '',
            successCount: 0,
            failCount: 0,
            isDone: false
        },

        // Direct Library Upload State
        directUploadModalOpen: false,
        directUploadType: 'floorplan',
        directUploadCategory: 'layout_3d',
        directUploadName: '',
        directUploadFiles: [],
        isDirectUploading: false,

        // Poster Composer State
        allTemplates: {!! json_encode($templates) !!},
        allPerspectives: {!! json_encode($perspectives) !!},
        allFloorplans: {!! json_encode($floorplans) !!},
        selectedTemplateId: '{{ request('template_id') ?: ($templates->first()?->id ?: 'default') }}',
        libCategory: 'all',
        slotImages: {},
        sharedSlotImages: {},
        imageCache: {},
        renderTimer: null,

        posterForm: {
            unit_code: 'C2.3-27-08',
            unit_type: '2PN',
            direction: 'NAM',
            view: 'Nội khu',
            area: '59.8',
            price_text: '2.039 tỷ',
            price_note: '(Chưa VAT và Kinh phí bảo trì)',
            block: 'Tòa C2.3',
            floor: 'Tầng 27',
            note: ''
        },

        form: {
            unit_code: 'CH-01',
            area: '75.5',
            direction: 'Đông Nam',
            door_direction: '',
            block: 'Tòa A',
            floor: 'Tầng 12',
            bedroom: 2,
            bathroom: 2,
            price_text: '3.5 Tỷ',
            status: 'available',
            note: ''
        },

        get currentTemplate() {
            if (this.allTemplates && this.allTemplates.length > 0) {
                const found = this.allTemplates.find(t => String(t.id) === String(this.selectedTemplateId));
                if (found) return found;
            }
            return {
                id: 'default',
                name: 'Phôi Luxury Gold 6 Ô (Chuẩn)',
                image_url: '{{ asset('images/default-poster-phoi.jpg') }}',
                width: 1000,
                height: 1400,
                slots_config: {
                    canvas: { width: 1000, height: 1400, bgColor: '#451a03' },
                    image_slots: [
                        { id: 'slot_1', key: 'boc_mai', name: '1. Mặt bằng bóc mái 3D', x: 650, y: 160, width: 280, height: 340, radius: 12 },
                        { id: 'slot_2', key: 'mat_bang_tang', name: '2. Mặt bằng tầng chỉ căn', x: 630, y: 525, width: 330, height: 240, radius: 12 },
                        { id: 'slot_3', key: 'phoi_canh_toa', name: '3. Phối cảnh tổng thể & tòa', x: 60, y: 380, width: 540, height: 210, radius: 12 },
                        { id: 'slot_4', key: 'tien_ich_1', name: '4. Phối cảnh tiện ích', x: 60, y: 610, width: 260, height: 230, radius: 12 },
                        { id: 'slot_5', key: 'phan_khu', name: '5. Mặt bằng phân khu / tiện ích', x: 340, y: 610, width: 260, height: 230, radius: 12 },
                        { id: 'slot_6', key: 'phoi_canh_dem', name: '6. Phối cảnh tòa đêm lung linh', x: 200, y: 860, width: 760, height: 240, radius: 12 }
                    ],
                    text_slots: {
                        unit_code: { x: 60, y: 45, fontSize: 26, color: '#ffffff' },
                        unit_type: { x: 395, y: 45, fontSize: 24, color: '#78350f' },
                        direction: { x: 60, y: 120, fontSize: 20, color: '#ffffff' },
                        view: { x: 60, y: 155, fontSize: 20, color: '#ffffff' },
                        area: { x: 60, y: 190, fontSize: 20, color: '#ffffff' },
                        price: { x: 280, y: 285, fontSize: 44, color: '#ffffff' }
                    }
                }
            };
        },

        get filteredPhotos() {
            let list = [];
            // Merge perspectives
            (this.allPerspectives || []).forEach(p => {
                list.push({
                    id: p.id,
                    name: p.name,
                    category: p.category,
                    category_label: p.category_label || 'Phối cảnh',
                    image_url: p.image_url || ('{{ url('admin/unit-locators/media') }}/' + p.image),
                    type: 'persp'
                });
            });
            // Merge floorplans
            (this.allFloorplans || []).forEach(fp => {
                list.push({
                    id: fp.id,
                    name: fp.name,
                    category: 'floorplan',
                    category_label: 'Mặt bằng tầng',
                    image_url: fp.image_url || ('{{ url('admin/unit-locators/media') }}/' + fp.image),
                    type: 'fp'
                });
            });

            if (this.libCategory === 'all') return list;
            return list.filter(item => item.category === this.libCategory);
        },

        get filteredUnitList() {
            if (!this.importedUnits || !Array.isArray(this.importedUnits)) return [];
            const search = (this.unitSearchText || '').trim().toLowerCase();
            const filter = this.unitFilterStatus || 'all';

            return this.importedUnits.map((u, idx) => ({ ...u, originalIndex: idx })).filter(u => {
                if (filter === 'has_image' && !u.has_image) return false;
                if (filter === 'no_image' && u.has_image) return false;
                if (filter === 'selected' && this.selectedUnitIds && this.selectedUnitIds.length > 0) {
                    if (!this.selectedUnitIds.includes(Number(u.id))) return false;
                }
                if (search) {
                    const code = (u.unit_code || '').toLowerCase();
                    const type = (u.unit_type || '').toLowerCase();
                    const block = (u.block || '').toLowerCase();
                    const floor = (u.floor || '').toLowerCase();
                    return code.includes(search) || type.includes(search) || block.includes(search) || floor.includes(search);
                }
                return true;
            });
        },

        get isAllFilteredSelected() {
            if (!this.filteredUnitList || this.filteredUnitList.length === 0) return false;
            return this.filteredUnitList.every(u => this.selectedIndices.includes(u.originalIndex));
        },

        init() {
            if (!this.allTemplates || this.allTemplates.length === 0) {
                this.allTemplates = [this.currentTemplate];
                this.selectedTemplateId = 'default';
            } else if (!this.selectedTemplateId || this.selectedTemplateId === 'default') {
                const def = this.allTemplates.find(t => t.is_default) || this.allTemplates[0];
                if (def) this.selectedTemplateId = def.id;
            }

            if (this.importedUnits && this.importedUnits.length > 0) {
                const targetUnits = '{{ request('units') }}';
                let targetIdx = 0;
                if (targetUnits) {
                    const targetIds = targetUnits.split(',').map(s => s.trim());
                    const matching = this.importedUnits.map((u, idx) => targetIds.includes(String(u.id)) ? idx : -1).filter(idx => idx !== -1);
                    if (matching.length > 0) {
                        this.selectedIndices = matching;
                        targetIdx = matching[0];
                    } else {
                        this.selectedIndices = [0];
                    }
                } else {
                    this.selectedIndices = [0];
                }
                this.selectUnitByIndex(targetIdx);
            }

            @if(request('import') === 'excel')
            this.openExcelModal();
            @endif

            this.$nextTick(() => {
                if (this.mode === 'poster') {
                    this.renderPosterCanvas();
                }
            });

            @if(request('floorplan_url'))
            this.loadSavedFloorplan('{{ request('floorplan_url') }}');
            @elseif ($floorplans->isNotEmpty())
            this.loadSavedFloorplan('{{ $floorplans->first()->image_url }}');
            @endif
        },

        onTemplateChange() {
            if (this.importedUnits && this.importedUnits[this.currentUnitIndex]) {
                this.slotImages = this.resolveUnitSlotImages(this.importedUnits[this.currentUnitIndex]);
            }
            this.$nextTick(() => {
                this.renderPosterCanvas();
            });
        },

        debouncedRender() {
            clearTimeout(this.renderTimer);
            this.renderTimer = setTimeout(() => {
                this.renderPosterCanvas();
            }, 60);
        },

        isFloorplanSlot(slot) {
            if (!slot) return false;
            const name = (slot.name || '').toLowerCase();
            const key = (slot.key || '').toLowerCase();
            const id = (slot.id || '').toLowerCase();

            // Bỏ qua các ô bóc mái 3D hoặc layout căn hộ nội thất
            if (name.includes('bóc mái') || name.includes('boc mai') || name.includes('3d') || name.includes('căn hộ') || name.includes('can ho') || key === 'boc_mai' || key === 'can_ho') {
                return false;
            }

            if (key === 'mat_bang_tang' || key === 'mat_bang' || key === 'floorplan' || key === 'locator' || key === 'chi_can') return true;
            if (name.includes('mặt bằng') || name.includes('mat bang') || name.includes('chỉ căn') || name.includes('chi can') || name.includes('sơ đồ') || name.includes('tầng')) return true;
            return false;
        },

        findMatchingFloorplan(unit) {
            if (!this.allFloorplans || this.allFloorplans.length === 0) return null;
            if (this.allFloorplans.length === 1) return this.allFloorplans[0];

            let floorRaw = String(unit?.floor || '').trim();
            if (!floorRaw && unit?.unit_code) {
                const m = unit.unit_code.match(/\.([0-9]{1,2}[A-Za-z]?)/);
                if (m) floorRaw = m[1];
            }

            let floorNum = null;
            const numMatch = floorRaw.match(/\d+/);
            if (numMatch) {
                floorNum = parseInt(numMatch[0], 10);
            }
            const floorLower = floorRaw.toLowerCase();

            let bestMatch = null;
            let bestScore = -1;

            for (const fp of this.allFloorplans) {
                const name = (fp.name || '').toLowerCase();
                const range = (fp.floor_range || '').toLowerCase();
                const desc = (fp.description || '').toLowerCase();
                const text = `${name} ${range} ${desc}`;
                let score = 0;

                // 1. Khớp chính xác chuỗi tầng (vd: "05a", "tầng 05a", "5")
                if (floorLower && (text.includes(floorLower) || text.includes('tầng ' + floorLower) || text.includes('tang ' + floorLower))) {
                    score += 100;
                }

                if (floorNum !== null) {
                    // 2. Khớp đúng tầng đơn lẻ vd: "tầng 5", "tầng 05", "tang 5"
                    const singleRegex = new RegExp(`(?:tầng|tang)\\s*0*${floorNum}\\b`, 'u');
                    if (singleRegex.test(text)) {
                        score += 80;
                    }

                    // 3. Khớp dải tầng vd: "6-12", "14-29", "tầng 6-12", "6 - 12"
                    const rangeRegex = /(\d+)\s*[-–—]\s*(\d+)/g;
                    let rm;
                    while ((rm = rangeRegex.exec(text)) !== null) {
                        const min = parseInt(rm[1], 10);
                        const max = parseInt(rm[2], 10);
                        if (floorNum >= min && floorNum <= max) {
                            score += 50;
                        }
                    }
                }

                if (score > bestScore) {
                    bestScore = score;
                    bestMatch = fp;
                }
            }

            return bestScore > 0 ? bestMatch : this.allFloorplans[0];
        },

        resolveUnitSlotImages(u) {
            if (!u) return { ...this.slotImages };
            const tmpl = this.currentTemplate;
            const slots = tmpl?.slots_config?.image_slots || [];
            const resolved = {};

            // 1. Lấy slotImages đã lưu của căn này nếu có
            let savedSlots = {};
            if (u._slotImages) {
                savedSlots = { ...u._slotImages };
            } else if (u.composer_data) {
                try {
                    const comp = (typeof u.composer_data === 'string') ? JSON.parse(u.composer_data) : u.composer_data;
                    if (comp && comp.slotImages) savedSlots = { ...comp.slotImages };
                } catch(e) {}
            }

            // 2. Xác định ô mặt bằng / chỉ căn
            const fpSlot = slots.find(s => this.isFloorplanSlot(s));
            const matchedFp = this.findMatchingFloorplan(u);

            for (const slot of slots) {
                const slotId = slot.id;
                const isFp = (fpSlot && slot.id === fpSlot.id);

                if (isFp) {
                    // Ưu tiên 1: Người dùng đã chủ động gán tay ảnh cho ô này trong phiên làm việc
                    if (u._manuallyAssignedSlots && u._manuallyAssignedSlots[slotId] && savedSlots[slotId]) {
                        resolved[slotId] = savedSlots[slotId];
                    }
                    // Ưu tiên 2: Căn đã có ảnh Studio chấm ghim riêng (unit_canvas_...)
                    else if (u.has_image && u.image_url && !u.image_url.includes('default.jpg') && u.image_url.includes('unit_canvas_')) {
                        resolved[slotId] = u.image_url;
                    }
                    // Ưu tiên 3: Tự động gán đúng ảnh mặt bằng tầng của căn này
                    else if (matchedFp && matchedFp.image_url) {
                        resolved[slotId] = matchedFp.image_url;
                    }
                    // Ưu tiên 4: Dùng slot đã lưu nếu có
                    else if (savedSlots[slotId]) {
                        resolved[slotId] = savedSlots[slotId];
                    }
                    // Ưu tiên 5: Dùng ảnh mặt bằng đầu tiên trong dự án
                    else if (this.allFloorplans && this.allFloorplans.length > 0) {
                        resolved[slotId] = this.allFloorplans[0].image_url;
                    }
                } else {
                    // Ô không phải mặt bằng: phối cảnh, tiện ích, căn hộ...
                    if (savedSlots[slotId]) {
                        resolved[slotId] = savedSlots[slotId];
                    } else if (this.sharedSlotImages && this.sharedSlotImages[slotId]) {
                        resolved[slotId] = this.sharedSlotImages[slotId];
                    } else if (this.slotImages[slotId]) {
                        resolved[slotId] = this.slotImages[slotId];
                    }
                }
            }

            return resolved;
        },

        assignPhotoToSlot(slotId, photoUrl) {
            this.slotImages[slotId] = photoUrl;
            if (!this.sharedSlotImages) this.sharedSlotImages = {};
            this.sharedSlotImages[slotId] = photoUrl;

            const tmpl = this.currentTemplate;
            const slot = (tmpl?.slots_config?.image_slots || []).find(s => String(s.id) === String(slotId));
            const isFp = this.isFloorplanSlot(slot);

            const activeIdxs = (this.selectedIndices && this.selectedIndices.length > 1) 
                ? this.selectedIndices 
                : [this.currentUnitIndex];

            activeIdxs.forEach(idx => {
                const su = this.importedUnits[idx];
                if (!su) return;
                if (!su._slotImages) su._slotImages = {};
                su._slotImages[slotId] = photoUrl;

                if (!su.composer_data) su.composer_data = {};
                if (typeof su.composer_data === 'string') {
                    try { su.composer_data = JSON.parse(su.composer_data); } catch(e) { su.composer_data = {}; }
                }
                if (!su.composer_data.slotImages) su.composer_data.slotImages = {};
                su.composer_data.slotImages[slotId] = photoUrl;

                if (isFp) {
                    if (!su._manuallyAssignedSlots) su._manuallyAssignedSlots = {};
                    su._manuallyAssignedSlots[slotId] = true;
                }
            });

            this.renderPosterCanvas();
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: activeIdxs.length > 1 ? `Đã gán ảnh cho ${activeIdxs.length} căn đang chọn!` : 'Đã gán ảnh vào khung thành công!',
                showConfirmButton: false,
                timer: 1500
            });
        },

        removeSlotImage(slotId) {
            this.slotImages[slotId] = null;
            if (this.sharedSlotImages) delete this.sharedSlotImages[slotId];

            const activeIdxs = (this.selectedIndices && this.selectedIndices.length > 1) 
                ? this.selectedIndices 
                : [this.currentUnitIndex];

            activeIdxs.forEach(idx => {
                const su = this.importedUnits[idx];
                if (!su) return;
                if (su._slotImages) delete su._slotImages[slotId];
                if (su._manuallyAssignedSlots) delete su._manuallyAssignedSlots[slotId];
                if (su.composer_data) {
                    if (typeof su.composer_data === 'string') {
                        try { su.composer_data = JSON.parse(su.composer_data); } catch(e) { su.composer_data = {}; }
                    }
                    if (su.composer_data.slotImages) delete su.composer_data.slotImages[slotId];
                }
            });

            this.renderPosterCanvas();
        },

        loadImageAsync(url) {
            if (!url) return Promise.resolve(null);
            if (this.imageCache[url]) return Promise.resolve(this.imageCache[url]);

            return new Promise((resolve) => {
                const img = new Image();
                img.crossOrigin = 'anonymous';
                img.onload = () => {
                    this.imageCache[url] = img;
                    resolve(img);
                };
                img.onerror = () => resolve(null);
                img.src = url;
            });
        },

        async renderPosterCanvas() {
            const canvas = document.getElementById('posterCanvas');
            if (!canvas) return;

            const tmpl = this.currentTemplate;
            const cfg = tmpl.slots_config || {};
            const canvasW = cfg.canvas?.width || tmpl.width || 1000;
            const canvasH = cfg.canvas?.height || tmpl.height || 1400;

            canvas.width = canvasW;
            canvas.height = canvasH;
            const ctx = canvas.getContext('2d');

            // 1. VẼ PHÔI NỀN (BACKGROUND)
            let bgDrawn = false;
            if (tmpl.image_url && tmpl.image_url.indexOf('placeholder') === -1) {
                const bgImg = await this.loadImageAsync(tmpl.image_url);
                if (bgImg) {
                    ctx.drawImage(bgImg, 0, 0, canvasW, canvasH);
                    bgDrawn = true;
                }
            }

            if (!bgDrawn) {
                // Nền gradient sang trọng kiểu Indochine Bronze / Luxury Gold nếu chưa có ảnh phôi
                const grad = ctx.createLinearGradient(0, 0, canvasW, canvasH);
                grad.addColorStop(0, '#3f1703');
                grad.addColorStop(0.5, '#6b2505');
                grad.addColorStop(1, '#270e02');
                ctx.fillStyle = grad;
                ctx.fillRect(0, 0, canvasW, canvasH);

                // Viền vàng kim tuyến tính bên ngoài
                ctx.strokeStyle = 'rgba(217, 119, 6, 0.4)';
                ctx.lineWidth = 12;
                ctx.strokeRect(10, 10, canvasW - 20, canvasH - 20);

                // Dải lụa vàng mềm mại phía dưới đáy
                ctx.save();
                ctx.beginPath();
                ctx.moveTo(0, canvasH - 260);
                ctx.bezierCurveTo(canvasW * 0.3, canvasH - 320, canvasW * 0.6, canvasH - 180, canvasW, canvasH - 240);
                ctx.lineTo(canvasW, canvasH);
                ctx.lineTo(0, canvasH);
                ctx.closePath();
                const waveGrad = ctx.createLinearGradient(0, canvasH - 300, 0, canvasH);
                waveGrad.addColorStop(0, 'rgba(245, 158, 11, 0.25)');
                waveGrad.addColorStop(1, 'rgba(180, 83, 9, 0.6)');
                ctx.fillStyle = waveGrad;
                ctx.fill();
                ctx.restore();
            }

            // 2. VẼ TỪNG KHUNG ẢNH (IMAGE SLOTS)
            const slots = cfg.image_slots || [];
            for (const slot of slots) {
                ctx.save();
                const x = slot.x;
                const y = slot.y;
                const w = slot.width;
                const h = slot.height;
                const r = (slot.radius !== undefined && slot.radius !== null) ? parseInt(slot.radius, 10) : 0;

                // Cắt theo góc bo (Clip rounded rect)
                this.drawCustomRoundRect(ctx, x, y, w, h, r);
                ctx.clip();

                const imgUrl = this.slotImages[slot.id];
                if (imgUrl) {
                    const slotImg = await this.loadImageAsync(imgUrl);
                    if (slotImg) {
                        // Tính tỷ lệ Cover (Fit fill)
                        const imgRatio = slotImg.naturalWidth / slotImg.naturalHeight;
                        const slotRatio = w / h;
                        let dw, dh, dx, dy;
                        if (imgRatio > slotRatio) {
                            dh = h;
                            dw = h * imgRatio;
                            dx = x - (dw - w) / 2;
                            dy = y;
                        } else {
                            dw = w;
                            dh = w / imgRatio;
                            dx = x;
                            dy = y - (dh - h) / 2;
                        }
                        ctx.drawImage(slotImg, dx, dy, dw, dh);
                    }
                } else {
                    // Placeholder nếu ô chưa có ảnh
                    ctx.fillStyle = 'rgba(15, 23, 42, 0.65)';
                    ctx.fillRect(x, y, w, h);
                    const titleSize = Math.max(18, Math.min(44, Math.round(w / 35)));
                    const subSize = Math.max(14, Math.min(28, Math.round(w / 55)));
                    ctx.fillStyle = '#fde68a';
                    ctx.font = `bold ${titleSize}px sans-serif`;
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(slot.name, x + w / 2, y + h / 2 - subSize * 0.7);
                    ctx.fillStyle = '#cbd5e1';
                    ctx.font = `${subSize}px sans-serif`;
                    ctx.fillText('(Kéo thả ảnh vào ô này)', x + w / 2, y + h / 2 + subSize * 0.9);
                }

                ctx.restore();

                // Chỉ vẽ viền ngoài khung nếu người dùng cấu hình có viền (> 0px và khác 'none')
                if (slot.border && slot.border !== 'none' && parseInt(slot.borderWidth, 10) > 0) {
                    ctx.save();
                    ctx.lineWidth = parseInt(slot.borderWidth, 10);
                    ctx.strokeStyle = slot.border;
                    this.drawCustomRoundRect(ctx, x, y, w, h, r);
                    ctx.stroke();
                    ctx.restore();
                }
            }

            // 3. VẼ CÁC KHỐI CHỮ THÔNG TIN (TEXT SLOTS)
            const tSlots = cfg.text_slots || {};

            // Hàm vẽ 1 khối chữ chuẩn WYSIWYG khớp 100% với Studio Board
            const drawTextItem = (pos, rawText, defaultPrefix = '', defaultSuffix = '') => {
                if (!pos) return;
                ctx.save();
                
                const prefix = (pos.prefix !== undefined && pos.prefix !== null && pos.prefix !== 'null') ? String(pos.prefix) : defaultPrefix;
                const suffix = (pos.suffix !== undefined && pos.suffix !== null && pos.suffix !== 'null') ? String(pos.suffix) : defaultSuffix;
                const displayText = prefix + (rawText || '') + suffix;
                if (!displayText) {
                    ctx.restore();
                    return;
                }

                const fontSize = pos.fontSize || 22;
                const fFam = (pos.fontFamily === 'serif') ? '"Times New Roman", Georgia, serif' : 'sans-serif';
                ctx.font = `${pos.fontWeight || 'bold'} ${fontSize}px ${fFam}`;

                const align = pos.align || 'left';
                const isBadge = pos.isBadge && pos.bgColor && pos.bgColor !== 'transparent';

                if (isBadge) {
                    const textMetrics = ctx.measureText(displayText);
                    const padX = Math.round(fontSize * 0.45);
                    const padY = Math.round(fontSize * 0.25);
                    const badgeW = pos.width || Math.round(textMetrics.width + padX * 2);
                    const badgeH = pos.height || Math.round(fontSize + padY * 2);

                    let badgeX = pos.x;
                    if (align === 'center') badgeX = pos.x - badgeW / 2;
                    else if (align === 'right') badgeX = pos.x - badgeW;

                    this.drawCustomRoundRect(ctx, badgeX, pos.y, badgeW, badgeH, pos.radius !== undefined ? pos.radius : 8);
                    ctx.fillStyle = pos.bgColor;
                    ctx.fill();
                    if (pos.borderColor && pos.borderColor !== 'transparent') {
                        ctx.lineWidth = pos.borderWidth || 2;
                        ctx.strokeStyle = pos.borderColor;
                        ctx.stroke();
                    }

                    ctx.fillStyle = pos.color || '#ffffff';
                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';
                    ctx.fillText(displayText, badgeX + badgeW / 2, pos.y + badgeH / 2);
                } else {
                    ctx.fillStyle = pos.color || '#ffffff';
                    ctx.textAlign = align;
                    ctx.textBaseline = 'top';
                    ctx.fillText(displayText, pos.x, pos.y);
                }

                ctx.restore();
            };

            // 3.1 Mã Căn (Unit Code)
            if (tSlots.unit_code) {
                drawTextItem(tSlots.unit_code, this.posterForm.unit_code || 'CH-01');
            }

            // 3.2 Loại Căn (Unit Type: 2PN)
            if (tSlots.unit_type) {
                drawTextItem(tSlots.unit_type, this.posterForm.unit_type || '2PN');
            }

            // 3.3 Hướng (Direction)
            if (tSlots.direction) {
                drawTextItem(tSlots.direction, this.posterForm.direction || 'NAM');
            }

            // 3.4 View
            if (tSlots.view) {
                drawTextItem(tSlots.view, this.posterForm.view || 'Nội khu');
            }

            // 3.5 Diện tích (Area)
            if (tSlots.area) {
                drawTextItem(tSlots.area, this.posterForm.area || '59.8');
            }

            // 3.6 Ghi chú Giá (Price note)
            if (tSlots.price_note && tSlots.price_note.x !== undefined) {
                drawTextItem(tSlots.price_note, this.posterForm.price_note || '(Chưa VAT và Kinh phí bảo trì)');
            } else if (!bgDrawn) {
                // Đường kẻ vàng phân cách nhẹ (chỉ vẽ cho phôi mặc định không có ảnh nền)
                ctx.save();
                ctx.strokeStyle = 'rgba(245, 158, 11, 0.4)';
                ctx.lineWidth = 1.5;
                ctx.beginPath();
                ctx.moveTo(60, 225);
                ctx.lineTo(540, 225);
                ctx.stroke();
                ctx.restore();

                ctx.save();
                ctx.fillStyle = '#ffffff';
                ctx.font = 'bold 20px sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'top';
                ctx.fillText('Giá bán:', 300, 245);

                ctx.fillStyle = '#e2e8f0';
                ctx.font = '14px sans-serif';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'top';
                ctx.fillText(this.posterForm.price_note || '(Chưa VAT và Kinh phí bảo trì)', 300, 275);
                ctx.restore();
            }

            // 3.7 Giá Bán lớn (Price prominent serif)
            if (tSlots.price) {
                drawTextItem(tSlots.price, this.posterForm.price_text || '2.039 tỷ');
            }

            // 3.8 Tòa / Block
            if (tSlots.block) {
                drawTextItem(tSlots.block, this.posterForm.block || 'Sky 2');
            }

            // 3.9 Tầng / Floor
            if (tSlots.floor) {
                drawTextItem(tSlots.floor, this.posterForm.floor || '27');
            }

            // 3.10 Khối chữ tùy chọn / bổ sung khác (Custom Text Slots)
            const standardKeys = ['unit_code', 'unit_type', 'direction', 'view', 'area', 'price', 'price_note', 'block', 'floor'];
            for (const key of Object.keys(tSlots)) {
                if (standardKeys.includes(key)) continue;
                const cPos = tSlots[key];
                if (!cPos) continue;
                const textVal = cPos.sampleText || cPos.label || '';
                drawTextItem(cPos, textVal);
            }
        },

        drawCustomRoundRect(ctx, x, y, w, h, r) {
            if (!r || r <= 0) {
                ctx.beginPath();
                ctx.rect(x, y, w, h);
                return;
            }
            if (typeof ctx.roundRect === 'function') {
                ctx.beginPath();
                ctx.roundRect(x, y, w, h, r);
                return;
            }
            // Fallback for older browsers
            ctx.beginPath();
            ctx.moveTo(x + r, y);
            ctx.arcTo(x + w, y, x + w, y + h, r);
            ctx.arcTo(x + w, y + h, x, y + h, r);
            ctx.arcTo(x, y + h, x, y, r);
            ctx.arcTo(x, y, x + w, y, r);
            ctx.closePath();
        },

        savePosterUnit(createNext = false) {
            if (!this.posterForm.unit_code) {
                Swal.fire('Thiếu thông tin', 'Vui lòng nhập Mã căn!', 'warning');
                return;
            }

            this.isSavingPoster = true;
            const canvas = document.getElementById('posterCanvas');
            const dataUrl = canvas.toDataURL('image/jpeg', 0.95);

            const payload = {
                project_id: '{{ $project->id }}',
                unit_code: this.posterForm.unit_code,
                unit_type: this.posterForm.unit_type,
                direction: this.posterForm.direction,
                view: this.posterForm.view,
                area: this.posterForm.area,
                price_text: this.posterForm.price_text,
                price_note: this.posterForm.price_note,
                block: this.posterForm.block,
                floor: this.posterForm.floor,
                image_data: dataUrl,
                composer_data: JSON.stringify({
                    template_id: this.selectedTemplateId,
                    slotImages: this.slotImages,
                    posterForm: this.posterForm
                })
            };

            fetch('{{ route('admin.unit-locators.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                this.isSavingPoster = false;
                if (data.success) {
                    if (this.importedUnits && this.importedUnits[this.currentUnitIndex]) {
                        const cur = this.importedUnits[this.currentUnitIndex];
                        cur.has_image = true;
                        cur.composer_data = payload.composer_data;
                        cur._slotImages = { ...this.slotImages };
                        if (data.image_url) cur.image_url = data.image_url;
                    }
                    Swal.fire({
                        icon: 'success',
                        title: 'Đã tạo poster chỉ căn thành công!',
                        text: `Chỉ căn ${this.posterForm.unit_code} đã được xuất thành ảnh độ nét cao và lưu vào hệ thống!`,
                        showCancelButton: createNext,
                        confirmButtonText: createNext ? 'Tạo căn tiếp theo' : 'Xem danh sách',
                        cancelButtonText: 'Ở lại chỉnh sửa'
                    }).then((result) => {
                        if (createNext) {
                            if (this.importedUnits && this.currentUnitIndex < this.importedUnits.length - 1) {
                                this.selectUnitByIndex(this.currentUnitIndex + 1);
                            } else {
                                // Tăng mã căn tự động
                                const code = this.posterForm.unit_code;
                                const match = code.match(/^(.*?)(\d+)$/);
                                if (match) {
                                    const prefix = match[1];
                                    const num = parseInt(match[2], 10) + 1;
                                    this.posterForm.unit_code = prefix + String(num).padStart(match[2].length, '0');
                                }
                                this.renderPosterCanvas();
                            }
                        } else if (result.isConfirmed) {
                            window.location.href = '{{ route('admin.unit-locators.project', $project->id) }}';
                        }
                    });
                } else {
                    Swal.fire('Lỗi', data.message || 'Không thể lưu poster', 'error');
                }
            })
            .catch(err => {
                this.isSavingPoster = false;
                Swal.fire('Lỗi máy chủ', err.message, 'error');
            });
        },

        downloadPosterCanvas() {
            const canvas = document.getElementById('posterCanvas');
            const link = document.createElement('a');
            link.download = `Poster-Chi-can-${this.posterForm.unit_code || 'export'}-{{ Str::slug($project->name) }}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        },

        formatPriceVND(val) {
            if (!val && val !== 0) return '';
            if (typeof val === 'string') {
                const cleanStr = val.replace(/,/g, '').trim();
                const num = parseFloat(cleanStr);
                if (!isNaN(num) && num > 10000000) {
                    val = num;
                } else {
                    return val;
                }
            }
            if (typeof val === 'number') {
                if (val >= 1000000000) {
                    const ty = (val / 1000000000).toFixed(3).replace(/\.?0+$/, '');
                    return ty + ' tỷ';
                } else if (val >= 1000000) {
                    const tr = (val / 1000000).toFixed(1).replace(/\.?0+$/, '');
                    return tr + ' triệu';
                }
                return val.toLocaleString('vi-VN') + ' đ';
            }
            return String(val);
        },

        selectUnitByIndex(idx) {
            if (!this.importedUnits || !this.importedUnits[idx]) return;
            this.currentUnitIndex = parseInt(idx, 10);
            if (!this.selectedIndices || this.selectedIndices.length <= 1) {
                this.selectedIndices = [this.currentUnitIndex];
            }
            const u = this.importedUnits[this.currentUnitIndex];
            this.posterForm.unit_code = u.unit_code || '';
            this.posterForm.unit_type = u.unit_type || '';
            this.posterForm.block = u.block || '';
            this.posterForm.floor = u.floor || '';
            this.posterForm.area = u.area !== null && u.area !== undefined ? String(u.area) : '';
            this.posterForm.direction = u.direction || '';
            this.posterForm.view = u.view || '';
            this.posterForm.price_text = u.price_text || (u.price ? this.formatPriceVND(u.price) : '');
            this.posterForm.price_note = u.price_note || '(Chưa VAT)';

            if (u.composer_data) {
                try {
                    const comp = (typeof u.composer_data === 'string') ? JSON.parse(u.composer_data) : u.composer_data;
                    if (comp && comp.template_id && comp.template_id !== this.selectedTemplateId) {
                        this.selectedTemplateId = comp.template_id;
                    }
                } catch(e) {}
            }

            // Đồng bộ slotImages chuẩn của căn này (khớp mặt bằng tầng / ảnh chỉ căn)
            this.slotImages = this.resolveUnitSlotImages(u);

            // Đồng bộ thông tin sang form Studio Chấm Ghim
            this.form.unit_code = u.unit_code || '';
            this.form.area = u.area !== null && u.area !== undefined ? String(u.area) : '';
            this.form.direction = u.direction || '';
            this.form.block = u.block || '';
            this.form.floor = u.floor || '';
            this.form.price_text = u.price_text || (u.price ? this.formatPriceVND(u.price) : '');
            if (u.bedroom) this.form.bedroom = u.bedroom;

            if (u.highlight_data) {
                try {
                    const hl = (typeof u.highlight_data === 'string') ? JSON.parse(u.highlight_data) : u.highlight_data;
                    if (hl) {
                        if (hl.marker) this.marker = { ...hl.marker };
                        if (hl.pinStyle) this.pinStyle = hl.pinStyle;
                        if (hl.pinColor) this.pinColor = hl.pinColor;
                        if (hl.pinSize) this.pinSize = hl.pinSize;
                        if (hl.cardPosition) this.cardPosition = hl.cardPosition;
                        if (hl.cardScalePercent) this.cardScalePercent = hl.cardScalePercent;
                        if (hl.cardCustomX !== undefined) this.cardCustomX = hl.cardCustomX;
                        if (hl.cardCustomY !== undefined) this.cardCustomY = hl.cardCustomY;
                    }
                } catch(e) {}
            }

            // Tự động nạp mặt bằng tương ứng vào Studio Mode nếu đang ở studio hoặc đổi căn
            const matchedFp = this.findMatchingFloorplan(u);
            if (matchedFp && matchedFp.image_url) {
                this.loadSavedFloorplan(matchedFp.image_url);
            } else if (this.mode === 'studio' && this.hasImage) {
                this.renderCanvas();
            }

            this.debouncedRender();

            // Tự động cuộn đến căn đang chọn trong danh sách Cột 1
            this.$nextTick(() => {
                const el = document.getElementById('unit-item-' + this.currentUnitIndex);
                if (el) {
                    el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            });
        },

        toggleUnitSelection(idx) {
            idx = parseInt(idx, 10);
            const pos = this.selectedIndices.indexOf(idx);
            if (pos !== -1) {
                if (this.selectedIndices.length > 1) {
                    this.selectedIndices.splice(pos, 1);
                    // Nếu bỏ chọn đúng căn đang xem trước, đổi xem trước sang 1 căn bất kỳ còn lại
                    if (this.currentUnitIndex === idx) {
                        this.currentUnitIndex = this.selectedIndices[0];
                        this.loadUnitPreview(this.currentUnitIndex);
                    }
                }
            } else {
                this.selectedIndices.push(idx);
                // "chọn nhiều thì hiển thị 1 chỉ căn bất kỳ": hiển thị căn vừa chọn
                this.currentUnitIndex = idx;
                this.loadUnitPreview(idx);
            }
        },

        toggleSelectAll() {
            if (this.isAllFilteredSelected) {
                // Bỏ chọn tất cả, chỉ giữ căn đang xem
                this.selectedIndices = [this.currentUnitIndex];
            } else {
                // Chọn tất cả các căn đang hiển thị trong bộ lọc
                const allIdxs = this.filteredUnitList.map(u => u.originalIndex);
                this.selectedIndices = Array.from(new Set([...this.selectedIndices, ...allIdxs]));
            }
        },

        clearSelectionToCurrent() {
            this.selectedIndices = [this.currentUnitIndex];
            this.selectUnitByIndex(this.currentUnitIndex);
        },

        selectSingleUnit(idx) {
            this.selectedIndices = [parseInt(idx, 10)];
            this.selectUnitByIndex(idx);
        },

        onRowBodyClick(idx) {
            idx = parseInt(idx, 10);
            if (this.selectedIndices.length > 1) {
                if (this.selectedIndices.includes(idx)) {
                    // Căn này đã được chọn -> chuyển xem trước căn này trên Canvas
                    this.currentUnitIndex = idx;
                    this.loadUnitPreview(idx);
                } else {
                    // Bấm vào dòng căn chưa chọn -> chuyển hẳn sang chế độ sửa riêng căn này
                    this.selectSingleUnit(idx);
                }
            } else {
                // Đang chọn 1 căn: bấm vào dòng chuyển sang sửa căn này
                this.selectSingleUnit(idx);
            }
        },

        loadUnitPreview(idx) {
            if (!this.importedUnits || !this.importedUnits[idx]) return;
            this.currentUnitIndex = parseInt(idx, 10);
            const u = this.importedUnits[this.currentUnitIndex];
            this.posterForm.unit_code = u.unit_code || '';
            this.posterForm.unit_type = u.unit_type || '';
            this.posterForm.block = u.block || '';
            this.posterForm.floor = u.floor || '';
            this.posterForm.area = u.area !== null && u.area !== undefined ? String(u.area) : '';
            this.posterForm.direction = u.direction || '';
            this.posterForm.view = u.view || '';
            this.posterForm.price_text = u.price_text || (u.price ? this.formatPriceVND(u.price) : '');
            this.posterForm.price_note = u.price_note || '(Chưa VAT)';

            // Đồng bộ slotImages chuẩn của căn này (khớp mặt bằng tầng / ảnh chỉ căn)
            this.slotImages = this.resolveUnitSlotImages(u);

            this.debouncedRender();

            this.$nextTick(() => {
                const el = document.getElementById('unit-item-' + this.currentUnitIndex);
                if (el) {
                    el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            });
        },

        async batchSaveSelectedUnits() {
            if (!this.selectedIndices || this.selectedIndices.length === 0) return;
            const total = this.selectedIndices.length;

            const confirm = await Swal.fire({
                title: `Lưu & áp dụng ảnh cho ${total} căn đã chọn?`,
                html: `Hệ thống sẽ dùng phôi hiện tại và các khung ảnh đã gán để xuất và lưu ảnh poster chỉ căn cho <b>${total} căn</b> đang chọn.<br><br><span class="text-xs text-slate-500">Thông tin riêng của từng căn (mã căn, loại căn, diện tích, giá) sẽ được giữ nguyên vẹn.</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: `🚀 Bắt đầu lưu ${total} căn`,
                cancelButtonText: 'Hủy'
            });

            if (!confirm.isConfirmed) return;

            this.isBatchGenerating = true;
            this.batchModalOpen = true;
            this.batchProgress = {
                current: 0,
                total: total,
                percent: 0,
                currentCode: '',
                successCount: 0,
                failCount: 0,
                isDone: false
            };

            const canvas = document.getElementById('posterCanvas');

            for (let i = 0; i < total; i++) {
                const idx = this.selectedIndices[i];
                const u = this.importedUnits[idx];
                if (!u) continue;

                this.batchProgress.current = i + 1;
                this.batchProgress.currentCode = u.unit_code;
                this.batchProgress.percent = Math.round(((i + 1) / total) * 100);

                // 1. Cập nhật posterForm tạm thời cho căn này
                this.posterForm.unit_code = u.unit_code || '';
                this.posterForm.unit_type = u.unit_type || '';
                this.posterForm.block = u.block || '';
                this.posterForm.floor = u.floor || '';
                this.posterForm.area = u.area !== null && u.area !== undefined ? String(u.area) : '';
                this.posterForm.direction = u.direction || '';
                this.posterForm.view = u.view || '';
                this.posterForm.price_text = u.price_text || (u.price ? this.formatPriceVND(u.price) : '');
                this.posterForm.price_note = u.price_note || '(Chưa VAT)';

                // 2. Đồng bộ slotImages chuẩn của căn này (đúng mặt bằng tầng/chỉ căn)
                this.slotImages = this.resolveUnitSlotImages(u);

                // 3. Render canvas đồng bộ
                await this.renderPosterCanvas();

                // 4. Lấy base64
                const dataUrl = canvas.toDataURL('image/jpeg', 0.95);

                // 5. Gửi lưu
                const payload = {
                    project_id: '{{ $project->id }}',
                    unit_code: this.posterForm.unit_code,
                    unit_type: this.posterForm.unit_type,
                    direction: this.posterForm.direction,
                    view: this.posterForm.view,
                    area: this.posterForm.area,
                    price_text: this.posterForm.price_text,
                    price_note: this.posterForm.price_note,
                    block: this.posterForm.block,
                    floor: this.posterForm.floor,
                    image_data: dataUrl,
                    composer_data: JSON.stringify({
                        template_id: this.selectedTemplateId,
                        slotImages: this.slotImages,
                        posterForm: this.posterForm
                    })
                };

                try {
                    const res = await fetch('{{ route('admin.unit-locators.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.batchProgress.successCount++;
                        u.has_image = true;
                        u.composer_data = payload.composer_data;
                        u._slotImages = { ...this.slotImages };
                        if (data.image_url) u.image_url = data.image_url;
                    } else {
                        this.batchProgress.failCount++;
                    }
                } catch(err) {
                    this.batchProgress.failCount++;
                }
            }

            this.batchProgress.isDone = true;
            this.isBatchGenerating = false;

            // Nạp lại preview cho căn hiện tại
            this.loadUnitPreview(this.currentUnitIndex);
        },

        prevUnit() {
            if (this.currentUnitIndex > 0) {
                this.selectUnitByIndex(this.currentUnitIndex - 1);
            }
        },

        nextUnit() {
            if (this.currentUnitIndex < this.importedUnits.length - 1) {
                this.selectUnitByIndex(this.currentUnitIndex + 1);
            }
        },

        openExcelModal() {
            this.excelModalOpen = true;
        },

        handleExcelUpload(event) {
            const file = event.target.files ? event.target.files[0] : (event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files[0] : null);
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                try {
                    const data = new Uint8Array(e.target.result);
                    const workbook = XLSX.read(data, { type: 'array' });
                    
                    const firstSheetName = workbook.SheetNames[0];
                    const worksheet = workbook.Sheets[firstSheetName];
                    
                    const rawRows = XLSX.utils.sheet_to_json(worksheet, { header: 1, defval: '' });
                    if (!rawRows || rawRows.length < 2) {
                        Swal.fire('File trống', 'File Excel không có dữ liệu!', 'warning');
                        return;
                    }

                    // Tìm dòng tiêu đề
                    let headerIndex = 0;
                    for (let i = 0; i < Math.min(rawRows.length, 6); i++) {
                        const row = rawRows[i];
                        if (!Array.isArray(row)) continue;
                        const rowStr = row.map(c => String(c || '').toLowerCase().trim()).join(' ');
                        if (rowStr.includes('mã căn') || rowStr.includes('căn số') || (rowStr.includes('tòa') && rowStr.includes('tầng')) || rowStr.includes('trục')) {
                            headerIndex = i;
                            break;
                        }
                    }

                    const headerRow = rawRows[headerIndex].map(c => String(c || '').trim());
                    
                    const colMap = {};
                    headerRow.forEach((colName, idx) => {
                        const norm = colName.toLowerCase().replace(/[\s\-_/\\()]/g, '');
                        if (norm.includes('trụctòa') || norm.includes('tòa') || norm.includes('tháp') || norm.includes('block') || norm.includes('trục')) {
                            colMap.block = idx;
                        } else if (norm.includes('tầng') || norm.includes('floor')) {
                            colMap.floor = idx;
                        } else if (norm.includes('cănsố') || norm.includes('sốcăn')) {
                            colMap.unit_number = idx;
                        } else if (norm.includes('mãcăn') || norm.includes('mã') || norm.includes('unitcode')) {
                            colMap.unit_code = idx;
                        } else if (norm.includes('loạicăn') || norm.includes('loại') || norm.includes('phòngngủ') || norm.includes('pn')) {
                            colMap.unit_type = idx;
                        } else if (norm.includes('diệntích') || norm.includes('dt') || norm.includes('area')) {
                            colMap.area = idx;
                        } else if (norm.includes('giá') || norm.includes('price')) {
                            colMap.price = idx;
                        } else if (norm.includes('hướng') || norm.includes('direction')) {
                            colMap.direction = idx;
                        } else if (norm.includes('view') || norm.includes('tầmview')) {
                            colMap.view = idx;
                        }
                    });

                    // Nếu không tìm thấy cột mã căn, thử cột D (index 3) như mẫu của user
                    if (colMap.unit_code === undefined && headerRow.length > 3) {
                        colMap.unit_code = 3;
                    }

                    const parsedUnits = [];
                    for (let r = headerIndex + 1; r < rawRows.length; r++) {
                        const row = rawRows[r];
                        if (!row || row.length === 0) continue;

                        let code = colMap.unit_code !== undefined ? String(row[colMap.unit_code] || '').trim() : '';
                        let block = colMap.block !== undefined ? String(row[colMap.block] || '').trim() : '';
                        let floor = colMap.floor !== undefined ? String(row[colMap.floor] || '').trim() : '';
                        let unitNumber = colMap.unit_number !== undefined ? String(row[colMap.unit_number] || '').trim() : '';

                        if (!code && (block || floor || unitNumber)) {
                            code = [block, floor, unitNumber].filter(Boolean).join('.');
                        }

                        if (!code) continue;

                        let unitType = colMap.unit_type !== undefined ? String(row[colMap.unit_type] || '').trim() : '';
                        let rawArea = colMap.area !== undefined ? row[colMap.area] : null;
                        let area = null;
                        if (rawArea) {
                            const parsedArea = parseFloat(String(rawArea).replace(',', '.'));
                            if (!isNaN(parsedArea)) area = parsedArea;
                        }

                        let rawPrice = colMap.price !== undefined ? row[colMap.price] : null;
                        let priceNum = null;
                        let priceText = '';
                        let priceNote = '(Chưa VAT)';
                        if (rawPrice !== null && rawPrice !== undefined && rawPrice !== '') {
                            if (typeof rawPrice === 'number') {
                                priceNum = rawPrice;
                                priceText = this.formatPriceVND(rawPrice);
                            } else {
                                const cleanStr = String(rawPrice).replace(/[,.\s]/g, '');
                                const pNum = parseFloat(cleanStr);
                                if (!isNaN(pNum) && pNum > 10000000) {
                                    priceNum = pNum;
                                    priceText = this.formatPriceVND(pNum);
                                } else {
                                    priceText = String(rawPrice).trim();
                                }
                            }
                        }

                        let direction = colMap.direction !== undefined ? String(row[colMap.direction] || '').trim() : '';
                        let view = colMap.view !== undefined ? String(row[colMap.view] || '').trim() : '';

                        parsedUnits.push({
                            unit_code: code,
                            unit_type: unitType,
                            block: block,
                            floor: floor,
                            area: area,
                            price: priceNum,
                            price_text: priceText,
                            price_note: priceNote,
                            direction: direction,
                            view: view,
                            has_image: false
                        });
                    }

                    if (parsedUnits.length === 0) {
                        Swal.fire('Không có dữ liệu', 'Không đọc được mã căn nào từ file Excel. Vui lòng kiểm tra lại cấu trúc file!', 'warning');
                        return;
                    }

                    this.excelFileName = file.name;
                    this.previewExcelUnits = parsedUnits;
                    this.excelModalOpen = false;
                    this.excelPreviewModalOpen = true;

                } catch (err) {
                    Swal.fire('Lỗi đọc file', 'Không thể đọc file Excel: ' + err.message, 'error');
                }
            };
            reader.readAsArrayBuffer(file);
        },

        confirmImportExcel(generateBatch = false) {
            if (!this.previewExcelUnits || this.previewExcelUnits.length === 0) return;

            this.isImportingExcel = true;

            fetch('{{ route('admin.unit-locators.import-excel', $project->id) }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ units: this.previewExcelUnits })
            })
            .then(res => res.json())
            .then(data => {
                this.isImportingExcel = false;
                this.excelPreviewModalOpen = false;

                if (data.success) {
                    this.importedUnits = data.units || this.previewExcelUnits;
                    this.selectedIndices = [0];
                    this.selectUnitByIndex(0);

                    Swal.fire({
                        toast: true,
                        position: 'bottom-start',
                        icon: 'success',
                        title: data.message || `Đã nhập thành công ${this.importedUnits.length} căn từ Excel!`,
                        showConfirmButton: false,
                        timer: 3000
                    });

                    if (generateBatch) {
                        setTimeout(() => {
                            this.batchGenerateAll();
                        }, 400);
                    }
                } else {
                    Swal.fire('Lỗi', data.message || 'Không thể lưu danh sách căn', 'error');
                }
            })
            .catch(err => {
                this.isImportingExcel = false;
                Swal.fire('Lỗi máy chủ', err.message, 'error');
            });
        },

        async batchGenerateAll() {
            if (!this.importedUnits || this.importedUnits.length === 0) {
                Swal.fire('Chưa có căn', 'Vui lòng nhập file Excel danh sách căn trước!', 'warning');
                return;
            }

            const total = this.importedUnits.length;
            const confirm = await Swal.fire({
                title: `Tự động tạo ${total} ảnh chỉ căn?`,
                html: `Hệ thống sẽ dùng phôi hiện tại và các khung ảnh đã gán để xuất và lưu <b>${total} ảnh poster chỉ căn</b> tương ứng với từng căn trong danh sách Excel.<br><br><span class="text-xs text-slate-500">Quá trình này diễn ra hoàn toàn tự động trong vài giây.</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: `🚀 Bắt đầu tạo ${total} căn`,
                cancelButtonText: 'Hủy'
            });

            if (!confirm.isConfirmed) return;

            this.isBatchGenerating = true;
            this.batchModalOpen = true;
            this.batchProgress = {
                current: 0,
                total: total,
                percent: 0,
                currentCode: '',
                successCount: 0,
                failCount: 0,
                isDone: false
            };

            const canvas = document.getElementById('posterCanvas');

            for (let i = 0; i < total; i++) {
                const u = this.importedUnits[i];
                this.batchProgress.current = i + 1;
                this.batchProgress.currentCode = u.unit_code;
                this.batchProgress.percent = Math.round(((i + 1) / total) * 100);

                // 1. Cập nhật posterForm
                this.posterForm.unit_code = u.unit_code || '';
                this.posterForm.unit_type = u.unit_type || '';
                this.posterForm.block = u.block || '';
                this.posterForm.floor = u.floor || '';
                this.posterForm.area = u.area !== null && u.area !== undefined ? String(u.area) : '';
                this.posterForm.direction = u.direction || '';
                this.posterForm.view = u.view || '';
                this.posterForm.price_text = u.price_text || (u.price ? this.formatPriceVND(u.price) : '');
                this.posterForm.price_note = u.price_note || '(Chưa VAT)';

                // 2. Đồng bộ slotImages chuẩn của căn này (đúng mặt bằng tầng/chỉ căn)
                this.slotImages = this.resolveUnitSlotImages(u);

                // 3. Render canvas đồng bộ
                await this.renderPosterCanvas();

                // 4. Lấy base64
                const dataUrl = canvas.toDataURL('image/jpeg', 0.95);

                // 5. Gửi lưu
                const payload = {
                    project_id: '{{ $project->id }}',
                    unit_code: this.posterForm.unit_code,
                    unit_type: this.posterForm.unit_type,
                    direction: this.posterForm.direction,
                    view: this.posterForm.view,
                    area: this.posterForm.area,
                    price_text: this.posterForm.price_text,
                    price_note: this.posterForm.price_note,
                    block: this.posterForm.block,
                    floor: this.posterForm.floor,
                    image_data: dataUrl,
                    composer_data: JSON.stringify({
                        template_id: this.selectedTemplateId,
                        slotImages: this.slotImages,
                        posterForm: this.posterForm
                    })
                };

                try {
                    const res = await fetch('{{ route('admin.unit-locators.store') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload)
                    });
                    const data = await res.json();
                    if (data.success) {
                        this.batchProgress.successCount++;
                        u.has_image = true;
                        u.composer_data = payload.composer_data;
                        u._slotImages = { ...this.slotImages };
                        if (data.image_url) u.image_url = data.image_url;
                    } else {
                        this.batchProgress.failCount++;
                    }
                } catch (e) {
                    this.batchProgress.failCount++;
                }

                await new Promise(r => setTimeout(r, 40));
            }

            this.batchProgress.isDone = true;
            this.isBatchGenerating = false;

            // Khôi phục xem trước chuẩn cho căn đang chọn
            if (this.importedUnits && this.importedUnits[this.currentUnitIndex]) {
                this.loadUnitPreview(this.currentUnitIndex);
            }
        },

        openDirectUploadModal() {
            if (this.libCategory === 'floorplan') {
                this.directUploadType = 'floorplan';
            } else if (this.libCategory === 'layout_3d' || this.libCategory === 'perspective' || this.libCategory === 'amenity') {
                this.directUploadType = 'perspective';
                this.directUploadCategory = this.libCategory;
            } else {
                this.directUploadType = 'floorplan';
            }
            this.directUploadName = '';
            this.directUploadFiles = [];
            this.directUploadModalOpen = true;
        },

        handleDirectFilesSelect(event) {
            const files = event.target.files;
            if (files && files.length > 0) {
                this.directUploadFiles = Array.from(files);
                if (!this.directUploadName && this.directUploadFiles[0]) {
                    this.directUploadName = this.directUploadFiles[0].name.replace(/\.[^/.]+$/, '');
                }
            }
        },

        handleDirectFilesDrop(event) {
            const files = event.dataTransfer.files;
            if (files && files.length > 0) {
                this.directUploadFiles = Array.from(files);
                if (!this.directUploadName && this.directUploadFiles[0]) {
                    this.directUploadName = this.directUploadFiles[0].name.replace(/\.[^/.]+$/, '');
                }
            }
        },

        submitDirectUpload() {
            if (!this.directUploadFiles || this.directUploadFiles.length === 0) {
                Swal.fire('Thiếu file', 'Vui lòng chọn ít nhất 1 file hình ảnh!', 'warning');
                return;
            }

            this.isDirectUploading = true;
            const formData = new FormData();
            formData.append('project_id', '{{ $project->id }}');

            if (this.directUploadType === 'floorplan') {
                formData.append('name', this.directUploadName || this.directUploadFiles[0].name);
                formData.append('floorplan_file', this.directUploadFiles[0]);

                fetch('{{ route('admin.unit-locators.floorplan.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    this.isDirectUploading = false;
                    if (data.success) {
                        const newFp = data.floorplan;
                        newFp.image_url = data.image_url || ('{{ url('admin/unit-locators/media') }}/' + newFp.image);
                        this.allFloorplans.unshift(newFp);
                        this.libCategory = 'floorplan';
                        this.directUploadModalOpen = false;

                        Swal.fire({
                            toast: true,
                            position: 'bottom-start',
                            icon: 'success',
                            title: 'Đã tải lên mặt bằng thành công!',
                            showConfirmButton: false,
                            timer: 2500
                        });
                    } else {
                        Swal.fire('Lỗi', data.message || 'Không thể tải lên mặt bằng', 'error');
                    }
                })
                .catch(err => {
                    this.isDirectUploading = false;
                    Swal.fire('Lỗi', err.message, 'error');
                });

            } else {
                formData.append('category', this.directUploadCategory || 'layout_3d');
                formData.append('name', this.directUploadName || '');
                for (let i = 0; i < this.directUploadFiles.length; i++) {
                    formData.append('image_files[]', this.directUploadFiles[i]);
                }

                fetch('{{ route('admin.unit-locators.perspectives.store') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    this.isDirectUploading = false;
                    if (data.success) {
                        if (data.perspectives && Array.isArray(data.perspectives)) {
                            data.perspectives.forEach(p => {
                                p.image_url = p.image_url || ('{{ url('admin/unit-locators/media') }}/' + p.image);
                                this.allPerspectives.unshift(p);
                            });
                        }
                        this.libCategory = this.directUploadCategory;
                        this.directUploadModalOpen = false;

                        Swal.fire({
                            toast: true,
                            position: 'bottom-start',
                            icon: 'success',
                            title: data.message || 'Đã tải lên ảnh thành công!',
                            showConfirmButton: false,
                            timer: 2500
                        });
                    } else {
                        Swal.fire('Lỗi', data.message || 'Không thể tải lên ảnh', 'error');
                    }
                })
                .catch(err => {
                    this.isDirectUploading = false;
                    Swal.fire('Lỗi', err.message, 'error');
                });
            }
        },

        loadSavedFloorplan(url) {
            if (!url) return;
            const img = new Image();
            img.crossOrigin = 'anonymous';
            img.onload = () => {
                this.imgObj = img;
                this.hasImage = true;
                this.marker = { x: img.naturalWidth / 2, y: img.naturalHeight / 2 };
                this.$nextTick(() => this.renderCanvas());
            };
            img.src = url;
        },

        handleFileUpload(event) {
            const file = event.target.files[0];
            if (!file) return;

            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    this.imgObj = img;
                    this.hasImage = true;
                    this.marker = { x: img.naturalWidth / 2, y: img.naturalHeight / 2 };
                    this.$nextTick(() => this.renderCanvas());
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        },

        getCanvasCoordinates(event) {
            const canvas = document.getElementById('studioCanvas');
            if (!canvas) return { x: 0, y: 0 };
            const rect = canvas.getBoundingClientRect();
            const scaleX = canvas.width / rect.width;
            const scaleY = canvas.height / rect.height;
            return {
                x: (event.clientX - rect.left) * scaleX,
                y: (event.clientY - rect.top) * scaleY
            };
        },

        isPointInResizeHandle(x, y) {
            if (this.cardPosition === 'none' || !this.cardRect) return false;
            const rX = this.cardRect.x + this.cardRect.w;
            const rY = this.cardRect.y + this.cardRect.h;
            const tolerance = Math.max(18, 24 * ((this.cardScalePercent || 100) / 100));
            return (x >= rX - tolerance && x <= rX + 12) &&
                   (y >= rY - tolerance && y <= rY + 12);
        },

        isPointInCard(x, y) {
            if (this.cardPosition === 'none' || !this.cardRect) return false;
            return x >= this.cardRect.x && x <= this.cardRect.x + this.cardRect.w &&
                   y >= this.cardRect.y && y <= this.cardRect.y + this.cardRect.h;
        },

        handleCanvasMouseDown(event) {
            if (!this.imgObj) return;
            const { x, y } = this.getCanvasCoordinates(event);
            const canvas = document.getElementById('studioCanvas');

            // 1. Kiểm tra bấm vào góc co giãn kích thước thẻ (Resize Handle)
            if (this.isPointInResizeHandle(x, y)) {
                this.isResizingCard = true;
                this.resizeStart = {
                    x: x,
                    y: y,
                    initialScale: this.cardScalePercent || 100
                };
                if (canvas) canvas.style.cursor = 'nwse-resize';
                return;
            }

            // 2. Kiểm tra bấm vào thân thẻ để kéo di chuyển vị trí (Drag Card)
            if (this.isPointInCard(x, y)) {
                this.isDraggingCard = true;
                this.cardPosition = 'custom';
                this.dragCardOffset = {
                    x: x - this.cardRect.x,
                    y: y - this.cardRect.y
                };
                if (canvas) canvas.style.cursor = 'grabbing';
                return;
            }

            // 3. Bấm ra ngoài thẻ -> Đặt điểm ghim chỉ căn
            this.marker.x = x;
            this.marker.y = y;
            this.renderCanvas();
        },

        handleCanvasMouseMove(event) {
            if (!this.imgObj) return;
            const { x, y } = this.getCanvasCoordinates(event);
            const canvas = document.getElementById('studioCanvas');
            if (!canvas) return;

            // Xử lý kéo góc để co giãn kích thước thẻ (Resize)
            if (this.isResizingCard) {
                const baseW = 380;
                const currentW = this.cardRect ? this.cardRect.w : (baseW * ((this.cardScalePercent || 100) / 100));
                const deltaX = x - this.resizeStart.x;
                const newW = currentW + deltaX;
                let newScale = Math.round((newW / baseW) * 100);
                newScale = Math.max(40, Math.min(250, newScale));
                this.cardScalePercent = newScale;
                this.resizeStart.x = x;
                this.renderCanvas();
                return;
            }

            // Xử lý kéo thân thẻ để đổi vị trí (Move)
            if (this.isDraggingCard) {
                this.cardCustomX = Math.round(x - this.dragCardOffset.x);
                this.cardCustomY = Math.round(y - this.dragCardOffset.y);
                this.renderCanvas();
                return;
            }

            // Đổi con trỏ chuột khi di chuyển bình thường
            if (this.isPointInResizeHandle(x, y)) {
                canvas.style.cursor = 'nwse-resize';
            } else if (this.isPointInCard(x, y)) {
                canvas.style.cursor = 'grab';
            } else {
                canvas.style.cursor = 'crosshair';
            }
        },

        handleCanvasMouseUp(event) {
            this.isResizingCard = false;
            this.isDraggingCard = false;
            const canvas = document.getElementById('studioCanvas');
            if (canvas && this.imgObj) {
                const { x, y } = this.getCanvasCoordinates(event);
                if (this.isPointInResizeHandle(x, y)) {
                    canvas.style.cursor = 'nwse-resize';
                } else if (this.isPointInCard(x, y)) {
                    canvas.style.cursor = 'grab';
                } else {
                    canvas.style.cursor = 'crosshair';
                }
            }
        },

        handleCanvasClick(event) {
            if (this.isDraggingCard || this.isResizingCard) return;
            const { x, y } = this.getCanvasCoordinates(event);
            if (this.isPointInCard(x, y) || this.isPointInResizeHandle(x, y)) return;
            this.marker.x = x;
            this.marker.y = y;
            this.renderCanvas();
        },

        handleTouchStart(event) {
            if (event.touches.length === 1) {
                const touch = event.touches[0];
                this.handleCanvasMouseDown({
                    clientX: touch.clientX,
                    clientY: touch.clientY
                });
            }
        },

        handleTouchMove(event) {
            if (event.touches.length === 1 && (this.isDraggingCard || this.isResizingCard)) {
                if (event.cancelable) event.preventDefault();
                const touch = event.touches[0];
                this.handleCanvasMouseMove({
                    clientX: touch.clientX,
                    clientY: touch.clientY
                });
            }
        },

        handleTouchEnd(event) {
            this.handleCanvasMouseUp(event);
        },

        resetMarker() {
            if (!this.imgObj) return;
            this.marker.x = this.imgObj.naturalWidth / 2;
            this.marker.y = this.imgObj.naturalHeight / 2;
            this.renderCanvas();
        },

        renderCanvas() {
            if (!this.imgObj) return;

            const canvas = document.getElementById('studioCanvas');
            if (!canvas) return;

            const ctx = canvas.getContext('2d');
            const w = this.imgObj.naturalWidth;
            const h = this.imgObj.naturalHeight;

            canvas.width = w;
            canvas.height = h;

            // 1. Vẽ ảnh nền mặt bằng gốc
            ctx.drawImage(this.imgObj, 0, 0, w, h);

            // 2. Vẽ điểm Ghim / Marker Chỉ Căn
            this.drawMarker(ctx, this.marker.x, this.marker.y);

            // 3. Vẽ Thẻ Thông Tin Căn Hộ (Info Card Banner)
            if (this.cardPosition !== 'none') {
                this.drawInfoCard(ctx, w, h);
            }
        },

        drawMarker(ctx, x, y) {
            const color = this.pinColor;
            let radius = 24;
            if (this.pinSize === 'small') radius = 16;
            if (this.pinSize === 'medium') radius = 24;
            if (this.pinSize === 'large') radius = 32;
            if (this.pinSize === 'xlarge') radius = 42;

            ctx.save();

            if (this.pinStyle === 'bullseye') {
                // Vòng hào quang bên ngoài
                ctx.beginPath();
                ctx.arc(x, y, radius * 1.8, 0, Math.PI * 2);
                ctx.fillStyle = this.hexToRgba(color, 0.25);
                ctx.fill();

                // Vòng tròn giữa
                ctx.beginPath();
                ctx.arc(x, y, radius * 1.2, 0, Math.PI * 2);
                ctx.fillStyle = this.hexToRgba(color, 0.5);
                ctx.fill();

                // Vòng tròn đặc
                ctx.beginPath();
                ctx.arc(x, y, radius * 0.7, 0, Math.PI * 2);
                ctx.fillStyle = color;
                ctx.fill();
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 3;
                ctx.stroke();

                // Chấm tâm
                ctx.beginPath();
                ctx.arc(x, y, radius * 0.25, 0, Math.PI * 2);
                ctx.fillStyle = '#ffffff';
                ctx.fill();

                // Nhãn mã căn bay phía trên
                if (this.form.unit_code) {
                    this.drawFloatingTag(ctx, x, y - radius * 1.4, this.form.unit_code, color);
                }

            } else if (this.pinStyle === 'pin') {
                // Biểu tượng Ghim Vị Trí
                ctx.shadowColor = 'rgba(0, 0, 0, 0.4)';
                ctx.shadowBlur = 10;
                ctx.shadowOffsetY = 5;

                ctx.beginPath();
                ctx.arc(x, y - radius * 1.2, radius, 0, Math.PI * 2);
                ctx.fillStyle = color;
                ctx.fill();
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 3;
                ctx.stroke();

                // Mũi nhọn ghim
                ctx.beginPath();
                ctx.moveTo(x - radius * 0.7, y - radius * 0.7);
                ctx.lineTo(x, y);
                ctx.lineTo(x + radius * 0.7, y - radius * 0.7);
                ctx.fillStyle = color;
                ctx.fill();

                // Chấm trắng ở giữa
                ctx.beginPath();
                ctx.arc(x, y - radius * 1.2, radius * 0.35, 0, Math.PI * 2);
                ctx.fillStyle = '#ffffff';
                ctx.fill();

                if (this.form.unit_code) {
                    this.drawFloatingTag(ctx, x, y - radius * 2.5, this.form.unit_code, color);
                }

            } else if (this.pinStyle === 'badge') {
                // Nhãn Badge bo góc nổi bật trực tiếp tại tâm
                const label = this.form.unit_code || 'CH-01';
                ctx.font = `bold ${radius * 1.1}px sans-serif`;
                const textWidth = ctx.measureText(label).width;
                const padX = 16;
                const padY = 8;
                const boxW = textWidth + padX * 2;
                const boxH = radius * 1.1 + padY * 2;

                ctx.shadowColor = 'rgba(0, 0, 0, 0.45)';
                ctx.shadowBlur = 12;
                ctx.shadowOffsetY = 4;

                // Bo góc
                this.roundRect(ctx, x - boxW / 2, y - boxH / 2, boxW, boxH, 10, color, '#ffffff', 2.5);

                ctx.fillStyle = '#ffffff';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(label, x, y);
            }

            ctx.restore();
        },

        drawFloatingTag(ctx, x, y, text, bgColor) {
            ctx.save();
            ctx.font = 'bold 15px sans-serif';
            const textWidth = ctx.measureText(text).width;
            const padX = 12;
            const padY = 6;
            const boxW = textWidth + padX * 2;
            const boxH = 26;

            ctx.shadowColor = 'rgba(0,0,0,0.3)';
            ctx.shadowBlur = 8;
            this.roundRect(ctx, x - boxW / 2, y - boxH, boxW, boxH, 6, bgColor, '#ffffff', 1.5);

            ctx.fillStyle = '#ffffff';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(text, x, y - boxH / 2);
            ctx.restore();
        },

        drawInfoCard(ctx, canvasW, canvasH) {
            ctx.save();

            const scale = (this.cardScalePercent || 100) / 100;
            const baseW = 380;
            const baseH = 140;
            const cardW = Math.round(baseW * scale);
            const cardH = Math.round(baseH * scale);
            const margin = Math.round(24 * Math.min(1.5, Math.max(0.6, scale)));

            let cardX = margin;
            let cardY = canvasH - cardH - margin;

            if (this.cardPosition === 'bottom-right') {
                cardX = canvasW - cardW - margin;
                cardY = canvasH - cardH - margin;
            } else if (this.cardPosition === 'top-left') {
                cardX = margin;
                cardY = margin;
            } else if (this.cardPosition === 'top-right') {
                cardX = canvasW - cardW - margin;
                cardY = margin;
            } else if (this.cardPosition === 'custom') {
                cardX = (this.cardCustomX !== null && this.cardCustomX !== undefined) ? this.cardCustomX : margin;
                cardY = (this.cardCustomY !== null && this.cardCustomY !== undefined) ? this.cardCustomY : (canvasH - cardH - margin);
                // Giữ thẻ nằm trong khung hình canvas
                cardX = Math.max(0, Math.min(cardX, canvasW - cardW));
                cardY = Math.max(0, Math.min(cardY, canvasH - cardH));
            }

            // Lưu bounding box của thẻ để phục vụ hit-test kéo thả và co giãn kích thước
            this.cardRect = { x: cardX, y: cardY, w: cardW, h: cardH };

            const cornerRadius = Math.max(6, Math.round(16 * scale));
            const padX = Math.round(20 * scale);
            const maxTextW = cardW - padX - Math.round(16 * scale);

            // Card Background (Dark Glassmorphism)
            ctx.shadowColor = 'rgba(0, 0, 0, 0.45)';
            ctx.shadowBlur = Math.round(20 * scale);
            ctx.shadowOffsetY = Math.round(6 * scale);
            this.roundRect(ctx, cardX, cardY, cardW, cardH, cornerRadius, 'rgba(15, 23, 42, 0.90)', 'rgba(255, 255, 255, 0.22)', Math.max(1, 1.5 * scale));

            // Dải màu gradient bên trái thẻ
            ctx.beginPath();
            ctx.fillStyle = this.pinColor;
            const stripW = Math.max(3, Math.round(6 * scale));
            if (ctx.roundRect) {
                ctx.roundRect(cardX, cardY, stripW, cardH, [cornerRadius, 0, 0, cornerRadius]);
            } else {
                ctx.rect(cardX, cardY, stripW, cardH);
            }
            ctx.fill();

            // 1. Dòng Subtitle Dự án
            ctx.fillStyle = '#94a3b8';
            ctx.font = `600 ${Math.max(8, Math.round(11 * scale))}px sans-serif`;
            ctx.textAlign = 'left';
            ctx.fillText('DỰ ÁN BẤT ĐỘNG SẢN', cardX + padX, cardY + Math.round(24 * scale));

            // 2. Tên Dự Án (Cắt bớt nếu quá dài)
            ctx.fillStyle = '#ffffff';
            ctx.font = `bold ${Math.max(10, Math.round(15 * scale))}px sans-serif`;
            let projName = '{{ $project->name }}';
            while (ctx.measureText(projName).width > maxTextW && projName.length > 5) {
                projName = projName.slice(0, -1);
            }
            if (projName !== '{{ $project->name }}') projName += '...';
            ctx.fillText(projName, cardX + padX, cardY + Math.round(44 * scale));

            // 3. Mã Căn Hộ Nổi Bật (Vàng)
            ctx.fillStyle = '#fde047';
            ctx.font = `900 ${Math.max(14, Math.round(24 * scale))}px sans-serif`;
            const unitLabel = this.form.unit_code || 'CH-01';
            ctx.fillText(unitLabel, cardX + padX, cardY + Math.round(78 * scale));

            // 4. Dòng Thông Tin (Diện tích, Hướng, Tòa)
            let infoLine = [];
            if (this.form.area) infoLine.push(`DT: ${this.form.area} m²`);
            if (this.form.direction) infoLine.push(`Hướng: ${this.form.direction}`);
            if (this.form.block) infoLine.push(`${this.form.block}`);

            ctx.fillStyle = '#cbd5e1';
            ctx.font = `600 ${Math.max(9, Math.round(12 * scale))}px sans-serif`;
            let infoText = infoLine.join('  •  ');
            while (ctx.measureText(infoText).width > maxTextW && infoText.length > 5) {
                infoText = infoText.slice(0, -1);
            }
            if (infoText !== infoLine.join('  •  ')) infoText += '...';
            ctx.fillText(infoText, cardX + padX, cardY + Math.round(104 * scale));

            // 5. Giá Bán (Cyan)
            if (this.form.price_text) {
                ctx.fillStyle = '#38bdf8';
                ctx.font = `bold ${Math.max(10, Math.round(13 * scale))}px sans-serif`;
                let priceText = `Giá: ${this.form.price_text}`;
                while (ctx.measureText(priceText).width > maxTextW && priceText.length > 5) {
                    priceText = priceText.slice(0, -1);
                }
                ctx.fillText(priceText, cardX + padX, cardY + Math.round(124 * scale));
            }

            // 6. Điểm chốt Resize Grip ở góc dưới phải của thẻ
            ctx.save();
            ctx.strokeStyle = 'rgba(255, 255, 255, 0.45)';
            ctx.lineWidth = Math.max(1.5, Math.round(2 * scale));
            const gripSize = Math.max(6, Math.round(12 * scale));
            const gripPad = Math.max(4, Math.round(8 * scale));
            const gX = cardX + cardW - gripPad;
            const gY = cardY + cardH - gripPad;
            ctx.beginPath();
            ctx.moveTo(gX - gripSize * 0.4, gY);
            ctx.lineTo(gX, gY - gripSize * 0.4);
            ctx.moveTo(gX - gripSize * 0.8, gY);
            ctx.lineTo(gX, gY - gripSize * 0.8);
            ctx.stroke();
            ctx.restore();

            ctx.restore();
        },

        roundRect(ctx, x, y, width, height, radius, fillColor, strokeColor, lineWidth) {
            ctx.beginPath();
            ctx.roundRect(x, y, width, height, radius);
            if (fillColor) {
                ctx.fillStyle = fillColor;
                ctx.fill();
            }
            if (strokeColor && lineWidth) {
                ctx.strokeStyle = strokeColor;
                ctx.lineWidth = lineWidth;
                ctx.stroke();
            }
        },

        hexToRgba(hex, alpha) {
            const r = parseInt(hex.slice(1, 3), 16);
            const g = parseInt(hex.slice(3, 5), 16);
            const b = parseInt(hex.slice(5, 7), 16);
            return `rgba(${r}, ${g}, ${b}, ${alpha})`;
        },

        saveUnit(createNext = false) {
            if (!this.hasImage) {
                Swal.fire('Lỗi', 'Vui lòng chọn ảnh mặt bằng trước!', 'error');
                return;
            }

            if (!this.form.unit_code) {
                Swal.fire('Thiếu thông tin', 'Vui lòng nhập Mã căn hộ!', 'warning');
                return;
            }

            this.isSaving = true;

            const canvas = document.getElementById('studioCanvas');
            const dataUrl = canvas.toDataURL('image/png', 0.95);

            const payload = {
                project_id: '{{ $project->id }}',
                unit_code: this.form.unit_code,
                area: this.form.area,
                direction: this.form.direction,
                door_direction: this.form.door_direction,
                block: this.form.block,
                floor: this.form.floor,
                bedroom: this.form.bedroom,
                bathroom: this.form.bathroom,
                price_text: this.form.price_text,
                status: this.form.status,
                note: this.form.note,
                image_data: dataUrl,
                highlight_data: JSON.stringify({
                    marker: this.marker,
                    pinStyle: this.pinStyle,
                    pinColor: this.pinColor,
                    pinSize: this.pinSize,
                    cardPosition: this.cardPosition,
                    cardScalePercent: this.cardScalePercent,
                    cardCustomX: this.cardCustomX,
                    cardCustomY: this.cardCustomY
                })
            };

            fetch('{{ route('admin.unit-locators.store') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json())
            .then(data => {
                this.isSaving = false;
                if (data.success) {
                    if (this.importedUnits && this.importedUnits[this.currentUnitIndex]) {
                        this.importedUnits[this.currentUnitIndex].has_image = true;
                        if (data.unit && data.unit.image_url) {
                            this.importedUnits[this.currentUnitIndex].image_url = data.unit.image_url;
                        }
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Đã lưu chỉ căn!',
                        text: `Chỉ căn ${this.form.unit_code} đã được tạo và lưu thành công vào hệ thống.`,
                        showCancelButton: createNext,
                        confirmButtonText: createNext ? 'Tạo căn tiếp theo' : 'Xem danh sách',
                        cancelButtonText: 'Ở lại trang này'
                    }).then((result) => {
                        if (createNext) {
                            // Tự động tăng mã căn lên nếu có số, ví dụ CH-01 -> CH-02
                            this.autoIncrementUnitCode();
                            this.renderCanvas();
                        } else if (result.isConfirmed) {
                            window.location.href = '{{ route('admin.unit-locators.project', $project->id) }}';
                        }
                    });
                } else {
                    Swal.fire('Lỗi', data.message || 'Không thể lưu chỉ căn', 'error');
                }
            })
            .catch(err => {
                this.isSaving = false;
                Swal.fire('Lỗi máy chủ', err.message, 'error');
            });
        },

        autoIncrementUnitCode() {
            const code = this.form.unit_code;
            const match = code.match(/^(.*?)(\d+)$/);
            if (match) {
                const prefix = match[1];
                const num = parseInt(match[2], 10) + 1;
                const paddedNum = String(num).padStart(match[2].length, '0');
                this.form.unit_code = prefix + paddedNum;
            } else {
                this.form.unit_code = '';
            }
        },

        downloadCanvas() {
            const canvas = document.getElementById('studioCanvas');
            const link = document.createElement('a');
            link.download = `Chi-can-${this.form.unit_code || 'export'}-{{ Str::slug($project->name) }}.png`;
            link.href = canvas.toDataURL('image/png');
            link.click();
        }
    };
}
</script>
@endpush
@endsection
