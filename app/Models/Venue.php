<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Venue extends Model
{
    use HasFactory;

    protected $table = "venues";

    public function venueOwners(): BelongsToMany
    {
        return $this->belongsToMany(VenueOwner::class, 'venue_owner_and_venue');
    }
}
