<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UsptoStatusTelemetry extends Model
{
    use HasFactory;

    protected $fillable = [
        'actor_id',
        'status',
        'response_time'
    ];
}
