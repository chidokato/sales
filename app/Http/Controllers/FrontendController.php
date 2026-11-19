<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SharedDatabase;
use App\Models\ProjectUnitLocator;
use App\Models\ProjectFloorplan;
use App\Models\ProjectPerspective;
use App\Models\ProjectTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Exception;

class FrontendController extends Controller
{
    /**
     * Hiển thị trang frontend cho người dùng (Khách hàng & Thành viên)
     */
    public function index(Request $request)
    {
        $requestedProjectId = (int) $request->query('project_id', 0);
        $activeMenu = $request->query('menu', 'projects'); // projects, locator, inventory, calculator, news, contact

        $projects = collect();
        $totalProjectsCount = 0;
        $dbError = null;

        // 1. Thống kê số lượng chỉ căn đã có theo từng dự án
        $locatorCounts = ProjectUnitLocator::select('project_id', \Illuminate\Support\Facades\DB::raw('count(*) as total'))
            ->groupBy('project_id')
            ->pluck('total', 'project_id')
            ->toArray();

        $locatorProjectIds = array_keys($locatorCounts);

        // 2. Lấy danh sách dự án BĐS từ DB dùng chung (bảng posts) - CHỈ LẤY CÁC DỰ ÁN CÓ CHỈ CĂN
        try {
            if (!empty($locatorProjectIds)) {
                $rawProjects = SharedDatabase::table('posts')
                    ->select([
                        'id',
                        'name',
                        'slug',
                        'img',
                        'content',
                        'price',
                        'price_max',
                        'address',
                        'total_product',
                        'acreage',
                        'acreage_max',
                        'bedroom',
                        'bedroom_max',
                        'description',
                        'created_at'
                    ])
                    ->whereIn('id', $locatorProjectIds)
                    ->get();
            } else {
                $rawProjects = collect();
            }

            // Đối với các project_id có trong ProjectUnitLocator nhưng không có trong bảng posts, tạo object dự phòng
            $foundIds = $rawProjects->pluck('id')->toArray();
            foreach ($locatorCounts as $pId => $cnt) {
                if (!in_array($pId, $foundIds)) {
                    $sampleLocator = ProjectUnitLocator::where('project_id', $pId)->first();
                    $rawProjects->push((object) [
                        'id' => $pId,
                        'name' => $sampleLocator ? $sampleLocator->project_name : 'Dự án BĐS #' . $pId,
                        'slug' => 'du-an-' . $pId,
                        'img' => null,
                        'price' => null,
                        'price_max' => null,
                        'address' => 'Hà Nội',
                        'total_product' => $cnt,
                        'acreage' => 50,
                        'acreage_max' => 120,
                        'bedroom' => 2,
                        'bedroom_max' => 3,
                        'description' => 'Dự án bất động sản đã có sơ đồ chỉ căn',
                        'created_at' => now(),
                    ]);
                }
            }

            // Gắn số lượng căn & trích xuất ảnh tổng quan, thông tin chi tiết
            $projects = $rawProjects->map(function ($item) use ($locatorCounts) {
                $item->unit_locator_count = $locatorCounts[$item->id] ?? 0;
                $item->has_locators = true;

                // 1. Trích xuất ảnh trong phần tổng quan (Overview Image)
                $overviewImg = null;
                if (!empty($item->content)) {
                    // Ưu tiên ảnh nằm ngay trong/sau mục TỔNG QUAN
                    if (preg_match('/(?:tổng\s*quan|tong\s*quan)[^<]*?<\/p>.*?<img[^>]+src=[\'"]([^\'"]+)[\'"]/is', $item->content, $m)) {
                        $overviewImg = $m[1];
                    } elseif (preg_match('/<img[^>]+src=[\'"]([^\'"]+)[\'"]/i', $item->content, $m)) {
                        $overviewImg = $m[1];
                    }
                }

                // Nếu không có trong content, tìm trong kho phối cảnh (ProjectPerspective)
                if (empty($overviewImg)) {
                    $persp = ProjectPerspective::where('project_id', $item->id)
                        ->where(function($q) {
                            $q->where('name', 'like', '%tong the%')
                              ->orWhere('name', 'like', '%tổng thể%')
                              ->orWhere('name', 'like', '%tong quan%')
                              ->orWhere('name', 'like', '%tổng quan%');
                        })->first();
                    if (!$persp) {
                        $persp = ProjectPerspective::where('project_id', $item->id)->first();
                    }
                    if ($persp) {
                        $overviewImg = $persp->image_url;
                    }
                }

                // Fallback nếu có img trong bài viết
                if (empty($overviewImg) && !empty($item->img)) {
                    if (str_starts_with($item->img, 'http://') || str_starts_with($item->img, 'https://')) {
                        $overviewImg = $item->img;
                    } else {
                        $overviewImg = 'https://indochinerealestate.vn/uploads/' . $item->img;
                    }
                }

                $item->overview_image = $overviewImg;

                // 2. Trích xuất bảng thông tin tổng quan (Overview Table)
                $tableData = [];
                if (!empty($item->content)) {
                    preg_match_all('/<tr>\s*<td>(.*?)<\/td>\s*<td>(.*?)<\/td>\s*<\/tr>/is', $item->content, $rows, PREG_SET_ORDER);
                    foreach ($rows as $r) {
                        $rawKey = trim(strip_tags(html_entity_decode($r[1])));
                        $val = trim(strip_tags(html_entity_decode($r[2])));
                        $cleanKey = trim(str_replace([':', "\xc2\xa0", '&nbsp;'], '', $rawKey));
                        if ($cleanKey && $val) {
                            $tableData[$cleanKey] = $val;
                        }
                    }
                }

                $item->overview_table = $tableData;
                $item->developer = $tableData['Đơn vị phát triển'] ?? $tableData['Chủ đầu tư'] ?? 'MIK Group';
                $item->overview_scale = $tableData['Quy mô'] ?? ($item->total_product ? number_format($item->total_product) . ' căn' : '6 tòa (30 - 35 tầng)');
                $item->unit_types_text = $tableData['Loại hình'] ?? 'Studio, 1 - 3 PN';
                $item->legal_status = $tableData['Pháp lý'] ?? 'Sở hữu lâu dài';
                $item->location_detail = $tableData['Vị trí'] ?? ($item->address ?: 'Mặt đường Đại lộ Thăng Long, Nam An Khánh, Hoài Đức, Hà Nội');

                return $item;
            })->values();

            $totalProjectsCount = $projects->count();

        } catch (Exception $e) {
            $dbError = $e->getMessage();
            Log::warning('FrontendController: Không thể nạp bảng posts từ DB dùng chung: ' . $dbError);
        }

        // Dự án đang chọn cho mục Sơ đồ chỉ căn (Ưu tiên dự án được yêu cầu hoặc dự án đầu tiên có chỉ căn như 826)
        $firstProjectWithUnitsId = !empty($locatorCounts) ? (int) array_key_first($locatorCounts) : 826;
        $activeProjectId = $requestedProjectId > 0 ? $requestedProjectId : $firstProjectWithUnitsId;

        // Lấy thông tin dự án đang chọn
        $activeProject = $projects->firstWhere('id', $activeProjectId);
        if (!$activeProject) {
            try {
                $activeProject = SharedDatabase::table('posts')->where('id', $activeProjectId)->first();
            } catch (Exception $e) {
                $activeProject = null;
            }
        }

        if (!$activeProject) {
            $firstLocator = ProjectUnitLocator::where('project_id', $activeProjectId)->first();
            $projectName = $firstLocator ? $firstLocator->project_name : 'Dự án BĐS';
            $activeProject = (object) [
                'id' => $activeProjectId,
                'name' => $projectName,
                'address' => 'Hà Nội',
                'description' => 'Dự án bất động sản cao cấp',
                'img' => null,
                'total_product' => 3000,
                'unit_locator_count' => $locatorCounts[$activeProjectId] ?? 0,
            ];
        }

        // 3. Nạp danh sách chỉ căn của dự án đang chọn
        $units = ProjectUnitLocator::where('project_id', $activeProjectId)
            ->orderBy('floor')
            ->orderBy('unit_code')
            ->get();

        // 4. Nạp mặt bằng và phối cảnh
        $floorplans = ProjectFloorplan::where('project_id', $activeProjectId)->get();
        $perspectives = ProjectPerspective::where('project_id', $activeProjectId)->get();
        $templates = ProjectTemplate::where('project_id', $activeProjectId)->get();

        // 5. Thống kê trạng thái căn hộ của dự án này
        $unitStats = [
            'total' => $units->count(),
            'available' => $units->where('status', 'available')->count(),
            'reserved' => $units->whereIn('status', ['reserved', 'deposited'])->count(),
            'sold' => $units->where('status', 'sold')->count(),
            'locked' => $units->where('status', 'locked')->count(),
        ];

        // 6. Danh sách blocks, tầng, loại căn, trục căn để làm bộ lọc
        $blocks = $units->pluck('block')->filter()->unique()->values();
        $floors = $units->pluck('floor')->filter()->unique()->values();
        $unitTypes = $units->pluck('unit_type')->filter()->unique()->values();
        $axes = $units->pluck('unit_axis')->filter()->unique()->sort()->values();

        // 7. Tin tức & Cẩm nang Bất động sản
        $articles = $this->getSampleArticles();

        return view('frontend.index', compact(
            'projects',
            'totalProjectsCount',
            'activeProject',
            'activeProjectId',
            'units',
            'unitStats',
            'blocks',
            'floors',
            'unitTypes',
            'axes',
            'floorplans',
            'perspectives',
            'templates',
            'articles',
            'activeMenu',
            'dbError'
        ));
    }

