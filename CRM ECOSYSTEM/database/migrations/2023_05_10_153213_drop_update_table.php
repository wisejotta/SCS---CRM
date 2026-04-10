<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('upsales', function (Blueprint $table) {
            $table->dropForeign(['lead_id']);
            $table->dropForeign(['upsale_option_id']);
        });

        Schema::dropIfExists('upsale_options');
        Schema::dropIfExists('upsales');
    }

    public function down()
    {
        
    }
};
