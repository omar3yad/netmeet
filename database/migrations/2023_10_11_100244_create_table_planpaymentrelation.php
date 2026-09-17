<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateTablePlanpaymentrelation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_gateway_plan_relation', function (Blueprint $table) {
            $table->id();
            $table->integer('plan_id')->comment('Plan Id from plan table');
            $table->string('plan_id_gateway')->nullable()->comment('Plan Id from payment gateway');
            $table->string('payment_gateway')->nullable()->comment('Payment Gateway');
            $table->string('plan_code')->nullable()->comment('Plan Code from payment gateway');
            $table->string('amount')->nullable()->comment('Amount');
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('email_token')->nullable()->unique()->after('api_token')->comment('This field is used for paystack payment gateway.');
            $table->string('customer_id')->nullable()->unique()->after('email_token')->comment('Customer ID from Mollie Payment gateway.');
            $table->string('avatar')->nullable()->after('customer_id')->comment('User profile picture');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_gateway_plan_relation');
    }
}
