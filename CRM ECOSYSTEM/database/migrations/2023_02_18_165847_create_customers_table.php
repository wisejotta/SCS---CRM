<?php

use App\Enums\CustomerStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users');

            $table->string('country');
            $table->string('residence');
            $table->enum('gender', ['male', 'female'])->nullable()->default(null);
            $table->date('dob')->nullable()->default(null);
            $table->tinyInteger('status')->default(CustomerStatus::ACTIVE->value);
            $table->string('phone_number');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('customers');
    }
};
