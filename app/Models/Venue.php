<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Venue extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    public function courts() {
        return $this->hasMany(Court::class)->where('courts.status', '<>', '0');
    }
    

    public function address() {
        return $this->belongsTo(Address::class);
    }
}
