<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Artisan, Schema};

class CreateTableRetryTypes extends Migration
{
    protected $tablename = "retry_types";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create($this->tablename, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('type', 25)->unique()->index();
            $table->text('description');
            $table->timestamps();
        });

        Artisan::call('db:seed', [
            '--class' => RetryTypesSeeder::class
        ]);
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
