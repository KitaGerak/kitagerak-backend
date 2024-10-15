<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Court extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function venue() {
        return $this->belongsTo(Venue::class);
    }

    public function images() {
        return $this->hasMany(CourtImage::class)->where('court_images.status', '<>', 0);
    }

    public function owner() {
        //TODO
    }

    public function schedules() {
        return $this->hasMany(Schedule::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

    public function ratings() {
        return $this->hasMany(Rating::class);
    }

    public function courtType() {
        return $this->belongsTo(CourtType::class);
    }

    public function courtCloseDays() {
        return $this->hasMany(CourtCloseDay::class)->where('court_close_days.close_at', '>=', DB::raw(NOW()));
    }

    public function rejectionMessages() {
        return $this->hasMany(CourtRejectionReason::class)->where('court_rejection_reasons.status', '<>', 0);
    }
}
