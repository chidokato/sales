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
        Schema::table('project_unit_locators', function (Blueprint $table) {
            $table->string('view')->nullable()->after('direction');
            $table->string('unit_type')->nullable()->after('unit_code');
            $table->string('price_note')->nullable()->after('price_text');
            $table->json('composer_data')->nullable()->after('highlight_data');
        });
    }

    public function down()
    {
        Schema::table('project_unit_locators', function (Blueprint $table) {
            $table->dropColumn(['view', 'unit_type', 'price_note', 'composer_data']);
        });
    }
};
