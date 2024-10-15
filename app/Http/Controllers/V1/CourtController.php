<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateCourtRequest;
use App\Http\Requests\V1\StoreCourtRequest;
use App\Http\Resources\V1\CourtCollection;
use App\Http\Resources\V1\CourtResource;
use App\Http\Resources\V1\CourtTypeCollection;
use App\Models\Court;
use App\Models\CourtImage;
use App\Models\CourtType;
use App\Services\V1\CourtQuery;

class CourtController extends Controller
{

    public function index(Request $request) {
        $filter = new CourtQuery();
        $queryItems = $filter->transform($request);

        $with = ['images', 'courtType', 'ratings'];

        if ($request->query('venue') != null && $request->query('venue') == "included") {
            array_push($with, 'venue');
        }
        
        $courts = Court::select('courts.*')->with($with)->where('courts.status', '<>', 0);

        if ($request->query('ownerId') != null) {
            $courts = $courts->leftJoin('venues', 'venues.id', '=', 'courts.venue_id')->where('venues.owner_id', $request->query('ownerId'));
        }

        if (count($queryItems) == 0) {
            if ($request->query('paginate') != null && $request->query('paginate') == 'true') {
                $courts = $courts->orderBy('courts.id', 'DESC')->paginate(30)->withQueryString();
            } else {
                $courts = $courts->orderBy('courts.id', 'DESC')->get();
            }
        } else {
            if ($request->query('paginate') != null && $request->query('paginate') == 'true') {
                $courts = $courts->where($queryItems)->orderBy('courts.id', 'DESC')->paginate(30)->withQueryString();
            } else {
                $courts = $courts->where($queryItems)->orderBy('courts.id', 'DESC')->get();
            }
        }

        // if($request->query('venueId'))
        //     $court = $court->where("venue_id", $request->query('venueId'));

        // if($request->query('ownerId'))
        // {
        //     $venues = Venue::where('owner_id', $request->query('ownerId'))->get();
        //     $venueIds = $venues->pluck('id');

        //     $court = $court->whereIn("venue_id", $venueIds);
        // }

        // $court = $court->get();
        
        // return response()->json(["data" => $court]);
        // return new CourtCollection(Court::paginate(10));

        return new CourtCollection($courts);
    }

    public function show(Court $court, Request $request) {
        $c = $court->loadMissing(['courtType', 'images', 'ratings']);

        if ($request->query('venue') != null && $request->query('venue') == "included") {
            $c = $c->loadMissing('venue');
        }

        return new CourtResource($c);
    }

    // StoreCourtRequest
    public function store(StoreCourtRequest $request) {
        if (auth('sanctum')->check()) {
            $userAuth = auth('sanctum')->user();
            if ($userAuth->role_id != 2 && $userAuth->role_id != 4) { //pemilik lapangan / admin
                return response()->json([
                    "status" => 0,
                    "message" => "Gagal memasukkan data. Anda bukan admin / pemilik lapangan"
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
                "message" => "Unauthenticated"
            ]);
        }

        if (!isset($request->images)) {
            return response()->json([
                "status" => false,
                "message" => "Setidaknya harus menyertakan 1 gambar"
            ], 422);
        }

        $court = Court::create($request->all());
        $this->uploadImages($request, $court->id);
        
        return new CourtResource(Court::where('id', $court->id)->first());
    }

    public function update(UpdateCourtRequest $request, Court $court) {
        if (auth('sanctum')->check()) {
            $userAuth = auth('sanctum')->user();
            if ($userAuth->role_id != 2 && $userAuth->role_id != 4) { //pemilik lapangan / admin
                return response()->json([
                    "status" => 0,
                    "message" => "Gagal memasukkan data. Anda bukan admin / pemilik lapangan"
                ]);
            }
            // TODO
            // else if ($venue->owner->id != $userAuth->id) {
            //     return response()->json([
            //         "status" => 0,
            //         "message" => "Gagal memasukkan data. Anda bukan pemilik lapangan"
            //     ]);
            // }
        } else {
            return response()->json([
                "status" => 0,
                "message" => "Unauthenticated"
            ]);
        }

        $court->update($request->all());
        if (isset($request->deleteImages) && $request->deleteImages != null) {
            foreach($request->deleteImages as $image) {
                CourtImage::where('url', 'like', '%' . $image)->update(['status' => 0]);
            }
        }

        if (isset($request->images) && $request->images != null) {
            $this->uploadImages($request, $court->id);
        }
    }

    private function uploadImages(Request $request, $courtId) {
        if ($request->has('images')) {
            $images = $request->images;

            $allowedImageExtensions = ['jpg', 'jpeg', 'png'];
            $allowedVideoExtensions = ['mp4', 'mov'];

            $invalidFile = [];

            foreach($images as $image) {
                $extension = $image->getClientOriginalExtension();

                if (!in_array(strtolower($extension), $allowedImageExtensions) && !in_array(strtolower($extension), $allowedVideoExtensions)) {
                    array_push($invalidFile, $image->getClientOriginalName());
                }
            }

            if(count($invalidFile) == 0) {
                $successFile = [];
                foreach($images as $image) {
                    $fileName = $image->store("private/images/courts/$courtId");
                    CourtImage::create([
                        'court_id' => $courtId,
                        'url' => $fileName,
                        'status' => 1,
                    ]);
                    array_push($successFile, $fileName);
                }
                return response()->json([
                    'status' => true,
                    'message' => 'Success',
                    'successFile' => $successFile,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Invalid File Type',
                    'invalidFileName' => $invalidFile,
                ], 500);
            }

            return true;
        }
    }

    public function destroy (Court $court) {
        $court->status = 0;
        $court->save();
    }

    public function getCourtTypes() {
        $res = CourtType::where('status', 1)->get();

        return new CourtTypeCollection($res);
    }
}
