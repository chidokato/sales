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
        Schema::create('project_unit_locators', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index()->comment('ID dự án từ bảng posts của DB dùng chung');
            $table->string('project_name')->nullable()->comment('Tên dự án được snapshot lại');
            $table->string('unit_code')->index()->comment('Mã căn hộ: ví dụ CH-01, A12.05');
            $table->string('block')->nullable()->comment('Tòa / Phân khu / Tháp: ví dụ Tòa A, Tháp Diamond');
            $table->string('floor')->nullable()->comment('Tầng: ví dụ Tầng 12, 12A');
            $table->decimal('area', 8, 2)->nullable()->comment('Diện tích sử dụng / thông thủy (m2)');
            $table->decimal('area_gross', 8, 2)->nullable()->comment('Diện tích tim tường / tổng thể (m2)');
            $table->string('direction')->nullable()->comment('Hướng chính / ban công: Đông, Tây, Nam, Bắc, Đông Nam...');
            $table->string('door_direction')->nullable()->comment('Hướng cửa chính');
            $table->unsignedTinyInteger('bedroom')->nullable()->comment('Số phòng ngủ');
            $table->unsignedTinyInteger('bathroom')->nullable()->comment('Số phòng vệ sinh');
            $table->decimal('price', 15, 2)->nullable()->comment('Giá bán dự kiến (VNĐ)');
            $table->string('price_text')->nullable()->comment('Giá hiển thị: ví dụ 3.5 Tỷ, 45 tr/m2');
            $table->string('status', 30)->default('available')->comment('Trạng thái: available, reserved, deposited, sold, locked');
            $table->string('image')->comment('Đường dẫn ảnh chỉ căn');
            $table->string('base_image')->nullable()->comment('Ảnh gốc mặt bằng tầng (nếu có)');
            $table->longText('highlight_data')->nullable()->comment('Tọa độ điểm ghim, màu sắc, marker JSON');
            $table->text('note')->nullable()->comment('Ghi chú, tiện ích, view, điểm nổi bật');
            $table->integer('sort_order')->default(0);
            $table->unsignedBigInteger('created_by')->nullable();
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
        Schema::dropIfExists('project_unit_locators');
    }
};
