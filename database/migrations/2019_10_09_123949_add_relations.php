<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRelations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purses', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients');
        });
        Schema::table('currency_quotes', function (Blueprint $table) {
            $table->foreign('currency_id')->references('id')->on('currencies');
        });
        Schema::table('operation_histories', function (Blueprint $table) {
            $table->foreign('purse_from')->references('id')->on('purses');
            $table->foreign('purse_to')->references('id')->on('purses');
            $table->foreign('currency_id')->references('id')->on('currencies');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purses', function (Blueprint $table) {
            $table->dropForeign('client_id');
        });
        Schema::table('currency_quotes', function (Blueprint $table) {
            $table->dropForeign('currency_id');
        });
        Schema::table('operation_histories', function (Blueprint $table) {
            $table->dropForeign('purse_from');
            $table->dropForeign('purse_to');
            $table->dropForeign('currency_id');
        });
    }
}
