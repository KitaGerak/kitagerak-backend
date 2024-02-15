<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateCourtRequest;
use App\Http\Requests\V1\StoreCourtRequest;
use App\Http\Resources\V1\CourtCollection;
use App\Http\Resources\V1\CourtResource;
use App\Models\Court;
use App\Models\CourtImage;

class CourtController extends Controller
{

    // public function index(Request $request) {
    //     return new CourtCollection(Court::paginate(10));
    // }

    public function show(Court $court) {
        return new CourtResource($court);
    }

    public function update(UpdateCourtRequest $request, Court $court) {
        $court->update($request->all());
    }

    public function updateImages(Request $request, Court $court) {
        $this->uploadImages($request, $court->id);
        if(isset($request->deleteImages)) {
            foreach ($request->deleteImages as $delImg) {
                CourtImage::where('id', $delImg)->update([
                    'status' => 0
                ]);
            }
        }
    }

    public function store(StoreCourtRequest $request) {
        $res = Court::create($request->all());
        $this->uploadImages($request, $res->id);

        return new CourtResource(Court::where('id', $res->id)->first());
    }

    public function uploadImages(Request $request, $courtId) {
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
                    $fileName = $image->store('private/images');
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
        $court->status = "0";
        $court->save();
    }
}
