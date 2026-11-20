<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProjectInventoryConfig extends Model
{
    use HasFactory;

    protected $table = 'project_inventory_configs';

    protected $fillable = [
        'project_id',
        'sheet_url',
        'sheet_gid',
        'sheet_tab_name',
        'visible_tabs',
        'csbh_url',
        'is_active',
        'auto_sync_interval',
        'last_synced_at',
        'notes'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'visible_tabs' => 'array',
        'last_synced_at' => 'datetime'
    ];
}
