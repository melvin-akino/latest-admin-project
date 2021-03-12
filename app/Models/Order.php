<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\Contracts\Activity;
use Carbon\Carbon;

class Order extends Model
{
    protected $table = "orders";

    protected $fillable = [
        'status',
        'profit_loss',
        'pl',
        'reason',
    ];

    use LogsActivity;

    public function OrderLog() {
        return $this->hasOne(App\Models\OrderLog::class, 'order_id', 'id');
    }

    protected static $logAttributes = ['id', 'status', 'profit_loss', 'reason'];

    protected static $logOnlyDirty = true;

    protected static $logName = 'Orders';

    public function tapActivity(Activity $activity, string $eventName)
    {
      $activity->properties = $activity->properties->put('action', ucfirst($eventName));
      $activity->properties = $activity->properties->put('ip_address', request()->ip());
    }
}
