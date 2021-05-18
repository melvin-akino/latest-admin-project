<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableProviderBets extends Migration
{
    protected $tablename = "provider_bets";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_bet_id')->index();
            $table->integer('provider_id')->index();
            $table->integer('provider_account_id')->index();
            $table->integer('provider_error_message_id')->nullable()->index();
            $table->string('market_id', 100)->index();
            $table->string('status', 30)->index();
            $table->string('bet_id', 30)->nullable()->index();
            $table->float('odds', 10, 2);
            $table->float('stake', 15, 2);
            $table->float('to_win', 15, 2);
            $table->float('profit_loss', 15, 2)->nullable();
            $table->string('game_schedule', 10)->nullable()->index();
            $table->text('reason')->nullable();
            $table->dateTimeTz('settled_date')->nullable()->index();
            $table->timestamps();

            $table->foreign('user_bet_id')
                ->references('id')
                ->on('user_bets')
                ->onUpdate('cascade');

            $table->foreign('provider_id')
                ->references('id')
                ->on('providers')
                ->onUpdate('cascade');

            $table->foreign('provider_account_id')
                ->references('id')
                ->on('provider_accounts')
                ->onUpdate('cascade');

            $table->foreign('provider_error_message_id')
                ->references('id')
                ->on('provider_error_messages')
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
