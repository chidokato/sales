<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SharedDatabase;
use App\Models\ProjectUnitLocator;
use App\Models\ProjectFloorplan;
use App\Models\ProjectTemplate;
use App\Models\ProjectPerspective;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Pagination\LengthAwarePaginator;
use Exception;

class ProjectUnitLocatorController extends Controller
{
    /**
     * Danh sách dự án BĐS từ DB dùng chung (bảng posts) và tổng quan chỉ căn
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $filter = $request->input('filter', 'all'); // all, has_units, no_units
        $page = (int) $request->input('page', 1);
        $perPage = (int) $request->input('per_page', 20);

        $dbError = null;
        $projects = collect();
        $totalProjectsCount = 0;

        try {
            // Lấy danh sách dự án từ bảng posts của DB dùng chung
            // Lọc các bản ghi là dự án (sort_by = 'Product' hoặc danh mục BĐS)
            $query = SharedDatabase::table('posts');

            // Kiểm tra cột sort_by nếu có
            try {
                $query = $query->where(function ($q) {
                    $q->where('sort_by', 'Product')
                      ->orWhereNull('sort_by');
                });
            } catch (Exception $e) {
                // Nếu cột sort_by không lọc được thì bỏ qua điều kiện này
            }

            if (!empty($search)) {
                $query = $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('slug', 'LIKE', "%{$search}%")
                      ->orWhere('address', 'LIKE', "%{$search}%");
                });
            }

            $rawProjects = $query->select([
                'id',
                'name',
                'slug',
                'img',
                'price',
                'unit',
                'acreage',
                'bedroom',
                'address',
                'status',
                'created_at',
            ])->orderBy('id', 'desc')->get();

            // Đếm số lượng chỉ căn đã tạo cho từng dự án
            $unitCounts = ProjectUnitLocator::selectRaw('project_id, count(*) as count, count(distinct block) as blocks_count')
                ->groupBy('project_id')
                ->get()
                ->keyBy('project_id');

            // Gắn thông tin chỉ căn vào từng dự án
            $allProjects = $rawProjects->map(function ($p) use ($unitCounts) {
                $stats = $unitCounts->get($p->id);
                $p->total_units = $stats ? $stats->count : 0;
                $p->total_blocks = $stats ? $stats->blocks_count : 0;
                return $p;
            });

            // Lọc theo trạng thái đã có chỉ căn hay chưa
            if ($filter === 'has_units') {
                $filtered = $allProjects->filter(fn($p) => $p->total_units > 0);
            } elseif ($filter === 'no_units') {
                $filtered = $allProjects->filter(fn($p) => $p->total_units === 0);
            } else {
                $filtered = $allProjects;
            }

            $totalProjectsCount = $allProjects->count();
            $totalFiltered = $filtered->count();

            // Phân trang thủ công bằng LengthAwarePaginator
            $itemsForCurrentPage = $filtered->slice(($page - 1) * $perPage, $perPage)->values();
            $projects = new LengthAwarePaginator(
                $itemsForCurrentPage,
                $totalFiltered,
                $perPage,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );

        } catch (Exception $e) {
            $dbError = $e->getMessage();
        }

        // Thống kê tổng quan
        $totalUnitsInSystem = ProjectUnitLocator::count();
        $projectsWithUnitsCount = ProjectUnitLocator::distinct('project_id')->count('project_id');
        $availableUnitsCount = ProjectUnitLocator::where('status', 'available')->count();

        return view('admin.unit-locators.index', compact(
            'projects',
            'search',
            'filter',
            'dbError',
            'totalProjectsCount',
            'totalUnitsInSystem',
            'projectsWithUnitsCount',
            'availableUnitsCount'
        ));
    }

    /**
     * Xem danh sách tất cả các ảnh chỉ căn của một dự án cụ thể
     */
    public function projectUnits(Request $request, $projectId)
    {
        // Lấy thông tin dự án từ DB dùng chung
        try {
            $project = SharedDatabase::table('posts')->where('id', $projectId)->first();
        } catch (Exception $e) {
            return redirect()->route('admin.unit-locators.index')->with('error', 'Không thể kết nối đến DB dùng chung: ' . $e->getMessage());
        }

        if (!$project) {
            return redirect()->route('admin.unit-locators.index')->with('error', 'Không tìm thấy dự án có ID #' . $projectId);
        }

        // Truy vấn danh sách chỉ căn
        $query = ProjectUnitLocator::where('project_id', $projectId);

        // Lọc theo tìm kiếm mã căn
        if ($search = trim($request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('unit_code', 'LIKE', "%{$search}%")
                  ->orWhere('block', 'LIKE', "%{$search}%")
                  ->orWhere('floor', 'LIKE', "%{$search}%")
                  ->orWhere('note', 'LIKE', "%{$search}%");
            });
        }

        // Lọc theo hướng
        if ($direction = $request->input('direction')) {
            $query->where('direction', $direction);
        }

        // Lọc theo tòa / tháp
        if ($block = $request->input('block')) {
            $query->where('block', $block);
        }

        // Lọc theo trục căn
        if ($axis = $request->input('unit_axis')) {
            $query->where('unit_axis', $axis);
        }

        // Lọc theo số phòng ngủ
        if ($bedroom = $request->input('bedroom')) {
            $query->where('bedroom', $bedroom);
        }

        // Lọc theo trạng thái
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Sắp xếp
        $sort = $request->input('sort', 'latest');
        match ($sort) {
            'code_asc' => $query->orderBy('unit_code', 'asc'),
            'axis_asc' => $query->orderBy('unit_axis', 'asc'),
            'axis_desc' => $query->orderBy('unit_axis', 'desc'),
            'area_asc' => $query->orderBy('area', 'asc'),
            'area_desc' => $query->orderBy('area', 'desc'),
            'price_asc' => $query->orderBy('price', 'asc'),
            'price_desc' => $query->orderBy('price', 'desc'),
            default => $query->orderBy('id', 'desc'),
        };

        $perPage = (int) $request->input('per_page', 100);
        $units = $query->paginate($perPage)->withQueryString();

