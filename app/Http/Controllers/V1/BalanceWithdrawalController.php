<?php

namespace App\Http\Controllers\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\BalanceWithdrawalDetailCollection;
use App\Models\BalanceWithdrawalDetail;
use Illuminate\Http\Request;

class BalanceWithdrawalController extends Controller
{
    public function index(Request $request) {
        if ($request->query('userId') == null) {
            //error
            return response()->json([
                "status" => 0,
                "message" => "User ID Required"
            ]);
        }

        if (auth('sanctum')->check()) {
            $userAuth = auth('sanctum')->user();
            if ($request->query('userId') != $userAuth->id) {
                //bukan ybs
                return response()->json([
                    "status" => 0,
                    "message" => "Gagal mengambil data. Anda bukan ID ybs"
                ]);
            } else if ($userAuth->role_id != 4) { //pemilik lapangan / admin
                //bukan admin
                return response()->json([
                    "status" => 0,
                    "message" => "Gagal mengambil data. Anda bukan ID ybs"
                ]);
            }
        } else {
            return response()->json([
                "status" => 0,
                "message" => "Unauthenticated"
            ]);
        }

        return new BalanceWithdrawalDetailCollection(BalanceWithdrawalDetail::where('user_id', $userAuth->id)->get());
    }
}
