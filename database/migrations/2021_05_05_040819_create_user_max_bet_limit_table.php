<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMaxBetLimitTable extends Migration
{
    protected $tablename = "user_max_bet_limit";
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Schema::create($this->tablename, function (Blueprint $table) {
        //     $table->bigIncrements('id');
        //     $table->integer('user_id')->index();
        //     $table->double('max_bet_limit')->default(0);
        //     $table->timestamps();

        //     $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

        // });

        // Artisan::call('db:seed', [
        //     '--class' => UserMaxBetLimitSeeder::class
        // ]);
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
