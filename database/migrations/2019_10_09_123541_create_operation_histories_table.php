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
            $table->unsignedBigInteger('purse_id');
            $table->dateTime('date');
            $table->float('currency_quote');
            $table->float('amount');
            $table->string('operation_comment');
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
