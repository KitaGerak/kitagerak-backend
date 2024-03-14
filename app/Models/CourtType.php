<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class CourtType extends Model
{
    use HasFactory;

    protected $guarded = [
        'id'
    ];

    private $withCourts = false;

    public function venues(): BelongsToMany {
        // echo($this->withCourts);
        $res = $this->belongsToMany(Venue::class, Court::class)->distinct();
        // if ($this->withCourts == true) {
            $res->with('courts');
        // }
        return $res;
    }
}
