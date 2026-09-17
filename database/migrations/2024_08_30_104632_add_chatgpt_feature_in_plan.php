<?php

use Illuminate\Database\Migrations\Migration;
use App\Models\Plan;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $plans = Plan::all();

        foreach($plans as $plan) {
            $features = (array)$plan->features;
            $features['chatgpt'] = '1';
            $plan->features = (object)$features;
            $plan->update();
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
};
