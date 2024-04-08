<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class VenueOwnerController extends Controller
{
    public function getEmployees($ownerId)
    {
        $user = User::where('owner_id', $ownerId)->get();
        return response()->json(["data" => $user]);
    }
}
