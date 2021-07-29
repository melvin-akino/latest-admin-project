<?php

namespace App\Console\Commands;

use App\Mail\SendCSV;
use App\Models\{SystemConfiguration AS SC, ProviderBet};
use Carbon\Carbon;
use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\{DB, Mail};


class PreviousBetExtraction extends Command
{
    /**
     * # Extract transaction/s from previous date
     * $ php artisan bets:extract
     *
     * # with `DATETIME` option
     * $ php artisan bets:extract --dt="2020-09-04"
     * // Extract Transactions from 2020-09-04 to present
     *
     * # with `STEP` option
     * $ php artisan bets:extract --step=3
     * // Extract Transaction from 3 days to present
     *
     * # with `DATETIME` and `STEP` options
     * $ php artisan bets:extract --dt="2020-09-01" --step=2
     * // Extract Transactions from 2020-09-01 to 2 days after
     */

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bets:extract {--dt=} {--step=} {--provider=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Bet Data Extraction for Previous Date/s.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private $providerTimezones = [
        'hg'  => 'Etc/GMT+4',
        'isn' => 'Etc/GMT+4'
    ];

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->line('Running command...');
            $provider = $this->option('provider') ?? 'hg';
            $timezone = $this->providerTimezones[$provider];
            $date = is_null($this->option('dt')) ? Carbon::now()->subDay()->format('Y-m-d H:i:') . "00" : $this->option('dt');
            $step = is_null($this->option('step')) ? Carbon::createFromFormat('Y-m-d H:i:s', $date)->diffInDays(Carbon::now()) : $this->option('step');
            $date = is_null($this->option('dt')) && !is_null($this->option('step')) ? Carbon::now()->subDay($step)->format('Y-m-d H:i:') . "00" : $date;
            $from = Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d H:i:s');
            $to   = Carbon::createFromFormat('Y-m-d H:i:s', $date)->addDays($step)->subSecond()->format('Y-m-d H:i:s');

            if ($date > Carbon::now()->format('Y-m-d H:i:s')) {
                $this->error("ERROR! Invalid 'date' option! Must be on or before current date.");
                return;
            }

            if (Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d H:i:s') == !$date) {
                $this->error("ERROR! Invalid 'date' option! Must be on or before current date.");
                return;
            }

            if (!is_numeric($step)) {
                $this->error("ERROR! Invalid 'step' option! Must be an integer.");
                return;
            }

            $subject = "Your Order Summary for " . strtoupper($provider);
            $coverage = '';
            if (!is_null($this->option('dt')) || !is_null($this->option('step'))) {
                if (!is_null($this->option('dt')) && is_null($this->option('step'))) {
                    $coverage .= $this->option('dt') . " - " . Carbon::now()->format('F d, Y H:i:s');
                } else if (is_null($this->option('dt')) && !is_null($this->option('step'))) {
                    $coverage .= 'from present minus ' . $this->option('step') . ' days';
                } else {
                    $coverage .= updateDateTimezone(Carbon::createFromFormat('Y-m-d H:i:s', $this->option('dt'))->format('F d, Y H:i:s'), 'F d, Y H:i:s', $timezone) . " - " . updateDateTimezone(Carbon::createFromFormat('Y-m-d H:i:s', $this->option('dt'))->subSecond()->addDays($this->option('step'))->format('F d, Y H:i:s'), 'F d, Y H:i:s', $timezone);
                }
            }

            $filename = strtoupper(env('APP_ENV')) . "_" . strtoupper($provider) . "_Extracted_Bet_Transactions_" . Carbon::now()->format('YmdHis') . ".csv";
            $file     = fopen($filename, 'w');
            $columns  = ['Email Address', 'ML Bet Identifier', 'Provider Bet ID', 'Username', 'Created At', 'Settled Date', 'Status', 'Currency', 'User Stake', 'User PL', 'User VS', 'ML Stake', 'ML PL', 'ML VS', 'Book Stake', 'Book PL', 'Book VS', 'Odds', 'Odd Label', 'Verified', 'Bookmaker', 'Exchange Rate'];
            $dups     = [];
            $data     = DB::table('orders AS o')
                ->join('provider_accounts AS pa', 'pa.id', '=', 'o.provider_account_id')
                ->join('users AS u', 'u.id', '=', 'o.user_id')
                ->join('order_logs AS ol', 'ol.order_id', '=', 'o.id')
                ->join('provider_account_orders AS pao', 'pao.order_log_id', '=', 'ol.id')
                ->join('odd_types AS ot', 'ot.id', '=', 'o.odd_type_id')
                ->join('providers AS p', 'p.id', 'o.provider_id')
                ->join('currency AS c', 'c.id', 'p.currency_id')
                ->where('o.created_at', '>=', $from)
                ->where('o.created_at', '<=', $to)
                ->where('o.bet_id', '!=', "")
                ->where('p.alias', strtoupper($provider))
                ->whereNotIn('o.status', ['FAILED'])
                ->orderBy('o.id', 'ASC')
                ->orderBy('pao.order_log_id', 'DESC')
                ->distinct()
                ->get([
                    'o.id',
                    'pao.order_log_id',
                    'u.email',
                    'o.ml_bet_identifier',
                    'o.bet_id',
                    'pa.username',
                    'o.created_at',
                    'o.settled_date',
                    'o.status',
                    'o.stake',
                    'c.code',
                    'o.profit_loss',
                    'pao.actual_stake',
                    'pao.actual_profit_loss',
                    'o.odds',
                    'o.odd_label',
                    'p.alias',
                    'pao.exchange_rate',
                ]);

            fputcsv($file, $columns);

            foreach ($data as $row) {
                if (!in_array($row->id, $dups)) {
                    fputcsv($file, [
                        $row->email,
                        $row->ml_bet_identifier,
                        $row->bet_id,
                        $row->username,
                        updateDateTimezone($row->created_at, "Y-m-d H:i:s", $timezone),
                        updateDateTimezone($row->settled_date, "Y-m-d H:i:sO", $timezone),
                        $row->status,
                        trim($row->code),
                        $row->stake,
                        $row->profit_loss,
                        abs($row->profit_loss),
                        $row->actual_stake,
                        $row->actual_profit_loss,
                        abs($row->actual_profit_loss),
                        '',
                        '',
                        '',
                        $row->odds,
                        $row->odd_label,
                        '',
                        $row->alias,
                        $row->exchange_rate,
                    ]);

                    $dups[] = $row->id;
                }
            }
            fclose($file);

            $to = SC::getSystemConfigurationValue('CSV_EMAIL_TO');
            $to = explode(',', $to->value);

            $cc = SC::getSystemConfigurationValue('CSV_EMAIL_CC');
            $cc = explode(',', $cc->value);

            $bcc = SC::getSystemConfigurationValue('CSV_EMAIL_BCC');
            $bcc = explode(',', $bcc->value);

            $coverage .= " for provider " . strtoupper($provider);

            Mail::to($to)
              ->cc($cc)
              ->bcc($bcc)
              ->send(new SendCSV("./" . $filename, $subject, $coverage));
            unlink("./" . $filename);
        } catch (Exception $e) {
            $this->error("ERROR! " . $e->getLine() . " : " . $e->getMessage() . ':' . $e->getTraceAsString());
        }
    }
}
