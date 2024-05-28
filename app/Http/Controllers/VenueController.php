<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index()
    {
        $venues = Venue::all();
        return view('venues.index', compact('venues'));
    }

    public function detail($id)
    {
        $venue = Venue::find($id);
        return view('venues.detail', compact('venue'));
    }

    public function acceptVenueRegistration($id)
    {
        $venue = Venue::find($id);
        $venue->status = 1;
        if($venue->save())
            return redirect()->back()->with('success', 'Berhasil menerima registrasi venue!');
    }
}
