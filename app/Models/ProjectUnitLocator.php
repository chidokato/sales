<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\SharedDatabase;

class ProjectUnitLocator extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'project_name',
        'unit_code',
        'unit_type',
        'unit_axis',
        'block',
        'floor',
        'area',
        'area_gross',
        'direction',
        'door_direction',
        'view',
        'bedroom',
        'bathroom',
        'price',
        'price_text',
        'price_note',
        'status',
        'image',
        'layout_3d_image',
        'base_image',
        'highlight_data',
        'composer_data',
        'note',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'highlight_data' => 'array',
        'composer_data' => 'array',
        'area' => 'float',
        'area_gross' => 'float',
        'price' => 'float',
        'bedroom' => 'integer',
        'bathroom' => 'integer',
        'sort_order' => 'integer',
    ];

    protected $appends = [
        'image_url',
        'layout_3d_image_url',
        'base_image_url',
        'status_info',
        'display_price',
    ];

    /**
     * URL ảnh chỉ căn
     */
    public function getImageUrlAttribute(): string
    {
        if (empty($this->image)) {
            try {
                return route('admin.unit-locators.project-image', 'default.jpg');
            } catch (\Exception $e) {
                return '';
            }
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        try {
            return route('admin.unit-locators.media', ['path' => $this->image]);
        } catch (\Exception $e) {
            return asset('storage/' . $this->image);
        }
    }

    /**
     * URL ảnh bóc mái 3D
     */
    public function getLayout3dImageUrlAttribute(): ?string
    {
        if (empty($this->layout_3d_image)) {
            return null;
        }

        if (str_starts_with($this->layout_3d_image, 'http://') || str_starts_with($this->layout_3d_image, 'https://')) {
            return $this->layout_3d_image;
        }

        try {
            return route('admin.unit-locators.media', ['path' => $this->layout_3d_image]);
        } catch (\Exception $e) {
            return asset('storage/' . $this->layout_3d_image);
        }
    }

    /**
     * URL ảnh gốc mặt bằng
     */
    public function getBaseImageUrlAttribute(): ?string
    {
        if (empty($this->base_image)) {
            return null;
        }

        if (str_starts_with($this->base_image, 'http://') || str_starts_with($this->base_image, 'https://')) {
            return $this->base_image;
        }

        try {
            return route('admin.unit-locators.media', ['path' => $this->base_image]);
        } catch (\Exception $e) {
            return asset('storage/' . $this->base_image);
        }
    }

    /**
     * Nhãn và màu sắc trạng thái
     */
    public function getStatusInfoAttribute(): array
    {
        return match ($this->status) {
            'available' => [
                'label' => 'Còn trống',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-500/20',
                'dot' => 'bg-emerald-500',
            ],
            'reserved' => [
                'label' => 'Đang giữ chỗ',
                'class' => 'bg-amber-50 text-amber-700 border-amber-200 ring-amber-500/20',
                'dot' => 'bg-amber-500',
            ],
            'deposited' => [
                'label' => 'Đã đặt cọc',
                'class' => 'bg-purple-50 text-purple-700 border-purple-200 ring-purple-500/20',
                'dot' => 'bg-purple-500',
            ],
            'sold' => [
                'label' => 'Đã bán',
                'class' => 'bg-rose-50 text-rose-700 border-rose-200 ring-rose-500/20',
                'dot' => 'bg-rose-500',
            ],
            'locked' => [
                'label' => 'Tạm khóa',
                'class' => 'bg-slate-100 text-slate-700 border-slate-300 ring-slate-500/20',
                'dot' => 'bg-slate-400',
            ],
            default => [
                'label' => 'Còn trống',
                'class' => 'bg-emerald-50 text-emerald-700 border-emerald-200 ring-emerald-500/20',
                'dot' => 'bg-emerald-500',
            ]
        };
    }

    /**
     * Hiển thị giá đẹp
     */
    public function getDisplayPriceAttribute(): string
    {
        if (!empty($this->price_text)) {
            return $this->price_text;
        }

        if ($this->price && $this->price > 0) {
            if ($this->price >= 1000000000) {
                return number_format($this->price / 1000000000, 2, ',', '.') . ' Tỷ';
            }
            if ($this->price >= 1000000) {
                return number_format($this->price / 1000000, 0, ',', '.') . ' Triệu';
            }
            return number_format($this->price, 0, ',', '.') . ' đ';
        }

        return 'Thỏa thuận';
    }

    /**
     * Lấy dữ liệu dự án từ DB dùng chung (bảng posts)
     */
    public function getProjectData()
    {
        try {
            return SharedDatabase::table('posts')->where('id', $this->project_id)->first();
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Người tạo
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Tự động trích xuất trục căn từ mã căn (ví dụ: S2.15A08A -> 08A, S1.3202 -> 02, S3.0506 -> 06)
     */
    public static function extractAxisFromCode(?string $unitCode, ?string $block = null, ?string $floor = null): ?string
    {
        if (empty($unitCode)) {
            return null;
        }

        $code = trim($unitCode);
        if (str_contains($code, '.')) {
            $parts = explode('.', $code, 2);
            $codeAfterDot = $parts[1];
        } else {
            $codeAfterDot = $code;
            if ($block && str_starts_with(strtoupper($codeAfterDot), strtoupper(trim($block)))) {
                $codeAfterDot = substr($codeAfterDot, strlen(trim($block)));
            }
        }

        $codeAfterDot = trim($codeAfterDot, '-_ ');

        if (!empty($floor)) {
            $f = trim($floor);
            if (str_starts_with(strtoupper($codeAfterDot), strtoupper($f))) {
                return substr($codeAfterDot, strlen($f));
            }
            $fPadded = str_pad($f, 2, '0', STR_PAD_LEFT);
            if (str_starts_with(strtoupper($codeAfterDot), strtoupper($fPadded))) {
                return substr($codeAfterDot, strlen($fPadded));
            }
            $fUnpadded = ltrim($f, '0');
            if (!empty($fUnpadded) && str_starts_with(strtoupper($codeAfterDot), strtoupper($fUnpadded))) {
                return substr($codeAfterDot, strlen($fUnpadded));
            }
        }

        if (preg_match('/([0-9]{2}[A-Za-z]?)$/', $codeAfterDot, $m)) {
            return $m[1];
        }

        return null;
    }
}
