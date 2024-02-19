<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function schedules() {
        return $this->hasMany(Schedule::class);
    }

    public function transactions() {
        return $this->hasMany(Transaction::class);
    }

    public function ratings() {
        return $this->hasMany(Rating::class);
    }
}
