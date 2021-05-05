<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableUserBets extends Migration
{
    protected $tablename = "user_bets";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->index();
            $table->integer('sport_id')->index();
            $table->integer('odd_type_id')->index();
            $table->string('market_id', 100)->index();
            $table->string('status', 30)->index();
            $table->float('odds', 10, 2);
            $table->float('stake', 15, 2);
            $table->enum('market_flag', [
                'HOME',
                'DRAW',
                'AWAY'
            ]);
            $table->string('order_expiry', 10);
            $table->string('odd_label', 10)->nullable();
            $table->string('ml_bet_identifier', 20)->index();
            $table->string('score_on_bet', 10)->nullable();
            $table->string('final_score', 10)->nullable();
            $table->string('master_event_market_unique_id', 100)->index();
            $table->string('master_event_unique_id', 30)->index();
            $table->string('master_league_name', 100)->index();
            $table->string('master_team_home_name', 100)->index();
            $table->string('master_team_away_name', 100)->index();
            $table->timestamps();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onUpdate('cascade');

            $table->foreign('sport_id')
                ->references('id')
                ->on('sports')
                ->onUpdate('cascade');

            $table->foreign('odd_type_id')
                ->references('id')
                ->on('odd_types')
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
