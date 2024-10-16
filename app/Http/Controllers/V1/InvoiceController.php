<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Http;

class InvoiceController extends Controller
{
    public function getXenditInvoice($invoiceId) {
        $response = Http::withHeaders([
            "Authorization" =>"Basic ".base64_encode(env("XENDIT_API_KEY", "") .':' . '')
        ])->
        get("https://api.xendit.co/v2/invoices/$invoiceId");

        if ($response->successful()) {
            return $response->collect();
        }
        return response()->json([
            'message' => 'Payment Gateway Error' 
        ], 500);
    }
}
