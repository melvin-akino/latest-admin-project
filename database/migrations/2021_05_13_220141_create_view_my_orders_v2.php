<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewMyOrdersV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("DROP VIEW IF EXISTS my_orders_v2;");
        DB::statement("CREATE VIEW my_orders_v2 AS
            SELECT
                ub.*,
                (
                    CASE
                        WHEN STRPOS(ot.type, 'HT') = 0 THEN
                            'FT ' || ot.type
                        ELSE
                            ot.type
                    END
                ) AS odd_type,
                ub.odds * ub.stake AS to_win,
                (SELECT SUM(pb.stake) FROM provider_bets AS pb WHERE pb.user_bet_id = ub.id AND pb.settled_date IS NOT NULL) * ub.odds AS profit_loss
            FROM user_bets AS ub
            JOIN odd_types AS ot
                ON ot.id = ub.odd_type_id
            ORDER BY ub.created_at DESC;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS my_orders_v2;");
    }
}
