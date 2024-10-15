<?php

namespace App\Http\Controllers;

use App\Models\Court;
use App\Models\CourtRejectionReason;
use Illuminate\Http\Request;

class CourtController extends Controller
{
    public function index(Request $request) {
        $title = "Active Courts";
        $status = $request->query('status');
        if ($status != null) {
            if ($status == "pending") {
                $courts = Court::where('status', -1)->orWhere('status', -2)->get();
                $title = "Pending Courts";
            } else if ($status == "active") {
                $courts = Court::where('status', 1)->get();
            }
        } else {
            $courts = Court::all();
            $title = "All Courts";
        }

        return view('courts.index', [
            "title" => $title,
            "courts" => $courts
        ]);
    }

    public function show(Court $court) {
        return view('courts.detail', [
            "title" => "Court details",
            "court" => $court
        ]);
    }

    public function declineCourtRegistration(Court $court, Request $request) {
        CourtRejectionReason::create([
            "court_id" => $court->id,
            "reason" => $request["reason"],
            "status" => 1
        ]);
        $court->status = -2;
        if ($court->save())
            return redirect()->back()->with('success', 'Berhasil melakukan penutupan / penolakan venue.');
    }

    public function acceptCourtRegistration(Court $court)
    {
        CourtRejectionReason::where("venue_id", $court->id)->update(["status" => 0]);
        $court->status = 1;
        if($court->save())
            return redirect()->back()->with('success', 'Berhasil menerima registrasi venue!');
    }
}
