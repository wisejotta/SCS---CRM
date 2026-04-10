<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_responses', function (Blueprint $table) {
            $table->unsignedBigInteger('agent_id')->nullable()->default(null);
            $table->foreign('agent_id')->references('id')->on('agents');
        });
    }

    public function down()
    {
        Schema::table('payment_responses', function (Blueprint $table) {
            $table->dropColumn('agent_id');
        });
    }
};
