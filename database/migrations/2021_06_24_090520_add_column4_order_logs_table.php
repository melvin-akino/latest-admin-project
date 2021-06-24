<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumn4OrderLogsTable extends Migration
{
    protected $tablename = "order_logs";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->integer('provider_account_id')->index()->nullable();

            $table->integer('provider_id')->nullable()->change();

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
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->integer('provider_id')->nullable(false)->change();

            $table->dropColumn('provider_account_id');
        });
    }
}
