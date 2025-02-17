<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreRatingRequest;
use App\Http\Resources\V1\RatingCollection;
use App\Http\Resources\V1\RatingResource;
use App\Models\Court;
use App\Models\Rating;
use App\Models\RatingPhoto;
use App\Models\Schedule;
use App\Models\Transaction;
use App\Services\V1\RatingQuery;

class RatingController extends Controller
{

    public function index(Request $request) {
        if (auth('sanctum')->check()){
            $userAuth = auth('sanctum')->user();

            $userId = $request->query('userId');
            $ownerId = $request->query('ownerId');

            if ($userId != null) {
                $userId = $userId['eq'];
            }

            if ($ownerId != null) {
                $ownerId = $ownerId['eq'];
            }

            if ($userAuth->role_id != 3) { //bukan admin
                if ($userAuth->role_id == 1) { //user - penyewa lapangan
                    if ($userId == null) {
                        return response()->json([
                            "status" => 0,
                            "message" => "Must specify user id"
                        ], 422);
                    }
        
                    if ($userId != $userAuth->id) {
                        return response()->json([
                            "status" => 0,
                            "message" => "Dilarang mengambil data user lain"
                        ], 422);
                    }
                } else if ($userAuth->role_id == 2) { //pemilik lapangan
                    if ($ownerId == null) {
                        return response()->json([
                            "status" => 0,
                            "message" => "Must specify owner id"
                        ], 422);
                    }
        
                    if ($ownerId != $userAuth->id) {
                        return response()->json([
                            "status" => 0,
                            "message" => "Dilarang mengambil data owner lain"
                        ], 422);
                    }
                }
            }

            $filter = new RatingQuery();
            $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]

            $res = Rating::select('ratings.*');

            if (count($queryItems) > 0) {
                $res = $res->leftJoin('users as u1', 'u1.id', 'ratings.user_id')->leftJoin('courts as c', 'c.id', 'ratings.court_id')->leftJoin('venues as v', 'v.id', 'c.venue_id')->leftJoin('users as u2', 'u2.id', 'v.owner_id')->where($queryItems);
            }

            return new RatingCollection($res->paginate(20)->withQueryString());
        }

        return response()->json([
            'status' => false,
            'message' => "Unauthenticated",
        ], 422);
    }

    public function store(StoreRatingRequest $request) {
        $userId = auth('sanctum')->user()->id;
        if ($userId != $request->userId) {
            return response()->json([
                'status' => false,
                'message' => "User ID Required",
                'data' => null,
            ], 422);
        }
        
        $transactionCount = Rating::where('user_id', $userId)->where('transaction_id', $request['transactionId'])->count();

        if ($transactionCount > 0) {
            return response()->json([
                'status' => false,
                'message' => "Anda sudah pernah melakukan review untuk transaksi dan lapangan bersangkutan",
                'data' => null,
            ], 422);
        }

        $rating = new RatingResource(Rating::create($request->all()));
        $this->uploadFile($request, $rating->id);
        
        $court = Court::where('id', $request->courtId)->first();

        $courtRating = ($court['sum_rating'] + $request->rating) / 2;
        $courtNumberVote = $court['number_of_people'] + 1;

        Court::where('id', $request->courtId)->update([
            'sum_rating' => $courtRating,
            'number_of_people' => $courtNumberVote,
        ]);

        return $rating;
    }

    private function uploadFile($request, $ratingId) {
        if ($request->has('files')) {
            $files = $request->file('files');

            $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'webp'];
            $allowedVideoExtensions = ['mp4', 'mov'];

            $invalidFile = [];

            foreach($files as $file) {
                $extension = $file->getClientOriginalExtension();

                if (!in_array(strtolower($extension), $allowedImageExtensions) && !in_array(strtolower($extension), $allowedVideoExtensions)) {
                    array_push($invalidFile, $file->getClientOriginalName());
                }
            }

            if(count($invalidFile) == 0) {
                $successFile = [];
                foreach($files as $file) {
                    $fileName = $file->store("private/images/ratings/$ratingId");
                    RatingPhoto::create([
                        'rating_id' => $ratingId,
                        'url' => $fileName,
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
}
