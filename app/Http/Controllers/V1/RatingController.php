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

class RatingController extends Controller
{

    public function index(Request $request) {
        if (auth('sanctum')->check()){
            $userIdAuth = auth('sanctum')->user()->id;
            $userId = $request->query('user_id');
            
            if (auth('sanctum')->user()->role_id == 1) {
                return new RatingCollection(Rating::paginate(10)->withQueryString());
            } if ($userId && $userIdAuth == $userId) {
                return new RatingCollection(Rating::where('user_id', $userId)->paginate(10)->withQueryString());
            }
        }

        return response()->json([
            'status' => false,
            'message' => "User ID Required",
            'data' => null,
        ], 422);
    }

    public function store(StoreRatingRequest $request) {
        $schedules = Schedule::select('id')->where('court_id', $request->courtId)->get();
        $cleanShcedulesId = [];
        foreach ($schedules as $schedule) {
            array_push($cleanShcedulesId, $schedule->id);
        }
        
        $userId = auth('sanctum')->user()->id;
        if ($userId != $request->userId) {
            return response()->json([
                'status' => false,
                'message' => "User ID Required",
                'data' => null,
            ], 422);
        }
        $transactionCount = Transaction::where('user_id', $userId)->whereIn('id', $cleanShcedulesId)->count();
        $ratingCount = Rating::where('user_id', $userId)->where('court_id', $request->courtId)->count();
        if ($transactionCount <= $ratingCount) {
            return response()->json([
                'status' => false,
                'message' => "Rating <= transaction",
                'data' => null,
            ], 422);
        }

        $res = new RatingResource(Rating::create($request->all()));
        
        $court = Court::where('id', $request->courtId)->first();

        $courtRating = ($court['sum_rating'] + $request->rating) / 2;
        $courtNumberVote = $court['number_of_people'] + 1;

        Court::where('id', $request->courtId)->update([
            'sum_rating' => $courtRating,
            'number_of_people' => $courtNumberVote,
        ]);
        return $res;
    }

    public function storePhoto(Request $request, Rating $rating) {
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
                    $fileName = $file->store('private/images/ratings');
                    RatingPhoto::create([
                        'rating_id' => $rating->id,
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
