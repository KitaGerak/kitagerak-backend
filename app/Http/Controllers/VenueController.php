<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function getVenueByOwner($venueOwnerId)
    {
        $venues = Venue::all();
        return response()->json($venues);
    }
}
