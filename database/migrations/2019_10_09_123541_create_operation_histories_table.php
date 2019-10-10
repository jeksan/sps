<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOperationHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('operation_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('purse_from')->nullable();
            $table->unsignedBigInteger('purse_to');
            $table->unsignedBigInteger('currency_id');
            $table->float('currency_quote');
            $table->float('amount');
            $table->dateTime('date');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('operation_histories');
    }
}
