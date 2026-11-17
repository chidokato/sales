<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('project_floorplans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index()->comment('ID dự án từ bảng posts');
            $table->string('name')->comment('Tên mặt bằng: ví dụ Mặt bằng Tầng 3-10 Tòa A');
            $table->string('image')->comment('Đường dẫn ảnh mặt bằng');
            $table->string('block')->nullable()->comment('Tòa / Tháp');
            $table->string('floor_range')->nullable()->comment('Khoảng tầng: ví dụ Tầng 5-15');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('project_floorplans');
    }
};
