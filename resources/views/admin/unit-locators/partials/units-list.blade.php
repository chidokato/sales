<!-- Units Gallery View -->
@if ($units->isEmpty())
<div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-16 text-center">
    <div class="w-20 h-20 rounded-3xl bg-blue-50 text-blue-500 mx-auto flex items-center justify-center text-3xl mb-4">
        <i class="fa-solid fa-map-location-dot"></i>
    </div>
    <h3 class="text-xl font-bold text-slate-800">Không tìm thấy căn hộ nào</h3>
    <p class="text-sm text-slate-500 max-w-md mx-auto mt-2">
        Không có căn hộ nào khớp với điều kiện tìm kiếm. Hãy thử đổi từ khóa hoặc xóa bớt tiêu chí lọc!
    </p>
    <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
        <button type="button" @click="resetFilters()" class="px-5 py-2.5 rounded-xl bg-blue-600 text-white font-bold text-sm hover:bg-blue-700 transition shadow-lg shadow-blue-500/25 flex items-center gap-2">
            <i class="fa-solid fa-rotate-left"></i>
            <span>Xem tất cả căn hộ</span>
        </button>
        <a href="{{ route('admin.unit-locators.create', $project->id) }}" class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold text-sm hover:bg-slate-200 transition flex items-center gap-2">
            <i class="fa-solid fa-wand-magic-sparkles"></i>
            <span>Vào Studio tạo căn mới</span>
        </a>
    </div>
</div>
@else

