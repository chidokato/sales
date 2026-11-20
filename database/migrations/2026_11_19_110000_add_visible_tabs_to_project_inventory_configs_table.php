<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('project_inventory_configs', function (Blueprint $table) {
            $table->json('visible_tabs')->nullable()->after('sheet_tab_name');
        });
    }

    public function down()
    {
        Schema::table('project_inventory_configs', function (Blueprint $table) {
            $table->dropColumn('visible_tabs');
        });
    }
};
