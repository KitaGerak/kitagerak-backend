<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Fee;
use App\Models\Transaction;
use App\Models\VenueOwnerBalance;

class PaymentWebhookController extends Controller
{
    public function xenditWebhook(Request $request) {
        // Ini akan menjadi Token Verifikasi Callback Anda yang dapat Anda peroleh dari dasbor.
        // Pastikan untuk menjaga kerahasiaan token ini dan tidak mengungkapkannya kepada siapa pun.
        // Token ini akan digunakan untuk melakukan verfikasi pesan callback bahwa pengirim callback tersebut adalah Xendit
        $xenditXCallbackToken = env("XENDIT_WEBHOOK_CALLBACK_TOKEN", "");

        // Bagian ini untuk mendapatkan Token callback dari permintaan header, 
        // yang kemudian akan dibandingkan dengan token verifikasi callback Xendit
        // $reqHeaders = getallheaders();
        // $xIncomingCallbackTokenHeader = isset($reqHeaders['X-CALLBACK-TOKEN']) ? $reqHeaders['X-CALLBACK-TOKEN'] : "";

        $headerToken = $request->header('X-CALLBACK-TOKEN');

        // print_r($xIncomingCallbackTokenHeader . " - " . $xenditXCallbackToken);
        // Untuk memastikan permintaan datang dari Xendit
        // Anda harus membandingkan token yang masuk sama dengan token verifikasi callback Anda
        // Ini untuk memastikan permintaan datang dari Xendit dan bukan dari pihak ketiga lainnya.
        // return $headerToken . " - " . $xenditXCallbackToken;

        if($headerToken === $xenditXCallbackToken){
            // Permintaan masuk diverifikasi berasal dari Xendit
                
            // Baris ini untuk mendapatkan semua input pesan dalam format JSON teks mentah
            $rawRequestInput = file_get_contents("php://input");
            // Baris ini melakukan format input mentah menjadi array asosiatif
            $arrRequestInput = json_decode($rawRequestInput, true);
            // print_r($arrRequestInput);
            
            // $_id = $arrRequestInput['id'];
            $_externalId = $arrRequestInput['external_id'];
            // $_userId = $arrRequestInput['user_id'];
            $_status = $arrRequestInput['status'];
            $_paidAmount = (isset($arrRequestInput['paid_amount']) == true) ? $arrRequestInput['paid_amount'] : 0;
            // $_paidAt = $arrRequestInput['paid_at'];
            // $_paymentChannel = $arrRequestInput['payment_channel'];
            // $_paymentDestination = $arrRequestInput['payment_destination'];

            $order = Transaction::where('external_id', $_externalId)->first();

            $returnMessage = [
                'status' => 0,
                'message' => 'unknown'
            ];

            if ($order != null && $order->transaction_status_id != 2 && $_status == "PAID" && $_paidAmount >= $order->amount_rp) {
                $order->transaction_status_id = 1;
                $order->save();
                $returnMessage['status'] = 1;
                $returnMessage['message'] = "Success";

                $venueOwnerBalance = VenueOwnerBalance::where('user_id', $order->schedule->first()->court->venue->owner->id)->first();
                $fee = Fee::where('name', 'app_admin')->first();

                if ($venueOwnerBalance == null) {
                    VenueOwnerBalance::create([
                        "user_id" => $order->schedule->first()->court->venue->owner->id,
                        "balance" => $order->amount_rp - $fee->amount_rp,
                    ]);
                } else {
                    $venueOwnerBalance->balance = $venueOwnerBalance->balance + ($order->amount_rp - $fee->amount_rp);
                }

            } else if ($order != null && $order->order_status_id != 4 && $_status == "EXPIRED") {
                $order->order_status_id = 2;
                $order->save();
                $returnMessage['status'] = 1;
                $returnMessage['message'] = "Expired";
            } else if ($order != null && ($order->order_status_id == 2 || $order->order_status_id == 4)) {
                $returnMessage['status'] = 0;
                $returnMessage['message'] = "Payment has been already processed";
            } else if ($order!= null && $_paidAmount != $order->amount_rp) {
                $returnMessage['status'] = 0;
                $returnMessage['message'] = "Paid amount less than amount";
            }

            return response()->json($returnMessage);

            // Kamu bisa menggunakan array objek diatas sebagai informasi callback yang dapat digunaka untuk melakukan pengecekan atau aktivas tertentu di aplikasi atau sistem kamu.

        } else {
            // Permintaan bukan dari Xendit, tolak dan buang pesan dengan HTTP status 403
            http_response_code(403);
        }
    }
}
