@extends('layouts.admin')

@section('title', 'Tạo Chỉ căn - ' . ($project->name ?? 'Dự án'))
@section('page-title', 'Tạo Chỉ Căn: ' . ($project->name ?? 'Dự án'))

@push('styles')
<style>
.custom-unit-scroll {
    scrollbar-width: thin;
    scrollbar-color: #cbd5e1 transparent;
}
.custom-unit-scroll::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}
.custom-unit-scroll::-webkit-scrollbar-track {
    background: transparent;
}
.custom-unit-scroll::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 6px;
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

            <!-- Bộ lọc tòa, tầng, trục căn và loại căn -->
            <div class="grid grid-cols-2 gap-1 shrink-0">
                <select x-model="posterBlockFilter" class="w-full px-1.5 py-1 rounded border border-slate-200 text-[10px] bg-white"><option value="">Tất cả tòa</option><template x-for="value in locatorBlocks" :key="value"><option :value="value" x-text="value"></option></template></select>
                <select x-model="posterFloorFilter" class="w-full px-1.5 py-1 rounded border border-slate-200 text-[10px] bg-white"><option value="">Tất cả tầng</option><template x-for="value in locatorFloors" :key="value"><option :value="value" x-text="'Tầng ' + value"></option></template></select>
                <select x-model="posterAxisFilter" class="w-full px-1.5 py-1 rounded border border-slate-200 text-[10px] bg-white"><option value="">Tất cả trục</option><template x-for="value in locatorAxes" :key="value"><option :value="value" x-text="'Trục ' + value"></option></template></select>
                <select x-model="posterTypeFilter" class="w-full px-1.5 py-1 rounded border border-slate-200 text-[10px] bg-white"><option value="">Tất cả loại</option><option value="studio">Studio</option><option value="1">1N</option><option value="2">2N</option><option value="3">3N</option><option value="4">4N</option></select>
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

                                    <!-- Biểu tượng đã có cấu hình poster -->
                                    <template x-if="u.has_image">
                                        <span class="text-[9px] text-emerald-600 font-bold" title="Đã lưu cấu hình poster">
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
                        <button type="button" @click="unitSearchText = ''; posterBlockFilter = ''; posterFloorFilter = ''; posterAxisFilter = ''; posterTypeFilter = ''" class="text-amber-600 font-bold block mt-1 hover:underline mx-auto">
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
                    <span>Đã có cấu hình: <strong class="text-emerald-700 font-bold" x-text="importedUnits.filter(u => u.has_image).length"></strong>/<span x-text="importedUnits.length"></span></span>
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
                            <div class="flex items-center gap-1 shrink-0">
                                <template x-if="isFloorplanSlot(slot)">
                                    <button 
                                        type="button" 
                                        @click="goToStudioWithCurrentUnit()" 
                                        class="px-1.5 py-0.5 rounded text-[9px] font-bold bg-amber-100 hover:bg-amber-200 text-amber-900 border border-amber-300 transition flex items-center gap-1 shadow-2xs"
                                        title="Chuyển sang Studio để định vị vị trí căn hoặc đồng bộ theo trục căn trên mặt bằng"
                                    >
                                        <i class="fa-solid fa-crosshairs text-[8px] text-amber-600"></i>
                                        <span>Định vị</span>
                                    </button>
                                </template>
                                <template x-if="slotImages[slot.id]">
                                    <button type="button" @click="removeSlotImage(slot.id)" class="p-1 text-slate-400 hover:text-rose-600 shrink-0" title="Bỏ ảnh này">
                                        <i class="fa-solid fa-xmark text-xs"></i>
                                    </button>
                                </template>
                            </div>
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
                        <span x-text="isBatchGenerating ? 'Đang lưu cấu hình...' : 'Lưu cấu hình cho ' + selectedIndices.length + ' căn đã chọn'"></span>
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
                        <span x-text="isSavingPoster ? 'Đang lưu cấu hình...' : 'Lưu cấu hình poster'"></span>
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
                            <span>Lưu cấu hình & căn tiếp</span>
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

                </div>

                <template x-if="importedUnits.length > 1 && selectedIndices.length <= 1">
                    <button 
                        type="button" 
                        @click="batchGenerateAll()"
                        :disabled="isBatchGenerating || isSavingPoster"
                        class="w-full py-1.5 px-2 rounded-lg bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-black text-[11px] transition flex items-center justify-center gap-1.5 shadow-sm shadow-emerald-600/20"
                        title="Lưu cấu hình poster cho tất cả các căn"
                    >
                        <i class="fa-solid fa-bolt text-amber-300"></i>
                        <span x-text="'Lưu cấu hình cho toàn bộ ' + importedUnits.length + ' căn'"></span>
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
    <div x-show="mode === 'studio'" class="flex-1 grid grid-cols-1 lg:grid-cols-12 gap-3 min-h-0 overflow-hidden">

        <!-- Danh sách căn để chọn nhanh trong Studio định vị -->
        <div class="lg:col-span-2 h-full min-h-0 flex flex-col bg-white rounded-2xl border border-slate-200/90 shadow-xs overflow-hidden">
            <div class="p-3 border-b border-slate-100 shrink-0">
                <div class="flex items-center justify-between mb-2">
                    <h3 class="text-xs font-black text-slate-800 uppercase flex items-center gap-1.5"><i class="fa-solid fa-list text-blue-600"></i> Danh sách căn</h3>
                    <span class="text-[10px] font-bold text-blue-700 bg-blue-50 px-1.5 py-0.5 rounded-full" x-text="filteredLocatorUnits.length + ' căn'"></span>
                </div>
                <input type="text" x-model="locatorUnitSearch" placeholder="Tìm mã căn..." class="w-full mb-2 px-2 py-1.5 rounded-lg border border-slate-200 text-[11px] focus:ring-1 focus:ring-blue-500">
                <div class="grid grid-cols-2 gap-1.5">
                    <select x-model="locatorBlockFilter" class="min-w-0 px-1.5 py-1.5 rounded-lg border border-slate-200 text-[10px] bg-slate-50"><option value="">Tất cả tòa</option><template x-for="value in locatorBlocks" :key="value"><option :value="value" x-text="value"></option></template></select>
                    <select x-model="locatorFloorFilter" class="min-w-0 px-1.5 py-1.5 rounded-lg border border-slate-200 text-[10px] bg-slate-50"><option value="">Tất cả tầng</option><template x-for="value in locatorFloors" :key="value"><option :value="value" x-text="'Tầng ' + value"></option></template></select>
                    <select x-model="locatorAxisFilter" class="min-w-0 px-1.5 py-1.5 rounded-lg border border-slate-200 text-[10px] bg-slate-50"><option value="">Tất cả trục</option><template x-for="value in locatorAxes" :key="value"><option :value="value" x-text="'Trục ' + value"></option></template></select>
                    <select x-model="locatorTypeFilter" class="min-w-0 px-1.5 py-1.5 rounded-lg border border-slate-200 text-[10px] bg-slate-50"><option value="">Tất cả loại</option><option value="studio">Studio</option><option value="1">1N</option><option value="2">2N</option><option value="3">3N</option><option value="4">4N</option></select>
                </div>
                <div class="flex items-center justify-between mt-2 text-[10px]">
                    <span class="text-blue-700 font-bold" x-text="locatorSelectedIds.length + ' căn đã chọn'"></span>
                    <button type="button" @click="toggleAllLocatorUnits()" class="text-blue-600 hover:underline font-bold" x-text="allFilteredLocatorUnitsSelected ? 'Bỏ chọn kết quả' : 'Chọn tất cả kết quả'"></button>
                </div>
            </div>
            <div class="flex-1 min-h-0 overflow-y-auto p-2 space-y-1 custom-unit-scroll">
                <template x-for="unit in filteredLocatorUnits" :key="unit.id || unit.originalIndex">
                    <div class="w-full flex items-center gap-1.5 px-1.5 py-1 rounded-lg border transition" :class="currentUnitIndex === unit.originalIndex ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-200 hover:border-blue-300'">
                        <input type="checkbox" class="shrink-0 rounded border-slate-300 text-blue-600 focus:ring-blue-500" :checked="locatorSelectedIds.includes(Number(unit.id))" @click.stop @change="toggleLocatorUnit(unit.id)">
                        <button type="button" @click="selectLocatorUnit(unit.originalIndex)" class="min-w-0 flex-1 flex items-center gap-1.5 text-left whitespace-nowrap overflow-hidden">
                            <span class="text-[10px] font-black shrink-0" x-text="unit.unit_code"></span>
                            <span class="text-[9px] truncate opacity-80" x-text="[unit.block, unit.floor ? 'T.' + unit.floor : '', unit.unit_axis ? 'Trục ' + unit.unit_axis : '', unit.unit_type].filter(Boolean).join(' · ')"></span>
                            <span x-show="unit.has_image" class="text-[8px] text-emerald-500 shrink-0">●</span>
                        </button>
                    </div>
                </template>
                <template x-if="filteredLocatorUnits.length === 0"><p class="p-3 text-center text-[10px] text-slate-400">Không tìm thấy căn phù hợp.</p></template>
            </div>
        </div>

        <!-- Left: Form Input Unit Info & Pin Customization (Có thể lăn chuột cuộn nội dung độc lập) -->
        <div class="lg:col-span-4 h-full min-h-0 overflow-y-auto space-y-3 pr-1.5 custom-unit-scroll">

            <!-- Card 1: Bounding Box & Pin Styling Controls (Hình Thức Định Vị) -->
            <div class="bg-white rounded-2xl border border-slate-200/90 shadow-xs p-4 space-y-3.5">
                <div class="flex items-center justify-between pb-2 border-b border-slate-100">
                    <h3 class="text-xs font-black text-slate-900 uppercase tracking-tight flex items-center gap-1.5">
                        <i class="fa-solid fa-crosshairs text-rose-600"></i>
                        <span>Hình Thức Định Vị</span>
                    </h3>
                    <span class="text-[10px] font-bold text-rose-600 uppercase font-mono">Chuẩn Bản Vẽ</span>
                </div>

                <!-- Kiểu định vị -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Kiểu đánh dấu vị trí căn</label>
                    <div class="grid grid-cols-3 gap-2">
                        <!-- Chỉ còn lại 1 nút cho kiểu khung -->
                        <button 
                            type="button" 
                            @click="locatorStyle = 'box'; hasArrow = false; renderCanvas()"
                            :class="locatorStyle === 'box' ? 'border-rose-600 bg-rose-50 text-rose-700 font-black ring-1 ring-rose-500/30' : 'border-slate-200 text-slate-600 hover:bg-slate-50 font-bold'"
                            class="p-2 rounded-xl border text-xs text-left transition flex items-center gap-2"
                        >
                            <span class="text-lg">🔲</span>
                            <div>
                                <span class="block font-black leading-tight">Khung</span>
                                <span class="text-[10px] opacity-75 font-normal">Viền đỏ</span>
                            </div>
                        </button>
                        <button 
                            type="button" 
                            @click="locatorStyle = 'bullseye'; pinStyle = 'bullseye'; renderCanvas()"
                            :class="locatorStyle === 'bullseye' ? 'border-blue-600 bg-blue-50 text-blue-700 font-black ring-1 ring-blue-500/30' : 'border-slate-200 text-slate-600 hover:bg-slate-50 font-bold'"
                            class="p-2 rounded-xl border text-xs text-left transition flex items-center gap-2"
                        >
                            <span class="text-lg">🎯</span>
                            <div>
                                <span class="block font-black leading-tight">Mục tiêu</span>
                                <span class="text-[10px] opacity-75 font-normal">Chấm tâm</span>
                            </div>
                        </button>
                        <button 
                            type="button" 
                            @click="locatorStyle = 'badge'; pinStyle = 'badge'; renderCanvas()"
                            :class="locatorStyle === 'badge' ? 'border-blue-600 bg-blue-50 text-blue-700 font-black ring-1 ring-blue-500/30' : 'border-slate-200 text-slate-600 hover:bg-slate-50 font-bold'"
                            class="p-2 rounded-xl border text-xs text-left transition flex items-center gap-2"
                        >
                            <span class="text-lg">🏷️</span>
                            <div>
                                <span class="block font-black leading-tight">Thẻ tên</span>
                                <span class="text-[10px] opacity-75 font-normal">Badge nổi</span>
                            </div>
                        </button>
                    </div>
                </div>

                <!-- BẢNG ĐIỀU KHIỂN RIÊNG CHO KHUNG VIỀN & MŨI TÊN (BOX & BOX_ARROW) -->
                <div x-show="locatorStyle === 'box' || locatorStyle === 'box_arrow'" class="space-y-3 pt-2 border-t border-slate-100">
                    
                    <!-- Nút thao tác nhanh với khung -->
                    <div class="flex items-center gap-2">
                        <button 
                            type="button" 
                            @click="startDrawNewBox()"
                            class="flex-1 py-1.5 px-2 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 border border-rose-200 font-bold text-xs transition flex items-center justify-center gap-1.5 shadow-2xs"
                        >
                            <i class="fa-solid fa-draw-polygon text-[11px]"></i>
                            <span>Kéo vẽ khung mới</span>
                        </button>
                        <button 
                            type="button" 
                            @click="centerLocatorBox()"
                            class="py-1.5 px-2.5 rounded-lg bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-semibold transition"
                            title="Đưa khung về tâm ảnh"
                        >
                            <i class="fa-solid fa-arrows-to-dot"></i>
                        </button>
                    </div>

                    <!-- Kích thước khung & Tăng giảm nhanh -->
                    <div class="flex items-center justify-between text-xs bg-slate-50 p-2 rounded-xl border border-slate-200/80">
                        <span class="font-bold text-slate-700">Kích thước khung:</span>
                        <div class="flex items-center gap-1.5">
                            <span class="font-mono text-[11px] font-bold text-slate-600" x-text="Math.round(locatorBox.w) + ' × ' + Math.round(locatorBox.h) + ' px'"></span>
                            <button type="button" @click="scaleLocatorBox(0.9)" class="w-6 h-6 rounded bg-white hover:bg-slate-200 text-slate-700 font-bold border border-slate-200 flex items-center justify-center text-xs" title="Thu nhỏ 10%">-</button>
                            <button type="button" @click="scaleLocatorBox(1.1)" class="w-6 h-6 rounded bg-white hover:bg-slate-200 text-slate-700 font-bold border border-slate-200 flex items-center justify-center text-xs" title="Phóng to 10%">+</button>
                        </div>
                    </div>

                    <!-- Tùy chọn Mũi tên chỉ căn -->
                    <div class="space-y-1.5">
                        <div class="flex items-center justify-between">
                            <!-- Mũi tên đã được bỏ -->
                        </div>
                        

                        <!-- 4 Hướng mũi tên nhanh (bị ẩn) -->
                        <div x-show="false && hasArrow" class="grid grid-cols-4 gap-1 text-[11px]">
                            <button type="button" @click="setArrowDirection('bottom-left')" :class="arrowDir === 'bottom-left' ? 'bg-rose-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'" class="py-1 rounded transition text-center truncate">↙ Dưới-T</button>
                            <button type="button" @click="setArrowDirection('bottom-right')" :class="arrowDir === 'bottom-right' ? 'bg-rose-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'" class="py-1 rounded transition text-center truncate">↘ Dưới-P</button>
                            <button type="button" @click="setArrowDirection('top-left')" :class="arrowDir === 'top-left' ? 'bg-rose-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'" class="py-1 rounded transition text-center truncate">↖ Trên-T</button>
                            <button type="button" @click="setArrowDirection('top-right')" :class="arrowDir === 'top-right' ? 'bg-rose-600 text-white font-bold' : 'bg-slate-100 text-slate-600 hover:bg-slate-200 font-medium'" class="py-1 rounded transition text-center truncate">↗ Trên-P</button>
                        </div>
                    </div>

                    <!-- Tùy chọn Nền mờ & Nhãn tên -->
                    <div class="grid grid-cols-2 gap-2 text-xs">
                        <label class="flex items-center gap-1.5 cursor-pointer text-slate-700">
                            <input type="checkbox" x-model="boxHasFill" @change="renderCanvas()" class="w-3.5 h-3.5 rounded border-slate-300 text-rose-600">
                            <span class="font-medium text-[11px]">Nền highlight</span>
                        </label>
                        <label class="flex items-center gap-1.5 cursor-pointer text-slate-700">
                            <input type="checkbox" x-model="boxHasLabel" @change="renderCanvas()" class="w-3.5 h-3.5 rounded border-slate-300 text-rose-600">
                            <span class="font-medium text-[11px]">Nhãn mã/trục căn</span>
                        </label>
                    </div>

                    <!-- Độ dày viền -->
                    <div class="flex items-center justify-between text-xs">
                        <span class="font-bold text-slate-700">Độ dày nét viền:</span>
                        <div class="flex items-center gap-1 font-semibold text-[11px]">
                            <button type="button" @click="boxBorderWidth = 3; renderCanvas()" :class="boxBorderWidth === 3 ? 'bg-rose-600 text-white font-bold' : 'bg-slate-100 text-slate-700'" class="px-2 py-0.5 rounded transition">3px</button>
                            <button type="button" @click="boxBorderWidth = 6; renderCanvas()" :class="boxBorderWidth === 6 ? 'bg-rose-600 text-white font-bold' : 'bg-slate-100 text-slate-700'" class="px-2 py-0.5 rounded transition">6px</button>
                            <button type="button" @click="boxBorderWidth = 10; renderCanvas()" :class="boxBorderWidth === 10 ? 'bg-rose-600 text-white font-bold' : 'bg-slate-100 text-slate-700'" class="px-2 py-0.5 rounded transition">10px</button>
                        </div>
                    </div>
                </div>

                <!-- Bảng màu sắc định vị -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1.5">Màu sắc định vị</label>
                    <div class="flex items-center gap-2">
                        <template x-for="c in ['#ef4444', '#f97316', '#3b82f6', '#10b981', '#8b5cf6', '#ec4899']" :key="c">
                            <button 
                                type="button" 
                                @click="pinColor = c; renderCanvas()"
                                :style="'background-color: ' + c"
                                :class="pinColor === c ? 'ring-4 ring-offset-2 ring-rose-400 scale-110' : 'hover:scale-105'"
                                class="w-7 h-7 rounded-full transition-transform shadow-2xs"
                            ></button>
                        </template>
                    </div>
                </div>
            </div>

            <!-- Card 3: Thẻ thông tin tóm tắt căn hộ (Info Card Banner) -->
            <!-- <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-3">
                <div class="flex items-center justify-between pb-1.5 border-b border-slate-100">
                    <h3 class="text-xs font-bold text-slate-900 uppercase tracking-wider flex items-center gap-1.5">
                        <i class="fa-solid fa-id-card text-indigo-600"></i>
                        <span>Thẻ Tóm Tắt Thông Tin</span>
                    </h3>
                    <select x-model="cardPosition" @change="renderCanvas()" class="py-0.5 px-2 rounded-lg border border-slate-200 text-[11px] bg-slate-50 font-bold text-slate-700">
                        <option value="none">Không hiển thị</option>
                        <option value="bottom-left">Góc dưới - Trái</option>
                        <option value="bottom-right">Góc dưới - Phải</option>
                        <option value="top-left">Góc trên - Trái</option>
                        <option value="top-right">Góc trên - Phải</option>
                        <option value="custom">Tự do (Kéo thả)</option>
                    </select>
                </div>

                <div x-show="cardPosition !== 'none'" class="space-y-2">
                    <div class="flex items-center justify-between text-xs">
                        <span class="text-slate-600 font-medium">Kích thước thẻ:</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-black bg-blue-100 text-blue-700 font-mono" x-text="cardScalePercent + '%'"></span>
                    </div>
                    <input type="range" min="40" max="250" step="5" x-model.number="cardScalePercent" @input="renderCanvas()" class="w-full accent-blue-600 h-2 bg-slate-200 rounded-lg cursor-pointer">
                </div>
            </div> -->

            <!-- Card 4: Nút Lưu & Xuất Ảnh -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-4 space-y-2.5">
                <button
                    type="button"
                    @click="saveSelectedLocator()"
                    :disabled="isSaving || !hasImage || (!locatorSelectedIds.length && !(importedUnits[currentUnitIndex] && importedUnits[currentUnitIndex].id))"
                    class="w-full py-3 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 disabled:opacity-50 text-white font-extrabold text-sm transition shadow-lg shadow-emerald-600/25 flex items-center justify-center gap-2"
                >
                    <i class="fa-solid fa-floppy-disk"></i>
                    <span x-text="isSaving ? 'Đang lưu...' : (locatorSelectedIds.length ? 'Lưu định vị cho ' + locatorSelectedIds.length + ' căn đã chọn' : 'Lưu định vị cho căn ' + (form.unit_code || 'đang chọn'))"></span>
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
        <div class="lg:col-span-6 flex flex-col h-full min-h-0 overflow-hidden space-y-2">
            <!-- Floorplan Source Selector -->
            <div class="bg-white rounded-xl border border-slate-200/80 shadow-xs px-3.5 py-1.5 flex flex-col sm:flex-row items-center justify-between gap-3 shrink-0">
                <div class="flex items-center gap-2.5 w-full sm:w-auto">
                    <div class="w-7 h-7 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 text-xs">
                        <i class="fa-solid fa-map"></i>
                    </div>
                    <div>
                        <h4 class="text-xs font-bold text-slate-800 uppercase tracking-tight">Ảnh mặt bằng tầng</h4>
                        <p class="text-[10px] text-slate-400">Chọn mặt bằng từ dự án hoặc tải ảnh mới</p>
                    </div>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <!-- Select from saved project floorplans -->
                    @if ($floorplans->isNotEmpty())
                    <select @change="loadSavedFloorplan($event.target.value)" class="py-1 px-2.5 rounded-lg border border-slate-200 text-xs bg-slate-50 font-semibold text-slate-700 focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Chọn mặt bằng dự án --</option>
                        @foreach ($floorplans as $fp)
                        <option value="{{ $fp->image_url }}">{{ $fp->name }}</option>
                        @endforeach
                    </select>
                    @endif

                    <!-- Upload new floorplan on the fly -->
                    <label class="cursor-pointer px-3 py-1 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 text-xs font-bold transition flex items-center gap-1.5 shrink-0 border border-blue-200 shadow-2xs">
                        <i class="fa-solid fa-upload text-[11px]"></i>
                        <span>Tải ảnh</span>
                        <input type="file" accept="image/*" @change="handleFileUpload($event)" class="hidden">
                    </label>
                </div>
            </div>

            <!-- Canvas Container -->
            <div class="flex-1 min-h-0 bg-slate-900 rounded-2xl border border-slate-800 shadow-xl overflow-hidden relative flex flex-col">
                <!-- Canvas Top Bar Controls -->
                <div class="px-4 py-2 bg-slate-950/80 border-b border-slate-800 flex items-center justify-between text-xs text-slate-300 z-10 shrink-0">
                    <div class="flex items-center gap-3">
                        <span class="flex items-center gap-1.5 text-amber-400 font-semibold text-[11px]">
                            <i class="fa-solid fa-mouse-pointer text-[10px]"></i>
                            <span>Kéo chuột vẽ khung • Kéo thân khung để dời • Kéo 4 góc đổi cỡ • Kéo đuôi mũi tên đổi hướng</span>
                        </span>
                    </div>

                    <div class="flex items-center gap-2">
                        <span class="text-slate-400 text-[11px] font-mono" x-show="hasImage" x-text="(locatorStyle === 'box' || locatorStyle === 'box_arrow') ? ('Khung: X:' + Math.round(locatorBox.x) + ' Y:' + Math.round(locatorBox.y) + ' W:' + Math.round(locatorBox.w) + ' H:' + Math.round(locatorBox.h)) : ('Tọa độ: X:' + Math.round(marker.x) + ' Y:' + Math.round(marker.y))"></span>
                        <button 
                            type="button" 
                            @click="centerLocatorBox()"
                            x-show="hasImage"
                            class="px-2 py-0.5 rounded-lg bg-slate-800 hover:bg-slate-700 text-slate-300 text-[11px] font-semibold"
                        >
                            Đưa về tâm
                        </button>
                    </div>
                </div>

                <!-- Empty State before floorplan selected -->
                <div 
                    x-show="!hasImage" 
                    class="flex-1 flex flex-col items-center justify-center p-8 text-center text-slate-400"
                >
                    <div class="w-16 h-16 rounded-2xl bg-slate-800/80 border border-slate-700 flex items-center justify-center text-2xl text-blue-400 mb-3">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                    </div>
                    <h3 class="text-base font-bold text-white">Chưa chọn ảnh mặt bằng</h3>
                    <p class="text-xs text-slate-400 max-w-sm mt-1">
                        Hãy chọn một mặt bằng từ menu phía trên hoặc bấm <strong>"Tải ảnh"</strong> để bắt đầu Studio chỉ căn.
                    </p>
                    <label class="mt-4 cursor-pointer px-4 py-2 rounded-xl bg-blue-600 text-white font-bold text-xs hover:bg-blue-700 transition shadow-lg shadow-blue-500/25">
                        <i class="fa-solid fa-image mr-1"></i>
                        <span>Chọn ảnh từ máy tính</span>
                        <input type="file" accept="image/*" @change="handleFileUpload($event)" class="hidden">
                    </label>
                </div>

                <!-- Active Canvas Area -->
                <div 
                    x-show="hasImage" 
                    class="flex-1 min-h-0 overflow-auto p-3 flex items-center justify-center bg-slate-950 select-none"
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
                        class="max-w-full max-h-full object-contain rounded-lg shadow-2xl border border-slate-800"
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
                    <div class="flex items-center justify-between pb-1 border-b border-slate-200/60">
                        <div class="font-bold text-slate-800 flex items-center gap-1.5">
                            <i class="fa-solid fa-circle-info text-emerald-600"></i>
                            <span>Cột dữ liệu tự động nhận diện theo mẫu:</span>
                        </div>
                        <button type="button" @click="downloadSampleExcel()" class="text-[11px] font-bold text-emerald-700 hover:text-emerald-800 flex items-center gap-1 hover:underline">
                            <i class="fa-solid fa-file-excel"></i> Tải file Excel mẫu chuẩn (.xlsx)
                        </button>
                    </div>
                    <div class="flex flex-wrap gap-1 text-[11px]">
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">Tòa</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">Tầng</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">Căn số</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-emerald-400 font-mono font-bold text-emerald-700">Mã Căn *</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">Loại căn</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">Diện tích</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">Giá (chưa VAT)</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">Hướng</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-slate-200 font-mono">View</span>
                        <span class="px-2 py-0.5 rounded bg-white border border-indigo-300 font-mono font-bold text-indigo-700">Trục căn</span>
                    </div>
                    <p class="text-[10px] text-slate-400 italic">Hỗ trợ đầy đủ định dạng Excel theo đúng biểu mẫu dự án của bạn (10 cột: Tòa, Tầng, Căn số, Mã Căn, Loại căn, Diện tích, Giá, Hướng, View, Trục căn).</p>
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
                                <th class="py-2 px-2 text-center w-10">STT</th>
                                <th class="py-2 px-2.5">Tòa</th>
                                <th class="py-2 px-2 text-center">Tầng</th>
                                <th class="py-2 px-2 text-center">Căn số</th>
                                <th class="py-2 px-3">Mã Căn</th>
                                <th class="py-2 px-2 text-center">Trục căn</th>
                                <th class="py-2 px-2.5">Loại Căn</th>
                                <th class="py-2 px-2.5 text-right">Diện Tích</th>
                                <th class="py-2 px-3 text-right">Giá Bán</th>
                                <th class="py-2 px-2">Hướng</th>
                                <th class="py-2 px-3">View</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <template x-for="(u, idx) in previewExcelUnits" :key="idx">
                                <tr class="hover:bg-emerald-50/50 transition">
                                    <td class="py-1.5 px-2 text-center font-mono text-slate-400" x-text="idx + 1"></td>
                                    <td class="py-1.5 px-2.5 font-bold text-slate-700" x-text="u.block || '-'"></td>
                                    <td class="py-1.5 px-2 text-center text-slate-600" x-text="u.floor || '-'"></td>
                                    <td class="py-1.5 px-2 text-center text-slate-600" x-text="u.unit_number || '-'"></td>
                                    <td class="py-1.5 px-3 font-black text-slate-900" x-text="u.unit_code"></td>
                                    <td class="py-1.5 px-2 text-center">
                                        <span class="px-1.5 py-0.5 rounded font-black text-[10px] bg-indigo-50 text-indigo-700 border border-indigo-200" x-text="u.unit_axis || '-'"></span>
                                    </td>
                                    <td class="py-1.5 px-2.5">
                                        <span class="px-1.5 py-0.5 rounded font-bold text-[10px] bg-amber-100 text-amber-800" x-text="u.unit_type || '2PN'"></span>
                                    </td>
                                    <td class="py-1.5 px-2.5 text-right font-bold text-slate-800" x-text="u.area ? u.area + ' m²' : '-'"></td>
                                    <td class="py-1.5 px-3 text-right font-black text-blue-700" x-text="u.price_text || '-'"></td>
                                    <td class="py-1.5 px-2 text-slate-600" x-text="u.direction || '-'"></td>
                                    <td class="py-1.5 px-3 text-slate-600 truncate max-w-[140px]" :title="u.view" x-text="u.view || '-'"></td>
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
                <h3 class="text-base font-black text-slate-900" x-text="batchProgress.isDone ? 'Đã lưu cấu hình poster!' : 'Đang lưu cấu hình poster...'"></h3>
                <p class="text-xs text-slate-500 mt-1" x-text="batchProgress.isDone ? ('Đã lưu ' + batchProgress.successCount + '/' + batchProgress.total + ' cấu hình. Ảnh sẽ được dựng khi khách tải về.') : ('Đang lưu căn: ' + batchProgress.currentCode + ' (' + batchProgress.current + '/' + batchProgress.total + ')')"></p>
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
        cardPosition: 'none',
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
                'unit_axis' => $u->unit_axis,
                'block' => $u->block,
                'floor' => $u->floor,
                'area' => $u->area,
                'direction' => $u->direction,
                'view' => $u->view,
                'bedroom' => $u->bedroom,
                'price' => $u->price,
                'price_text' => $u->price_text,
                'price_note' => $u->price_note,
                'has_image' => !empty($u->composer_data),
                'image_url' => $u->image_url,
                'floorplan_image' => $u->floorplan_image,
                'floorplan_image_url' => $u->floorplan_image_url,
                'base_image' => $u->base_image,
                'layout_3d_image' => $u->layout_3d_image,
                'layout_3d_image_url' => $u->layout_3d_image_url,
                'composer_data' => $u->composer_data,
                'highlight_data' => $u->highlight_data,
            ];
        })) !!},
        axisLocatorsMap: {!! json_encode($axisLocators ?? []) !!},

        // Bounding Box & Axis Locator State
        locatorStyle: 'box_arrow', // 'box_arrow', 'box', 'bullseye', 'badge'
        locatorBox: { x: 0, y: 0, w: 120, h: 180 },
        hasArrow: true,
        arrowDir: 'bottom-left',
        arrowStart: { x: 0, y: 0 },
        arrowHead: { x: 0, y: 0 },
        boxBorderWidth: 6,
        boxBorderRadius: 8,
        boxHasFill: true,
        boxHasLabel: true,
        selectedAxis: '',
        syncAxisLocatorOption: true,
        inheritedFromAxis: false,
        currentFloorplanPath: '',
        currentFloorplanUrl: '',
        isDrawingBox: false,
        isDraggingBox: false,
        isResizingBox: false,
        activeResizeCorner: null,
        isDraggingArrowTail: false,
        dragBoxStart: { x: 0, y: 0, boxX: 0, boxY: 0, arrowStartX: 0, arrowStartY: 0, arrowHeadX: 0, arrowHeadY: 0 },
        resizeBoxStart: { x: 0, y: 0, box: { x: 0, y: 0, w: 0, h: 0 } },
        drawStart: { x: 0, y: 0 },
        currentUnitIndex: 0,
        locatorUnitSearch: '',
        locatorBlockFilter: '',
        locatorFloorFilter: '',
        locatorAxisFilter: '',
        locatorTypeFilter: '',
        locatorSelectedIds: [],
        showUnitsCol: true,
        unitSearchText: '',
        posterBlockFilter: '',
        posterFloorFilter: '',
        posterAxisFilter: '',
        posterTypeFilter: '',
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
        posterRenderId: 0,
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
            unit_axis: '',
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

        get allAxes() {
            const map = {};
            (this.importedUnits || []).forEach(u => {
                const ax = (u.unit_axis || '').trim();
                if (!ax) return;
                if (!map[ax]) {
                    map[ax] = {
                        axis: ax,
                        count: 0,
                        has_locator: !!(this.axisLocatorsMap && this.axisLocatorsMap[ax] && this.axisLocatorsMap[ax].has_locator)
                    };
                }
                map[ax].count++;
            });
            if (this.axisLocatorsMap) {
                Object.keys(this.axisLocatorsMap).forEach(ax => {
                    if (!map[ax]) {
                        map[ax] = {
                            axis: ax,
                            count: this.axisLocatorsMap[ax].unit_count || 0,
                            has_locator: !!this.axisLocatorsMap[ax].has_locator
                        };
                    }
                });
            }
            return Object.values(map).sort((a, b) => {
                return a.axis.localeCompare(b.axis, undefined, { numeric: true, sensitivity: 'base' });
            });
        },

        getAxisUnitCount(axis) {
            if (!axis) return 0;
            const ax = this.allAxes.find(a => a.axis === axis);
            return ax ? ax.count : 0;
        },

        get locatorBlocks() {
            return [...new Set((this.importedUnits || []).map(u => String(u.block || '').trim()).filter(Boolean))]
                .sort((a, b) => a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' }));
        },

        get locatorFloors() {
            return [...new Set((this.importedUnits || []).map(u => String(u.floor || '').trim()).filter(Boolean))]
                .sort((a, b) => a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' }));
        },

        get locatorAxes() {
            return [...new Set((this.importedUnits || []).map(u => String(u.unit_axis || '').trim()).filter(Boolean))]
                .sort((a, b) => a.localeCompare(b, undefined, { numeric: true, sensitivity: 'base' }));
        },

        get filteredLocatorUnits() {
            const search = (this.locatorUnitSearch || '').trim().toLowerCase();
            return (this.importedUnits || []).map((unit, originalIndex) => ({ ...unit, originalIndex })).filter(unit => {
                if (search && ![unit.unit_code, unit.unit_type, unit.block, unit.floor, unit.unit_axis].some(value => String(value || '').toLowerCase().includes(search))) return false;
                if (this.locatorBlockFilter && String(unit.block || '') !== this.locatorBlockFilter) return false;
                if (this.locatorFloorFilter && String(unit.floor || '') !== this.locatorFloorFilter) return false;
                if (this.locatorAxisFilter && String(unit.unit_axis || '') !== this.locatorAxisFilter) return false;
                if (this.locatorTypeFilter) {
                    const bedrooms = unit.bedroom === null || unit.bedroom === undefined || unit.bedroom === '' ? NaN : Number(unit.bedroom);
                    const type = String(unit.unit_type || '').toLowerCase();
                    if (this.locatorTypeFilter === 'studio') {
                        if (!(bedrooms === 0 || type.includes('studio'))) return false;
                    } else {
                        const count = Number(this.locatorTypeFilter);
                        const typeMatch = new RegExp('(?:^|\\D)' + count + '\\s*(?:pn|n)', 'i').test(type);
                        if (!(Number.isFinite(bedrooms) && bedrooms === count) && !typeMatch) return false;
                    }
                }
                return true;
            });
        },

        get allFilteredLocatorUnitsSelected() {
            return this.filteredLocatorUnits.length > 0 && this.filteredLocatorUnits.every(unit => this.locatorSelectedIds.includes(Number(unit.id)));
        },

        toggleLocatorUnit(id) {
            id = Number(id);
            this.locatorSelectedIds = this.locatorSelectedIds.includes(id)
                ? this.locatorSelectedIds.filter(selectedId => selectedId !== id)
                : [...this.locatorSelectedIds, id];
        },

        toggleAllLocatorUnits() {
            const visibleIds = this.filteredLocatorUnits.map(unit => Number(unit.id));
            this.locatorSelectedIds = this.allFilteredLocatorUnitsSelected
                ? this.locatorSelectedIds.filter(id => !visibleIds.includes(id))
                : [...new Set([...this.locatorSelectedIds, ...visibleIds])];
        },

        selectLocatorUnit(index) {
            this.selectUnitByIndex(index);
            const unit = this.importedUnits[index];
            if (unit && unit.unit_axis) this.selectedAxis = unit.unit_axis;
            this.renderCanvas();
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

            return this.importedUnits.map((u, idx) => ({ ...u, originalIndex: idx })).filter(u => {
                if (this.posterBlockFilter && String(u.block || '') !== this.posterBlockFilter) return false;
                if (this.posterFloorFilter && String(u.floor || '') !== this.posterFloorFilter) return false;
                if (this.posterAxisFilter && String(u.unit_axis || '') !== this.posterAxisFilter) return false;
                if (this.posterTypeFilter) {
                    const bedrooms = u.bedroom === null || u.bedroom === undefined || u.bedroom === '' ? NaN : Number(u.bedroom);
                    const type = String(u.unit_type || '').toLowerCase();
                    if (this.posterTypeFilter === 'studio') {
                        if (!(bedrooms === 0 || type.includes('studio'))) return false;
                    } else {
                        const count = Number(this.posterTypeFilter);
                        const typeMatch = new RegExp('(?:^|\\D)' + count + '\\s*(?:pn|n)', 'i').test(type);
                        if (!(Number.isFinite(bedrooms) && bedrooms === count) && !typeMatch) return false;
                    }
                }
                if (search) {
                    const code = (u.unit_code || '').toLowerCase();
                    const type = (u.unit_type || '').toLowerCase();
                    const block = (u.block || '').toLowerCase();
                    const floor = (u.floor || '').toLowerCase();
                    const axis = (u.unit_axis || '').toLowerCase();
                    if (!(code.includes(search) || type.includes(search) || block.includes(search) || floor.includes(search) || axis.includes(search))) return false;
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

                // Khởi tạo trục căn đang chọn
                const initialUnit = this.importedUnits[targetIdx];
                if (initialUnit && initialUnit.unit_axis) {
                    this.selectedAxis = initialUnit.unit_axis;
                } else if (this.allAxes && this.allAxes.length > 0) {
                    this.selectedAxis = this.allAxes[0].axis;
                }

                @if(request('axis'))
                this.mode = 'studio';
                this.selectedAxis = '{{ request('axis') }}';
                @endif

                if (this.selectedAxis && this.axisLocatorsMap && this.axisLocatorsMap[this.selectedAxis]) {
                    this.loadAxisLocator(this.selectedAxis);
                }
            }

            @if(request('import') === 'excel')
            this.openExcelModal();
            @endif

            this.$nextTick(() => {
                if (this.mode === 'poster') {
                    this.renderPosterCanvas();
                } else if (this.mode === 'studio') {
                    this.renderCanvas();
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

            // Ảnh phân khu/tiện ích chỉ là ảnh minh họa, không phải mặt bằng tầng để định vị căn.
            if (name.includes('phân khu') || name.includes('phan khu') || name.includes('tiện ích') || name.includes('tien ich') || key.includes('phan_khu') || key.includes('tien_ich') || id.includes('phan_khu') || id.includes('tien_ich')) {
                return false;
            }

            // Bỏ qua các ô bóc mái 3D hoặc layout căn hộ nội thất
            if (name.includes('bóc mái') || name.includes('boc mai') || name.includes('3d') || name.includes('căn hộ') || name.includes('can ho') || key === 'boc_mai' || key === 'can_ho' || key === 'layout_3d') {
                return false;
            }

            if (key === 'mat_bang_tang' || key === 'mat_bang' || key === 'floorplan' || key === 'locator' || key === 'chi_can') return true;
            if (name.includes('mặt bằng') || name.includes('mat bang') || name.includes('chỉ căn') || name.includes('chi can') || name.includes('sơ đồ') || name.includes('tầng')) return true;

            // Nếu template chỉ có 2 ô và ô này là ô kích thước lớn (ô chính hiển thị mặt bằng tầng / chỉ căn)
            const tmpl = this.currentTemplate;
            const allSlots = tmpl?.slots_config?.image_slots || [];
            if (allSlots.length <= 2 && (slot.width >= 500 || slot.height >= 500)) {
                return true;
            }

            return false;
        },

        isBocMaiSlot(slot) {
            if (!slot) return false;
            const name = (slot.name || '').toLowerCase();
            const key = (slot.key || '').toLowerCase();
            return key === 'boc_mai' || key === 'layout_3d' || name.includes('bóc mái') || name.includes('boc mai') || name.includes('3d');
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
            const blockRaw = String(unit?.block || '').trim().toLowerCase();

            let bestMatch = null;
            let bestScore = -1;

            for (const fp of this.allFloorplans) {
                const name = (fp.name || '').toLowerCase();
                const range = (fp.floor_range || '').toLowerCase();
                const desc = (fp.description || '').toLowerCase();
                const fpBlock = (fp.block || '').toLowerCase();
                const text = `${name} ${range} ${desc} ${fpBlock}`;
                let score = 0;

                // Khớp tòa / phân khu (block) nếu có
                if (blockRaw) {
                    const blockNumMatch = blockRaw.match(/\d+/);
                    if (text.includes(blockRaw)) {
                        score += 30;
                    } else if (blockNumMatch && (text.includes('sky ' + blockNumMatch[0]) || text.includes('s' + blockNumMatch[0]))) {
                        score += 30;
                    }
                }

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
                            score += 60;
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
                const isBocMai = this.isBocMaiSlot(slot);
                const bocMaiUrl = u.layout_3d_image_url || u.layout_3d_image;

                // Ưu tiên ảnh định vị đã lưu trực tiếp trên căn để dùng lại khi ghép poster.
                const savedLocatorImage = u.floorplan_image_url || u.floorplan_image;
                if (isFp && savedLocatorImage) {
                    resolved[slotId] = savedLocatorImage;
                    continue;
                }

                // Khi chỉnh sửa poster đã lưu, giữ nguyên ảnh người dùng đã gán vào từng ô.
                // Các nhánh tự động bên dưới chỉ nên dùng làm fallback cho poster chưa có slotImages.
                if (savedSlots[slotId]) {
                    resolved[slotId] = savedSlots[slotId];
                    continue;
                }

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
                    // Ưu tiên 4: Dùng slot đã lưu nếu có (nếu là ảnh mặt bằng)
                    else if (savedSlots[slotId] && (savedSlots[slotId].includes('floorplan') || savedSlots[slotId].includes('fp_'))) {
                        resolved[slotId] = savedSlots[slotId];
                    }
                    // Ưu tiên 5: Dùng ảnh mặt bằng đầu tiên trong dự án
                    else if (this.allFloorplans && this.allFloorplans.length > 0) {
                        resolved[slotId] = this.allFloorplans[0].image_url;
                    }
                    else if (savedSlots[slotId]) {
                        resolved[slotId] = savedSlots[slotId];
                    }
                } else if (isBocMai) {
                    // Ô bóc mái 3D:
                    // Ưu tiên 1: Đã gán tay trong phiên làm việc
                    if (u._manuallyAssignedSlots && u._manuallyAssignedSlots[slotId] && savedSlots[slotId]) {
                        resolved[slotId] = savedSlots[slotId];
                    }
                    // Ưu tiên 2: Căn đã có ảnh bóc mái riêng
                    else if (bocMaiUrl) {
                        resolved[slotId] = bocMaiUrl;
                    }
                    // Ưu tiên 3: Đồng bộ ảnh bóc mái từ các căn khác cùng trục căn (unit_axis)
                    else {
                        const axis = u.unit_axis;
                        let axisBocMai = null;
                        if (axis && this.importedUnits) {
                            const sibling = this.importedUnits.find(other => 
                                other.id !== u.id && other.unit_axis === axis && (other.layout_3d_image_url || other.layout_3d_image)
                            );
                            if (sibling) {
                                axisBocMai = sibling.layout_3d_image_url || sibling.layout_3d_image;
                            }
                        }
                        if (axisBocMai) {
                            resolved[slotId] = axisBocMai;
                        }
                        // Ưu tiên 4: Slot đã lưu nếu là ảnh bóc mái thực sự
                        else if (savedSlots[slotId] && (savedSlots[slotId].includes('layout_3d') || savedSlots[slotId].includes('boc_mai'))) {
                            resolved[slotId] = savedSlots[slotId];
                        }
                        // Không gán ảnh phối cảnh toàn khu vào ô bóc mái 3D căn hộ
                        else {
                            resolved[slotId] = null;
                        }
                    }
                } else {
                    // Các ô khác: phối cảnh, tiện ích...
                    if (u._manuallyAssignedSlots && u._manuallyAssignedSlots[slotId] && savedSlots[slotId]) {
                        resolved[slotId] = savedSlots[slotId];
                    } else if (savedSlots[slotId]) {
                        resolved[slotId] = savedSlots[slotId];
                    } else {
                        // TỰ ĐỘNG NẠP ẢNH TỪ CĂN GỐC NẾU CÓ TRONG COMPOSER DATA
                        const comp = (typeof u.composer_data === 'string') ? JSON.parse(u.composer_data || '{}') : (u.composer_data || {});
                        if (comp.slotImages && comp.slotImages[slotId]) {
                            resolved[slotId] = comp.slotImages[slotId];
                        } else if (this.sharedSlotImages && this.sharedSlotImages[slotId]) {
                            resolved[slotId] = this.sharedSlotImages[slotId];
                        } else if (this.slotImages[slotId]) {
                            resolved[slotId] = this.slotImages[slotId];
                        }
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

        normalizeMediaUrl(url) {
            if (!url) return '';
            const adminIdx = window.location.pathname.indexOf('/admin/');
            const basePath = adminIdx !== -1 ? window.location.pathname.substring(0, adminIdx) : '';
            const mediaBase = window.location.origin + basePath + '/unit-locators/media/';

            const mediaPattern = '/unit-locators/media/';
            const idx = url.indexOf(mediaPattern);
            if (idx !== -1) {
                const subPath = url.substring(idx + mediaPattern.length);
                return mediaBase + subPath;
            }

            // Nếu url là đường dẫn tương đối trong storage (vd: floorplans/826/..., perspectives/826/..., layout_3d/826/...)
            if (!url.startsWith('http://') && !url.startsWith('https://') && !url.startsWith('data:') && !url.startsWith('blob:')) {
                const clean = url.replace(/^storage\//, '').replace(/^\/+/, '');
                return mediaBase + clean;
            }

            return url;
        },

        loadImageAsync(url) {
            if (!url) return Promise.resolve(null);
            url = this.normalizeMediaUrl(url);
            if (this.imageCache[url]) return Promise.resolve(this.imageCache[url]);

            return new Promise((resolve) => {
                const img = new Image();
                // Chỉ set crossOrigin nếu khác origin để tránh browser CORS cache collision với ảnh thông thường
                if (url.startsWith('http') && !url.startsWith(window.location.origin)) {
                    img.crossOrigin = 'anonymous';
                }
                img.onload = () => {
                    this.imageCache[url] = img;
                    resolve(img);
                };
                img.onerror = () => {
                    if (img.crossOrigin) {
                        const retryImg = new Image();
                        retryImg.onload = () => {
                            this.imageCache[url] = retryImg;
                            resolve(retryImg);
                        };
                        retryImg.onerror = () => resolve(null);
                        retryImg.src = url;
                    } else {
                        resolve(null);
                    }
                };
                img.src = url;
            });
        },

        async renderPosterCanvas() {
            const currentRenderId = ++this.posterRenderId;
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
                if (this.posterRenderId !== currentRenderId) return;
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
                if (this.posterRenderId !== currentRenderId) return;
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
                let imgDrawn = false;
                if (imgUrl) {
                    const slotImg = await this.loadImageAsync(imgUrl);
                    if (this.posterRenderId !== currentRenderId) return;
                    if (slotImg) {
                        // Tính tỷ lệ Cover (Fit fill)
                        const imgRatio = slotImg.naturalWidth / slotImg.naturalHeight;
                        const slotRatio = w / h;
                        let dw, dh, dx, dy;

                        // Nhận diện theo key/tên ô vì id thường chỉ là slot_1, slot_2...
                        const fitFullImage = this.isBocMaiSlot(slot) || this.isFloorplanSlot(slot);

                        if (fitFullImage) {
                            // Ảnh bóc mái và mặt bằng: giữ tỷ lệ, hiển thị trọn ảnh, không tô phần dư
                            if (imgRatio > slotRatio) {
                                dw = w;
                                dh = w / imgRatio;
                                dx = x;
                                dy = y + (h - dh) / 2;
                            } else {
                                dh = h;
                                dw = h * imgRatio;
                                dx = x + (w - dw) / 2;
                                dy = y;
                            }
                        } else {
                            // Chế độ Cover cũ (cắt ảnh)
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
                        }
                        ctx.drawImage(slotImg, dx, dy, dw, dh);
                        imgDrawn = true;

                        // Tự động vẽ đè khung định vị chỉ căn nếu là ô Mặt bằng tầng
                        if (this.isFloorplanSlot(slot)) {
                            this.drawLocatorOnPosterSlot(ctx, slot, slotImg, dx, dy, dw, dh);
                        }
                    }
                }

                if (!imgDrawn) {
                    // Placeholder nếu ô chưa có ảnh hoặc ảnh tải thất bại
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

            if (this.posterRenderId !== currentRenderId) return;

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
                const typePos = { ...tSlots.unit_type, isBadge: false };
                drawTextItem(typePos, this.posterForm.unit_type || '2PN');
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

        buildPosterFormForUnit(unit) {
            return {
                unit_code: unit.unit_code || '',
                unit_type: unit.unit_type || '',
                direction: unit.direction || '',
                view: unit.view || '',
                area: unit.area !== null && unit.area !== undefined ? String(unit.area) : '',
                price_text: unit.price_text || (unit.price ? this.formatPriceVND(unit.price) : ''),
                price_note: unit.price_note || '(Chưa VAT)',
                block: unit.block || '',
                floor: unit.floor || '',
                unit_axis: unit.unit_axis || ''
            };
        },

        buildPosterConfigPayload(unit, formData, slotImages) {
            return {
                unit_id: unit.id,
                unit_code: formData.unit_code,
                unit_type: formData.unit_type,
                direction: formData.direction,
                view: formData.view,
                area: formData.area,
                price_text: formData.price_text,
                price_note: formData.price_note,
                block: formData.block,
                floor: formData.floor,
                composer_data: JSON.stringify({
                    template_id: this.selectedTemplateId,
                    slotImages: { ...slotImages },
                    posterForm: { ...formData }
                })
            };
        },

        async savePosterUnit(createNext = false) {
            if (!this.posterForm.unit_code) {
                Swal.fire('Thiếu thông tin', 'Vui lòng nhập Mã căn!', 'warning');
                return;
            }

            const curUnit = this.importedUnits?.[this.currentUnitIndex];
            if (!curUnit || !curUnit.id) {
                Swal.fire('Chưa có căn trong hệ thống', 'Hãy nhập/lưu danh sách căn trước khi lưu cấu hình poster.', 'warning');
                return;
            }

            this.isSavingPoster = true;
            const formSnapshot = { ...this.posterForm };
            const payload = this.buildPosterConfigPayload(curUnit, formSnapshot, this.slotImages);

            try {
                const response = await fetch('{{ route('admin.unit-locators.save-poster-config', $project->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();
                this.isSavingPoster = false;
                if (!response.ok || !data.success) throw new Error(data.message || 'Không thể lưu cấu hình poster.');

                curUnit.has_image = true;
                curUnit.composer_data = payload.composer_data;
                curUnit._slotImages = { ...this.slotImages };

                Swal.fire({
                    icon: 'success',
                    title: 'Đã lưu cấu hình poster!',
                    text: `Ảnh căn ${formSnapshot.unit_code} sẽ được dựng khi khách tải ảnh.`,
                    showCancelButton: createNext,
                    confirmButtonText: createNext ? 'Tiếp tục căn sau' : 'Đóng',
                    cancelButtonText: 'Ở lại chỉnh sửa'
                }).then(result => {
                    if (createNext && result.isConfirmed) {
                        if (this.currentUnitIndex < this.importedUnits.length - 1) {
                            this.selectUnitByIndex(this.currentUnitIndex + 1);
                        }
                    }
                });
            } catch (error) {
                this.isSavingPoster = false;
                Swal.fire('Lỗi', error.message, 'error');
            }
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
            this.posterForm.unit_axis = u.unit_axis || '';
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
            this.slotImages = {};
            const resolved = this.resolveUnitSlotImages(u);
            this.slotImages = { ...resolved };

            // Đồng bộ thông tin sang form Studio Chấm Ghim
            this.form.unit_code = u.unit_code || '';
            this.form.unit_axis = u.unit_axis || '';
            this.form.area = u.area !== null && u.area !== undefined ? String(u.area) : '';
            this.form.direction = u.direction || '';
            this.form.block = u.block || '';
            this.form.floor = u.floor || '';
            this.form.price_text = u.price_text || (u.price ? this.formatPriceVND(u.price) : '');
            if (u.bedroom) this.form.bedroom = u.bedroom;

            if (u.unit_axis) {
                this.selectedAxis = u.unit_axis;
            }

            this.inheritedFromAxis = false;
            let hl = null;
            if (u.highlight_data) {
                try {
                    hl = (typeof u.highlight_data === 'string') ? JSON.parse(u.highlight_data) : u.highlight_data;
                } catch(e) {}
            }
            // Nếu căn chưa có highlight_data nhưng trục căn có highlight_data -> tự động kế thừa!
            if ((!hl || (!hl.locatorBox && !hl.locator_box && !hl.marker)) && u.unit_axis && this.axisLocatorsMap && this.axisLocatorsMap[u.unit_axis]) {
                const axData = this.axisLocatorsMap[u.unit_axis];
                if (axData && axData.highlight_data) {
                    try {
                        hl = (typeof axData.highlight_data === 'string') ? JSON.parse(axData.highlight_data) : axData.highlight_data;
                        this.inheritedFromAxis = u.unit_axis;
                    } catch(e) {}
                }
            }

            if (hl) {
                if (hl.marker) this.marker = { ...hl.marker };
                if (hl.locatorStyle) this.locatorStyle = hl.locatorStyle;
                if (hl.locatorBox || hl.locator_box) {
                    const lb = hl.locatorBox || hl.locator_box;
                    this.locatorBox = {
                        x: lb.x || 0,
                        y: lb.y || 0,
                        w: lb.w || lb.width || 120,
                        h: lb.h || lb.height || 180
                    };
                }
                if (hl.hasArrow !== undefined) this.hasArrow = hl.hasArrow;
                if (hl.arrowDir) this.arrowDir = hl.arrowDir;
                if (hl.arrowStart) this.arrowStart = { ...hl.arrowStart };
                if (hl.arrowHead) this.arrowHead = { ...hl.arrowHead };
                if (hl.boxBorderWidth) this.boxBorderWidth = hl.boxBorderWidth;
                if (hl.boxBorderRadius !== undefined) this.boxBorderRadius = hl.boxBorderRadius;
                if (hl.boxHasFill !== undefined) this.boxHasFill = hl.boxHasFill;
                if (hl.boxHasLabel !== undefined) this.boxHasLabel = hl.boxHasLabel;
                if (hl.pinStyle) this.pinStyle = hl.pinStyle;
                if (hl.pinColor) this.pinColor = hl.pinColor;
                if (hl.pinSize) this.pinSize = hl.pinSize;
                // Thẻ thông tin nổi đã được bỏ khỏi ảnh định vị; không khôi phục vị trí cũ đã lưu.
                this.cardPosition = 'none';
                if (hl.cardScalePercent) this.cardScalePercent = hl.cardScalePercent;
                if (hl.cardCustomX !== undefined) this.cardCustomX = hl.cardCustomX;
                if (hl.cardCustomY !== undefined) this.cardCustomY = hl.cardCustomY;
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
            this.posterForm.unit_axis = u.unit_axis || '';
            this.posterForm.area = u.area !== null && u.area !== undefined ? String(u.area) : '';
            this.posterForm.direction = u.direction || '';
            this.posterForm.view = u.view || '';
            this.posterForm.price_text = u.price_text || (u.price ? this.formatPriceVND(u.price) : '');
            this.posterForm.price_note = u.price_note || '(Chưa VAT)';

            // Đồng bộ slotImages chuẩn của căn này (khớp mặt bằng tầng / ảnh chỉ căn)
            this.slotImages = {};
            const resolved = this.resolveUnitSlotImages(u);
            this.slotImages = { ...resolved };

            this.debouncedRender();

            this.$nextTick(() => {
                const el = document.getElementById('unit-item-' + this.currentUnitIndex);
                if (el) {
                    el.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                }
            });
        },

        async saveUnitPosterConfig(unit) {
            if (!unit?.id) throw new Error(`Căn ${unit?.unit_code || ''} chưa được lưu trong hệ thống.`);
            const formData = this.buildPosterFormForUnit(unit);
            const slotImages = this.resolveUnitSlotImages(unit);
            const payload = this.buildPosterConfigPayload(unit, formData, slotImages);
            const response = await fetch('{{ route('admin.unit-locators.save-poster-config', $project->id) }}', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (!response.ok || !result.success) throw new Error(result.message || `Không lưu được căn ${unit.unit_code}.`);
            unit.has_image = true;
            unit.composer_data = payload.composer_data;
            unit._slotImages = { ...slotImages };
            return result;
        },

        async saveUnitConfigsBatch(units, title) {
            const targets = (units || []).filter(Boolean);
            if (!targets.length) return;
            const confirm = await Swal.fire({
                title: `${title} ${targets.length} căn?`,
                html: `Hệ thống sẽ lưu cấu hình phôi và ảnh đã gán cho <b>${targets.length} căn</b>. Ảnh poster chỉ được dựng khi khách tải về.`,
                icon: 'question', showCancelButton: true,
                confirmButtonText: `Lưu cấu hình`, cancelButtonText: 'Hủy'
            });
            if (!confirm.isConfirmed) return;

            this.isBatchGenerating = true;
            this.batchModalOpen = true;
            this.batchProgress = { current: 0, total: targets.length, percent: 0, currentCode: '', successCount: 0, failCount: 0, isDone: false };
            for (let i = 0; i < targets.length; i++) {
                const unit = targets[i];
                this.batchProgress.current = i + 1;
                this.batchProgress.currentCode = unit.unit_code || '';
                this.batchProgress.percent = Math.round(((i + 1) / targets.length) * 100);
                try {
                    await this.saveUnitPosterConfig(unit);
                    this.batchProgress.successCount++;
                } catch (error) {
                    this.batchProgress.failCount++;
                    console.error('Không lưu được cấu hình poster:', unit.unit_code, error);
                }
            }
            this.batchProgress.isDone = true;
            this.isBatchGenerating = false;
            this.loadUnitPreview(this.currentUnitIndex);
        },

        async batchSaveSelectedUnits() {
            const units = (this.selectedIndices || []).map(index => this.importedUnits[index]);
            return this.saveUnitConfigsBatch(units, 'Lưu cấu hình cho');
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
                        if (norm.includes('trụccăn') || norm === 'trục' || norm.includes('axis')) {
                            colMap.unit_axis = idx;
                        } else if (norm.includes('cănsố') || norm.includes('sốcăn') || norm === 'căn') {
                            colMap.unit_number = idx;
                        } else if (norm.includes('trụctòa') || norm.includes('tòa') || norm.includes('tháp') || norm.includes('block')) {
                            colMap.block = idx;
                        } else if (norm.includes('tầng') || norm.includes('floor')) {
                            colMap.floor = idx;
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
                        let unitAxis = colMap.unit_axis !== undefined ? String(row[colMap.unit_axis] || '').trim() : '';

                        if (!unitAxis && unitNumber) {
                            unitAxis = unitNumber;
                        }

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
                            unit_number: unitNumber,
                            unit_axis: unitAxis,
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

        downloadSampleExcel() {
            if (typeof XLSX === 'undefined') {
                Swal.fire('Lỗi', 'Thư viện SheetJS chưa sẵn sàng!', 'error');
                return;
            }
            const sampleData = [
                ['Tòa', 'Tầng', 'Căn số', 'Mã Căn', 'Loại căn', 'Diện tích', 'Giá (chưa VAT)', 'Hướng', 'View', 'Trục căn'],
                ['C1.3', '28', '4', 'C1.3-28-04', '1PN+ 1WC', 42, 1725000000, 'Đông', 'Tổ hợp tiện ích Victory Central', '04'],
                ['C1.2', '23', '15', 'C1.2-23-15', '2PN, 2WC', 60.9, 2445000000, 'Nam', 'Tổ hợp tiện ích Victory Central', '15'],
                ['C1.2', '19', '4', 'C1.2-19-04', '2PN, 2WC', 62.3, 2130000000, 'Bắc', 'Phân khu Glory Sky', '04'],
                ['C1.3', '24', '3', 'C1.3-24-03', '1PN+ 1WC', 42, 1716000000, 'Đông', 'Tổ hợp tiện ích Victory Central', '03'],
                ['C1.3', '27', '9', 'C1.3-27-09', '1PN+ 1WC', 47.1, 1710000000, 'Tây', 'Nội khu', '09'],
                ['C2.3', '25', '12', 'C2.3-25-12', '1PN+ 1WC', 47.1, 1641000000, 'Tây', 'Nội khu', '12']
            ];
            const ws = XLSX.utils.aoa_to_sheet(sampleData);
            const wb = XLSX.utils.book_new();
            XLSX.utils.book_append_sheet(wb, ws, 'Danh sách căn');
            XLSX.writeFile(wb, 'Mau_nhap_can_ho_{{ Str::slug($project->name) }}.xlsx');
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
            return this.saveUnitConfigsBatch(this.importedUnits, 'Lưu cấu hình cho');
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
            url = this.normalizeMediaUrl(url);
            this.currentFloorplanUrl = url;
            const fp = (this.allFloorplans || []).find(f => f.image_url === url || this.normalizeMediaUrl(f.image_url) === url);
            if (fp && fp.image) {
                this.currentFloorplanPath = fp.image;
            } else {
                const match = url.match(/(floorplans\/.*$)/) || url.match(/(storage\/.*$)/);
                if (match) this.currentFloorplanPath = match[1].replace('storage/', '');
            }

            const img = new Image();
            if (url.startsWith('http') && !url.startsWith(window.location.origin)) {
                img.crossOrigin = 'anonymous';
            }
            img.onload = () => {
                this.imgObj = img;
                this.hasImage = true;
                if (!this.marker || (this.marker.x === 0 && this.marker.y === 0)) {
                    this.marker = { x: img.naturalWidth / 2, y: img.naturalHeight / 2 };
                }
                if (!this.locatorBox || this.locatorBox.w === 0 || this.locatorBox.w === 120) {
                    this.initDefaultBox();
                }
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
                    if (!this.locatorBox || this.locatorBox.w === 0 || this.locatorBox.w === 120) {
                        this.initDefaultBox();
                    }
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

        isPointInBox(x, y) {
            const b = this.locatorBox;
            if (!b || b.w <= 0 || b.h <= 0) return false;
            return x >= b.x && x <= (b.x + b.w) && y >= b.y && y <= (b.y + b.h);
        },

        getBoxResizeCorner(x, y) {
            const b = this.locatorBox;
            if (!b || b.w <= 0 || b.h <= 0) return null;
            const r = 20; // Sai số bấm góc
            if (Math.hypot(x - b.x, y - b.y) <= r) return 'tl';
            if (Math.hypot(x - (b.x + b.w), y - b.y) <= r) return 'tr';
            if (Math.hypot(x - b.x, y - (b.y + b.h)) <= r) return 'bl';
            if (Math.hypot(x - (b.x + b.w), y - (b.y + b.h)) <= r) return 'br';
            return null;
        },

        isPointNearArrowTail(x, y) {
            if (!this.hasArrow || !this.arrowStart) return false;
            return Math.hypot(x - this.arrowStart.x, y - this.arrowStart.y) <= 24;
        },

        selectAxis(axis) {
            this.selectedAxis = axis;
            this.loadAxisLocator(axis);
            this.renderCanvas();
        },

        loadAxisLocator(axis) {
            if (!axis) return;
            const axData = this.axisLocatorsMap ? this.axisLocatorsMap[axis] : null;
            if (axData && axData.highlight_data) {
                let hl = axData.highlight_data;
                if (typeof hl === 'string') {
                    try { hl = JSON.parse(hl); } catch(e) {}
                }
                if (hl) {
                    if (hl.locatorStyle) this.locatorStyle = hl.locatorStyle;
                    if (hl.locatorBox || hl.locator_box) {
                        const lb = hl.locatorBox || hl.locator_box;
                        this.locatorBox = {
                            x: lb.x || 0,
                            y: lb.y || 0,
                            w: lb.w || lb.width || 120,
                            h: lb.h || lb.height || 180
                        };
                    }
                    if (hl.hasArrow !== undefined) this.hasArrow = hl.hasArrow;
                    if (hl.arrowDir) this.arrowDir = hl.arrowDir;
                    if (hl.arrowStart) this.arrowStart = { ...hl.arrowStart };
                    if (hl.arrowHead) this.arrowHead = { ...hl.arrowHead };
                    if (hl.boxBorderWidth) this.boxBorderWidth = hl.boxBorderWidth;
                    if (hl.boxBorderRadius !== undefined) this.boxBorderRadius = hl.boxBorderRadius;
                    if (hl.boxHasFill !== undefined) this.boxHasFill = hl.boxHasFill;
                    if (hl.boxHasLabel !== undefined) this.boxHasLabel = hl.boxHasLabel;
                    if (hl.pinColor) this.pinColor = hl.pinColor;
                }
                if (axData.base_image && this.allFloorplans) {
                    const matched = this.allFloorplans.find(f => f.image === axData.base_image || f.image_url?.includes(axData.base_image));
                    if (matched && matched.image_url && this.currentFloorplanUrl !== this.normalizeMediaUrl(matched.image_url)) {
                        this.loadSavedFloorplan(matched.image_url);
                        return;
                    }
                }
            } else {
                if (this.imgObj) {
                    this.initDefaultBox();
                }
            }
        },

        initDefaultBox() {
            if (!this.imgObj) return;
            const w = Math.round(this.imgObj.naturalWidth * 0.08) || 120;
            const h = Math.round(w * 1.5) || 180;
            const x = Math.round((this.imgObj.naturalWidth - w) / 2);
            const y = Math.round((this.imgObj.naturalHeight - h) / 2);
            this.locatorBox = { x, y, w, h };
            this.setArrowDirection(this.arrowDir || 'bottom-left');
        },

        setArrowDirection(dir) {
            this.arrowDir = dir;
            const b = this.locatorBox;
            if (!b) return;
            if (dir === 'bottom-left') {
                this.arrowHead = { x: Math.round(b.x), y: Math.round(b.y + b.h * 0.8) };
                this.arrowStart = { x: Math.round(b.x - b.w * 0.9), y: Math.round(b.y + b.h * 1.35) };
            } else if (dir === 'bottom-right') {
                this.arrowHead = { x: Math.round(b.x + b.w), y: Math.round(b.y + b.h * 0.8) };
                this.arrowStart = { x: Math.round(b.x + b.w * 1.9), y: Math.round(b.y + b.h * 1.35) };
            } else if (dir === 'top-left') {
                this.arrowHead = { x: Math.round(b.x), y: Math.round(b.y + b.h * 0.2) };
                this.arrowStart = { x: Math.round(b.x - b.w * 0.9), y: Math.round(b.y - b.h * 0.4) };
            } else if (dir === 'top-right') {
                this.arrowHead = { x: Math.round(b.x + b.w), y: Math.round(b.y + b.h * 0.2) };
                this.arrowStart = { x: Math.round(b.x + b.w * 1.9), y: Math.round(b.y - b.h * 0.4) };
            }
            this.renderCanvas();
        },

        scaleLocatorBox(factor) {
            const b = this.locatorBox;
            if (!b) return;
            const cx = b.x + b.w / 2;
            const cy = b.y + b.h / 2;
            b.w = Math.max(30, Math.round(b.w * factor));
            b.h = Math.max(30, Math.round(b.h * factor));
            b.x = Math.round(cx - b.w / 2);
            b.y = Math.round(cy - b.h / 2);
            this.setArrowDirection(this.arrowDir);
            this.renderCanvas();
        },

        centerLocatorBox() {
            if (!this.imgObj) return;
            const b = this.locatorBox;
            b.x = Math.round((this.imgObj.naturalWidth - b.w) / 2);
            b.y = Math.round((this.imgObj.naturalHeight - b.h) / 2);
            this.setArrowDirection(this.arrowDir);
            this.renderCanvas();
        },

        startDrawNewBox() {
            this.isDrawingBox = true;
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: 'Hãy bấm và kéo chuột trên mặt bằng để vẽ khung mới!',
                timer: 3000,
                showConfirmButton: false
            });
        },

        goToStudioWithCurrentUnit() {
            this.mode = 'studio';
            const u = this.importedUnits[this.currentUnitIndex];
            if (u && u.unit_axis) this.selectedAxis = u.unit_axis;
            const matchedFp = this.findMatchingFloorplan(u);
            if (matchedFp && matchedFp.image_url) {
                this.loadSavedFloorplan(matchedFp.image_url);
            } else {
                this.$nextTick(() => this.renderCanvas());
            }
        },

        handleCanvasMouseDown(event) {
            if (!this.imgObj) return;
            const { x, y } = this.getCanvasCoordinates(event);
            const canvas = document.getElementById('studioCanvas');

            // 0. NẾU Ở KIỂU ĐỊNH VỊ KHUNG CHỮ NHẬT / MŨI TÊN (BOX / BOX_ARROW)
            if (this.locatorStyle === 'box' || this.locatorStyle === 'box_arrow') {
                if (this.isDrawingBox) {
                    this.drawStart = { x, y };
                    this.locatorBox = { x: Math.round(x), y: Math.round(y), w: 10, h: 10 };
                    return;
                }

                // Kiểm tra bấm vào đuôi mũi tên để kéo/xoay
                if (this.hasArrow && this.isPointNearArrowTail(x, y)) {
                    this.isDraggingArrowTail = true;
                    if (canvas) canvas.style.cursor = 'crosshair';
                    return;
                }

                // Kiểm tra bấm vào 4 góc để co giãn (Resize)
                const corner = this.getBoxResizeCorner(x, y);
                if (corner) {
                    this.isResizingBox = true;
                    this.activeResizeCorner = corner;
                    this.resizeBoxStart = { x, y, box: { ...this.locatorBox } };
                    if (canvas) canvas.style.cursor = (corner === 'tl' || corner === 'br') ? 'nwse-resize' : 'nesw-resize';
                    return;
                }

                // Kiểm tra bấm vào thân khung để kéo di chuyển (Move)
                if (this.isPointInBox(x, y)) {
                    this.isDraggingBox = true;
                    this.dragBoxStart = {
                        x: x,
                        y: y,
                        boxX: this.locatorBox.x,
                        boxY: this.locatorBox.y,
                        arrowStartX: this.arrowStart ? this.arrowStart.x : 0,
                        arrowStartY: this.arrowStart ? this.arrowStart.y : 0,
                        arrowHeadX: this.arrowHead ? this.arrowHead.x : 0,
                        arrowHeadY: this.arrowHead ? this.arrowHead.y : 0
                    };
                    if (canvas) canvas.style.cursor = 'move';
                    return;
                }

                // Bấm ra ngoài -> tự động kích hoạt chế độ kéo vẽ khung mới
                this.isDrawingBox = true;
                this.drawStart = { x, y };
                this.locatorBox = { x: Math.round(x), y: Math.round(y), w: 10, h: 10 };
                return;
            }

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

            // Xử lý kéo vẽ hoặc thao tác Bounding Box & Mũi tên
            if (this.locatorStyle === 'box' || this.locatorStyle === 'box_arrow') {
                if (this.isDrawingBox) {
                    const left = Math.min(this.drawStart.x, x);
                    const top = Math.min(this.drawStart.y, y);
                    const w = Math.abs(x - this.drawStart.x);
                    const h = Math.abs(y - this.drawStart.y);
                    this.locatorBox = {
                        x: Math.round(left),
                        y: Math.round(top),
                        w: Math.max(20, Math.round(w)),
                        h: Math.max(20, Math.round(h))
                    };
                    this.setArrowDirection(this.arrowDir || 'bottom-left');
                    this.renderCanvas();
                    return;
                }

                if (this.isDraggingArrowTail) {
                    this.arrowStart = { x: Math.round(x), y: Math.round(y) };
                    this.renderCanvas();
                    return;
                }

                if (this.isResizingBox) {
                    const start = this.resizeBoxStart;
                    const dx = x - start.x;
                    const dy = y - start.y;
                    let newBox = { ...start.box };
                    if (this.activeResizeCorner === 'br') {
                        newBox.w = Math.max(20, start.box.w + dx);
                        newBox.h = Math.max(20, start.box.h + dy);
                    } else if (this.activeResizeCorner === 'tr') {
                        newBox.w = Math.max(20, start.box.w + dx);
                        const newH = start.box.h - dy;
                        if (newH >= 20) {
                            newBox.y = start.box.y + dy;
                            newBox.h = newH;
                        }
                    } else if (this.activeResizeCorner === 'bl') {
                        const newW = start.box.w - dx;
                        if (newW >= 20) {
                            newBox.x = start.box.x + dx;
                            newBox.w = newW;
                        }
                        newBox.h = Math.max(20, start.box.h + dy);
                    } else if (this.activeResizeCorner === 'tl') {
                        const newW = start.box.w - dx;
                        const newH = start.box.h - dy;
                        if (newW >= 20) {
                            newBox.x = start.box.x + dx;
                            newBox.w = newW;
                        }
                        if (newH >= 20) {
                            newBox.y = start.box.y + dy;
                            newBox.h = newH;
                        }
                    }
                    this.locatorBox = {
                        x: Math.round(newBox.x),
                        y: Math.round(newBox.y),
                        w: Math.round(newBox.w),
                        h: Math.round(newBox.h)
                    };
                    this.setArrowDirection(this.arrowDir || 'bottom-left');
                    this.renderCanvas();
                    return;
                }

                if (this.isDraggingBox) {
                    const dx = x - this.dragBoxStart.x;
                    const dy = y - this.dragBoxStart.y;
                    this.locatorBox.x = Math.round(this.dragBoxStart.boxX + dx);
                    this.locatorBox.y = Math.round(this.dragBoxStart.boxY + dy);
                    if (this.arrowStart) {
                        this.arrowStart.x = Math.round(this.dragBoxStart.arrowStartX + dx);
                        this.arrowStart.y = Math.round(this.dragBoxStart.arrowStartY + dy);
                    }
                    if (this.arrowHead) {
                        this.arrowHead.x = Math.round(this.dragBoxStart.arrowHeadX + dx);
                        this.arrowHead.y = Math.round(this.dragBoxStart.arrowHeadY + dy);
                    }
                    this.renderCanvas();
                    return;
                }

                // Cập nhật con trỏ chuột hover
                if (this.hasArrow && this.isPointNearArrowTail(x, y)) {
                    canvas.style.cursor = 'crosshair';
                } else {
                    const corner = this.getBoxResizeCorner(x, y);
                    if (corner) {
                        canvas.style.cursor = (corner === 'tl' || corner === 'br') ? 'nwse-resize' : 'nesw-resize';
                    } else if (this.isPointInBox(x, y)) {
                        canvas.style.cursor = 'move';
                    } else {
                        canvas.style.cursor = 'crosshair';
                    }
                }
                return;
            }

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
            if (this.locatorStyle === 'box' || this.locatorStyle === 'box_arrow') {
                this.isDrawingBox = false;
                this.isDraggingBox = false;
                this.isResizingBox = false;
                this.isDraggingArrowTail = false;
                this.activeResizeCorner = null;
                this.renderCanvas();
                return;
            }

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
            if (this.locatorStyle === 'box' || this.locatorStyle === 'box_arrow') return;
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
            if (event.touches.length === 1 && (this.isDraggingCard || this.isResizingCard || this.isDraggingBox || this.isResizingBox || this.isDrawingBox || this.isDraggingArrowTail)) {
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
            this.centerLocatorBox();
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

            // 2. Vẽ Định Vị Chỉ Căn (Khung Bounding Box viền đỏ + Mũi tên hoặc Marker)
            if (this.locatorStyle === 'box' || this.locatorStyle === 'box_arrow') {
                const labelText = this.form.unit_code || (this.selectedAxis ? ('Trục ' + this.selectedAxis) : '');
                this.drawBoundingBox(ctx, this.locatorBox, {
                    borderColor: this.pinColor || '#ef4444',
                    borderWidth: this.boxBorderWidth || 6,
                    borderRadius: this.boxBorderRadius !== undefined ? this.boxBorderRadius : 8,
                    hasFill: this.boxHasFill,
                    hasLabel: this.boxHasLabel,
                    labelText: labelText,
                    hasArrow: this.hasArrow,
                    arrowStart: this.arrowStart,
                    arrowHead: this.arrowHead,
                    showHandles: true
                });
            } else {
                this.drawMarker(ctx, this.marker.x, this.marker.y);
            }

            // 3. Vẽ Thẻ Thông Tin Căn Hộ (chỉ khi ở mode Từng Căn)
            if (this.cardPosition !== 'none') {
                this.drawInfoCard(ctx, w, h);
            }
        },

        drawBoundingBox(ctx, box, opts = {}) {
            if (!box || box.w <= 0 || box.h <= 0) return;
            const color = opts.borderColor || '#ef4444';
            const borderWidth = opts.borderWidth || 6;
            const borderRadius = opts.borderRadius !== undefined ? opts.borderRadius : 8;
            const hasFill = opts.hasFill !== false;
            const hasLabel = opts.hasLabel !== false;
            const labelText = opts.labelText || '';
            const hasArrow = opts.hasArrow !== false;
            const showHandles = !!opts.showHandles;

            ctx.save();

            // 1. Vẽ khung chữ nhật bo góc
            this.drawCustomRoundRect(ctx, box.x, box.y, box.w, box.h, borderRadius);

            // Nền highlight mờ bên trong
            if (hasFill) {
                ctx.fillStyle = this.hexToRgba(color, 0.16);
                ctx.fill();
            }

            // Nét viền đỏ nổi bật
            ctx.strokeStyle = color;
            ctx.lineWidth = borderWidth;
            ctx.lineJoin = 'round';
            ctx.stroke();

            // 2. Vẽ Mũi tên đỏ chỉ vào căn (như ảnh mẫu)
            if (false && hasArrow && (opts.arrowStart || this.arrowStart) && (opts.arrowHead || this.arrowHead)) {
                const aStart = opts.arrowStart || this.arrowStart;
                const aHead = opts.arrowHead || this.arrowHead;
                const aWidth = opts.arrowWidth || Math.max(4, Math.round(borderWidth * 0.8));
                this.drawArrow(ctx, aStart.x, aStart.y, aHead.x, aHead.y, color, aWidth);

                // Nếu trong chế độ chỉnh sửa Studio: vẽ tay nắm ở đuôi mũi tên
                if (showHandles) {
                    ctx.save();
                    ctx.beginPath();
                    ctx.arc(aStart.x, aStart.y, 10, 0, Math.PI * 2);
                    ctx.fillStyle = '#ffffff';
                    ctx.fill();
                    ctx.strokeStyle = color;
                    ctx.lineWidth = 2.5;
                    ctx.stroke();
                    ctx.beginPath();
                    ctx.arc(aStart.x, aStart.y, 3, 0, Math.PI * 2);
                    ctx.fillStyle = color;
                    ctx.fill();
                    ctx.restore();
                }
            }

            // 3. Vẽ nhãn mã / trục căn ở đỉnh khung nếu bật
            if (hasLabel && labelText) {
                const fSize = Math.max(12, Math.min(28, Math.round(box.w / 4)));
                ctx.font = `bold ${fSize}px sans-serif`;
                const tMetrics = ctx.measureText(labelText);
                const padX = Math.round(fSize * 0.5);
                const padY = Math.round(fSize * 0.25);
                const badgeW = tMetrics.width + padX * 2;
                const badgeH = fSize + padY * 2;
                const badgeX = box.x + (box.w - badgeW) / 2;
                const badgeY = Math.max(10, box.y - badgeH - (borderWidth / 2) - 4);

                ctx.shadowColor = 'rgba(0,0,0,0.35)';
                ctx.shadowBlur = 6;
                this.drawCustomRoundRect(ctx, badgeX, badgeY, badgeW, badgeH, 6);
                ctx.fillStyle = color;
                ctx.fill();
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = 1.5;
                ctx.stroke();

                ctx.shadowBlur = 0;
                ctx.fillStyle = '#ffffff';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(labelText, badgeX + badgeW / 2, badgeY + badgeH / 2);
            }

            // 4. Nếu trong chế độ chỉnh sửa Studio: vẽ 4 tay nắm góc (Resize Handles)
            if (showHandles) {
                const handleSize = Math.max(10, Math.min(16, Math.round(borderWidth * 2)));
                const half = handleSize / 2;
                const corners = [
                    { x: box.x, y: box.y },
                    { x: box.x + box.w, y: box.y },
                    { x: box.x, y: box.y + box.h },
                    { x: box.x + box.w, y: box.y + box.h }
                ];
                corners.forEach(c => {
                    ctx.fillStyle = '#ffffff';
                    ctx.fillRect(c.x - half, c.y - half, handleSize, handleSize);
                    ctx.strokeStyle = color;
                    ctx.lineWidth = 2;
                    ctx.strokeRect(c.x - half, c.y - half, handleSize, handleSize);
                });
            }

            ctx.restore();
        },

        drawArrow(ctx, fromX, fromY, toX, toY, color = '#ef4444', lineWidth = 5) {
            const angle = Math.atan2(toY - fromY, toX - fromX);
            const headLen = Math.max(18, lineWidth * 4); // Chiều dài mũi tên tam giác

            ctx.save();
            ctx.shadowColor = 'rgba(0,0,0,0.35)';
            ctx.shadowBlur = 6;

            // 1. Thân mũi tên (Shaft line)
            ctx.beginPath();
            ctx.moveTo(fromX, fromY);
            const shaftEndX = toX - (headLen * 0.6) * Math.cos(angle);
            const shaftEndY = toY - (headLen * 0.6) * Math.sin(angle);
            ctx.lineTo(shaftEndX, shaftEndY);
            ctx.strokeStyle = color;
            ctx.lineWidth = lineWidth;
            ctx.lineCap = 'round';
            ctx.stroke();

            // 2. Chóp mũi tên tam giác (Arrowhead)
            ctx.beginPath();
            ctx.moveTo(toX, toY);
            ctx.lineTo(
                toX - headLen * Math.cos(angle - Math.PI / 7),
                toY - headLen * Math.sin(angle - Math.PI / 7)
            );
            ctx.lineTo(
                toX - (headLen * 0.7) * Math.cos(angle),
                toY - (headLen * 0.7) * Math.sin(angle)
            );
            ctx.lineTo(
                toX - headLen * Math.cos(angle + Math.PI / 7),
                toY - headLen * Math.sin(angle + Math.PI / 7)
            );
            ctx.closePath();
            ctx.fillStyle = color;
            ctx.fill();
            ctx.strokeStyle = '#ffffff';
            ctx.lineWidth = 1.5;
            ctx.stroke();

            ctx.restore();
        },

        drawLocatorOnPosterSlot(ctx, slot, slotImg, dx, dy, dw, dh) {
            const imgUrl = this.slotImages[slot.id];
            if (imgUrl && imgUrl.includes('unit_canvas_')) return;

            const u = this.importedUnits[this.currentUnitIndex];
            if (!u && !this.selectedAxis) return;

            let hl = null;
            if (u && u.highlight_data) {
                try {
                    hl = (typeof u.highlight_data === 'string') ? JSON.parse(u.highlight_data) : u.highlight_data;
                } catch(e) {}
            }

            const unitAxis = (u && u.unit_axis) || this.selectedAxis;
            if ((!hl || (!hl.locatorBox && !hl.locator_box && !hl.marker)) && unitAxis && this.axisLocatorsMap && this.axisLocatorsMap[unitAxis]) {
                const axData = this.axisLocatorsMap[unitAxis];
                if (axData && axData.highlight_data) {
                    try {
                        hl = (typeof axData.highlight_data === 'string') ? JSON.parse(axData.highlight_data) : axData.highlight_data;
                    } catch(e) {}
                }
            }

            if (!hl && (this.locatorBox && this.locatorBox.w > 0 && this.locatorBox.h > 0)) {
                hl = {
                    locatorStyle: this.locatorStyle,
                    locatorBox: this.locatorBox,
                    hasArrow: this.hasArrow,
                    arrowDir: this.arrowDir,
                    arrowStart: this.arrowStart,
                    arrowHead: this.arrowHead,
                    boxBorderWidth: this.boxBorderWidth,
                    boxBorderRadius: this.boxBorderRadius,
                    boxHasFill: this.boxHasFill,
                    boxHasLabel: this.boxHasLabel,
                    pinColor: this.pinColor
                };
            }

            if (!hl) return;

            const scale = dw / slotImg.naturalWidth;
            const lStyle = hl.locatorStyle || 'box_arrow';
            const color = hl.pinColor || '#ef4444';

            if (lStyle === 'box' || lStyle === 'box_arrow') {
                const box = hl.locatorBox || hl.locator_box;
                if (!box) return;

                const scaledBox = {
                    x: dx + (box.x * scale),
                    y: dy + (box.y * scale),
                    w: box.w * scale,
                    h: box.h * scale
                };

                const scaledArrowStart = hl.arrowStart ? {
                    x: dx + (hl.arrowStart.x * scale),
                    y: dy + (hl.arrowStart.y * scale)
                } : null;

                const scaledArrowHead = hl.arrowHead ? {
                    x: dx + (hl.arrowHead.x * scale),
                    y: dy + (hl.arrowHead.y * scale)
                } : null;

                const labelText = (u && u.unit_code) ? u.unit_code : (unitAxis ? ('Trục ' + unitAxis) : '');

                this.drawBoundingBox(ctx, scaledBox, {
                    borderColor: color,
                    borderWidth: Math.max(3, Math.round((hl.boxBorderWidth || 6) * scale)),
                    borderRadius: Math.max(3, Math.round((hl.boxBorderRadius || 8) * scale)),
                    hasFill: hl.boxHasFill !== false,
                    hasLabel: hl.boxHasLabel !== false,
                    labelText: labelText,
                    hasArrow: hl.hasArrow !== false,
                    arrowStart: scaledArrowStart,
                    arrowHead: scaledArrowHead,
                    arrowWidth: Math.max(3, Math.round(5 * scale)),
                    showHandles: false
                });
            } else if (hl.marker) {
                const mx = dx + (hl.marker.x * scale);
                const my = dy + (hl.marker.y * scale);
                ctx.save();
                ctx.beginPath();
                ctx.arc(mx, my, Math.max(10, 20 * scale), 0, Math.PI * 2);
                ctx.fillStyle = color;
                ctx.fill();
                ctx.strokeStyle = '#ffffff';
                ctx.lineWidth = Math.max(2, 3 * scale);
                ctx.stroke();
                ctx.restore();
            }
        },

        async saveSelectedLocator() {
            if (!this.hasImage) {
                Swal.fire('Lỗi', 'Vui lòng chọn ảnh mặt bằng trước!', 'error');
                return;
            }

            const currentUnit = this.importedUnits[this.currentUnitIndex];
            const ids = this.locatorSelectedIds.length
                ? [...this.locatorSelectedIds]
                : (currentUnit && currentUnit.id ? [Number(currentUnit.id)] : []);
            if (ids.length === 0) {
                Swal.fire('Chưa chọn căn', 'Hãy chọn một căn ở danh sách bên trái.', 'warning');
                return;
            }

            const highlightData = {
                marker: this.marker,
                locatorStyle: this.locatorStyle,
                locatorBox: this.locatorBox,
                hasArrow: this.hasArrow,
                arrowDir: this.arrowDir,
                arrowStart: this.arrowStart,
                arrowHead: this.arrowHead,
                boxBorderWidth: this.boxBorderWidth,
                boxBorderRadius: this.boxBorderRadius,
                boxHasFill: this.boxHasFill,
                boxHasLabel: this.boxHasLabel,
                pinColor: this.pinColor,
                pinStyle: this.pinStyle,
                pinSize: this.pinSize,
                cardPosition: this.cardPosition,
                cardScalePercent: this.cardScalePercent,
                cardCustomX: this.cardCustomX,
                cardCustomY: this.cardCustomY
            };

            this.isSaving = true;
            try {
                const response = await fetch('{{ route('admin.unit-locators.bulk-sync-locator', $project->id) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        ids,
                        base_image: this.currentFloorplanPath || '',
                        highlight_data: JSON.stringify(highlightData),
                        image_data: document.getElementById('studioCanvas').toDataURL('image/png')
                    })
                });
                const data = await response.json();
                if (!response.ok || !data.success) throw new Error(data.message || 'Không thể lưu định vị.');

                this.importedUnits.forEach(unit => {
                    if (ids.includes(Number(unit.id))) {
                        unit.highlight_data = highlightData;
                        if (this.currentFloorplanPath) unit.base_image = this.currentFloorplanPath;
                        unit.floorplan_image = data.floorplan_image;
                        unit.floorplan_image_url = data.floorplan_image_url;
                    }
                });
                this.locatorSelectedIds = [];
                await Swal.fire({ icon: 'success', title: 'Đã lưu định vị', text: `Đã áp dụng cho ${data.count || ids.length} căn.`, timer: 2200, showConfirmButton: true });
            } catch (error) {
                Swal.fire('Lỗi', error.message, 'error');
            } finally {
                this.isSaving = false;
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

            const hlData = {
                marker: this.marker,
                locatorStyle: this.locatorStyle,
                locatorBox: this.locatorBox,
                hasArrow: this.hasArrow,
                arrowDir: this.arrowDir,
                arrowStart: this.arrowStart,
                arrowHead: this.arrowHead,
                boxBorderWidth: this.boxBorderWidth,
                boxBorderRadius: this.boxBorderRadius,
                boxHasFill: this.boxHasFill,
                boxHasLabel: this.boxHasLabel,
                pinStyle: this.pinStyle,
                pinColor: this.pinColor,
                pinSize: this.pinSize,
                cardPosition: this.cardPosition,
                cardScalePercent: this.cardScalePercent,
                cardCustomX: this.cardCustomX,
                cardCustomY: this.cardCustomY
            };

            const payload = {
                project_id: '{{ $project->id }}',
                unit_code: this.form.unit_code,
                unit_axis: this.form.unit_axis || this.selectedAxis || '',
                sync_axis_locator: this.syncAxisLocatorOption,
                base_image: this.currentFloorplanPath || '',
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
                highlight_data: JSON.stringify(hlData)
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
                        this.importedUnits[this.currentUnitIndex].highlight_data = hlData;
                        if (this.currentFloorplanPath) {
                            this.importedUnits[this.currentUnitIndex].base_image = this.currentFloorplanPath;
                        }
                        if (data.unit && data.unit.image_url) {
                            this.importedUnits[this.currentUnitIndex].image_url = data.unit.image_url;
                        }
                    }

                    const savedAxis = this.form.unit_axis || this.selectedAxis;
                    if (this.syncAxisLocatorOption && savedAxis) {
                        if (!this.axisLocatorsMap) this.axisLocatorsMap = {};
                        this.axisLocatorsMap[savedAxis] = {
                            unit_axis: savedAxis,
                            has_locator: true,
                            highlight_data: hlData,
                            base_image: this.currentFloorplanPath
                        };
                        if (this.importedUnits) {
                            this.importedUnits.forEach(u => {
                                if (u.unit_axis === savedAxis) {
                                    u.highlight_data = hlData;
                                    if (this.currentFloorplanPath) u.base_image = this.currentFloorplanPath;
                                }
                            });
                        }
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Đã lưu chỉ căn!',
                        text: `Chỉ căn ${this.form.unit_code} đã được tạo và lưu thành công vào hệ thống.` + (this.syncAxisLocatorOption && savedAxis ? ` Đã đồng bộ cho các căn thuộc Trục ${savedAxis}!` : ''),
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
