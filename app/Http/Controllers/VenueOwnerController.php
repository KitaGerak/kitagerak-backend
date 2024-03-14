<?php

namespace App\Http\Controllers;

use App\Http\Requests\VenueOwnerLoginRequest;
use App\Http\Requests\VenueOwnerRegisterRequest;
use App\Http\Resources\VenueOwnerResource;
use App\Models\VenueOwner;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class VenueOwnerController extends Controller
{
    public function register(VenueOwnerRegisterRequest $request): JsonResponse
    {
        $data = $request->validated();

        if (VenueOwner::where('email', $data['email'])->count() == 1) {
            throw new HttpResponseException(response([
                "errors" => [
                    "email" => [
                        "email already exist"
                    ]
                ]
            ], 400));
        }

        if (VenueOwner::where('phone_number', $data['phone_number'])->count() == 1) {
            throw new HttpResponseException(response([
                "errors" => [
                    "phone_number" => [
                        "phone number already exist"
                    ]
                ]
            ], 400));
        }

        if (VenueOwner::where('national_id_number', $data['national_id_number'])->count() == 1) {
            throw new HttpResponseException(response([
                "errors" => [
                    "national_id_number" => [
                        "national id number already exist"
                    ]
                ]
            ], 400));
        }

        $venue_owner = new VenueOwner($data);
        $venue_owner->password = Hash::make($data['password']);
        $venue_owner->save();
        return (new VenueOwnerResource($venue_owner))->response()->setStatusCode(201);
    }

    public function login(VenueOwnerLoginRequest $request)
    {
        $data = $request->validated();

        $venue_owner = VenueOwner::where('email', $data['email'])->first();

        if (!$venue_owner || !Hash::check($data['password'], $venue_owner->password)) {
            // throw new HttpResponseException(response([
            //     "errros" => [
            //         "message" => [
            //             "email or password wrong"
            //         ]
            //     ]
            // ], 401));
            return response()->json([
                'success' => false,
                'message' => 'Invalid email or password!',
            ], 401);
        }

        $venue_owner->token = Str::uuid()->toString();
        $venue_owner->save();

        return response()->json([
            'success' => true,
            'token' => $venue_owner->token,
            'venue_owner_id' => $venue_owner->id,
            'venue_owner' => $venue_owner->email,
        ]);
    }
}
