<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use PDO;
use Exception;

class SharedDatabase extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'connection_key',
        'host',
        'port',
        'database',
        'username',
        'password',
        'shared_tables',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'shared_tables' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Đăng ký cấu hình connection động vào hệ thống Laravel tại runtime
     */
    public function registerConnection(): string
    {
        $connName = $this->connection_key;

        config([
            "database.connections.{$connName}" => [
                'driver' => 'mysql',
                'host' => $this->host ?: '127.0.0.1',
                'port' => $this->port ?: '3306',
                'database' => $this->database,
                'username' => $this->username ?: 'root',
                'password' => $this->password ?: '',
                'charset' => 'utf8mb4',
                'collation' => 'utf8mb4_unicode_ci',
                'prefix' => '',
                'strict' => false,
                'engine' => null,
            ]
        ]);

        return $connName;
    }

    /**
     * Lấy Query Builder cho 1 bảng cụ thể trong database này
     */
    public function queryTable(string $table)
    {
        $conn = $this->registerConnection();
        return DB::connection($conn)->table($table);
    }

    /**
     * Thử kết nối PDO trực tiếp
     */
    public function testConnection(): array
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";
            $pdo = new PDO($dsn, $this->username, $this->password ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_TIMEOUT => 3,
            ]);

            return [
                'success' => true,
                'message' => 'Kết nối cơ sở dữ liệu thành công!',
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => 'Kết nối thất bại: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Lấy toàn bộ danh sách bảng thực tế trong Database này
     */
    public function fetchAllTables(): array
    {
        try {
            $dsn = "mysql:host={$this->host};port={$this->port};dbname={$this->database};charset=utf8mb4";
            $pdo = new PDO($dsn, $this->username, $this->password ?? '', [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $stmt = $pdo->query('SHOW TABLES');
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

            return [
                'success' => true,
                'tables' => $tables,
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
                'tables' => [],
            ];
        }
    }

    /**
     * Static Helper: Tự động tìm Database nào chứa bảng $tableName và trả về Query Builder
     * Cách dùng: SharedDatabase::table('du_an')->where('trang_thai', 1)->get();
     */
    public static function table(string $tableName)
    {
        $activeDbs = self::where('is_active', true)->get();

        foreach ($activeDbs as $sharedDb) {
            $tables = $sharedDb->shared_tables ?? [];
            if (in_array($tableName, $tables)) {
                return $sharedDb->queryTable($tableName);
            }
        }

        throw new Exception("Bảng '{$tableName}' chưa được định nghĩa trong bất kỳ Cơ sở dữ liệu dùng chung nào!");
    }
}
