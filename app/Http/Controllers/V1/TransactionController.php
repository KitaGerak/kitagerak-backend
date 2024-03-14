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
use App\Models\TransactionStatus;
use App\Services\V1\TransactionQuery;

class TransactionController extends Controller
{
    public function index(Request $request) {
        if (auth('sanctum')->check()){
            $userIdAuth = auth('sanctum')->user()->id;
            $userId = $request->query('user_id')["eq"];

            $filter = new TransactionQuery();
            $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]

            $res = Transaction::select('transactions.id', 'transactions.external_id', 'transactions.order_id', 'transactions.user_id', 'transactions.schedule_id', 'transactions.court_id', 'transactions.reason', 'transactions.transaction_status_id', 'transactions.created_at', 'transactions.updated_at')->with('schedule')->with('court')->with('transactionStatus');

            if (count($queryItems) > 0) {
                $res->leftJoin('transaction_statuses', 'transaction_statuses.id', '=', 'transactions.transaction_status_id')->where($queryItems);
            }
            
            if (auth('sanctum')->user()->role_id == 1) {
                return new TransactionCollection($res->paginate(10)->withQueryString());
            } if ($userId && $userIdAuth == $userId) {
                $res->where('transactions.user_id', $userId);
                return new TransactionCollection($res->paginate(10)->withQueryString());
            }
        }

        return response()->json([
            'status' => false,
            'message' => "User ID Required",
            'data' => null,
        ], 422);
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
        ], 422);
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

    public function filterOptions() {
        $transactionStatuses = TransactionStatus::all();
        $res = [];
        foreach($transactionStatuses as $i=>$transactionStatus) {
            $res[] = $transactionStatus['status'];
        }

        return response()->json($res);
    }
}
