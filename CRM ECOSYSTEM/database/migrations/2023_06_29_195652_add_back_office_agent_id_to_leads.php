<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedBigInteger('bo_agent_id')->nullable()->default(null);
            $table->foreign('bo_agent_id')->references('id')->on('agents');
        });
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['bo_agent_id']);
            $table->dropColumn('bo_agent_id');
        });
    }
};
