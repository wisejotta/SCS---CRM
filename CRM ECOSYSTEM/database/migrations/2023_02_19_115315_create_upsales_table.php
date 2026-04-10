<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('upsales', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('lead_id')->nullable()->default(null);
            $table->foreign('lead_id')->references('id')->on('leads');

            $table->unsignedBigInteger('upsale_option_id')->nullable();
            $table->foreign('upsale_option_id')->references('id')->on('upsale_options');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('upsales');
    }
};
