<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Venue extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function courts() {
        return $this->hasMany(Court::class)->where('courts.status', '<>', '0');
    }

    public function venueImages() {
        return $this->hasMany(VenueImage::class)->where('venue_images.status', '<>', 0);
    }

    public function address() {
        return $this->belongsTo(Address::class);
    }

    public function openDays() {
        return $this->hasMany(VenueOpenDays::class);
    }

    // public function venueOwner(): BelongsToMany
    // {
    //     return $this->belongsToMany(User::class, 'venue_owner_and_venue');
    // }

    public function owner() {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function facilities() {
        // return $this->belongsToMany
        
    }

    public function rejectionMessages() {
        return $this->hasMany(VenueRejectionReason::class)->where('venue_rejection_reasons.status', '<>', 0);
    }
}
