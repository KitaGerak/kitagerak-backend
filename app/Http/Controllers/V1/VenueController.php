<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreVenueRequest;
use App\Http\Requests\V1\UpdateVenueRequest;
use App\Http\Resources\V1\VenueCollection;
use App\Http\Resources\V1\VenueResource;
use App\Mail\RegisterNewVenueMail;
use App\Models\Address;
use App\Models\Court;
use App\Models\CourtType;
use App\Models\Venue;
use App\Models\VenueFacilities;
use App\Models\VenueImage;
use App\Services\V1\VenueQuery;
use Illuminate\Support\Facades\Mail;

class VenueController extends Controller
{

    public function index(Request $request) {
        $filter = new VenueQuery();
        $queryItems = $filter->transform($request);

        $with = [];

        if ($request->query('courts') != null && $request->query('courts') == "included") {
            array_push($with, 'courts');
        }

        if ($request->query('owner') != null && $request->query('owner') == "included") {
            array_push($with, 'owner');
        }

        $venues = Venue::select('venues.*')->with($with)->where('venues.status', '<>', 0)->distinct();

        if (isset($request->orderBy)) {
            $obs = explode(",", $request->orderBy);
            foreach ($obs as $orderBy) {
                $ob = explode("|", $orderBy);
                $venues = $venues->orderBy($ob[0], $ob[1]);
            }
        } else {
            $venues = $venues->orderBy('venues.id', 'DESC');
        }

        if (count($queryItems) == 0) {
            if ($request->query('paginate') != null && $request->query('paginate') == 'true') {
                $venues = $venues->paginate(30)->withQueryString();
            } else {
                $venues = $venues->get();
            }
        } else {
            $venues = $venues->leftJoin('courts', 'courts.venue_id', '=', 'venues.id')->leftJoin('court_types', 'court_types.id', '=', 'courts.court_type_id')->leftJoin('addresses', 'addresses.id', '=', 'venues.address_id');
            if ($request->query('paginate') != null && $request->query('paginate') == 'true') {
                $venues = $venues->where($queryItems)->paginate(30)->withQueryString();
            } else {
                $venues = $venues->where($queryItems)->get();
            }
        }

        return new VenueCollection($venues);
    }

    public function fetchImage(Venue $venue)
    {
        $venueImages = VenueImage::where('venue_id', $venue->id)->get();
        $venueImages = $venueImages->pluck('url');
        return response()->json(["data"=>$venueImages]);
    }

    public function show(Venue $venue, Request $request) {
        $v = $venue;
        if ($request->query('courts') != null && $request->query('courts') == 'included') {
            $v = $v->loadMissing('courts');
        }

        if ($request->query('owner') != null && $request->query('owner') == 'included') {
            $v = $v->loadMissing('owner');
        }
    
        return new VenueResource($v);
    }

    public function update(UpdateVenueRequest $request, Venue $venue) {

        if (auth('sanctum')->check()) {
            $userAuth = auth('sanctum')->user();
            if ($userAuth->role_id != 2 && $userAuth->role_id != 3) { //pemilik lapangan / admin
                return response()->json([
                    "status" => 0,
                    "message" => "Gagal memasukkan data. Anda bukan admin / pemilik lapangan"
                ]);
            } else if ($venue->owner->id != $userAuth->id) {
                return response()->json([
                    "status" => 0,
                    "message" => "Gagal memasukkan data. Anda bukan pemilik lapangan"
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
                "message" => "Unauthenticated"
            ]);
        }

        $venue->update($request->all());

        if (isset($request->address) && $request->address != null) {
            $address = $request->address;
            Address::where('id', $address['id'])->update([
                'street' => $address['street'],
                'postal_code' => $address['postalCode'],
                'longitude' => $address['longitude'],
                'latitude' => $address['latitude']
            ]);
        }

        if (isset($request->deleteImages) && $request->deleteImages != null) {
            foreach($request->deleteImages as $image) {
                VenueImage::where('url', 'like', '%' . $image)->update(['status' => 0]);
            }
        }

        if (isset($request->images) && $request->images != null) {
            $this->uploadImages($request, $venue->id);
        }
    }

    //StoreVenueRequest
    public function store(StoreVenueRequest $request) {
        $userAuth = null;

        return $this->uploadImages($request, 1);

        //get currently logged in user;
        if (auth('sanctum')->check()) {
            $userAuth = auth('sanctum')->user();
            if ($userAuth->role_id != 2 && $userAuth->role_id != 3) { //pemilik lapangan / admin
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

        // TODO
        // Mail::to($user->email)->send(new RegisterNewVenueMail());

        if (isset($request['address'])) {
            $address = Address::create([
                'street' => $request['address']['street'],
                'city' => $request['address']['city'],
                'province' => $request['address']['province'],
                'postal_code' => $request['address']['postalCode'],
                'longitude' => $request['address']['longitude'],
                'latitude' => $request['address']['latitude'],
            ]);    
        }

        $addressId = null;

        if (isset($request['addressId'])) {
            $addressId = $request['addressId'];
        } else {
            $addressId = $address->id;
        }

        if ($addressId == null) {
            return response()->json([
                "status" => 0,
                "message" => "Invalid Address"
            ]);
        }
        
        $venue = new VenueResource(Venue::create([
            'name' => $request['name'],
            'description' => $request['description'],
            'owner_id' => $userAuth->id,
            'address_id' => $addressId,
            'status' => -1,
        ]));

        foreach ($request->facilitiesId as $facilityId) {
            VenueFacilities::create([
                'venue_id' => $venue->id,
                'facility_id' => $facilityId
            ]);
        }

        $this->uploadImages($request, $venue->id);

        return $venue;
    }

    public function destroy(Venue $venue) {
        $venue->status = 0;
        $venue->save();
    }

    public function filterOptions() {
        $floorTypes = Court::select('floor_type')->where('status', '<>', 0)->distinct()->get();
        $courtTypes = CourtType::select('type')->where('status', '<>', 0)->distinct()->get();
        
        $floorTypesArr = [];
        $courtTypesArr = [];
        foreach ($floorTypes as $floorType) {
            $floorTypesArr[] = $floorType['floor_type'];
        }

        foreach($courtTypes as $courtType) {
            $courtTypesArr[] = $courtType['type'];
        }

        $res = [
            'floorType' => $floorTypesArr,
            'courtType' => $courtTypesArr,
        ];

        return response()->json($res);
    }

    private function uploadImages(Request $request, $venueId) {
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
                    $fileName = $image->store("private/images/venues/$venueId");
                    VenueImage::create([
                        'venue_id' => $venueId,
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
        } else {
            return response()->json([
                'status' => false,
                'message' => 'No File(s)',
                'invalidFileName' => null,
            ], 500);
        }
    }

    public function searchSuggestion() {
        $venues = Venue::select('name')->where('status', '<>', 0)->distinct()->get();
        $res = [];
        foreach ($venues as $venue) {
            $res[] = $venue->name;
        }
        return response()->json($res);
    }
}
