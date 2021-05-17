<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableBlockedLines extends Migration
{
    protected $tablename = "blocked_lines";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('event_id')->index();
            $table->integer('odd_type_id')->index();
            $table->string('points', 10)->nullable()->index();
            $table->string('line')->index();
            $table->timestamps();

            $table->foreign('event_id')
                ->references('id')
                ->on('events')
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
