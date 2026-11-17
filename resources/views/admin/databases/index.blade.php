@extends('layouts.admin')

@section('title', 'Cấu hình Database Dùng chung')
@section('page-title', 'Cấu hình Database Dùng chung')

@section('header-actions')
    <button 
        type="button" 
        @click="$dispatch('open-db-modal')"
        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition"
        title="Thêm Database mới"
    >
        <i class="fa-solid fa-plus text-xs"></i>
        <span>Thêm Database dùng chung</span>
    </button>
@endsection

@section('content')

    <div x-data="sharedDbManager()" @open-db-modal.window="openCreateModal()">

        <!-- Database Cards Grid -->
        @if ($databases->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
                @foreach ($databases as $db)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm hover:shadow-md transition p-6 flex flex-col justify-between">
                        <div>
                            <!-- Header: Name & Status -->
                            <div class="flex items-start justify-between gap-3 mb-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                                        <i class="fa-solid fa-database"></i>
                                    </div>
                                    <div>
                                        <h3 class="text-sm font-bold text-slate-900">{{ $db->name }}</h3>
                                        <p class="text-xs font-mono text-blue-600 font-bold">{{ $db->database }}</p>
                                    </div>
                                </div>
                                <div>
                                    @if ($db->is_active)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Đang bật
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold bg-slate-100 text-slate-500 border border-slate-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Đang tắt
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <!-- Connection Info -->
                            <div class="p-3 rounded-xl bg-slate-50 text-xs text-slate-600 space-y-1.5 font-mono mb-4">
                                <div class="flex justify-between">
                                    <span class="text-slate-400 font-sans">Máy chủ:</span>
                                    <span class="font-bold text-slate-800">{{ $db->host }}:{{ $db->port }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-slate-400 font-sans">Tài khoản:</span>
                                    <span class="text-slate-700">{{ $db->username }}</span>
                                </div>
                                @if ($db->notes)
                                    <div class="pt-1.5 border-t border-slate-200/60 font-sans text-slate-500 text-[11px]">
                                        {{ $db->notes }}
                                    </div>
                                @endif
                            </div>

                            <!-- Shared Tables Pills -->
                            <div>
                                <p class="text-xs font-bold uppercase tracking-wider text-slate-500 mb-2 flex items-center justify-between">
                                    <span>Bảng được dùng chung:</span>
                                    <span class="text-blue-600 text-[11px] font-semibold">({{ count($db->shared_tables ?? []) }} bảng)</span>
                                </p>
                                <div class="flex flex-wrap gap-1.5 min-h-[40px]">
                                    @forelse ($db->shared_tables ?? [] as $table)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-mono font-semibold border border-indigo-100">
                                            <i class="fa-solid fa-table text-[10px] text-indigo-400"></i>
                                            <span>{{ $table }}</span>
                                        </span>
                                    @empty
                                        <span class="text-slate-400 text-xs italic">Chưa chỉ định bảng nào.</span>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- Card Action Buttons -->
                        <div class="pt-4 border-t border-slate-100 mt-5 flex items-center justify-between gap-2">
                            <button 
                                type="button" 
                                @click="testExistingDb('{{ $db->host }}', '{{ $db->port }}', '{{ $db->database }}', '{{ $db->username }}', '{{ $db->password }}')" 
                                class="text-xs font-bold text-blue-600 hover:text-blue-700 flex items-center gap-1.5 p-1.5 rounded-lg hover:bg-blue-50 transition"
                                title="Kiểm tra kết nối trực tiếp"
                            >
                                <i class="fa-solid fa-plug text-xs"></i>
                                <span>Test kết nối</span>
                            </button>

                            <div class="flex items-center gap-1">
                                <!-- Toggle Active Status Form -->
                                <form action="{{ route('admin.databases.toggle', $db->id) }}" method="POST">
                                    @csrf
                                    <button 
                                        type="submit" 
                                        title="{{ $db->is_active ? 'Tắt dùng chung' : 'Bật dùng chung' }}"
                                        class="p-2 rounded-lg {{ $db->is_active ? 'text-amber-600 hover:bg-amber-50' : 'text-emerald-600 hover:bg-emerald-50' }} transition"
                                    >
                                        <i class="fa-solid {{ $db->is_active ? 'fa-pause' : 'fa-play' }}"></i>
                                    </button>
                                </form>

                                <!-- Edit Button -->
                                <button 
                                    type="button" 
                                    @click="openEditModal({{ json_encode($db) }})"
                                    class="p-2 rounded-lg text-slate-600 hover:bg-slate-100 transition"
                                    title="Chỉnh sửa cấu hình"
                                >
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <!-- Delete Form -->
                                <form id="deleteDbForm-{{ $db->id }}" action="{{ route('admin.databases.destroy', $db->id) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button 
                                        type="button" 
                                        onclick="confirmDeleteDb('deleteDbForm-{{ $db->id }}', '{{ addslashes($db->name) }}')"
                                        class="p-2 rounded-lg text-rose-500 hover:bg-rose-50 transition"
                                        title="Xóa cấu hình DB"
                                    >
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-2xl border border-slate-100 p-12 text-center shadow-sm mb-8">
                <div class="w-16 h-16 rounded-3xl bg-blue-50 text-blue-600 flex items-center justify-center text-2xl mx-auto mb-4">
                    <i class="fa-solid fa-database"></i>
                </div>
                <h3 class="text-base font-bold text-slate-900">Chưa có Database dùng chung nào</h3>
                <p class="text-xs text-slate-500 max-w-md mx-auto mt-1 mb-6 leading-relaxed">
                    Bạn có thể bấm nút bên dưới để thêm thông tin Database và chọn các bảng (vd: dự án, sản phẩm, tin tức) từ website khác trên cùng hosting.
                </p>
                <button 
                    type="button" 
                    @click="openCreateModal()"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-bold text-xs shadow-md shadow-blue-500/20 transition"
                >
                    <i class="fa-solid fa-plus text-xs"></i>
                    <span>Thêm cấu hình Database đầu tiên</span>
                </button>
            </div>
        @endif

        <!-- Add / Edit Database Modal (Alpine.js) -->
        <div 
            x-show="modalOpen" 
            x-cloak
            class="fixed inset-0 z-50 overflow-y-auto"
            aria-labelledby="modal-title" role="dialog" aria-modal="true"
        >
            <div 
                x-show="modalOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0"
                x-transition:enter-end="opacity-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100"
                x-transition:leave-end="opacity-0"
                class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm"
                @click="modalOpen = false"
            ></div>

            <div class="flex min-h-full items-center justify-center p-4">
                <div 
                    x-show="modalOpen"
                    x-transition:enter="ease-out duration-300"
                    x-transition:enter-start="opacity-0 scale-95"
                    x-transition:enter-end="opacity-100 scale-100"
                    x-transition:leave="ease-in duration-200"
                    x-transition:leave-start="opacity-100 scale-100"
                    x-transition:leave-end="opacity-0 scale-95"
                    class="relative w-full max-w-2xl bg-white rounded-3xl p-6 sm:p-8 shadow-2xl border border-slate-100"
                >
                    <div class="flex items-center justify-between pb-4 border-b border-slate-100">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg">
                                <i class="fa-solid fa-database"></i>
                            </div>
                            <div>
                                <h4 class="text-base font-bold text-slate-900" x-text="isEdit ? 'Chỉnh sửa Cấu hình Database' : 'Thêm Database Dùng chung'"></h4>
                                <p class="text-xs text-slate-500">Cấu hình kết nối và chọn các bảng dữ liệu dùng chung</p>
                            </div>
                        </div>
                        <button @click="modalOpen = false" class="text-slate-400 hover:text-slate-600 p-2">
                            <i class="fa-solid fa-xmark text-base"></i>
                        </button>
                    </div>

                    <!-- Modal Form -->
                    <form :action="isEdit ? updateUrl : '{{ route('admin.databases.store') }}'" method="POST" class="space-y-4 mt-5">
                        @csrf
                        <template x-if="isEdit">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <!-- Tên gợi nhớ -->
                        <div>
                            <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tên gợi nhớ (Ví dụ: Web Bất Động Sản IDC)</label>
                            <input type="text" name="name" x-model="formData.name" required placeholder="Nhập tên website hoặc mô tả..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        </div>

                        <!-- Thông tin kết nối MySQL: Host, Port, Database, User, Password -->
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                            <div class="sm:col-span-8">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Máy chủ Host (thường là 127.0.0.1 hoặc localhost)</label>
                                <input type="text" name="host" x-model="formData.host" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div class="sm:col-span-4">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Cổng (Port)</label>
                                <input type="number" name="port" x-model="formData.port" required class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                            <div class="sm:col-span-4">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tên Database</label>
                                <input type="text" name="database" x-model="formData.database" required placeholder="vd: idc_bds" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono font-bold focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div class="sm:col-span-4">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Tài khoản (Username)</label>
                                <input type="text" name="username" x-model="formData.username" required placeholder="root" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div class="sm:col-span-4">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Mật khẩu (Password)</label>
                                <input type="password" name="password" x-model="formData.password" placeholder="Bỏ trống nếu không có" class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                        </div>

                        <!-- Button Kiểm tra kết nối & Lấy danh sách bảng tự động -->
                        <div class="p-3 rounded-2xl bg-slate-50 border border-slate-200 flex flex-wrap items-center justify-between gap-3">
                            <div class="text-xs text-slate-500">
                                <i class="fa-solid fa-wand-magic-sparkles text-blue-600 mr-1"></i>
                                <span>Bấm nút để kiểm tra kết nối và tải danh sách các bảng có sẵn:</span>
                            </div>
                            <button 
                                type="button" 
                                @click="fetchAvailableTables()" 
                                :disabled="loadingTables"
                                class="px-3 py-1.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs flex items-center gap-1.5 transition disabled:opacity-50"
                            >
                                <i class="fa-solid fa-rotate text-xs" :class="loadingTables ? 'animate-spin' : ''"></i>
                                <span x-text="loadingTables ? 'Đang kiểm tra...' : 'Test & Lấy danh sách bảng'"></span>
                            </button>
                        </div>

                        <!-- Danh sách bảng khả dụng sau khi load -->
                        <div x-show="availableTables.length > 0" class="p-3 rounded-2xl bg-indigo-50/60 border border-indigo-100">
                            <p class="text-xs font-bold text-indigo-900 mb-2">Click vào bảng để thêm/bỏ chọn dùng chung:</p>
                            <div class="flex flex-wrap gap-1.5 max-h-36 overflow-y-auto">
                                <template x-for="tbl in availableTables" :key="tbl">
                                    <button 
                                        type="button" 
                                        @click="toggleTableSelection(tbl)" 
                                        :class="isTableSelected(tbl) ? 'bg-blue-600 text-white border-blue-600' : 'bg-white text-slate-700 border-slate-200 hover:border-blue-400'"
                                        class="px-2.5 py-1 rounded-lg text-xs font-mono font-medium border transition flex items-center gap-1"
                                    >
                                        <i class="fa-solid" :class="isTableSelected(tbl) ? 'fa-check text-[10px]' : 'fa-plus text-[10px] text-slate-400'"></i>
                                        <span x-text="tbl"></span>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- Các bảng dùng chung (Input text dạng phân tách bằng dấu phẩy) -->
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700">
                                    Bảng dùng chung (Nhập tên bảng, cách nhau bằng dấu phẩy)
                                </label>
                            </div>
                            <input 
                                type="text" 
                                name="shared_tables" 
                                x-model="formData.shared_tables_input" 
                                required 
                                placeholder="Ví dụ: du_an, bat_dong_san, categories" 
                                class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs font-mono focus:ring-2 focus:ring-blue-500 focus:outline-none"
                            >
                            <p class="text-[11px] text-slate-400 mt-1">Hệ thống sẽ chỉ cho phép truy vấn các bảng được liệt kê trong danh sách này.</p>
                        </div>

                        <!-- Ghi chú & Trạng thái -->
                        <div class="grid grid-cols-1 sm:grid-cols-12 gap-3">
                            <div class="sm:col-span-8">
                                <label class="block text-xs font-bold uppercase tracking-wider text-slate-700 mb-1.5">Ghi chú (Tùy chọn)</label>
                                <input type="text" name="notes" x-model="formData.notes" placeholder="Mục đích sử dụng, lưu ý..." class="w-full px-3.5 py-2.5 rounded-xl bg-slate-50 border border-slate-200 text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                            </div>
                            <div class="sm:col-span-4 flex items-center pt-6">
                                <label class="flex items-center gap-2 cursor-pointer select-none">
                                    <input type="checkbox" name="is_active" value="1" x-model="formData.is_active" class="w-4 h-4 rounded text-blue-600 border-slate-300 focus:ring-blue-500">
                                    <span class="text-xs text-slate-700 font-bold">Kích hoạt ngay</span>
                                </label>
                            </div>
                        </div>

                        <!-- Buttons -->
                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-100">
                            <button type="button" @click="modalOpen = false" class="px-4 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold transition">
                                Hủy bỏ
                            </button>
                            <button type="submit" class="px-5 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold shadow-md shadow-blue-500/20 transition">
                                <span x-text="isEdit ? 'Lưu thay đổi' : 'Thêm Database'"></span>
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
    function sharedDbManager() {
        return {
            modalOpen: false,
            isEdit: false,
            updateUrl: '',
            loadingTables: false,
            availableTables: [],
            formData: {
                name: '',
                host: '127.0.0.1',
                port: 3306,
                database: '',
                username: 'root',
                password: '',
                shared_tables_input: '',
                is_active: true,
                notes: '',
            },

            openCreateModal() {
                this.isEdit = false;
                this.updateUrl = '';
                this.availableTables = [];
                this.formData = {
                    name: '',
                    host: '127.0.0.1',
                    port: 3306,
                    database: '',
                    username: 'root',
                    password: '',
                    shared_tables_input: '',
                    is_active: true,
                    notes: '',
                };
                this.modalOpen = true;
            },

            openEditModal(db) {
                this.isEdit = true;
                this.updateUrl = '{{ route('admin.databases.update', ':id') }}'.replace(':id', db.id);
                this.availableTables = [];
                this.formData = {
                    name: db.name,
                    host: db.host || '127.0.0.1',
                    port: db.port || 3306,
                    database: db.database,
                    username: db.username || 'root',
                    password: db.password || '',
                    shared_tables_input: (db.shared_tables || []).join(', '),
                    is_active: Boolean(db.is_active),
                    notes: db.notes || '',
                };
                this.modalOpen = true;
            },

            // Lấy danh sách bảng bằng AJAX
            fetchAvailableTables() {
                if (!this.formData.database) {
                    Toast.fire({ icon: 'warning', title: 'Vui lòng nhập tên Database trước!' });
                    return;
                }

                this.loadingTables = true;

                fetch('{{ route('admin.databases.fetch-tables') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        host: this.formData.host,
                        port: this.formData.port,
                        database: this.formData.database,
                        username: this.formData.username,
                        password: this.formData.password,
                    })
                })
                .then(res => res.json())
                .then(data => {
                    this.loadingTables = false;
                    if (data.success) {
                        this.availableTables = data.tables;
                        Toast.fire({
                            icon: 'success',
                            title: `Kết nối thành công! Tìm thấy ${data.tables.length} bảng trong Database.`
                        });
                    } else {
                        confirmAction({
                            title: 'Kết nối thất bại',
                            text: data.message,
                            icon: 'error',
                            showCancelButton: false,
                            confirmButtonText: 'Đóng'
                        });
                    }
                })
                .catch(err => {
                    this.loadingTables = false;
                    Toast.fire({ icon: 'error', title: 'Lỗi mạng khi kết nối database!' });
                });
            },

            // Kiểm tra bảng đã chọn chưa
            isTableSelected(tbl) {
                const current = this.formData.shared_tables_input.split(',').map(s => s.trim()).filter(Boolean);
                return current.includes(tbl);
            },

            // Bật/tắt chọn bảng từ danh sách gợi ý
            toggleTableSelection(tbl) {
                let current = this.formData.shared_tables_input.split(',').map(s => s.trim()).filter(Boolean);
                if (current.includes(tbl)) {
                    current = current.filter(t => t !== tbl);
                } else {
                    current.push(tbl);
                }
                this.formData.shared_tables_input = current.join(', ');
            },

            // Test kết nối từ card danh sách
            testExistingDb(host, port, database, username, password) {
                Toast.fire({ icon: 'info', title: `Đang kiểm tra kết nối đến '${database}'...` });

                fetch('{{ route('admin.databases.test') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ host, port, database, username, password })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Toast.fire({ icon: 'success', title: data.message });
                    } else {
                        confirmAction({
                            title: 'Kết nối không thành công',
                            text: data.message,
                            icon: 'error',
                            showCancelButton: false,
                            confirmButtonText: 'Đã hiểu'
                        });
                    }
                })
                .catch(err => {
                    Toast.fire({ icon: 'error', title: 'Lỗi kiểm tra kết nối!' });
                });
            }
        }
    }

    // Xác nhận xóa cấu hình DB
    function confirmDeleteDb(formId, dbName) {
        confirmAction({
            title: 'Xác nhận xóa cấu hình?',
            text: `Bạn có chắc muốn xóa cấu hình Database "${dbName}"? Các tính năng sử dụng bảng dùng chung của DB này sẽ bị gián đoạn.`,
            icon: 'warning',
            confirmButtonText: 'Có, xóa ngay',
            cancelButtonText: 'Không, giữ lại',
            confirmButtonColor: '#e11d48'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById(formId).submit();
            }
        });
    }
</script>
@endpush
