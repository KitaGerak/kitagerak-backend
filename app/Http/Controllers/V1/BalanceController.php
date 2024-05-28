<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\VenueOwnerBalance;

class BalanceController extends Controller
{
    public function index(User $user) {
        if (auth('sanctum')->check()){
            $userAuth = auth('sanctum')->user();
            if ($userAuth->id == $user->id) { 
                return response()->json([
                    'status' => true,
                    'message' => '',
                    'data' => [
                        'balance' => VenueOwnerBalance::where('user_id', $user->id)->first()->balance
                    ],
                ]);
            }
        }
        
        return response()->json([
            'status' => false,
            'message' => 'Autentikasi gagal',
            'data' => [],
        ]);
    }
}
