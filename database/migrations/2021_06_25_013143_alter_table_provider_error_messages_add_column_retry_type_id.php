<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTableProviderErrorMessagesAddColumnRetryTypeId extends Migration
{
    protected $tablename = "provider_error_messages";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->integer('retry_type_id')->nullable()->index();

            $table->foreign('retry_type_id')
                ->references('id')
                ->on('retry_types')
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
            $table->dropColumn('retry_type_id');
        });
    }
}
