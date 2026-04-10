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
        Schema::table('retainers', function (Blueprint $table) {
            $table->unsignedBigInteger('visa_id')->default(1);
            $table->foreign('visa_id')->references('id')->on('visas');
            
            $table->string('results')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('retainers', function (Blueprint $table) {
            $table->dropForeign(['visa_id']);
            $table->dropColumn('visa_id');
            $table->dropColumn('results');
        });
    }
};
