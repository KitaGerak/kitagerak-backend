<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionRequest;
use App\Http\Requests\V1\UpdateTransactionRequest;
use App\Http\Resources\V1\ScheduleCollection;
use App\Http\Resources\V1\TransactionCollection;
use App\Http\Resources\V1\TransactionResource;
use App\Models\BalanceWithdrawalDetail;
use App\Models\Fee;
use App\Models\Schedule;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use App\Models\User;
use App\Services\V1\TransactionQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class TransactionController extends Controller
{
    public function index(Request $request) {
        if (auth('sanctum')->check()){
            $userAuth = auth('sanctum')->user();
            $userId = $request->query('userId')["eq"];
            $ownerId = $request->query('ownerId')["eq"];

            if ($userAuth->role_id != 3) { //bukan admin
                if ($userAuth->role_id == 1) { //user - penyewa lapangan
                    if ($userId == null) {
                        return response()->json([
                            "status" => 0,
                            "message" => "Must specify user id"
                        ]);
                    }
        
                    if ($userId != $userAuth->id) {
                        return response()->json([
                            "status" => 0,
                            "message" => "Dilarang mengambil data user lain"
                        ]);
                    }
                } else if ($userAuth->role_id == 2) { //pemilik lapangan
                    if ($ownerId == null) {
                        return response()->json([
                            "status" => 0,
                            "message" => "Must specify owner id"
                        ]);
                    }
        
                    if ($ownerId != $userAuth->id) {
                        return response()->json([
                            "status" => 0,
                            "message" => "Dilarang mengambil data owner lain"
                        ]);
                    }
                }
            }
            
            $filter = new TransactionQuery();
            $queryItems = $filter->transform($request); //[['column', 'operator', 'value']]

            $res = Transaction::select('transactions.*')->with('schedules')->with('court')->with('status');

            if (count($queryItems) > 0) {
                $res = $res->leftJoin('users as u1', 'u1.id', 'transactions.user_id')->leftJoin('transaction_statuses as ts', 'ts.id', '=', 'transactions.transaction_status_id')->leftJoin('courts as c', 'c.id', 'transactions.court_id')->leftJoin('venues as v', 'v.id', 'c.venue_id')->leftJoin('users as u2', 'u2.id', 'v.owner_id')->where($queryItems);
            }
            return new TransactionCollection($res->paginate(20)->withQueryString());
        }

        return response()->json([
            'status' => false,
            'message' => "Unauthenticated",
        ], 422);
    }

    public function show(Transaction $transaction) {
        return $transaction;
        if (auth('sanctum')->check()){
            $userAuth = auth('sanctum')->user();

            if ($userAuth->role_id == 3 || $userAuth->id == $transaction->user->id || $userAuth->id == $transaction->court->venue->owner->id) {
                return new TransactionResource($transaction->loadMissing('schedules')->loadMissing('court')->loadMissing('status'));
            } 

            return response()->json([
                'status' => false,
                'message' => "Dilarang melihat data user lain",
            ], 422);

        }
        
        return response()->json([
            'status' => false,
            'message' => "Unauthenticated",
        ], 422);
    }

    private function xenditPayment($externalId, $user, $price, $fee) {
        //XENDIT HERE
        $xenditParams = [
            'external_id' => $externalId,
            'payer_email' => $user->email,
            'description' => "Pembayaran Penyewaan Lapangan pada " . $externalId,
            'amount' => $price + $fee,
            'success_redirect_url' => url(env('APP_HOST') . "/payment-success"),
            'failed_redirect_url' => url(env('APP_HOST') . "/payment-failed"),
            'invoice_duration' => 18000, // 5 hours
            'currency' => "IDR",
            "customer" => [
                "given_names" => $user->name,
                "surname" => $user->name,
                "email" => $user->email,
                // "mobile_number" => "+62" . $user->phone_number,
                "mobile_number" => "+6281803551677",
            ],
            "customer_notification_preference" => [
                "invoice_created" => [
                    // "whatsapp",
                    "email",
                ],
                "invoice_reminder" => [
                    // "whatsapp",
                    "email",
                ],
                "invoice_paid" => [
                    "whatsapp",
                    "email",
                ],
                "invoice_expired" => [
                    // "whatsapp",
                    "email",
                ]
            ],
            "fees" => [
                [
                    "type" => "Jasa Aplikasi",
                    "value" => $fee,
                ]
            ]
        ];

        $response = Http::withHeaders([
            "Authorization" =>"Basic ".base64_encode(env("XENDIT_API_KEY", "") .':' . '')
        ])->
        post('https://api.xendit.co/v2/invoices/', $xenditParams);

        return $response;
    }

    public function checkSchedules(Request $request) {
        $dayOfWeeks = "";
        $timeStart = "";
        $timeFinish = "";

        if ($request->startDate != null && $request->monthInterval != null && $request->schedules != null) {
            foreach ($request->schedules as $i=>$schedule) {
                if ($i != count($request->schedules) - 1) {
                    $dayOfWeeks .= "'" . $schedule['dayOfWeek'] . "'" . ",";
                } else {
                    $dayOfWeeks .= "'" . $schedule['dayOfWeek'] . "'";
                }
                foreach($schedule['times'] as $j=>$time) {
                    $tm = explode("-", $time);
                    if ($j != count($schedule['times']) - 1) {
                        $timeStart .= "'" . $tm[0] . "'" . ", ";
                        $timeFinish .= "'" . $tm[1] . "'" . ", ";
                    } else {
                        $timeStart .= "'" . $tm[0] . "'";
                        $timeFinish .= "'" . $tm[1] . "'";
                    }
                }
            }

            $res = DB::Select("SELECT *, DAYOFWEEK(date) AS dayOfWeek FROM `schedules` WHERE date > NOW() AND date >= ? AND court_id = ? AND date <= DATE_ADD(?, interval ? MONTH) AND availability = 1 AND status = 1 AND time_start IN ($timeStart) AND time_finish IN ($timeFinish) HAVING dayOfWeek IN ($dayOfWeeks) ORDER BY date, time_start", [$request->startDate, $request->courtId, $request->startDate, $request->monthInterval]);
        } else if ($request->scheduleIds != null) {
            $scheduleIds = "";
            foreach ($request->scheduleIds as $i=>$scheduleId) {
                if ($i != count($request->scheduleIds) - 1) {
                    $scheduleIds .= $scheduleId . ", ";
                } else {
                    $scheduleIds .= $scheduleId;
                }
            }

            $res = DB::Select("SELECT *, DAYOFWEEK(date) AS dayOfWeek FROM `schedules` WHERE id IN ($scheduleIds)");
        } else {
            return response()->json([
                'status' => false,
                'message' => "Parameter tidak lengkap",
            ], 422);
        }

        return new ScheduleCollection($res);
    }

    public function store(StoreTransactionRequest $request) {

        $checkUserSanctum = $this->checkUserSanctum($request->userId);

        if ($checkUserSanctum == 0) {
            return response()->json([
                'status' => false,
                'message' => "Unauthenticated.",
            ], 422);
        } else if ($checkUserSanctum == -4 || auth('sanctum')->user()->role_id != 1) {
            return response()->json([
                'status' => false,
                'message' => "Akun Anda tidak dapat melakukan pemesanan",
            ], 422);
        }

        $scheduleIds = "";
        foreach ($request->scheduleIds as $i=>$scheduleId) {
            if ($i != count($request->scheduleIds) - 1) {
                $scheduleIds .= $scheduleId . ", ";
            } else {
                $scheduleIds .= $scheduleId;
            }
        }

        $unavailableSchedule = DB::Select("SELECT COUNT(*) AS `countUnavailableSchedule` FROM `schedules` WHERE id IN ($scheduleIds) AND (availability = 0 OR status = 0)");
        $courtIdCount = DB::Select("SELECT COUNT(DISTINCT(court_id)) AS courtIdCount FROM `schedules` WHERE id IN ($scheduleIds)"); //Cara untuk cek apakah user pesan dari court yang sama / tidak. Jika tidak, TOLAK

       

        if ($unavailableSchedule[0]->countUnavailableSchedule == 0 && $courtIdCount[0]->courtIdCount == 1) {
            $type = "";
            $query = "";
            if ($request->type == "member") {
                $type = "MEMBER";
                $query = "member_price - member_price * member_discount";
            } else {
                $type = "DAILY";
                $query = "regular_price - regular_price * regular_discount";
            }

            $externalId = $type . "_" . rand();
            $user = auth('sanctum')->user();

            $insertedTransaction = Transaction::create([
                'external_id' => $externalId,
                'user_id' => $request->userId,
                'amount_rp' => -1,
                'transaction_status_id' => 5,
            ]);

            DB::statement("UPDATE `schedules` SET transaction_id = $insertedTransaction->id, availability = 0 WHERE id IN ($scheduleIds)");

            $totalPrice = DB::Select("SELECT SUM($query) AS amount_rp FROM `schedules` WHERE transaction_id = $insertedTransaction->id")[0]['amount_rp'];
            $fee = Fee::where('name', 'app_admin')->first()->amount_rp;

            //XENDIT
            $user = User::where('id', $request->userId)->first();
            $fee = Fee::where('name', 'app_admin')->first()->amount_rp;
            $externalId = "DAILY_" . time();

            $xenditResponse = $this->xenditPayment($externalId, $user, $totalPrice, $fee);
            if (!$xenditResponse->successful()) {
                return response()->json([
                    "status" => false,
                    "message" => "Payment failed because system error",
                ], 500);
            }

            $schedule = Schedule::where('id', $request->scheduleIds[0]);

            // DB::statement("UPDATE `transactions` SET checkout_link = '" . $xenditResponse->collect()['invoice_url'] . "', invoice_id = '" . $xenditResponse->collect()['id'] . "', amount_rp = (SELECT SUM($query) AS amount_rp FROM `schedules` WHERE transaction_id = $insertedTransaction->id) + (SELECT amount_rp FROM `fees` WHERE name = 'app_admin'), schedule_id = " . $request->scheduleIds[0] . " WHERE id = $insertedTransaction->id");

            DB::statement("UPDATE `transactions` SET checkout_link = '" . $xenditResponse->collect()['invoice_url'] . "', invoice_id = '" . $xenditResponse->collect()['id'] . "', amount_rp = (SELECT SUM($query) AS amount_rp FROM `schedules` WHERE transaction_id = $insertedTransaction->id) + (SELECT amount_rp FROM `fees` WHERE name = 'app_admin'), court_id = " . $schedule->court->id . " WHERE id = $insertedTransaction->id");

            return new TransactionResource($insertedTransaction);
        } else {
            return response()->json([
                'status' => false,
                'message' => "Maaf, ada jadwal yang tidak tersedia [Mungkin keduluan orang lain]",
            ], 422);
        }
    }

    // store: replaced with new method

    // getAvailMemberSchedules: removed

    // getUnAvailMemberSchedules: removed

    // tgl indo: removed because unused

    //bulk store: removed

    private function checkUserSanctum($currentUserId) {
        if (auth('sanctum')->check()) {
            $userIdAuth = auth('sanctum')->user();
            if ($userIdAuth->id == $currentUserId) {
                return $currentUserId;
            } elseif ($userIdAuth->roleId == 4) { // 4 == Admin.
                return -4;
            } 
        }
        return 0;
    }

    private function checkCancelation($transaction) {
        $currentUser = $this->checkUserSanctum($transaction->user_id);

        if ($currentUser != -4) {
            $check = DB::Select("SELECT COUNT(t.id) AS countTransactions FROM `schedules` s LEFT JOIN `transactions` t ON s.transaction_id = t.id LEFT JOIN `courts` c ON c.id = s.court_id LEFT JOIN `venues` v ON v.id = c.venue_id WHERE t.id = $transaction->id AND (t.user_id = $currentUser OR v.owner_id = $currentUser) ORDER BY s.date ASC, s.time_start ASC LIMIT 1;")[0];            
            if ($check->countTransactions <= 0) {    
                return -2; 
            }
        }

        $countTransaction = DB::select("SELECT COUNT(DISTINCT(transaction_id)) AS countTransaction FROM `transaction_schedule_cancelation_histories` h LEFT JOIN `transactions` t ON t.id = h.transaction_id WHERE t.user_id = $transaction->user_id AND h.created_at BETWEEN DATE_SUB(NOW(), INTERVAL 30 DAY) AND NOW()")[0];
                
        //cek apakah user ybs sudah punya catatan pembatalan lebih dari 5x sepanjang bulan ini?
        if ((!isset($countTransaction->courtTransaction) && $countTransaction->countTransaction < 5) || $currentUser == -4) { //currentUser -4 = Admin
            
            $firstSchedule = DB::Select("SELECT CONCAT(date, ' ', time_start) AS dateTime, t.external_id AS externalId, t.user_id AS userId FROM `schedules` s LEFT JOIN `transactions` t ON s.transaction_id = t.id WHERE s.transaction_id = $transaction->id ORDER BY s.date ASC, s.time_start ASC LIMIT 1;")[0];
                
            $dateTimeNow1d = date('Y-m-d H:i:s', strtotime('+1 days'));
            $dateTimeNow = date('Y-m-d H:i:s');

            if (strtotime($firstSchedule->dateTime) > strtotime($dateTimeNow1d)) { //pengembalian 100%
                if (str_contains($firstSchedule->externalId, 'DAILY')) { //harian
                    return 1;
                } else { // bulanan / member
                    return 0.5;
                }
            } else if (strtotime($firstSchedule->dateTime) < strtotime($dateTimeNow1d) && strtotime($firstSchedule->dateTime) > strtotime($dateTimeNow) && ($firstSchedule->userId == $transaction->user_id || $currentUser == -4)) { //pengembalian 50% dan harus customer / Admin yang melakukan pembatalan. Kalau sudah kurang dari 24 jam, pemilik lapangan tidak bisa melakukan pembatalan! || currentUser -4 = Admin
                if (str_contains($firstSchedule->externalId, 'DAILY')) { //harian
                    return 0.5;
                } else { // bulanan / member
                    return 0.5;
                }
            } else {
                return 0;
            }
        } else {
            return -1;
        }      
    }


    public function checkTransactionCancelation(Transaction $transaction) {
        $refund = $this->checkCancelation($transaction);
        if ($refund == -1) {
            return response()->json([
                "status" => false,
                "message" => "Gagal melakukan pembatalan pesanan. Kuota pembatalan Anda telah habis. Anda sudah membatalkan pesanan 5 (lima) kali sepanjang sebulan terakhir."
            ]);
        } else if ($refund == 1 || $refund == 0.5) {
            $percentage = 0;
            if ($refund == 1) {
                $percentage = 100;
            } else {
                $percentage = 50;
            }
            return response()->json([
                "status" => true,
                "message" => "Pembatalan dapat dilakukan dengan pengembalian dana sebesar $percentage% kepada customer dipotong pajak / fee."
            ]);
        } else {
            return response()->json([
                "status" => false,
                "message" => "Tidak dapat melakukan pembatalan."
            ]);
        }
    }


    public function cancelTransaction(Transaction $transaction) {
        $refundPercentage = $this->checkCancelation($transaction);

        if ($transaction->transaction_status_id == 3) {
            return response()->json([
                "status" => false,
                "message" => "Gagal melakukan pembatalan pesanan. Transaksi ini sudah berstatus 'DIBATALKAN'."
            ]);
        }

        if ($refundPercentage == -1) {
            return response()->json([
                "status" => false,
                "message" => "Gagal melakukan pembatalan pesanan. Kuota pembatalan Anda telah habis. Anda sudah membatalkan pesanan 5 (lima) kali sepanjang sebulan terakhir."
            ]);
        } else if ($refundPercentage == 1 || $refundPercentage == 0.5) {
            $schedules = Schedule::where('transaction_id', $transaction->id)->get();

            foreach($schedules as $schedule) {
                DB::Statement("INSERT INTO `transaction_schedule_cancelation_histories` VALUES (NULL, $schedule->id, $transaction->id, DEFAULT, DEFAULT)");
            }

            if ($transaction->transaction_status_id == 1) { // konsumen sudah bayar, jadi harus ada saldo yang dikembalikan

                $afftectedRow = DB::update("UPDATE `users` SET balance = balance + ((SELECT amount_rp * $refundPercentage FROM `transactions` WHERE id = $transaction->id) - (SELECT amount_rp FROM `fees` WHERE name = 'app_admin')) WHERE id = $transaction->user_id");
                $fee = Fee::where('name', 'app_admin')->first()->amount_rp;
            }

            BalanceWithdrawalDetail::create([
                'user_id' => $transaction->user_id,
                'amount' => $transaction->amount_rp - $fee,
                'info' => "Pengembalian dana dari transaksi: " . $transaction->external_id,
                'status' => 1,
            ]);

            $currentUser = $this->checkUserSanctum($transaction->user_id);

            if ($currentUser == -4) { // dibatalkan oleh admin;
                $transaction->transaction_status_id = 2; //= dibatalkan sistem;
            } else if ($currentUser == $transaction->user_id) {
                $transaction->transaction_status_id = 3; //= dibatalkan penyewa / konsumen;
            } else {
                $transaction->transaction_status_id = 4; //= dibatalkan pemilik lapangan;
            }
            $transaction->save();

            DB::raw("UPDATE schedules SET availability = 1 WHERE id IN (SELECT schedule_id FROM transaction_schedule_details WHERE transaction_id = $transaction->id)");

            return response()->json([
                "status" => true,
                "message" => "Sukses membatalkan pesanan"
            ]); 
        } else {
            return response()->json([
                "status" => false,
                "message" => "Tidak dapat melakukan pembatalan."
            ]);
        }
    }


    public function update(UpdateTransactionRequest $request, Transaction $transaction) {
        $transaction->update($request->all());
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
