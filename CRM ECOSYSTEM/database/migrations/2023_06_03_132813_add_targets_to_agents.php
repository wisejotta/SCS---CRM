<?php

use App\Models\Agent;
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
        Schema::table('agents', function (Blueprint $table) {
            $table->json('targets')->nullable()->default(null);
        });

        $agents = Agent::all();
        foreach($agents as $agent) {
            $agent->update([
                'targets' => [
                    'switch' => [
                        '_28' => 100,
                        '_48' => 140,
                        'goal' => 15
                    ],
                    'upgrade' => [
                        'percentage' => 10,
                        'goal' => 20000
                    ]
                ]
            ]);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('agents', function (Blueprint $table) {
            $table->dropColumn('targets');
        });
    }
};
