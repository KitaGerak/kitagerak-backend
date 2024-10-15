<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use App\Models\VenueRejectionReason;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');
        $title = "Active Venues";
        if ($status != null) {
            if ($status == "pending") {
                $venues = Venue::where('status', -1)->orWhere('status', -2)->get();
                $title = "Pending Venues";
            } else if ($status == "active") {
                $venues = Venue::where('status', 1)->get();
            }
        } else {
            $venues = Venue::all();
            $title = "All Venues";
        }
        
        
        return view('venues.index', [
            "title" => $title,
            "venues" => $venues
        ]);
    }

    public function detail(Venue $venue)
    {
        return view('venues.detail', [
            "title" => $venue->name . " Detail",
            "venue" => $venue
        ]);
    }

    public function declineVenueRegistration(Venue $venue, Request $request) {
        VenueRejectionReason::create([
            "venue_id" => $venue->id,
            "reason" => $request["reason"],
            "status" => 1
        ]);
        $venue->status = -2;
        if ($venue->save())
            return redirect()->back()->with('success', 'Berhasil melakukan penutupan / penolakan venue.');
    }

    public function acceptVenueRegistration(Venue $venue)
    {
        VenueRejectionReason::where("venue_id", $venue->id)->update(["status" => 0]);
        $venue->status = 1;
        if($venue->save())
            return redirect()->back()->with('success', 'Berhasil menerima registrasi venue!');
    }
}
