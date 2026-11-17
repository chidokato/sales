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
}
