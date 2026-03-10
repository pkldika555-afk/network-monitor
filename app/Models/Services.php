<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Services extends Model
{
    protected $fillable = [
        'name',
        'url',
        'category',
        'department',
        'auth_type',
        'auth_value',
        'is_active',
        'status',
        'response_ms',
        'last_checked_at',
        'assigned_to',
        'assigned_at',
    ];
    protected $cast = [
        'is_active' => 'boolean',
        'last_checked_at' => 'datetime',
    ];
    public function logs()
    {
        return $this->hasMany(CheckLog::class);
    }
    public function latestLog()
    {
        return $this->hasOne(CheckLog::class)->latest('checked_at');
    }
}
