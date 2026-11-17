<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectTemplate extends Model
{
    use HasFactory;

    protected $table = 'project_templates';

    protected $fillable = [
        'project_id',
        'name',
        'image',
        'width',
        'height',
        'slots_config',
        'is_default',
        'description',
    ];

    protected $casts = [
        'slots_config' => 'array',
        'width' => 'integer',
        'height' => 'integer',
        'is_default' => 'boolean',
    ];

    protected $appends = [
        'image_url',
    ];

    public function getImageUrlAttribute(): string
    {
        if (empty($this->image)) {
            return asset('images/placeholder-floorplan.svg');
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return route('admin.unit-locators.media', ['path' => $this->image]);
    }

    /**
     * Cấu hình tọa độ mẫu chuẩn (mặc định) dựa theo Poster BĐS 6 khung ảnh
     */
    public static function getDefaultSlotsConfig(): array
    {
        return [
            'canvas' => [
                'width' => 1000,
                'height' => 1400,
                'bgColor' => '#451a03',
            ],
            'image_slots' => [
                [
                    'id' => 'slot_1',
                    'key' => 'boc_mai',
                    'name' => '1. Mặt bằng bóc mái 3D',
                    'x' => 650,
                    'y' => 160,
                    'width' => 280,
                    'height' => 340,
                    'radius' => 0,
                    'border' => 'none',
                    'borderWidth' => 0,
                ],
                [
                    'id' => 'slot_2',
                    'key' => 'mat_bang_tang',
                    'name' => '2. Mặt bằng tầng chỉ căn',
                    'x' => 630,
                    'y' => 525,
                    'width' => 330,
                    'height' => 240,
                    'radius' => 0,
                    'border' => 'none',
                    'borderWidth' => 0,
                ],
                [
                    'id' => 'slot_3',
                    'key' => 'phoi_canh_toa',
                    'name' => '3. Phối cảnh tổng thể & tòa',
                    'x' => 60,
                    'y' => 380,
                    'width' => 540,
                    'height' => 210,
                    'radius' => 12,
                    'border' => 'none',
                    'borderWidth' => 0,
                ],
                [
                    'id' => 'slot_4',
                    'key' => 'tien_ich_1',
                    'name' => '4. Phối cảnh tiện ích',
                    'x' => 60,
                    'y' => 610,
                    'width' => 260,
                    'height' => 230,
                    'radius' => 12,
                    'border' => 'none',
                    'borderWidth' => 0,
                ],
                [
                    'id' => 'slot_5',
                    'key' => 'phan_khu',
                    'name' => '5. Mặt bằng phân khu / tiện ích',
                    'x' => 340,
                    'y' => 610,
                    'width' => 260,
                    'height' => 230,
                    'radius' => 12,
                    'border' => 'none',
                    'borderWidth' => 0,
                ],
                [
                    'id' => 'slot_6',
                    'key' => 'phoi_canh_dem',
                    'name' => '6. Phối cảnh tòa đêm lung linh',
                    'x' => 200,
                    'y' => 860,
                    'width' => 760,
                    'height' => 240,
                    'radius' => 12,
                    'border' => 'none',
                    'borderWidth' => 0,
                ],
            ],
            'text_slots' => [
                'unit_code' => [
                    'label' => 'Mã căn',
                    'x' => 60,
                    'y' => 45,
                    'width' => 320,
                    'height' => 50,
                    'fontSize' => 26,
                    'fontWeight' => 'bold',
                    'color' => '#ffffff',
                    'bgColor' => 'rgba(0,0,0,0.4)',
                    'borderColor' => '#d97706',
                ],
                'unit_type' => [
                    'label' => 'Loại căn (2PN)',
                    'x' => 395,
                    'y' => 45,
                    'width' => 160,
                    'height' => 50,
                    'fontSize' => 24,
                    'fontWeight' => 'bold',
                    'color' => '#78350f',
                    'bgColor' => '#fbbf24',
                    'borderColor' => 'transparent',
                ],
                'direction' => [
                    'label' => 'Hướng',
                    'x' => 60,
                    'y' => 120,
                    'fontSize' => 20,
                    'color' => '#ffffff',
                    'prefix' => 'Hướng: ',
                ],
                'view' => [
                    'label' => 'View',
                    'x' => 60,
                    'y' => 155,
                    'fontSize' => 20,
                    'color' => '#ffffff',
                    'prefix' => 'View: ',
                ],
                'area' => [
                    'label' => 'Diện tích thông thủy',
                    'x' => 60,
                    'y' => 190,
                    'fontSize' => 20,
                    'color' => '#ffffff',
                    'prefix' => 'Diện tích thông thủy: ',
                    'suffix' => ' m²',
                ],
                'price_note' => [
                    'label' => 'Ghi chú giá',
                    'x' => 280,
                    'y' => 245,
                    'fontSize' => 14,
                    'color' => '#e2e8f0',
                    'align' => 'center',
                ],
                'price' => [
                    'label' => 'Giá bán',
                    'x' => 280,
                    'y' => 285,
                    'fontSize' => 44,
                    'fontWeight' => 'bold',
                    'color' => '#ffffff',
                    'fontFamily' => 'serif',
                    'align' => 'center',
                ],
            ]
        ];
    }
}