        // Xử lý request AJAX tìm kiếm / lọc / phân trang không load lại trang
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'html' => view('admin.unit-locators.partials.units-list', compact('project', 'units'))->render(),
                'total' => $units->total(),
                'count' => $units->count(),
                'page_unit_ids' => $units->pluck('id')->values()->all(),
            ]);
        }

        // Lấy danh sách các hướng, tòa, trục căn đã có trong dự án để làm bộ lọc
        $existingDirections = ProjectUnitLocator::where('project_id', $projectId)->whereNotNull('direction')->distinct()->pluck('direction');
        $existingBlocks = ProjectUnitLocator::where('project_id', $projectId)->whereNotNull('block')->distinct()->pluck('block');
        $existingAxes = ProjectUnitLocator::where('project_id', $projectId)->whereNotNull('unit_axis')->where('unit_axis', '!=', '')->distinct()->orderBy('unit_axis')->pluck('unit_axis');
        $floorplans = ProjectFloorplan::where('project_id', $projectId)->latest()->get();
        $templates = ProjectTemplate::where('project_id', $projectId)->latest()->get();
        $perspectives = ProjectPerspective::where('project_id', $projectId)->latest()->get();

        // Thống kê nhanh của dự án
        $stats = [
            'total' => ProjectUnitLocator::where('project_id', $projectId)->count(),
            'available' => ProjectUnitLocator::where('project_id', $projectId)->where('status', 'available')->count(),
            'deposited' => ProjectUnitLocator::where('project_id', $projectId)->where('status', 'deposited')->count(),
            'sold' => ProjectUnitLocator::where('project_id', $projectId)->where('status', 'sold')->count(),
        ];

        return view('admin.unit-locators.project', compact(
            'project',
            'units',
            'existingDirections',
            'existingBlocks',
            'existingAxes',
            'floorplans',
            'templates',
            'perspectives',
            'stats'
        ));
    }

    /**
     * Màn hình Studio & Trình tạo chỉ căn cho dự án
     */
    public function create(Request $request, $projectId)
    {
        try {
            $project = SharedDatabase::table('posts')->where('id', $projectId)->first();
        } catch (Exception $e) {
            return redirect()->route('admin.unit-locators.index')->with('error', 'Không thể kết nối đến DB dùng chung: ' . $e->getMessage());
        }

        if (!$project) {
            return redirect()->route('admin.unit-locators.index')->with('error', 'Không tìm thấy dự án');
        }

        $floorplans = ProjectFloorplan::where('project_id', $projectId)->latest()->get();
        $templates = ProjectTemplate::where('project_id', $projectId)->latest()->get();
        $perspectives = ProjectPerspective::where('project_id', $projectId)->latest()->get();

        $selectedUnitIds = [];
        if ($request->filled('units')) {
            $selectedUnitIds = array_values(array_filter(array_map('intval', explode(',', (string)$request->input('units')))));
        }
        $existingUnits = ProjectUnitLocator::where('project_id', $projectId)->orderBy('unit_code', 'asc')->get();

        $axisLocators = [];
        foreach ($existingUnits as $u) {
            if (!empty($u->unit_axis) && !empty($u->highlight_data) && !isset($axisLocators[$u->unit_axis])) {
                $axisLocators[$u->unit_axis] = [
                    'highlight_data' => $u->highlight_data,
                    'base_image' => $u->base_image,
                    'unit_code' => $u->unit_code,
                ];
            }
        }

        return view('admin.unit-locators.create', compact('project', 'floorplans', 'templates', 'perspectives', 'existingUnits', 'selectedUnitIds', 'axisLocators'));
    }

    /**
     * Nhập danh sách căn từ file Excel
     */
    public function importExcel(Request $request, $projectId)
    {
        try {
            $project = SharedDatabase::table('posts')->where('id', $projectId)->first();
        } catch (Exception $e) {
            $project = null;
        }
        $projectName = $project->name ?? 'Dự án #' . $projectId;

        $unitsData = $request->input('units', []);
        if (empty($unitsData) || !is_array($unitsData)) {
            return response()->json([
                'success' => false,
                'message' => 'Không có dữ liệu căn hộ nào để nhập!'
            ], 422);
        }

        $savedUnits = [];
        $createdCount = 0;
        $updatedCount = 0;

        foreach ($unitsData as $item) {
            $unitCode = trim($item['unit_code'] ?? '');
            if (empty($unitCode)) continue;

            $existing = ProjectUnitLocator::where('project_id', $projectId)
                ->where('unit_code', $unitCode)
                ->first();

            $unitAxis = !empty($item['unit_axis']) ? trim($item['unit_axis']) : (!empty($item['truc']) ? trim($item['truc']) : (!empty($item['truc_can']) ? trim($item['truc_can']) : null));
            if (empty($unitAxis)) {
                $unitAxis = ProjectUnitLocator::extractAxisFromCode($unitCode, $item['block'] ?? null, $item['floor'] ?? null);
            }

            $data = [
                'project_id' => $projectId,
                'project_name' => $projectName,
                'unit_code' => $unitCode,
                'unit_type' => !empty($item['unit_type']) ? trim($item['unit_type']) : null,
                'unit_axis' => $unitAxis,
                'block' => !empty($item['block']) ? trim($item['block']) : null,
                'floor' => !empty($item['floor']) ? trim($item['floor']) : null,
                'area' => !empty($item['area']) ? (float)$item['area'] : null,
                'direction' => !empty($item['direction']) ? trim($item['direction']) : null,
                'view' => !empty($item['view']) ? trim($item['view']) : null,
                'bedroom' => !empty($item['bedroom']) ? (int)$item['bedroom'] : null,
                'bathroom' => !empty($item['bathroom']) ? (int)$item['bathroom'] : null,
                'price' => !empty($item['price']) ? (float)$item['price'] : null,
                'price_text' => !empty($item['price_text']) ? trim($item['price_text']) : null,
                'price_note' => !empty($item['price_note']) ? trim($item['price_note']) : null,
                'status' => !empty($item['status']) ? trim($item['status']) : 'available',
            ];

            if (!empty($item['image'])) {
                $data['image'] = $item['image'];
            }
            if (!empty($item['layout_3d_image'])) {
                $data['layout_3d_image'] = $item['layout_3d_image'];
            } elseif (!empty($item['boc_mai'])) {
                $data['layout_3d_image'] = $item['boc_mai'];
            }

            if ($existing) {
                $existing->update($data);
                $savedUnits[] = $existing;
                $updatedCount++;
            } else {
                $data['image'] = $data['image'] ?? null;
                $data['created_by'] = auth()->id();
                $newUnit = ProjectUnitLocator::create($data);
                $savedUnits[] = $newUnit;
                $createdCount++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Đã nhập thành công " . count($savedUnits) . " căn ({$createdCount} căn mới, {$updatedCount} căn cập nhật)!",
            'total' => count($savedUnits),
            'created_count' => $createdCount,
            'updated_count' => $updatedCount,
            'units' => $savedUnits,
        ]);
    }

    /**
     * Sửa thông tin chỉ căn hàng loạt
     */
    public function bulkUpdate(Request $request, $projectId)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chọn ít nhất 1 căn để chỉnh sửa!'
            ], 422);
        }

        $applyFields = $request->input('apply_fields', []);
        $data = [];

        if (in_array('status', $applyFields) && $request->filled('status')) {
            $data['status'] = trim($request->input('status'));
        }
        if (in_array('block', $applyFields) && $request->filled('block')) {
            $data['block'] = trim($request->input('block'));
        }
        if (in_array('floor', $applyFields) && $request->filled('floor')) {
            $data['floor'] = trim($request->input('floor'));
        }
        if (in_array('unit_axis', $applyFields) && $request->filled('unit_axis')) {
            $data['unit_axis'] = trim($request->input('unit_axis'));
        }
        if (in_array('layout_3d_image', $applyFields) && $request->filled('layout_3d_image')) {
            $data['layout_3d_image'] = trim($request->input('layout_3d_image'));
        }
        if (in_array('unit_type', $applyFields) && $request->filled('unit_type')) {
            $data['unit_type'] = trim($request->input('unit_type'));
        }
        if (in_array('direction', $applyFields) && $request->filled('direction')) {
            $data['direction'] = trim($request->input('direction'));
        }
        if (in_array('door_direction', $applyFields) && $request->filled('door_direction')) {
            $data['door_direction'] = trim($request->input('door_direction'));
        }
        if (in_array('view', $applyFields) && $request->filled('view')) {
            $data['view'] = trim($request->input('view'));
        }
        if (in_array('bedroom', $applyFields)) {
            $data['bedroom'] = $request->filled('bedroom') ? (int) $request->input('bedroom') : null;
        }
        if (in_array('bathroom', $applyFields)) {
            $data['bathroom'] = $request->filled('bathroom') ? (int) $request->input('bathroom') : null;
        }
        if (in_array('area', $applyFields) && $request->filled('area')) {
            $data['area'] = (float) $request->input('area');
        }
        if (in_array('price_text', $applyFields) && $request->filled('price_text')) {
            $data['price_text'] = trim($request->input('price_text'));
        }
        if (in_array('price_note', $applyFields)) {
            $data['price_note'] = $request->filled('price_note') ? trim($request->input('price_note')) : null;
        }
        if (in_array('note', $applyFields)) {
            $data['note'] = $request->filled('note') ? trim($request->input('note')) : null;
        }

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng tích chọn và nhập ít nhất một trường cần cập nhật!'
            ], 422);
        }

        $data['updated_at'] = now();

        $updatedCount = ProjectUnitLocator::where('project_id', $projectId)
            ->whereIn('id', $ids)
            ->update($data);

        return response()->json([
            'success' => true,
            'message' => "Đã cập nhật thành công {$updatedCount} căn hộ!",
            'updated_count' => $updatedCount,
        ]);
    }

    /**
     * Xóa chỉ căn hàng loạt
     */
    public function bulkDelete(Request $request, $projectId)
    {
        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'Vui lòng chọn ít nhất 1 căn để xóa!'
            ], 422);
        }

        $units = ProjectUnitLocator::where('project_id', $projectId)
            ->whereIn('id', $ids)
            ->get();

        foreach ($units as $unit) {
            if (!empty($unit->image) && Storage::disk('public')->exists($unit->image)) {
                Storage::disk('public')->delete($unit->image);
            }
            $unit->delete();
        }

        return response()->json([
            'success' => true,
            'message' => "Đã xóa thành công " . count($units) . " căn hộ!",
            'deleted_count' => count($units),
        ]);
    }

    /**
     * Lưu chỉ căn mới (từ Studio Canvas hoặc Upload thông thường)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required',
            'unit_code' => 'required|string|max:100',
            'block' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:50',
            'area' => 'nullable|numeric|min:0',
            'area_gross' => 'nullable|numeric|min:0',
            'direction' => 'nullable|string|max:50',
            'door_direction' => 'nullable|string|max:50',
            'bedroom' => 'nullable|integer|min:0',
            'bathroom' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'price_text' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:available,reserved,deposited,sold,locked',
            'note' => 'nullable|string|max:2000',
            'highlight_data' => 'nullable|string',
            'image_file' => 'nullable|image|max:12288', // max 12MB
            'image_data' => 'nullable|string', // base64 từ Studio Canvas
            'base_image' => 'nullable|string',
        ]);

        // Xử lý lưu ảnh chỉ căn
        $imagePath = null;

        if ($request->hasFile('image_file')) {
            $file = $request->file('image_file');
            $filename = 'unit_' . $request->project_id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $imagePath = $file->storeAs('unit_locators/' . $request->project_id, $filename, 'public');
        } elseif (!empty($request->image_data)) {
            // Xử lý chuỗi Base64 xuất từ Studio Canvas
            $imageData = $request->image_data;
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]); // jpg, png, webp

                $imageData = base64_decode($imageData);
                if ($imageData === false) {
                    return back()->withErrors(['image_data' => 'Dữ liệu ảnh Canvas không hợp lệ!']);
                }

                $prefix = !empty($request->composer_data) ? 'poster_' : 'unit_canvas_';
                $filename = $prefix . $request->project_id . '_' . time() . '_' . uniqid() . '.' . $type;
                $storageDir = 'unit_locators/' . $request->project_id;
                
                Storage::disk('public')->makeDirectory($storageDir);
                Storage::disk('public')->put($storageDir . '/' . $filename, $imageData);
                $imagePath = $storageDir . '/' . $filename;
            }
        }

        if (!$imagePath) {
            return back()->withErrors(['image_file' => 'Vui lòng tải lên ảnh chỉ căn hoặc xuất ảnh từ Studio!'])->withInput();
        }

        // Lấy tên dự án từ DB dùng chung để snapshot
        $projectName = null;
        try {
            $projectName = SharedDatabase::table('posts')->where('id', $request->project_id)->value('name');
        } catch (Exception $e) {
            // Ignore
        }

        // Parse highlight_data nếu có
        $highlightData = null;
        if (!empty($request->highlight_data)) {
            $decoded = json_decode($request->highlight_data, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $highlightData = $decoded;
            }
        }

        // Parse composer_data nếu có
        $composerData = null;
        if (!empty($request->composer_data)) {
            $decodedComposer = is_string($request->composer_data) ? json_decode($request->composer_data, true) : $request->composer_data;
            if (json_last_error() === JSON_ERROR_NONE) {
                $composerData = $decodedComposer;
            }
        }

        $existingUnit = ProjectUnitLocator::where('project_id', $request->project_id)
            ->where('unit_code', trim($request->unit_code))
            ->first();

        $unitAxis = $request->filled('unit_axis') 
            ? trim($request->unit_axis) 
            : ($existingUnit?->unit_axis ?: ProjectUnitLocator::extractAxisFromCode($request->unit_code, $request->block, $request->floor));

        $layout3dPath = null;
        if ($request->hasFile('layout_3d_file')) {
            $file = $request->file('layout_3d_file');
            $filename = 'boc_mai_' . $request->project_id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $layout3dPath = $file->storeAs('layout_3d/' . $request->project_id, $filename, 'public');
        } elseif ($request->filled('layout_3d_image')) {
            $layout3dPath = trim($request->layout_3d_image);
        } elseif ($request->boolean('remove_layout_3d')) {
            $layout3dPath = null;
        } elseif ($existingUnit) {
            $layout3dPath = $existingUnit->layout_3d_image;
        }

        $unitData = [
            'project_id' => $request->project_id,
            'project_name' => $projectName,
            'unit_code' => trim($request->unit_code),
            'unit_type' => $request->unit_type ? trim($request->unit_type) : null,
            'unit_axis' => $unitAxis,
            'block' => $request->block ? trim($request->block) : null,
            'floor' => $request->floor ? trim($request->floor) : null,
            'area' => $request->area ? (float) $request->area : null,
            'area_gross' => $request->area_gross ? (float) $request->area_gross : null,
            'direction' => $request->direction ? trim($request->direction) : null,
            'door_direction' => $request->door_direction ? trim($request->door_direction) : null,
            'view' => $request->view ? trim($request->view) : null,
            'bedroom' => $request->bedroom ? (int) $request->bedroom : null,
            'bathroom' => $request->bathroom ? (int) $request->bathroom : null,
            'price' => $request->price ? (float) $request->price : null,
            'price_text' => $request->price_text ? trim($request->price_text) : null,
            'price_note' => $request->price_note ? trim($request->price_note) : null,
            'status' => $request->status ?: 'available',
            'image' => $imagePath,
            'layout_3d_image' => $layout3dPath,
            'base_image' => $request->base_image,
            'highlight_data' => $highlightData,
            'composer_data' => $composerData,
            'note' => $request->note ? trim($request->note) : null,
        ];

        if ($existingUnit) {
            $existingUnit->update($unitData);
            $unit = $existingUnit;
        } else {
            $unitData['created_by'] = auth()->id();
            $unit = ProjectUnitLocator::create($unitData);
        }

        if ($request->boolean('sync_axis_layout_3d') && !empty($unit->unit_axis) && !empty($unit->layout_3d_image)) {
            ProjectUnitLocator::where('project_id', $unit->project_id)
                ->where('unit_axis', $unit->unit_axis)
                ->update(['layout_3d_image' => $unit->layout_3d_image]);
        }

        if (($request->boolean('sync_axis_locator') || $request->input('sync_axis_locator') === '1' || $request->input('sync_axis_locator') === 'true') && !empty($unit->unit_axis) && !empty($highlightData)) {
            $syncData = ['highlight_data' => $highlightData];
            if (!empty($request->base_image)) {
                $syncData['base_image'] = $request->base_image;
            }
            ProjectUnitLocator::where('project_id', $unit->project_id)
                ->where('unit_axis', $unit->unit_axis)
                ->where('id', '!=', $unit->id)
                ->update($syncData);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Đã tạo thành công chỉ căn {$unit->unit_code}!",
                'unit' => $unit,
                'image_url' => $unit->image_url,
            ]);
        }

        return redirect()->route('admin.unit-locators.project', $request->project_id)
            ->with('success', "Đã tạo thành công chỉ căn {$unit->unit_code}!");
    }

    /**
     * Tải lên mặt bằng dùng chung cho dự án
     */
    public function storeFloorplan(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'name' => 'required|string|max:255',
            'floorplan_file' => 'required|image|max:15360',
            'block' => 'nullable|string|max:100',
            'floor_range' => 'nullable|string|max:100',
        ]);

        $file = $request->file('floorplan_file');
        $filename = 'fp_' . $request->project_id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('floorplans/' . $request->project_id, $filename, 'public');

        $floorplan = ProjectFloorplan::create([
            'project_id' => $request->project_id,
            'name' => trim($request->name),
            'image' => $path,
            'block' => $request->block ? trim($request->block) : null,
            'floor_range' => $request->floor_range ? trim($request->floor_range) : null,
            'description' => $request->description ? trim($request->description) : null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã tải lên mặt bằng thành công!',
                'floorplan' => $floorplan,
                'image_url' => $floorplan->image_url,
            ]);
        }

        return back()->with('success', 'Đã tải lên mặt bằng thành công!');
    }

    /**
     * Xóa mặt bằng
     */
    public function destroyFloorplan($id)
    {
        try {
            $floorplan = ProjectFloorplan::findOrFail($id);

            if (!empty($floorplan->image) && Storage::disk('public')->exists($floorplan->image)) {
                Storage::disk('public')->delete($floorplan->image);
            }

            $floorplan->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã xóa mặt bằng thành công!',
                ]);
            }

            return back()->with('success', 'Đã xóa mặt bằng thành công!');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa mặt bằng: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Không thể xóa mặt bằng: ' . $e->getMessage());
        }
    }

    /**
     * Tải lên và cấu hình phôi mẫu mới cho dự án
     */
    public function storeTemplate(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'name' => 'required|string|max:255',
            'template_file' => 'nullable|image|max:20480', // max 20MB
            'slots_config' => 'nullable|string',
        ]);

        $imagePath = null;
        $width = 1000;
        $height = 1400;

        if ($request->hasFile('template_file')) {
            $file = $request->file('template_file');
            $filename = 'template_' . $request->project_id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $imagePath = $file->storeAs('templates/' . $request->project_id, $filename, 'public');

            try {
                $imageSize = @getimagesize($file->getRealPath());
                if ($imageSize) {
                    $width = $imageSize[0];
                    $height = $imageSize[1];
                }
            } catch (Exception $e) {}
        }

        $slotsConfig = null;
        if (!empty($request->slots_config)) {
            $decoded = json_decode($request->slots_config, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $slotsConfig = $decoded;
            }
        }

        if (!$slotsConfig) {
            $slotsConfig = ProjectTemplate::getDefaultSlotsConfig();
            $slotsConfig['canvas']['width'] = $width;
            $slotsConfig['canvas']['height'] = $height;
        }

        $isFirst = ProjectTemplate::where('project_id', $request->project_id)->count() === 0;
        $isDefault = $request->boolean('is_default') || $isFirst;

        if ($isDefault) {
            ProjectTemplate::where('project_id', $request->project_id)->update(['is_default' => false]);
        }

        $template = ProjectTemplate::create([
            'project_id' => $request->project_id,
            'name' => trim($request->name),
            'image' => $imagePath,
            'width' => $width,
            'height' => $height,
            'slots_config' => $slotsConfig,
            'is_default' => $isDefault,
            'description' => $request->description ? trim($request->description) : null,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã lưu cấu hình phôi mẫu thành công!',
                'template' => $template,
                'image_url' => $template->image_url,
            ]);
        }

        return back()->with('success', 'Đã lưu cấu hình phôi mẫu thành công!');
    }

    /**
     * Cập nhật thông số tọa độ khung ảnh & chữ của phôi mẫu
     */
    public function updateTemplate(Request $request, $id)
    {
        $template = ProjectTemplate::findOrFail($id);

        $request->validate([
            'name' => 'nullable|string|max:255',
            'slots_config' => 'required',
            'template_file' => 'nullable|image|max:20480',
        ]);

        if ($request->name) {
            $template->name = trim($request->name);
        }

        if ($request->hasFile('template_file')) {
            $file = $request->file('template_file');
            $filename = 'template_' . $template->project_id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $imagePath = $file->storeAs('templates/' . $template->project_id, $filename, 'public');
            $template->image = $imagePath;

            try {
                $imageSize = @getimagesize($file->getRealPath());
                if ($imageSize) {
                    $template->width = $imageSize[0];
                    $template->height = $imageSize[1];
                }
            } catch (Exception $e) {}
        }

        $slotsConfig = is_string($request->slots_config) ? json_decode($request->slots_config, true) : $request->slots_config;
        if ($slotsConfig && json_last_error() === JSON_ERROR_NONE) {
            $template->slots_config = $slotsConfig;
        }

        if ($request->has('is_default') && $request->boolean('is_default')) {
            ProjectTemplate::where('project_id', $template->project_id)->update(['is_default' => false]);
            $template->is_default = true;
        }

        $template->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cập nhật tọa độ & cài đặt phôi mẫu thành công!',
                'template' => $template,
            ]);
        }

        return back()->with('success', 'Đã cập nhật tọa độ phôi mẫu thành công!');
    }

    /**
     * Xóa phôi mẫu
     */
    public function destroyTemplate($id)
    {
        try {
            $template = ProjectTemplate::findOrFail($id);
            if (!empty($template->image) && Storage::disk('public')->exists($template->image)) {
                Storage::disk('public')->delete($template->image);
            }
            $template->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã xóa phôi mẫu thành công!',
                ]);
            }

            return back()->with('success', 'Đã xóa phôi mẫu!');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa phôi mẫu: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Không thể xóa phôi mẫu: ' . $e->getMessage());
        }
    }

    /**
     * Tải lên ảnh phối cảnh dự án
     */
    public function storePerspective(Request $request)
    {
        $request->validate([
            'project_id' => 'required',
            'name' => 'nullable|string|max:255',
            'category' => 'required|string|in:layout_3d,perspective,amenity,template,other',
            'image_file' => 'nullable|image|max:15360',
            'image_files.*' => 'nullable|image|max:15360',
        ]);

        $uploadedCount = 0;
        $files = [];

        if ($request->hasFile('image_files')) {
            $files = $request->file('image_files');
        } elseif ($request->hasFile('image_file')) {
            $files = [$request->file('image_file')];
        }

        $createdPerspectives = [];

        foreach ($files as $file) {
            $filename = 'persp_' . $request->project_id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('perspectives/' . $request->project_id, $filename, 'public');

            $title = $request->name ? trim($request->name) : pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            if ($uploadedCount > 0 && $request->name) {
                $title .= ' (' . ($uploadedCount + 1) . ')';
            }

            $persp = ProjectPerspective::create([
                'project_id' => $request->project_id,
                'name' => $title,
                'category' => $request->category,
                'image' => $path,
                'description' => $request->description ? trim($request->description) : null,
            ]);

            $createdPerspectives[] = $persp;
            $uploadedCount++;
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Đã tải lên {$uploadedCount} ảnh phối cảnh thành công!",
                'perspectives' => $createdPerspectives,
            ]);
        }

        return back()->with('success', "Đã tải lên {$uploadedCount} ảnh phối cảnh thành công!");
    }

    /**
     * Xóa ảnh phối cảnh
     */
    public function destroyPerspective($id)
    {
        try {
            $persp = ProjectPerspective::findOrFail($id);
            if (!empty($persp->image) && Storage::disk('public')->exists($persp->image)) {
                Storage::disk('public')->delete($persp->image);
            }
            $persp->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Đã xóa ảnh phối cảnh thành công!',
                ]);
            }

            return back()->with('success', 'Đã xóa ảnh phối cảnh!');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa ảnh phối cảnh: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Không thể xóa ảnh phối cảnh: ' . $e->getMessage());
        }
    }

    /**
     * Lấy chi tiết chỉ căn (JSON cho Modal / Ajax)
     */
    public function show($id)
    {
        $unit = ProjectUnitLocator::findOrFail($id);

        return response()->json([
            'success' => true,
            'unit' => $unit,
            'image_url' => $unit->image_url,
            'layout_3d_image_url' => $unit->layout_3d_image_url,
            'base_image_url' => $unit->base_image_url,
            'status_info' => $unit->status_info,
            'display_price' => $unit->display_price,
        ]);
    }

    /**
     * Cập nhật thông tin chỉ căn
     */
    public function update(Request $request, $id)
    {
        $unit = ProjectUnitLocator::findOrFail($id);

        $validated = $request->validate([
            'unit_code' => 'required|string|max:100',
            'unit_axis' => 'nullable|string|max:50',
            'block' => 'nullable|string|max:100',
            'floor' => 'nullable|string|max:50',
            'area' => 'nullable|numeric|min:0',
            'area_gross' => 'nullable|numeric|min:0',
            'direction' => 'nullable|string|max:50',
            'door_direction' => 'nullable|string|max:50',
            'bedroom' => 'nullable|integer|min:0',
            'bathroom' => 'nullable|integer|min:0',
            'price' => 'nullable|numeric|min:0',
            'price_text' => 'nullable|string|max:100',
            'status' => 'nullable|string|in:available,reserved,deposited,sold,locked',
            'note' => 'nullable|string|max:2000',
            'image_file' => 'nullable|image|max:12288',
            'layout_3d_file' => 'nullable|image|max:15360',
            'layout_3d_image' => 'nullable|string',
            'sync_axis_layout_3d' => 'nullable',
        ]);

        if ($request->hasFile('image_file')) {
            // Xóa ảnh cũ nếu có
            if (!empty($unit->image) && Storage::disk('public')->exists($unit->image)) {
                Storage::disk('public')->delete($unit->image);
            }

            $file = $request->file('image_file');
            $filename = 'unit_' . $unit->project_id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $unit->image = $file->storeAs('unit_locators/' . $unit->project_id, $filename, 'public');
        }

        // Xử lý ảnh bóc mái 3D
        if ($request->hasFile('layout_3d_file')) {
            if (!empty($unit->layout_3d_image) && Storage::disk('public')->exists($unit->layout_3d_image)) {
                Storage::disk('public')->delete($unit->layout_3d_image);
            }
            $file3d = $request->file('layout_3d_file');
            $filename3d = 'boc_mai_' . $unit->project_id . '_' . time() . '_' . uniqid() . '.' . $file3d->getClientOriginalExtension();
            $unit->layout_3d_image = $file3d->storeAs('layout_3d/' . $unit->project_id, $filename3d, 'public');
        } elseif ($request->filled('layout_3d_image')) {
            $unit->layout_3d_image = trim($request->layout_3d_image);
        } elseif ($request->boolean('remove_layout_3d')) {
            if (!empty($unit->layout_3d_image) && Storage::disk('public')->exists($unit->layout_3d_image)) {
                Storage::disk('public')->delete($unit->layout_3d_image);
            }
            $unit->layout_3d_image = null;
        }

        $unit->unit_code = trim($request->unit_code);
        $unit->unit_axis = $request->filled('unit_axis') ? trim($request->unit_axis) : ($unit->unit_axis ?: ProjectUnitLocator::extractAxisFromCode($request->unit_code, $request->block, $request->floor));
        $unit->block = $request->block ? trim($request->block) : null;
        $unit->floor = $request->floor ? trim($request->floor) : null;
        $unit->area = $request->area ? (float) $request->area : null;
        $unit->area_gross = $request->area_gross ? (float) $request->area_gross : null;
        $unit->direction = $request->direction ? trim($request->direction) : null;
        $unit->door_direction = $request->door_direction ? trim($request->door_direction) : null;
        $unit->bedroom = $request->bedroom ? (int) $request->bedroom : null;
        $unit->bathroom = $request->bathroom ? (int) $request->bathroom : null;
        $unit->price = $request->price ? (float) $request->price : null;
        $unit->price_text = $request->price_text ? trim($request->price_text) : null;
        $unit->status = $request->status ?: $unit->status;
        $unit->note = $request->note ? trim($request->note) : null;

        if ($request->has('highlight_data')) {
            $hl = $request->input('highlight_data');
            $unit->highlight_data = is_string($hl) ? json_decode($hl, true) : $hl;
        }
        if ($request->filled('base_image')) {
            $unit->base_image = trim($request->base_image);
        }

        $unit->save();

        // Đồng bộ ảnh bóc mái cho các căn cùng trục căn nếu được yêu cầu
        $syncAxis = $request->boolean('sync_axis_layout_3d') || $request->input('sync_axis_layout_3d') === '1' || $request->input('sync_axis_layout_3d') === 'true';
        if ($syncAxis && !empty($unit->unit_axis) && !empty($unit->layout_3d_image)) {
            ProjectUnitLocator::where('project_id', $unit->project_id)
                ->where('unit_axis', $unit->unit_axis)
                ->update(['layout_3d_image' => $unit->layout_3d_image]);
        }

        // Đồng bộ định vị trên mặt bằng cho các căn cùng trục căn nếu được yêu cầu
        $syncAxisLocator = $request->boolean('sync_axis_locator') || $request->input('sync_axis_locator') === '1' || $request->input('sync_axis_locator') === 'true';
        if ($syncAxisLocator && !empty($unit->unit_axis) && !empty($unit->highlight_data)) {
            $syncData = ['highlight_data' => $unit->highlight_data];
            if (!empty($unit->base_image)) {
                $syncData['base_image'] = $unit->base_image;
            }
            ProjectUnitLocator::where('project_id', $unit->project_id)
                ->where('unit_axis', $unit->unit_axis)
                ->where('id', '!=', $unit->id)
                ->update($syncData);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => "Đã cập nhật chỉ căn {$unit->unit_code} thành công!",
                'unit' => $unit,
                'image_url' => $unit->image_url,
                'layout_3d_image_url' => $unit->layout_3d_image_url,
            ]);
        }

        return back()->with('success', "Đã cập nhật chỉ căn {$unit->unit_code} thành công!");
    }

    /**
     * Cập nhật / Tải lên nhanh ảnh bóc mái 3D cho 1 căn (Ajax)
     */
    public function updateLayout3d(Request $request, $id)
    {
        $unit = ProjectUnitLocator::findOrFail($id);

        $request->validate([
            'layout_3d_file' => 'nullable|image|max:15360',
            'layout_3d_image' => 'nullable|string',
            'sync_axis' => 'nullable',
        ]);

        $imagePath = $unit->layout_3d_image;

        if ($request->hasFile('layout_3d_file')) {
            if (!empty($unit->layout_3d_image) && Storage::disk('public')->exists($unit->layout_3d_image)) {
                Storage::disk('public')->delete($unit->layout_3d_image);
            }
            $file = $request->file('layout_3d_file');
            $filename = 'boc_mai_' . $unit->project_id . '_' . time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $imagePath = $file->storeAs('layout_3d/' . $unit->project_id, $filename, 'public');
        } elseif ($request->filled('layout_3d_image')) {
            $imagePath = trim($request->layout_3d_image);
        } elseif ($request->boolean('remove_image')) {
            if (!empty($unit->layout_3d_image) && Storage::disk('public')->exists($unit->layout_3d_image)) {
                Storage::disk('public')->delete($unit->layout_3d_image);
            }
            $imagePath = null;
        }

        $unit->layout_3d_image = $imagePath;
        $unit->save();

        $syncedCount = 1;
        $syncAxis = $request->boolean('sync_axis') || $request->input('sync_axis') === '1' || $request->input('sync_axis') === 'true';
        if ($syncAxis && !empty($unit->unit_axis)) {
            $syncedCount = ProjectUnitLocator::where('project_id', $unit->project_id)
                ->where('unit_axis', $unit->unit_axis)
                ->update(['layout_3d_image' => $imagePath]);
        }

        return response()->json([
            'success' => true,
            'message' => ($syncAxis && !empty($unit->unit_axis))
                ? "Đã cập nhật ảnh bóc mái cho {$syncedCount} căn thuộc trục căn {$unit->unit_axis}!"
                : "Đã cập nhật ảnh bóc mái căn {$unit->unit_code} thành công!",
            'unit' => $unit,
            'layout_3d_image_url' => $unit->layout_3d_image_url,
            'synced_count' => $syncedCount,
        ]);
    }

    /**
     * Đồng bộ nhanh ảnh bóc mái theo trục căn cho toàn dự án (Ajax)
     */
    public function syncAxisLayout3d(Request $request, $projectId)
    {
        $request->validate([
            'unit_axis' => 'required|string',
            'layout_3d_image' => 'nullable|string',
            'layout_3d_file' => 'nullable|image|max:15360',
        ]);

        $axis = trim($request->unit_axis);
        $imagePath = null;

        if ($request->hasFile('layout_3d_file')) {
            $file = $request->file('layout_3d_file');
            $filename = 'boc_mai_axis_' . $projectId . '_' . $axis . '_' . time() . '.' . $file->getClientOriginalExtension();
            $imagePath = $file->storeAs('layout_3d/' . $projectId, $filename, 'public');
        } elseif ($request->filled('layout_3d_image')) {
            $imagePath = trim($request->layout_3d_image);
        }

        $count = ProjectUnitLocator::where('project_id', $projectId)
            ->where('unit_axis', $axis)
            ->update(['layout_3d_image' => $imagePath]);

        $imageUrl = null;
        if ($imagePath) {
            $imageUrl = str_starts_with($imagePath, 'http') ? $imagePath : route('admin.unit-locators.media', ['path' => $imagePath]);
        }

        return response()->json([
            'success' => true,
            'message' => "Đã đồng bộ ảnh bóc mái cho {$count} căn thuộc trục {$axis}!",
            'unit_axis' => $axis,
            'layout_3d_image' => $imagePath,
            'layout_3d_image_url' => $imageUrl,
            'count' => $count,
        ]);
    }

    /**
     * Đồng bộ nhanh định vị vị trí căn trên mặt bằng theo trục căn cho toàn dự án (Ajax)
     * Các căn cùng trục căn sẽ có định vị giống nhau
     */
    public function syncAxisLocator(Request $request, $projectId)
    {
        $request->validate([
            'unit_axis' => 'required|string',
            'highlight_data' => 'nullable',
            'base_image' => 'nullable|string',
            'image_data' => 'nullable|string',
        ]);

        $axis = trim($request->unit_axis);
        $highlightData = null;
        if (!empty($request->highlight_data)) {
            $highlightData = is_string($request->highlight_data) 
                ? json_decode($request->highlight_data, true) 
                : $request->highlight_data;
        }

        $baseImage = $request->filled('base_image') ? trim($request->base_image) : null;

        $updateData = [
            'highlight_data' => $highlightData,
        ];
        if ($baseImage !== null) {
            $updateData['base_image'] = $baseImage;
        }

        // Nếu gửi kèm ảnh chỉ căn được xuất từ Studio Canvas (image_data base64)
        if (!empty($request->image_data)) {
            $imageData = $request->image_data;
            if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                $imageData = substr($imageData, strpos($imageData, ',') + 1);
                $type = strtolower($type[1]);
                $imageData = base64_decode($imageData);
                if ($imageData !== false) {
                    $filename = 'unit_canvas_axis_' . $projectId . '_' . preg_replace('/[^a-zA-Z0-9_-]/', '_', $axis) . '_' . time() . '.' . $type;
                    $storageDir = 'unit_locators/' . $projectId;
                    Storage::disk('public')->makeDirectory($storageDir);
                    Storage::disk('public')->put($storageDir . '/' . $filename, $imageData);
                    $updateData['image'] = $storageDir . '/' . $filename;
                }
            }
        }

        $count = ProjectUnitLocator::where('project_id', $projectId)
            ->where('unit_axis', $axis)
            ->update($updateData);

        $imageUrl = null;
        if (isset($updateData['image'])) {
            $imageUrl = route('admin.unit-locators.media', ['path' => $updateData['image']]);
        }

        return response()->json([
            'success' => true,
            'message' => "Đã đồng bộ định vị mặt bằng cho {$count} căn thuộc trục {$axis} thành công!",
            'unit_axis' => $axis,
            'highlight_data' => $highlightData,
            'base_image' => $baseImage,
            'image' => $updateData['image'] ?? null,
            'image_url' => $imageUrl,
            'count' => $count,
        ]);
    }

    /**
     * Lấy danh sách định vị theo các trục căn của dự án
     */
    public function getAxisLocators($projectId)
    {
        $units = ProjectUnitLocator::where('project_id', $projectId)
            ->whereNotNull('unit_axis')
            ->where('unit_axis', '!=', '')
            ->get();

        $axes = [];
        foreach ($units as $u) {
            $axis = $u->unit_axis;
            if (!isset($axes[$axis])) {
                $axes[$axis] = [
                    'axis' => $axis,
                    'total_units' => 0,
                    'has_locator' => false,
                    'highlight_data' => null,
                    'base_image' => null,
                    'sample_unit' => $u->unit_code,
                    'block' => $u->block,
                ];
            }
            $axes[$axis]['total_units']++;
            if (!empty($u->highlight_data) && !$axes[$axis]['has_locator']) {
                $axes[$axis]['has_locator'] = true;
                $axes[$axis]['highlight_data'] = $u->highlight_data;
                $axes[$axis]['base_image'] = $u->base_image;
            }
        }

        ksort($axes);

        return response()->json([
            'success' => true,
            'axes' => array_values($axes),
        ]);
    }

    /**
     * Đổi nhanh trạng thái chỉ căn (Ajax)
     */
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:available,reserved,deposited,sold,locked',
        ]);

        $unit = ProjectUnitLocator::findOrFail($id);
        $unit->status = $request->status;
        $unit->save();

        return response()->json([
            'success' => true,
            'message' => "Đã chuyển trạng thái căn {$unit->unit_code} thành: {$unit->status_info['label']}",
            'status_info' => $unit->status_info,
        ]);
    }

    /**
     * Xóa chỉ căn
     */
    public function destroy($id)
    {
        try {
            $unit = ProjectUnitLocator::findOrFail($id);
            $code = $unit->unit_code;
            $projectId = $unit->project_id;

            // Xóa file ảnh trong storage
            if (!empty($unit->image) && Storage::disk('public')->exists($unit->image)) {
                Storage::disk('public')->delete($unit->image);
            }

            $unit->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => "Đã xóa chỉ căn {$code}!",
                ]);
            }

            return redirect()->route('admin.unit-locators.project', $projectId)
                ->with('success', "Đã xóa chỉ căn {$code}!");
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Không thể xóa chỉ căn: ' . $e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Không thể xóa chỉ căn: ' . $e->getMessage());
        }
    }

    /**
     * Proxy phục vụ hiển thị ảnh thumbnail của dự án từ thư mục indochinerealestate
     * Đảm bảo hiển thị ảnh mượt mà không bị lỗi 404
     */
    public function projectImageProxy($filename)
    {
        // Danh sách các đường dẫn ảnh khả dụng cục bộ
        $candidates = [
            base_path('../indochinerealestate/public/data/images/' . $filename),
            base_path('../indochinerealestate/public/uploads/' . $filename),
            base_path('../indochinerealestate_new/public/data/images/' . $filename),
            public_path('uploads/' . $filename),
            storage_path('app/public/' . $filename),
        ];

        foreach ($candidates as $path) {
            if (File::exists($path) && is_file($path)) {
                $mimeType = File::mimeType($path) ?: 'image/jpeg';
                return response()->file($path, [
                    'Content-Type' => $mimeType,
                    'Cache-Control' => 'public, max-age=86400',
                ]);
            }
        }

        // Nếu không có file cục bộ, chuyển hướng về server chính indochinerealestate.vn hoặc ảnh dự án tổng quan
        if ($filename && $filename !== 'default.jpg') {
            return redirect('https://indochinerealestate.vn/uploads/' . $filename);
        }

        // Ảnh fallback dự án tổng quan
        return redirect('https://indochinerealestate.vn/uploads/1777014988.jpg');
    }

    /**
     * Phục vụ an toàn hình ảnh storage (Chỉ căn & Mặt bằng) cho mọi môi trường (localhost/subfolder/domain)
     */
    public function serveMedia($path)
    {
        $cleanPath = ltrim(str_replace(['../', '..\\'], '', $path), '/\\');

        $candidates = [
            storage_path('app/public/' . $cleanPath),
            public_path('storage/' . $cleanPath),
            public_path($cleanPath),
        ];

        foreach ($candidates as $filePath) {
            if (File::exists($filePath) && is_file($filePath)) {
                $mimeType = File::mimeType($filePath) ?: 'image/png';
                return response()->file($filePath, [
                    'Content-Type' => $mimeType,
                    'Cache-Control' => 'public, max-age=86400',
                    'Access-Control-Allow-Origin' => '*',
                ]);
            }
        }

        return $this->projectImageProxy('default.jpg');
    }
}
