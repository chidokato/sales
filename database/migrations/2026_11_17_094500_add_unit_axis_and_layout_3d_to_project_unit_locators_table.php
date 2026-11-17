<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('project_unit_locators', function (Blueprint $table) {
            $table->string('unit_axis', 50)->nullable()->after('floor')->comment('Trục căn: ví dụ 05, 06, 08A...');
            $table->string('layout_3d_image')->nullable()->after('image')->comment('Đường dẫn ảnh bóc mái 3D');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('project_unit_locators', function (Blueprint $table) {
            $table->dropColumn(['unit_axis', 'layout_3d_image']);
        });
    }
};
