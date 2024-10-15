<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;

class Transaction extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function schedules() {
        return $this->hasMany(Schedule::class);
    }

    // public function schedule() {
    //     return $this->belongsTo(Schedule::class);
    // }

    // public function court():HasOneThrough {
    //     return $this->hasOneThrough(Court::class, Schedule::class, 'id', 'id', 'schedule_id', 'court_id');
    // }

    public function court() {
        return $this->belongsTo(Court::class);
    }

    public function status() {
        return $this->belongsTo(TransactionStatus::class);
    }
    
    public function user() {
        return $this->belongsTo(User::class);
    }
}
