<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreCourtTypeRequest;
use App\Http\Requests\V1\UpdateCourtTypeRequest;
use App\Http\Resources\V1\CourtTypeCollection;
use App\Http\Resources\V1\CourtTypeResource;
use App\Models\CourtType;

class CourtTypeController extends Controller
{
    public function index() {
        return new CourtTypeCollection(CourtType::all());
    }

    public function store(StoreCourtTypeRequest $request) {
        return new CourtTypeResource(CourtType::create($request->all()));
    }

    public function update(CourtType $courtType, UpdateCourtTypeRequest $request) {
        $courtType->update($request->all());
    }

    public function destroy(CourtType $courtType) {
        $courtType->status = "0";
        $courtType->save();
    }
    
}
