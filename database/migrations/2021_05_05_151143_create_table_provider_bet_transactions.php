<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProviderBetTransactions extends Migration
{
    protected $tablename = "provider_bet_transactions";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('provider_bet_id')->index();
            $table->integer('exchange_rate_id')->index();
            $table->float('actual_stake', 15, 2);
            $table->float('actual_to_win', 15, 2);
            $table->float('actual_profit_loss', 15, 2);
            $table->float('exchange_rate');
            $table->timestamps();

            $table->foreign('provider_bet_id')
                ->references('id')
                ->on('provider_bets')
                ->onUpdate('cascade');

            $table->foreign('exchange_rate_id')
                ->references('id')
                ->on('exchange_rates')
                ->onUpdate('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists($this->tablename);
    }
}
