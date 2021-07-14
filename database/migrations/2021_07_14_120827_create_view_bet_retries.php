<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

class CreateViewBetRetries extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("DROP VIEW IF EXISTS bet_retries;");

        DB::statement("CREATE VIEW bet_retries AS
            SELECT
                o.id,
                pa.username,
                p.alias,
                o.bet_selection,
                ol.status,
                ol.reason,
                rt.\"type\"
            FROM orders AS o
            LEFT JOIN order_logs AS ol
                ON ol.order_id = o.id
            LEFT JOIN provider_error_messages AS pem
                ON pem.message ILIKE ol.reason
            LEFT JOIN retry_types AS rt
                ON rt.id = pem.retry_type_id
            LEFT JOIN provider_accounts AS pa
                ON pa.id = o.provider_account_id
            LEFT JOIN providers AS p
                ON p.id = o.provider_id
            ORDER BY ol.id DESC;
        ");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS bet_retries;");
    }
}
