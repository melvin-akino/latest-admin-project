<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateViewBetBarV2 extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::statement("DROP VIEW IF EXISTS bet_bar_v2;");
        DB::statement("CREATE VIEW bet_bar_v2 AS
            SELECT
                pb.user_bet_id,
                ub.user_id,
                ub.sport_id,
                ub.master_event_unique_id,
                ub.mem_uid,
                ub.master_league_name,
                ub.master_team_home_name,
                ub.master_team_away_name,
                ub.market_flag,
                ub.stake,
                ub.odds,
                ub.odd_type_id,
                ub.odds_label,
                (
                    CASE
                        WHEN STRPOS(ot.type, 'HT') = 0 THEN
                            'FT ' || ot.type
                        ELSE
                            ot.type
                    END
                ) AS odd_type,
                ub.score_on_bet,
                ub.order_expiry,
                pb.status,
                SUM(pb.stake),
                ub.created_at
            FROM user_bets AS ub
            JOIN provider_bets AS pb
                ON pb.user_bet_id = ub.id
            JOIN odd_types AS ot
                ON ot.id = ub.odd_type_id
            JOIN providers AS p
                ON p.id = pb.provider_id
            WHERE pb.settled_date IS NULL
            GROUP BY
                pb.user_bet_id,
                ub.user_id,
                ub.sport_id,
                ub.master_event_unique_id,
                ub.mem_uid,
                ub.master_league_name,
                ub.master_team_home_name,
                ub.master_team_away_name,
                ub.market_flag,
                ub.stake,
                ub.odds,
                ub.odd_type_id,
                ub.odds_label,
                ub.score_on_bet,
                ub.order_expiry,
                pb.status,
                ot.type,
                ub.created_at
            ORDER BY ub.created_at DESC;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement("DROP VIEW IF EXISTS bet_bar_v2;");
    }
}