<!-- ==================== VIEW 1: TABLE VIEW (MỖI CĂN 1 DÒNG, TỐI ƯU GỌN GÀNG KHÔNG CUỘN NGANG) ==================== -->
<div x-show="viewMode === 'table'" class="bg-white rounded-xl border border-slate-200/80 shadow-xs overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
            <thead>
                <tr class="bg-slate-50/90 border-b border-slate-200 text-slate-500 font-bold uppercase tracking-wider text-[10px]">
                    <th class="py-2.5 px-2 w-8 text-center">
                        <input 
                            type="checkbox" 
                            @change="toggleSelectAll($event)" 
                            :checked="isAllSelected"
                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer align-middle"
                            title="Chọn tất cả căn trên trang này"
                        >
                    </th>
                    <th class="py-2.5 px-2 w-10 text-center">Ảnh</th>
                    <th class="py-2.5 px-2.5">Mã căn</th>
                    <th class="py-2.5 px-2 text-center">Tòa</th>
                    <th class="py-2.5 px-2 text-center">Tầng</th>
                    <th class="py-2.5 px-2 text-center">Trục</th>
                    <th class="py-2.5 px-2 text-center">Ảnh bóc mái</th>
                    <th class="py-2.5 px-2.5">Diện tích</th>
                    <th class="py-2.5 px-2">Hướng BC</th>
                    <th class="py-2.5 px-2">Hướng cửa</th>
                    <th class="py-2.5 px-2 text-center">PN / WC</th>
                    <th class="py-2.5 px-2.5">Giá bán</th>
                    <th class="py-2.5 px-2 text-center">Trạng thái</th>
                    <th class="py-2.5 px-2.5 text-right">Thao tác</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @foreach ($units as $unit)
                <tr id="unit-row-{{ $unit->id }}" data-unit-id="{{ $unit->id }}" data-unit-axis="{{ $unit->unit_axis }}" :class="selectedUnitIds.includes({{ $unit->id }}) ? 'bg-indigo-50/50' : 'hover:bg-blue-50/30'" class="transition duration-150">
                    <!-- Checkbox chọn căn -->
                    <td class="py-1.5 px-2 text-center" @click.stop>
                        <input 
                            type="checkbox" 
                            :value="{{ $unit->id }}" 
                            x-model="selectedUnitIds"
                            class="w-4 h-4 rounded border-slate-300 text-blue-600 focus:ring-blue-500 cursor-pointer align-middle"
                        >
                    </td>

                    <!-- Ảnh Thumbnail (click mở Lightbox) -->
                    <td class="py-1.5 px-2 text-center">
                        <div 
                            class="w-8 h-8 rounded-lg overflow-hidden bg-slate-900 border border-slate-200 mx-auto shrink-0 shadow-2xs cursor-pointer relative group/img"
                            @click="openLightbox('{{ $unit->image_url }}', '{{ $unit->unit_code }}', {{ json_encode($unit) }})"
                            title="Bấm để xem ảnh chỉ căn kích thước lớn"
                        >
                            <img 
                                src="{{ $unit->image_url }}" 
                                alt="Chỉ căn {{ $unit->unit_code }}" 
                                class="w-full h-full object-cover group-hover/img:scale-110 transition duration-300"
                                onerror="this.onerror=null; this.src='{{ route('admin.unit-locators.project-image', 'default.jpg') }}';"
                            >
                            <div class="absolute inset-0 bg-black/30 flex items-center justify-center opacity-0 group-hover/img:opacity-100 transition">
                                <i class="fa-solid fa-magnifying-glass-plus text-white text-[9px]"></i>
                            </div>
                        </div>
                    </td>

                    <!-- Mã căn -->
                    <td class="py-1.5 px-2.5">
                        <button 
                            type="button"
                            class="font-black text-xs text-slate-900 hover:text-blue-600 transition flex items-center gap-1"
                            @click="openLightbox('{{ $unit->image_url }}', '{{ $unit->unit_code }}', {{ json_encode($unit) }})"
                        >
                            <span>{{ $unit->unit_code }}</span>
                            @if ($unit->unit_type)
                            <span class="px-1 py-0.2 rounded text-[9px] font-black bg-amber-100 text-amber-800 border border-amber-300" title="Loại căn: {{ $unit->unit_type }}">
                                {{ $unit->unit_type }}
                            </span>
                            @endif
                        </button>
                    </td>

                    <!-- Tòa / Block -->
                    <td class="py-1.5 px-2 text-center">
                        <span class="font-semibold text-slate-700">{{ $unit->block ?: '—' }}</span>
                    </td>

                    <!-- Tầng -->
                    <td class="py-1.5 px-2 text-center">
                        <span class="text-slate-600 font-medium">{{ $unit->floor ?: '—' }}</span>
                    </td>

                    <!-- Trục căn -->
                    <td class="py-1.5 px-2 text-center">
                        @if ($unit->unit_axis)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-black bg-indigo-50 text-indigo-700 border border-indigo-200/80 shadow-2xs" title="Trục căn: {{ $unit->unit_axis }}">
                            {{ $unit->unit_axis }}
                        </span>
                        @else
                        <span class="text-slate-300 font-medium">—</span>
                        @endif
                    </td>

                    <!-- Ảnh bóc mái 3D -->
                    <td class="py-1.5 px-2 text-center" id="unit-bocmai-cell-{{ $unit->id }}">
                        @if ($unit->layout_3d_image_url)
                        <div class="inline-flex items-center gap-1">
                            <div 
                                class="w-8 h-8 rounded-lg overflow-hidden bg-slate-900 border border-indigo-200 shrink-0 shadow-2xs cursor-pointer relative group/bocmai"
                                @click="openLightbox('{{ $unit->layout_3d_image_url }}', 'Bóc mái căn {{ $unit->unit_code }}', {{ json_encode($unit) }})"
                                title="Xem ảnh bóc mái 3D căn {{ $unit->unit_code }} (Click phóng to)"
                            >
                                <img 
                                    src="{{ $unit->layout_3d_image_url }}" 
                                    alt="Bóc mái {{ $unit->unit_code }}" 
                                    class="w-full h-full object-cover group-hover/bocmai:scale-110 transition duration-300"
                                >
                                <div class="absolute inset-0 bg-black/35 flex items-center justify-center opacity-0 group-hover/bocmai:opacity-100 transition">
                                    <i class="fa-solid fa-magnifying-glass-plus text-white text-[8px]"></i>
                                </div>
                            </div>
                            <button 
                                type="button"
                                @click="openQuickBocMaiModal({{ json_encode($unit) }})"
                                class="w-5 h-5 rounded bg-slate-50 hover:bg-indigo-50 text-slate-400 hover:text-indigo-600 transition flex items-center justify-center border border-slate-200 hover:border-indigo-300"
                                title="Đổi ảnh bóc mái hoặc đồng bộ theo trục"
                            >
                                <i class="fa-solid fa-pen text-[9px]"></i>
                            </button>
                        </div>
                        @else
                        <button 
                            type="button" 
                            @click="openQuickBocMaiModal({{ json_encode($unit) }})"
                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50/70 hover:bg-indigo-100/80 border border-dashed border-indigo-300 transition"
                            title="Thêm ảnh bóc mái 3D"
                        >
                            <i class="fa-solid fa-cube text-[9px]"></i>
                            <span>+ Bóc mái</span>
                        </button>
                        @endif
                    </td>

                    <!-- Diện tích (thông thủy) -->
                    <td class="py-1.5 px-2.5">
                        @if ($unit->area)
                        <span class="font-bold text-amber-600 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200/60 text-xs">
                            {{ $unit->area }} m²
                        </span>
                        @else
                        <span class="text-slate-400">—</span>
                        @endif
                    </td>

                    <!-- Hướng ban công & View -->
                    <td class="py-1.5 px-2">
                        <div class="flex items-center gap-1">
                            @if ($unit->direction)
                            <span class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded text-[10px] font-bold bg-blue-50 text-blue-700 border border-blue-200">
                                <i class="fa-solid fa-compass text-[8px]"></i>
                                <span>{{ $unit->direction }}</span>
                            </span>
                            @endif
                            @if ($unit->view)
                            <span class="inline-flex items-center gap-1 px-1 py-0.5 rounded text-[9px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200" title="View: {{ $unit->view }}">
                                <i class="fa-solid fa-eye text-[7px]"></i>
                                <span>{{ $unit->view }}</span>
                            </span>
                            @endif
                            @if (!$unit->direction && !$unit->view)
                            <span class="text-slate-400">—</span>
                            @endif
                        </div>
                    </td>

                    <!-- Hướng cửa -->
                    <td class="py-1.5 px-2">
                        <span class="text-slate-600 text-xs">{{ $unit->door_direction ?: '—' }}</span>
                    </td>

                    <!-- Phòng ngủ / WC -->
                    <td class="py-1.5 px-2 text-center">
                        @if ($unit->bedroom || $unit->bathroom)
                        <span class="font-semibold text-slate-700 text-xs">
                            {{ $unit->bedroom ? $unit->bedroom . 'PN' : '' }}
                            {{ $unit->bathroom ? ' • ' . $unit->bathroom . 'WC' : '' }}
                        </span>
                        @else
                        <span class="text-slate-400">—</span>
                        @endif
                    </td>

                    <!-- Giá bán -->
                    <td class="py-1.5 px-2.5">
                        <div>
                            <span class="font-extrabold text-blue-600 text-xs">
                                {{ $unit->display_price }}
                            </span>
                            @if ($unit->price_note)
                            <div class="text-[9px] text-slate-400 font-normal leading-tight">{{ $unit->price_note }}</div>
                            @endif
                        </div>
                    </td>

                    <!-- Trạng thái -->
                    <td class="py-1.5 px-2 text-center">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $unit->status_info['class'] }}">
                            <span class="w-1.5 h-1.5 rounded-full {{ $unit->status_info['dot'] }}"></span>
                            <span>{{ $unit->status_info['label'] }}</span>
                        </span>
                    </td>

                    <!-- Thao tác -->
                    <td class="py-1.5 px-2.5 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <!-- Nút Xem ảnh popup -->
                            <button 
                                type="button" 
                                @click="openLightbox('{{ $unit->image_url }}', '{{ $unit->unit_code }}', {{ json_encode($unit) }})"
                                class="p-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 transition border border-blue-200/80 shadow-2xs"
                                title="Xem ảnh chỉ căn kích thước lớn (Popup)"
                            >
                                <i class="fa-solid fa-eye text-xs"></i>
                            </button>

                            <!-- Tải ảnh -->
                            <a 
                                href="{{ $unit->image_url }}" 
                                download="Chi-can-{{ $unit->unit_code }}-{{ Str::slug($project->name) }}.png"
                                class="p-1.5 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-emerald-600 transition border border-slate-200/80 shadow-2xs"
                                title="Tải ảnh chỉ căn"
                            >
                                <i class="fa-solid fa-download text-xs"></i>
                            </a>

                            <!-- Sửa ảnh trong Studio -->
                            <a 
                                href="{{ route('admin.unit-locators.create', ['projectId' => $project->id, 'units' => $unit->id]) }}"
                                class="p-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-600 transition border border-indigo-200/80 shadow-2xs"
                                title="Mở Studio sửa ảnh chỉ căn này"
                            >
                                <i class="fa-solid fa-wand-magic-sparkles text-xs"></i>
                            </a>

                            <!-- Sửa -->
                            <button 
                                type="button" 
                                @click="openEditModal({{ json_encode($unit) }})"
                                class="p-1.5 rounded-lg bg-slate-50 hover:bg-slate-100 text-slate-500 hover:text-indigo-600 transition border border-slate-200/80 shadow-2xs"
                                title="Sửa thông số căn"
                            >
                                <i class="fa-regular fa-pen-to-square text-xs"></i>
                            </button>

                            <!-- Xóa -->
                            <button 
                                type="button" 
                                @click="deleteUnit({{ $unit->id }}, '{{ $unit->unit_code }}')"
                                class="p-1.5 rounded-lg bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 transition border border-slate-200/80 shadow-2xs"
                                title="Xóa chỉ căn"
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

