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
        Schema::create('project_inventory_configs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('project_id')->index();
            $table->text('sheet_url')->nullable();
            $table->string('sheet_gid')->nullable();
            $table->string('sheet_tab_name')->nullable();
            $table->text('csbh_url')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('auto_sync_interval')->default('3m');
            $table->timestamp('last_synced_at')->nullable();
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
        Schema::dropIfExists('project_inventory_configs');
    }
};
