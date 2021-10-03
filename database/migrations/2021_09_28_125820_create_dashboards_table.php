<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDashboardsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dashboards', function (Blueprint $table) {
            $table->id();
            $table->string('todays_parcel_entry');
            $table->string('todays_cancel_parcel');
            $table->string('total_parcel_entry_till_now');
            $table->string('total_cancel_parcel_till_now');
            $table->string('total_delivered_today');
            $table->string('total_delivered_till_now');
            $table->string('delivery_charge_entered_today');
            $table->string('delivery_charge_total_receivable');
            $table->string('delivery_charge_collected_today');
            $table->string('delivery_charge_due_today');
            $table->string('delivery_charge_collected_till_now');
            $table->string('cod_entry_today');
            $table->string('cod_collected_receivable_by_merchant');
            $table->string('total_cod_paid_to_merchant');
            $table->string('total_cod_due');
            $table->string('cod_collected_till_now');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('dashboards');
    }
}
