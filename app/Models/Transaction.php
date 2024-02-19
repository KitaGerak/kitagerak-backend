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

    public function schedule() {
        return $this->belongsTo(Schedule::class);
    }

    public function court():HasOneThrough {
        return $this->hasOneThrough(Court::class, Schedule::class, 'id', 'id', 'schedule_id', 'court_id');
    }

    public function transactionStatus() {
        return $this->belongsTo(TransactionStatus::class);
    }
    
    public function user() {
        return $this->belongsTo(User::class);
    }
}
