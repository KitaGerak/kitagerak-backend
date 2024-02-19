<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreTransactionRequest;
use App\Http\Requests\V1\UpdateTransactionRequest;
use App\Http\Resources\V1\TransactionCollection;
use App\Http\Resources\V1\TransactionResource;
use App\Models\Schedule;
use App\Models\Transaction;

class TransactionController extends Controller
{
    public function index(Request $request) {
        if (auth('sanctum')->check()){
            $userIdAuth = auth('sanctum')->user()->id;
            $userId = $request->query('user_id');
            
            if (auth('sanctum')->user()->role_id == 1) {
                return new TransactionCollection(Transaction::with('schedule')->with('court')->with('transactionStatus')->paginate(10));
            } if ($userId && $userIdAuth == $userId) {
                return new TransactionCollection(Transaction::where('user_id', $userId)->with('schedule')->with('court')->with('transactionStatus')->paginate(10));
            }
        }

        return response()->json([
            'status' => false,
            'message' => "User ID Required",
            'data' => null,
        ]);
    }

    public function show(Transaction $transaction) {
        if (auth('sanctum')->check()){
            $userIdAuth = auth('sanctum')->user()->id;

            if (auth('sanctum')->user()->role_id == 1) {
                return new TransactionResource($transaction->loadMissing('schedule')->loadMissing('court')->loadMissing('transactionStatus'));
            } else if ($transaction->user_id && $userIdAuth == $transaction->user_id) {
                return new TransactionResource($transaction->loadMissing('schedule')->loadMissing('court')->loadMissing('transactionStatus'));
            }
        }
        
        return response()->json([
            'status' => false,
            'message' => "Unauthenticated",
            'data' => null,
        ]);
    }

    public function store(StoreTransactionRequest $request) {
        $res = new TransactionResource(Transaction::create($request->all()));
        Schedule::where('id', $request->scheduleId)->update([
            'availability' => '0'
        ]);
        return $res;
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction) {
        $transaction->update($request->all());
        if ($request->transactionStatusId == 2 || $request->transactionStatusId == 3 || $request->transactionStatusId == 4) {
            Schedule::where('id', $transaction->scheduleId)->update([
                'availability' => '1'
            ]);
        }
    }
}
