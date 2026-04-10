<?php

use App\Models\Lead;
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
        Schema::table('leads', function (Blueprint $table) {
            $table->timestamp('sort_date')->useCurrent();
        });

        for($i = 0; true; $i++) {
            $leads = Lead::skip($i * 500)->take(500)->get();
            if($leads->count()) {
                foreach($leads as $lead) {
                    $lead->update([
                        'sort_date' => $lead->created_at,
                    ]);
                }
            } else {
                break;
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn('sort_date');
        });
    }
};
