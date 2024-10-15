<?php

namespace App\Http\Controllers;

use App\Models\CourtType;
use Exception;
use Illuminate\Http\Request;

class CourtTypeController extends Controller
{
    public function index() {
        return view('courts.court-types', [
            "title" => "Manage Court Types",
            "courtTypes" => CourtType::all()
        ]);
    }

    public function store(Request $request) {
        try {
            CourtType::create([
                "type" => $request["type"],
                "status" => 1
            ]);
            return back()->with('success', "Sukses tambah court type");
        } catch (Exception $e) {
            return back()->with('error', $e);
        }
    }

    public function update(CourtType $courtType, Request $request) {
        try {
            CourtType::where('id', $courtType->id)->update([
                'type' => $request['type']
            ]);
            return back()->with('success', "Sukses update court type");
        } catch (Exception $e) {
            return back()->with('error', "Gagal update court type");
        }
    }

    public function reactivate(CourtType $courtType) {
        $courtType->status = 1;
        $courtType->save();
        return back()->with('success', "Sukses mengaktivasi court type");
    }

    public function destroy(CourtType $courtType) {
        $courtType->status = 0;
        $courtType->save();
        return back()->with('success', "Sukses mendeaktivasi court type");
    }

    // public function update(CourtType $courtType, UpdateCourtTypeRequest $request) {
    //     $courtType->update($request->all());
    // }

    // public function destroy(CourtType $courtType) {
    //     $courtType->status = "0";
    //     $courtType->save();
    // }
    
}
