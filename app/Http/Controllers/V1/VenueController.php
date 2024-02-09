<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BulkStoreVenueRequest;
use App\Http\Requests\V1\StoreVenueRequest;
use App\Http\Requests\V1\UpdateVenueRequest;
use App\Http\Resources\V1\VenueCollection;
use App\Http\Resources\V1\VenueResource;
use App\Models\Address;
use App\Models\CourtType;
use App\Models\Venue;
use Illuminate\Support\Arr;

class VenueController extends Controller
{

    private $courtTypeObj;

    public function __construct(CourtType $courtType) {
        $this->courtTypeObj = $courtType;
    }

    public function index(Request $request) {
        $includeCourts = $request->query('includeCourts');
        $ownerId = $request->query('ownerId');
        $courtType = $request->query('courtType');

        $res = Venue::where('status', '<>', 0);

        if ($includeCourts) {
            $this->courtTypeObj->withCourts = true;
            $res->with('courts');
        }

        if ($courtType) {
            $this->courtTypeObj->withCourts = true;
            return new VenueCollection($this->courtTypeObj->where('type', $courtType)->with('venues')->first()['venues']);
        }

        if ($ownerId) {
            $res->where('owner_id', $ownerId);
        }
        
        return new VenueCollection($res->paginate(10));
    }

    public function show(Venue $venue, Request $request) {
        $includeCourts = $request->query('includeCourts');

        if ($includeCourts) {
            return new VenueResource($venue->loadMissing('courts'));
        }
        return new VenueResource($venue);
    }

    public function update(UpdateVenueRequest $request, Venue $venue) {
        $venue->update($request->all());
    }

    public function store(StoreVenueRequest $request) {
        // return $request['address'];
        $address = Address::create([
            'street' => $request['address']['street'],
            'city' => $request['address']['city'],
            'province' => $request['address']['province'],
            'postal_code' => $request['address']['postalCode'],
            'longitude' => $request['address']['longitude'],
            'latitude' => $request['address']['latitude'],
        ]);
        return new VenueResource(Venue::create([
            'name' => $request['name'],
            'owner_id' => $request['owner_id'],
            'image_url' => $request['image_url'],
            'address_id' => $address['id'],
        ]));
    }

    public function bulkStore(BulkStoreVenueRequest $request) {
        $bulk = collect($request->all())->map(function($arr, $key) {
            return Arr::except($arr, ['ownerId', 'imageUrl']);
        });

        Venue::insert($bulk->toArray());
    }
}
