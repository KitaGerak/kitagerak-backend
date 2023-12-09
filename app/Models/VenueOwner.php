<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VenueOwner extends Model
{
    protected $table = "venue_owners";
    protected $primaryKey = "id";
    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'password',
        'national_id_number',
    ];
}
