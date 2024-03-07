<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\BulkStoreVenueRequest;
use App\Http\Requests\V1\StoreVenueRequest;
use App\Http\Requests\V1\UpdateVenueRequest;
use App\Http\Resources\V1\VenueResource;
use App\Models\Address;
use App\Models\Court;
use App\Models\CourtType;
use App\Models\Venue;
use App\Services\V1\VenueQuery;
use Illuminate\Support\Arr;

class VenueController extends Controller
{

    private $courtTypeObj;

    public function __construct(CourtType $courtType) {
        $this->courtTypeObj = $courtType;
    }

    public function index(Request $request) {
        $includeCourts = $request->query('includeCourts');

        $filter = new VenueQuery();
        $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]

        $res = Venue::where('venues.status', '<>', 0);
        if ($includeCourts) {
            $this->courtTypeObj->withCourts = true;
            $res->with('courts');
        }

        $limit = $request->query('limit');

        if ($limit == "") {
            $sortBy = $request->query('sort_by');
            $orderBy = $request->query('order_by');

            $contains = $request->query('contains');

            if ($sortBy != "" || count($queryItems) > 0) {
                $res->selectRaw('venues.*, SUM(courts.number_of_people) AS number_of_people, AVG(courts.sum_rating) AS sum_rating, AVG(courts.price) AS price')->groupBy('id')->leftJoin('courts', 'courts.venue_id', '=', 'venues.id');
            } else {
                $res->select('venues.id', 'venues.name', 'venues.address_id', 'venues.description', 'venues.image_url', 'venues.owner_id', 'venues.status');
            }

            if ($sortBy != "") {
                $validSortBy = ['rating', 'price', 'numberOfReviews'];
                $validOrderBy = ['asc', 'desc'];

                foreach (explode(',', $sortBy) as $i=>$sb) {
                    $explodeOrderBy = explode(',', $orderBy);
                    // echo !isset($explodeOrderBy[$i]) ? "desc" : $explodeOrderBy[$i] . "->" . $i . " ... ";
                    if (in_array($sb, $validSortBy) && in_array(!isset($explodeOrderBy[$i]) ? "desc" : $explodeOrderBy[$i], $validOrderBy)) {
                        $sort = $sb;
                        if ($sb == 'numberOfReviews') {
                            $sort = 'number_of_people';
                        } else if ($sb == 'rating') {
                            $sort = 'sum_rating';
                        }
                        $res->orderBy($sort, !isset($explodeOrderBy[$i]) ? "desc" : $explodeOrderBy[$i]);
                    }
                }
            }

            if ($contains != "") {
                $res->where('venues.name', 'like', '%' . $contains . '%');
            }

            if (count($queryItems) == 0) {
                return VenueResource::collection($res->distinct()->paginate(10)->withQueryString());
            } else {
                $res->leftJoin('court_types', 'courts.court_type_id', '=', 'court_types.id')->where($queryItems);

                return VenueResource::collection($res->paginate(10)->withQueryString());
            }
        } else {
            $res->select('venues.id', 'venues.name', 'venues.address_id', 'venues.description', 'venues.image_url', 'venues.owner_id', 'venues.status');
            return VenueResource::collection($res->distinct()->paginate($limit)->withQueryString());
        }

        // $ownerId = $request->query('ownerId');
        // $courtType = $request->query('courtType');


        // if ($courtType) {
        //     // $courtTypeCount = CourtType::where('type', $courtType)->where('status', '1')->get()->count();
        //     // if ($courtTypeCount == 0) {
        //     //     return response()->json([
        //     //         'data' => []
        //     //     ]);
        //     // }
        //     $this->courtTypeObj->withCourts = true;
        //     return new VenueCollection($this->courtTypeObj->where('type', $courtType)->with('venues')->first()['venues']);
        // }

        // if ($ownerId) {
        //     $res->where('owner_id', $ownerId);
        // }
    }

    public function show(Venue $venue, Request $request) {
        $includeCourts = $request->query('includeCourts');

        if ($includeCourts) {
            return new VenueResource($venue->loadMissing('courts'));
        }
        return new VenueResource($venue);
    }

    public function update(UpdateVenueRequest $request, Venue $venue) {
        if (isset($request['address'])) {
            $address = $venue->address;
            foreach($request['address'] as $key=>$addr) {
                if ($key == "postalCode") {
                    $address->postal_code = $addr;
                } else {
                    $address->$key = $addr;
                }
            }

            $address->save();
        }
        $venue->update($request->all());
    }

    public function store(StoreVenueRequest $request) {
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

    public function destroy(Venue $venue) {
        $venue->status = "0";
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
            'courtSize' => [
                [
                    'display' => '<= 10m',
                    'query' => 'courtSize[lte]=10'
                ],

                [
                    'display' => '10-15m',
                    'query' => 'courtSize[gte]=10&courtSize[lte]=15'
                ],

                [
                    'display' => '16-20m',
                    'query' => 'courtSize[gte]=16&courtSize[lte]=20'
                ],

                [
                    'display' => '21-25m',
                    'query' => 'courtSize[gte]=21&courtSize[lte]=25'
                ],

            ]
        ];

        return response()->json($res);
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