    /**
     * API AJAX lấy danh sách căn hộ theo ID dự án
     */
    public function getProjectUnits(Request $request, $projectId)
    {
        $projectId = (int) $projectId;

        $project = null;
        try {
            $project = SharedDatabase::table('posts')->where('id', $projectId)->first();
        } catch (Exception $e) {
            // bỏ qua
        }

        $units = ProjectUnitLocator::where('project_id', $projectId)
            ->orderBy('floor')
            ->orderBy('unit_code')
            ->get();

        $floorplans = ProjectFloorplan::where('project_id', $projectId)->get();
        $perspectives = ProjectPerspective::where('project_id', $projectId)->get();
        $templates = ProjectTemplate::where('project_id', $projectId)->get();

        $stats = [
            'total' => $units->count(),
            'available' => $units->where('status', 'available')->count(),
            'reserved' => $units->whereIn('status', ['reserved', 'deposited'])->count(),
            'sold' => $units->where('status', 'sold')->count(),
            'locked' => $units->where('status', 'locked')->count(),
        ];

        $blocks = $units->pluck('block')->filter()->unique()->values();
        $floors = $units->pluck('floor')->filter()->unique()->values();
        $unitTypes = $units->pluck('unit_type')->filter()->unique()->values();
        $axes = $units->pluck('unit_axis')->filter()->unique()->sort()->values();

        return response()->json([
            'success' => true,
            'project' => $project,
            'units' => $units,
            'floorplans' => $floorplans,
            'perspectives' => $perspectives,
            'templates' => $templates,
            'stats' => $stats,
            'blocks' => $blocks,
            'floors' => $floors,
            'unitTypes' => $unitTypes,
            'axes' => $axes,
        ]);
    }

