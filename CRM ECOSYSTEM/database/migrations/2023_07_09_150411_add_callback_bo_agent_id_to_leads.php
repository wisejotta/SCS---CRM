<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedBigInteger('callback_bo_agent_id')->nullable()->default(null);
            $table->foreign('callback_bo_agent_id')->references('id')->on('agents');
            $table->timestamp('bo_callback')->nullable()->default(null);
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['callback_bo_agent_id']);
            $table->dropColumn('callback_bo_agent_id');
            $table->dropColumn('bo_callback');
        });
    }
};
