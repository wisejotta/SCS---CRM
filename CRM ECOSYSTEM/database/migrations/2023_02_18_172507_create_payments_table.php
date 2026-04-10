<?php

use App\Enums\LeadStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            $table->unsignedBigInteger('lead_id')->nullable();
            $table->foreign('lead_id')->references('id')->on('leads');

            $table->double('amount', 8, 2);
            $table->tinyInteger('lead_status')->default(LeadStatus::FILE_OPENING->value); // which stage was the payment sent
            $table->tinyInteger('status')->default(PaymentStatus::SENT->value);
            $table->char('lan', 2)->default('en');
            $table->json('misc')->nullable()->default(null);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('payments');
    }
};
