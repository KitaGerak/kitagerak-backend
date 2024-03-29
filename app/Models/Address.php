<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function venue() {
        return $this->belongsTo(Venue::class);
    }
}
