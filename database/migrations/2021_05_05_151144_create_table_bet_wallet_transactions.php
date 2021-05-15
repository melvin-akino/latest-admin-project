<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableBetWalletTransactions extends Migration
{
    protected $tablename = "bet_wallet_transactions";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('provider_bet_log_id')->index();
            $table->integer('user_id')->index();
            $table->integer('source_id')->index();
            $table->integer('currency_id')->index();
            $table->integer('wallet_ledger_id')->index();
            $table->integer('provider_account_id')->index();
            $table->text('reason');
            $table->float('amount', 15, 2);
            $table->timestamps();

            $table->foreign('provider_bet_log_id')
                ->references('id')
                ->on('provider_bet_logs')
                ->onUpdate('cascade');

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');

            $table->foreign('source_id')
                ->references('id')
                ->on('sources')
                ->onUpdate('cascade');

            $table->foreign('currency_id')
                ->references('id')
                ->on('currency')
                ->onUpdate('cascade');

            $table->foreign('wallet_ledger_id')
                ->references('id')
                ->on('wallet_ledger')
                ->onUpdate('cascade');

            $table->foreign('provider_account_id')
                ->references('id')
                ->on('provider_accounts')
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
