<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\StoreTransactionRequest;
use App\Http\Requests\V1\UpdateTransactionRequest;
use App\Http\Resources\V1\TransactionCollection;
use App\Http\Resources\V1\TransactionResource;
use App\Models\Fee;
use App\Models\Schedule;
use App\Models\Transaction;
use App\Models\TransactionScheduleDetail;
use App\Models\TransactionStatus;
use App\Models\User;
use App\Services\V1\TransactionQuery;
use Illuminate\Support\Facades\Http;

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
        $schedule = Schedule::where('id', $request->scheduleId)->first();

        if ($schedule->availability == 0 || $schedule->status == 0) {
            return response()->json([
                'message' => "Unavailable Schedule",
            ]);
        }

        $user = User::where('id', $request->userId)->first();
        $fee = Fee::where('name', 'app_admin')->first()->amount_rp;

        // //XENDIT HERE
        // $xenditParams = [
        //     'external_id' => $request->externalId,
        //     'payer_email' => $user->email,
        //     'description' => "Pembayaran Penyewaan Lapangan pada " . $schedule->date,
        //     'amount' => $schedule->price + $fee,
        //     //TODO:: Nunggu domain
        //     'success_redirect_url' => url("https://www.google.com"),
        //     'failed_redirect_url' => url("https://www.google.com"),
        //     'invoice_duration' => 18000, // 5 hours
        //     'currency' => "IDR",
        //     "customer" => [
        //         "given_names" => $user->name,
        //         "surname" => $user->name,
        //         "email" => $user->email,
        //         "mobile_number" => "+62" . $user->phone_number,
        //         // "addresses" => [
        //         //     [
        //         //         "city" => "Jakarta Selatan",
        //         //         "country" => "Indonesia",
        //         //         "postal_code" => "12345",
        //         //         "state" => "Daerah Khusus Ibukota Jakarta",
        //         //         "street_line1" => "Jalan Makan",
        //         //         "street_line2" => "Kecamatan Kebayoran Baru"
        //         //     ]
        //         // ]
        //     ],
        //     "customer_notification_preference" => [
        //         "invoice_created" => [
        //             // "whatsapp",
        //             "email",
        //         ],
        //         "invoice_reminder" => [
        //             // "whatsapp",
        //             "email",
        //         ],
        //         "invoice_paid" => [
        //             "whatsapp",
        //             "email",
        //         ],
        //         "invoice_expired" => [
        //             // "whatsapp",
        //             "email",
        //         ]
        //     ],
        //     "fees" => [
        //         [
        //             "type" => "Jasa Aplikasi",
        //             "value" => $fee,
        //         ]
        //     ]
        // ];

        // $response = Http::withHeaders([
        //     "Authorization" =>"Basic ".base64_encode(env("XENDIT_API_KEY", "") .':' . '')
        // ])->
        // post('https://api.xendit.co/v2/invoices/', $xenditParams);

        // if (!$response->successful()) {
        //     // return redirect()->intended('/checkout-failed');
        //     return response()->json([
        //         "message" => "Payment Failed because system error",
        //     ], 500);
        // }
        // //XENDIT UNTIL HERE

        $transaction = Transaction::create($request->all());
        $transaction->amount_rp = $schedule->price;
        $transaction->transaction_status_id = 5; // menunggu konfirmasi / pembayaran

        // // XENDIT HERE
        // $transaction->checkout_link = $response->collect()['invoice_url'];
        // $transaction->invoice_id = $response->collect()['id'];
        // // UNTIL HERE

        $transaction->save();

        // $res = new TransactionResource($transaction); //UNUSED LINE
        $schedule->availability = "0";
        $schedule->save();
        
        return true;
    }

    public function bulkStore(Request $request) {
        if (isset($request->externalId) && isset($request->userId) && isset($request->scheduleId)) {
            if (!is_array($request->scheduleId)) {
                return response()->json([
                    "message" => "schedule ID must be array of integer",
                ], 500);
            }
            $amount_rp = 0;
            foreach($request->scheduleId as $scheduleId) {
                $schedule = Schedule::where('id', $scheduleId)->where('status', '<>', 0)->where('availability', '<>', 0)->first();
                if ($schedule == null) {
                    return response()->json([
                        "message" => "1 or more schedule is unavailable",
                    ], 500);
                }
                $amount_rp += $schedule->price;
            }

            $user = User::where('id', $request->userId)->first();
            $fee = Fee::where('name', 'app_admin')->first()->amount_rp;

            // // XENDIT HERE
            // $xenditParams = [
            //     'external_id' => $request->externalId,
            //     'payer_email' => $user->email,
            //     'description' => "Pembayaran Penyewaan Lapangan pada " . $schedule->date,
            //     'amount' => $schedule->price + $fee,
            //     //TODO:: nunggu domain
            //     'success_redirect_url' => url("https://www.google.com"),
            //     'failed_redirect_url' => url("https://www.google.com"),
            //     'invoice_duration' => 18000, // 5 hours
            //     'currency' => "IDR",
            //     "customer" => [
            //         "given_names" => $user->name,
            //         "surname" => $user->name,
            //         "email" => $user->email,
            //         "mobile_number" => "+62" . $user->phone_number,
            //         // "addresses" => [
            //         //     [
            //         //         "city" => "Jakarta Selatan",
            //         //         "country" => "Indonesia",
            //         //         "postal_code" => "12345",
            //         //         "state" => "Daerah Khusus Ibukota Jakarta",
            //         //         "street_line1" => "Jalan Makan",
            //         //         "street_line2" => "Kecamatan Kebayoran Baru"
            //         //     ]
            //         // ]
            //     ],
            //     "customer_notification_preference" => [
            //         "invoice_created" => [
            //             // "whatsapp",
            //             "email",
            //         ],
            //         "invoice_reminder" => [
            //             // "whatsapp",
            //             "email",
            //         ],
            //         "invoice_paid" => [
            //             "whatsapp",
            //             "email",
            //         ],
            //         "invoice_expired" => [
            //             // "whatsapp",
            //             "email",
            //         ]
            //     ],
            //     //TODO :: Fees
            //     "fees" => [
            //         [
            //             "type" => "Jasa Aplikasi",
            //             "value" => $fee,
            //         ]
            //     ]
            // ];
    
            // $response = Http::withHeaders([
            //     "Authorization" =>"Basic ".base64_encode(env("XENDIT_API_KEY", "") .':' . '')
            // ])->
            // post('https://api.xendit.co/v2/invoices/', $xenditParams);
    
            // if (!$response->successful()) {
            //     // return redirect()->intended('/checkout-failed');
            //     return response()->json([
            //         "message" => "Payment Failed because system error",
            //     ], 500);
            // }
            // // XENDIT UNTIL HERE

            $transaction = Transaction::create([
                "external_id" => $request->externalId,
                "user_id" => $request->userId,
                "transaction_status_id" => 5, // menunggu konfirmasi / pembayaran
                "amount_rp" => $amount_rp,
                "schedule_id" => $request->scheduleId[1],

                // // XENDIT HERE
                // "checkout_link" => $response->collect()['invoice_url'],
                // "invoice_id" => $response->collect()['id'],
                // // UNTIL HERE

            ]);
            foreach($request->scheduleId as $scheduleId) {
                TransactionScheduleDetail::create([
                    "schedule_id" => $scheduleId,
                    "transaction_id" => $transaction->id,
                ]);
                Schedule::where('id', $scheduleId)->update([
                    'availability' => 0,
                ]);
            }
        } else {
            return response()->json([
                "message" => "Required Parameter: External ID, User ID, schedule ID"
            ], 500);
        }

        return true;
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
