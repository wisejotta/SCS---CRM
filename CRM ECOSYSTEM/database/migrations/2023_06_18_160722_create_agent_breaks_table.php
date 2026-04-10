<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('agent_breaks', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('agent_id')->nullable()->default(null);
            $table->foreign('agent_id')->references('id')->on('agents');

            $table->enum('type', [
                'cigarettes',
                'toilet',
                'lunch',
                'meeting',
                'training',
            ]);
            $table->integer('seconds')->nullable()->default(null);
            $table->boolean('active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('agent_breaks');
    }
};
