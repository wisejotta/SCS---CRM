<?php

use App\Models\Lead;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->unsignedBigInteger('callback_agent_id')->nullable()->default(null);
            $table->foreign('callback_agent_id')->references('id')->on('agents');
        });
        
        $leads = Lead::whereNotNull('callback')->get();
        foreach($leads as $lead) {
            $lead->update([
                'callback_agent_id' => $lead->agent_id
            ]);
        }
    }

    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropForeign(['callback_agent_id']);
            $table->dropColumn('callback_agent_id');
        });
    }
};
