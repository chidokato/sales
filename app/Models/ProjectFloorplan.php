<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use App\Models\SharedDatabase;

class ProjectFloorplan extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'name',
        'image',
        'block',
        'floor_range',
        'description',
    ];

    protected $appends = [
        'image_url',
    ];

    /**
     * URL ảnh mặt bằng
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
}