    /**
     * Tiếp nhận yêu cầu tư vấn & báo giá căn hộ từ khách hàng
     */
    public function submitConsultation(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:20',
            'email' => 'nullable|email|max:100',
            'project_name' => 'nullable|string|max:150',
            'unit_code' => 'nullable|string|max:50',
            'note' => 'nullable|string|max:500',
        ], [
            'name.required' => 'Vui lòng nhập họ và tên của bạn.',
            'phone.required' => 'Vui lòng nhập số điện thoại liên hệ.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
        ]);

        // Ghi log hoặc gửi thông báo
        Log::info('Yêu cầu tư vấn BĐS mới:', $data);

        return response()->json([
            'success' => true,
            'message' => 'Cảm ơn quý khách! Chuyên viên tư vấn IDC đã tiếp nhận thông tin và sẽ liên hệ hỗ trợ bạn trong ít phút.',
            'data' => $data,
        ]);
    }

    /**
     * Cập nhật thông tin tài khoản người dùng
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện.'], 401);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
        ], [
            'name.required' => 'Tên không được để trống.',
            'email.required' => 'Email không được để trống.',
            'email.unique' => 'Địa chỉ email này đã có người sử dụng.',
        ]);

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Cập nhật thông tin tài khoản thành công!',
                'user' => [
                    'name' => $user->name,
                    'email' => $user->email,
                ]
            ]);
        }

        return back()->with('success', 'Cập nhật thông tin tài khoản thành công!');
    }

    /**
     * Đổi mật khẩu tài khoản
     */
    public function changePassword(Request $request)
    {
        $user = Auth::user();
        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Vui lòng đăng nhập để thực hiện.'], 401);
        }

        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'new_password.required' => 'Vui lòng nhập mật khẩu mới.',
            'new_password.min' => 'Mật khẩu mới phải có ít nhất 6 ký tự.',
            'new_password.confirmed' => 'Xác nhận mật khẩu mới không trùng khớp.',
        ]);

        if (!Hash::check($request->current_password, $user->password)) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mật khẩu hiện tại không chính xác.',
                ], 422);
            }
            return back()->withErrors(['current_password' => 'Mật khẩu hiện tại không chính xác.']);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Đổi mật khẩu thành công! Vui lòng bảo mật mật khẩu mới.',
            ]);
        }

        return back()->with('success', 'Đổi mật khẩu thành công!');
    }

    /**
     * Dữ liệu tin tức và cẩm nang mẫu phong phú cho khách hàng
     */
    private function getSampleArticles()
    {
        return [
            [
                'id' => 1,
                'title' => 'Cẩm nang chọn căn hộ tầng cao hay tầng trung: Lợi ích & Phong thủy',
                'category' => 'Tư vấn Mua nhà',
                'date' => '15/11/2026',
                'read_time' => '4 phút đọc',
                'summary' => 'Phân tích chi tiết ưu nhược điểm giữa căn hộ tầng trung (tầng 10-20) và tầng cao (trên tầng 25) về không khí trong lành, tầm view thoáng đãng và giá trị thanh khoản.',
                'tag' => 'Phong thủy',
                'image' => 'https://images.unsplash.com/photo-1545324418-cc1a3fa10c00?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 2,
                'title' => 'Chính sách ưu đãi lãi suất vay mua nhà mới nhất quý 4/2026',
                'category' => 'Tài chính & Ngân hàng',
                'date' => '12/11/2026',
                'read_time' => '5 phút đọc',
                'summary' => 'Cập nhật gói hỗ trợ lãi suất 0% trong 24 tháng, ân hạn nợ gốc từ các ngân hàng đối tác chiến lược BIDV, Vietcombank, Techcombank dành cho dự án Imperia Sky Park.',
                'tag' => 'Lãi suất 0%',
                'image' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 3,
                'title' => 'Quy hoạch hạ tầng phía Tây Hà Nội: Tuyến Metro và bước ngoặt tăng giá BĐS',
                'category' => 'Thị trường & Quy hoạch',
                'date' => '08/11/2026',
                'read_time' => '6 phút đọc',
                'summary' => 'Sự bứt phá của trục Đại lộ Thăng Long và tuyến đường sắt đô thị Metro số 5 đang thúc đẩy thị trường căn hộ khu vực Nam An Khánh trở thành điểm đến an cư lý tưởng.',
                'tag' => 'Hạ tầng Metro',
                'image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=600&auto=format&fit=crop&q=80',
            ],
            [
                'id' => 4,
                'title' => '5 tiêu chí vàng đánh giá chất lượng thiết kế căn hộ 2 phòng ngủ',
                'category' => 'Thiết kế Nội thất',
                'date' => '02/11/2026',
                'read_time' => '3 phút đọc',
                'summary' => 'Cách nhận biết mặt bằng thông minh: tối ưu diện tích sử dụng, phòng khách liên thông ban công, tất cả phòng ngủ đều đón ánh sáng tự nhiên không bị góc chết.',
                'tag' => 'Thiết kế',
                'image' => 'https://images.unsplash.com/photo-1502672260266-1c1ef2d93688?w=600&auto=format&fit=crop&q=80',
            ],
        ];
    }
}
