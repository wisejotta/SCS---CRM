<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('retainers', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid');
            $table->timestamp('signed_at')->nullable()->default(null);
            $table->string('file');

            $table->unsignedBigInteger('lead_id')->nullable()->default(null);
            $table->foreign('lead_id')->references('id')->on('leads');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('retainers');
    }
};
