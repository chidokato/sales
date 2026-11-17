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
        Schema::create('shared_databases', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Tên gợi nhớ: vd "Web Bất Động Sản IDC"
            $table->string('connection_key')->unique(); // vd "shared_bds"
            $table->string('host')->default('127.0.0.1');
            $table->string('port')->default('3306');
            $table->string('database'); // Tên database: vd "idc_bds"
            $table->string('username')->default('root');
            $table->string('password')->nullable();
            $table->json('shared_tables'); // Danh sách bảng chỉ định: ["du_an", "tin_tuc"]
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
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
        Schema::dropIfExists('shared_databases');
    }
};
