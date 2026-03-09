<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'service_id', 'status', 'response_ms',
        'http_code', 'error_message', 'triggered_by', 'checked_at',
    ];

    protected $casts = [
        'checked_at' => 'datetime',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
