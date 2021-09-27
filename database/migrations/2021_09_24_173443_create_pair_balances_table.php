<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePairBalancesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pair_balances', function (Blueprint $table) {
            $table->id();
            $table->string('s1');
            $table->string('balance_s1', 10);
            $table->string('balance_s1_usd', 10);
            $table->string('price_at_trade_s1', 10);
            $table->string('s2');
            $table->string('balance_s2', 10);
            $table->string('balance_s2_usd', 10);
            $table->string('price_at_trade_s2', 10);
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
        Schema::dropIfExists('pair_balances');
    }
}
