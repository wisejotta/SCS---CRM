<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('payment_responses', function (Blueprint $table) {
            $table->unsignedBigInteger('payment_id')->nullable()->default(null)->change();
        });
    }

    public function down()
    {
        Schema::table('payment_responses', function (Blueprint $table) { });
    }
};