<!-- ==================== VIEW 2: GRID VIEW (THẺ LƯỚI) ==================== -->
<div x-show="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
    @foreach ($units as $unit)
    <div id="unit-card-{{ $unit->id }}" data-unit-id="{{ $unit->id }}" data-unit-axis="{{ $unit->unit_axis }}" :class="selectedUnitIds.includes({{ $unit->id }}) ? 'ring-2 ring-indigo-500 border-indigo-400 bg-indigo-50/10 shadow-lg' : 'border-slate-200/80 hover:border-blue-400'" class="group bg-white rounded-2xl border overflow-hidden shadow-sm hover:shadow-xl transition-all duration-300 flex flex-col justify-between">
        <div>
            <!-- Unit Image Card -->
            <div class="relative h-56 bg-slate-900 overflow-hidden cursor-pointer" @click="openLightbox('{{ $unit->image_url }}', '{{ $unit->unit_code }}', {{ json_encode($unit) }})">
                <img 
                    src="{{ $unit->image_url }}" 
                    alt="Chỉ căn {{ $unit->unit_code }}" 
                    class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    onerror="this.onerror=null; this.src='{{ route('admin.unit-locators.project-image', 'default.jpg') }}';"
                >
                <div class="absolute inset-0 bg-gradient-to-t from-slate-950/80 via-transparent to-black/20"></div>

                <!-- Status Badge -->
                <div class="absolute top-3 right-3">
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border backdrop-blur-md {{ $unit->status_info['class'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $unit->status_info['dot'] }}"></span>
                        <span>{{ $unit->status_info['label'] }}</span>
                    </span>
                </div>

                <!-- Checkbox chọn căn trên Card Grid -->
                <div class="absolute top-3 left-3 z-20" @click.stop>
                    <input 
                        type="checkbox" 
                        :value="{{ $unit->id }}" 
                        x-model="selectedUnitIds"
                        class="w-5 h-5 rounded border-2 border-white/90 bg-black/40 text-indigo-600 focus:ring-indigo-500 cursor-pointer shadow-md"
                    >
                </div>

                <!-- Direction Badge -->
                @if (!empty($unit->direction))
                <div class="absolute top-3 left-10">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-[10px] font-extrabold bg-blue-600/90 text-white backdrop-blur-md shadow-sm">
                        <i class="fa-solid fa-compass text-[9px]"></i>
                        <span>{{ $unit->direction }}</span>
                    </span>
                </div>
                @endif

                <!-- Zoom / Click hint -->
                <div class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity bg-black/30 backdrop-blur-[1px]">
                    <span class="px-3.5 py-1.5 rounded-xl bg-white/90 text-slate-900 font-bold text-xs shadow-lg flex items-center gap-1.5 transform translate-y-2 group-hover:translate-y-0 transition-transform">
                        <i class="fa-solid fa-magnifying-glass-plus text-blue-600"></i>
                        <span>Xem ảnh lớn</span>
                    </span>
                </div>

                <!-- Unit Code Prominent Banner on image -->
                <div class="absolute bottom-3 left-3 right-3 text-white flex items-end justify-between">
                    <div>
                        <div class="flex items-center gap-1.5 mb-0.5">
                            <span class="text-[11px] text-blue-300 font-bold uppercase tracking-wider">
                                {{ $unit->block ?: 'Căn hộ' }} {{ $unit->floor ? '• Tầng ' . $unit->floor : '' }}
                            </span>
                            @if ($unit->unit_axis)
                            <span class="px-1.5 py-0.2 rounded text-[10px] font-black bg-indigo-600/90 text-white shadow-xs">
                                Trục {{ $unit->unit_axis }}
                            </span>
                            @endif
                        </div>
                        <h3 class="text-xl font-black text-white drop-shadow-md">
                            {{ $unit->unit_code }}
                        </h3>
                    </div>
                    @if ($unit->area)
                    <div class="text-right">
                        <span class="text-lg font-black text-amber-300 drop-shadow-md">
                            {{ $unit->area }} <span class="text-xs">m²</span>
                        </span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Unit Details Attributes -->
            <div class="p-4 space-y-3">
                <div class="grid grid-cols-2 gap-2 text-xs">
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Giá bán</span>
                        <span class="font-extrabold text-blue-600">
                            {{ $unit->display_price }}
                        </span>
                    </div>
                    <div class="p-2 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold tracking-wider">Phòng ngủ / WC</span>
                        <span class="font-bold text-slate-800">
                            {{ $unit->bedroom ? $unit->bedroom . ' PN' : '—' }} {{ $unit->bathroom ? '• ' . $unit->bathroom . ' WC' : '' }}
                        </span>
                    </div>
                </div>

                <!-- Ảnh bóc mái 3D -->
                <div id="unit-card-bocmai-{{ $unit->id }}" class="p-2.5 rounded-xl bg-slate-50 border border-slate-100 flex items-center justify-between text-xs">
                    <div class="flex items-center gap-1.5 text-slate-600">
                        <i class="fa-solid fa-cube text-indigo-500 text-xs"></i>
                        <span class="font-bold">Ảnh bóc mái 3D:</span>
                    </div>
                    @if ($unit->layout_3d_image_url)
                    <div class="flex items-center gap-1.5">
                        <button 
                            type="button"
                            @click="openLightbox('{{ $unit->layout_3d_image_url }}', 'Bóc mái căn {{ $unit->unit_code }}', {{ json_encode($unit) }})"
                            class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-800"
                            title="Xem ảnh bóc mái lớn"
                        >
                            <span>Xem 3D</span>
                            <i class="fa-solid fa-arrow-up-right-from-square text-[9px]"></i>
                        </button>
                        <button 
                            type="button"
                            @click="openQuickBocMaiModal({{ json_encode($unit) }})"
                            class="w-5 h-5 rounded bg-white hover:bg-indigo-50 text-slate-400 hover:text-indigo-600 flex items-center justify-center border border-slate-200 transition"
                            title="Đổi ảnh hoặc đồng bộ theo trục"
                        >
                            <i class="fa-solid fa-pen text-[9px]"></i>
                        </button>
                    </div>
                    @else
                    <button 
                        type="button"
                        @click="openQuickBocMaiModal({{ json_encode($unit) }})"
                        class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-700 bg-indigo-50 hover:bg-indigo-100 px-2 py-0.5 rounded border border-dashed border-indigo-300 transition"
                    >
                        <i class="fa-solid fa-plus text-[9px]"></i>
                        <span>Thêm ảnh</span>
                    </button>
                    @endif
                </div>

                @if (!empty($unit->note))
                <p class="text-xs text-slate-500 line-clamp-2 italic bg-amber-50/50 p-2 rounded-xl border border-amber-100/60">
                    <i class="fa-solid fa-circle-info text-amber-500 mr-1"></i>
                    {{ $unit->note }}
                </p>
                @endif
            </div>
        </div>

        <!-- Card Bottom Quick Action Toolbar -->
        <div class="p-4 pt-0 border-t border-slate-100 mt-2">
            <div class="grid grid-cols-4 gap-1 pt-3">
                <!-- Nút Xem ảnh popup -->
                <button 
                    type="button" 
                    @click="openLightbox('{{ $unit->image_url }}', '{{ $unit->unit_code }}', {{ json_encode($unit) }})"
                    class="p-2 rounded-xl bg-blue-50 hover:bg-blue-100 text-blue-600 text-xs font-semibold transition flex flex-col items-center justify-center gap-1"
                    title="Xem ảnh popup kích thước lớn"
                >
                    <i class="fa-solid fa-eye text-sm"></i>
                    <span class="text-[10px]">Xem ảnh</span>
                </button>

                <!-- Download Image -->
                <a 
                    href="{{ $unit->image_url }}" 
                    download="Chi-can-{{ $unit->unit_code }}-{{ Str::slug($project->name) }}.png"
                    class="p-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-emerald-600 text-xs font-semibold transition flex flex-col items-center justify-center gap-1"
                    title="Tải ảnh chỉ căn về máy"
                >
                    <i class="fa-solid fa-download text-sm"></i>
                    <span class="text-[10px]">Tải ảnh</span>
                </a>

                <!-- Edit Modal -->
                <button 
                    type="button" 
                    @click="openEditModal({{ json_encode($unit) }})"
                    class="p-2 rounded-xl bg-slate-50 hover:bg-slate-100 text-slate-600 hover:text-indigo-600 text-xs font-semibold transition flex flex-col items-center justify-center gap-1"
                    title="Chỉnh sửa thông số chỉ căn"
                >
                    <i class="fa-regular fa-pen-to-square text-sm"></i>
                    <span class="text-[10px]">Sửa</span>
                </button>

                <!-- Delete Button -->
                <button 
                    type="button" 
                    @click="deleteUnit({{ $unit->id }}, '{{ $unit->unit_code }}')"
                    class="p-2 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 text-xs font-semibold transition flex flex-col items-center justify-center gap-1"
                    title="Xóa chỉ căn này"
                >
                    <i class="fa-regular fa-trash-can text-sm"></i>
                    <span class="text-[10px]">Xóa</span>
                </button>
            </div>
        </div>
    </div>
    @endforeach
</div>

<!-- Pagination & Summary -->
<div class="pt-4 flex flex-col sm:flex-row items-center justify-between gap-3">
    <div class="text-xs text-slate-500 font-medium">
        Hiển thị <span class="font-bold text-slate-700">{{ $units->firstItem() ?? 0 }}</span> - <span class="font-bold text-slate-700">{{ $units->lastItem() ?? 0 }}</span> trong tổng số <span class="font-black text-blue-600">{{ $units->total() }}</span> căn (100 căn/trang)
    </div>
    <div>
        {{ $units->links() }}
    </div>
</div>
@endif
