<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectPerspective extends Model
{
    use HasFactory;

    protected $table = 'project_perspectives';

    protected $fillable = [
        'project_id',
        'name',
        'category',
        'image',
        'description',
        'sort_order',
    ];

    protected $appends = [
        'image_url',
        'category_label',
    ];

    public function getImageUrlAttribute()
    {
        if (empty($this->image)) {
            return asset('images/placeholder-floorplan.svg');
        }

        if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
            return $this->image;
        }

        return route('admin.unit-locators.media', ['path' => $this->image]);
    }

    public function getCategoryLabelAttribute()
    {
        return match ($this->category) {
            'template' => 'Phôi ảnh chỉ căn',
            'layout_3d' => 'Mặt bằng bóc mái 3D',
            'perspective' => 'Phối cảnh tòa / tổng thể',
            'amenity' => 'Phối cảnh tiện ích',
            default => 'Ảnh phối cảnh khác',
        };
    }
}
