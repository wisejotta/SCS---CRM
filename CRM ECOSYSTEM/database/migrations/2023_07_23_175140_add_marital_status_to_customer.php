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
        Schema::table('customers', function (Blueprint $table) {
            $table->enum('marital_status', ['single', 'married', 'widowed', 'divorced'])
                ->nullable()
                ->default(null);
            $table->string('education')->nullable()->default(null);
            $table->string('language')->nullable()->default(null);
            $table->string('occupation')->nullable()->default(null);
            $table->string('experience')->nullable()->default(null);
            $table->string('arrange_after_employment')->nullable()->default(null);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('marital_status');
            $table->dropColumn('education');
            $table->dropColumn('language');
            $table->dropColumn('occupation');
            $table->dropColumn('experience');
            // $table->dropColumn('arrange_after_employment');
        });
    }
};
