<?php

namespace App\Http\Controllers;

use App\Models\SharedDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use PDO;
use Exception;

class SharedDatabaseController extends Controller
{
    /**
     * Display a listing of the shared databases.
     */
    public function index()
    {
        $databases = SharedDatabase::latest()->get();
        return view('admin.databases.index', compact('databases'));
    }

    /**
     * Store a newly created shared database.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|numeric',
            'database' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string',
            'shared_tables' => 'required',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        // Xử lý danh sách bảng dùng chung
        if (is_string($validated['shared_tables'])) {
            $tables = array_filter(array_map('trim', explode(',', $validated['shared_tables'])));
        } else {
            $tables = (array) $validated['shared_tables'];
        }

        $validated['shared_tables'] = array_values(array_unique($tables));
        $validated['connection_key'] = 'shared_' . Str::slug($validated['database']) . '_' . Str::random(4);
        $validated['is_active'] = $request->has('is_active') ? true : false;

        SharedDatabase::create($validated);

        return redirect()->route('admin.databases.index')
            ->with('success', 'Đã thêm cấu hình Cơ sở dữ liệu dùng chung thành công!');
    }

    /**
     * Update the specified shared database.
     */
    public function update(Request $request, $id)
    {
        $sharedDb = SharedDatabase::findOrFail($id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'host' => 'required|string|max:255',
            'port' => 'required|numeric',
            'database' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string',
            'shared_tables' => 'required',
            'is_active' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        if (is_string($validated['shared_tables'])) {
            $tables = array_filter(array_map('trim', explode(',', $validated['shared_tables'])));
        } else {
            $tables = (array) $validated['shared_tables'];
        }

        $validated['shared_tables'] = array_values(array_unique($tables));
        $validated['is_active'] = $request->has('is_active') ? true : false;

        $sharedDb->update($validated);

        return redirect()->route('admin.databases.index')
            ->with('success', 'Đã cập nhật cấu hình dùng chung thành công!');
    }

    /**
     * Remove the specified shared database.
     */
    public function destroy($id)
    {
        $sharedDb = SharedDatabase::findOrFail($id);
        $sharedDb->delete();

        return redirect()->route('admin.databases.index')
            ->with('success', 'Đã xóa cấu hình Cơ sở dữ liệu dùng chung!');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus($id)
    {
        $sharedDb = SharedDatabase::findOrFail($id);
        $sharedDb->is_active = !$sharedDb->is_active;
        $sharedDb->save();

        $statusText = $sharedDb->is_active ? 'Đã kích hoạt' : 'Đã tạm dừng';
        return back()->with('success', "{$statusText} cấu hình dùng chung '{$sharedDb->name}'!");
    }

    /**
     * Test connection via AJAX.
     */
    public function testConnection(Request $request)
    {
        $host = $request->input('host', '127.0.0.1');
        $port = $request->input('port', '3306');
        $database = $request->input('database');
        $username = $request->input('username', 'root');
        $password = $request->input('password', '');

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Kết nối thành công đến database '{$database}'!",
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi kết nối: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Fetch all tables from remote/external DB via AJAX.
     */
    public function fetchTables(Request $request)
    {
        $host = $request->input('host', '127.0.0.1');
        $port = $request->input('port', '3306');
        $database = $request->input('database');
        $username = $request->input('username', 'root');
        $password = $request->input('password', '');

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$database};charset=utf8mb4";
            $pdo = new PDO($dsn, $username, $password ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3,
            ]);

            $stmt = $pdo->query('SHOW TABLES');
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return response()->json([
                'success' => true,
                'tables' => $tables,
                'message' => 'Lấy danh sách bảng thành công!',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Lỗi khi đọc bảng: ' . $e->getMessage(),
                'tables' => [],
            ], 422);
        }
    }
}
