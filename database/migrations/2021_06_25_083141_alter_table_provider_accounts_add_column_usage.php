<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{Artisan, Schema};

class AlterTableProviderAccountsAddColumnUsage extends Migration
{
    protected $tablename = "provider_accounts";

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->string('usage', 10)->default('OPEN')->index();
        });

        Artisan::call('db:seed', [
            '--class' => ProviderAccountUsageSeeder::class
        ]);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table($this->tablename, function (Blueprint $table) {
            $table->dropColumn('usage');
        });
    }
}
