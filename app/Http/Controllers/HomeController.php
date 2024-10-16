<?php

namespace App\Http\Controllers;

use App\Http\Resources\V1\TransactionStatusCollection;
use App\Models\SystemWarning;
use App\Models\Transaction;
use App\Models\TransactionStatus;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $transaction = Transaction::all();
        $systemWarnings = SystemWarning::where('status', 1)->get();
        
        return view('home', [
            "title" => "Admin Dashboard",
            "transactions" => $transaction,
            "systemWarnings" => $systemWarnings,
        ]);
    }

    public function removeSystemWarning(SystemWarning $systemWarning) {
        $systemWarning->status = 0;
        $systemWarning->save();

        return redirect()->back()->with('success', 'Berhasil hapus system warning');
    }

    public function test() {
        return new TransactionStatusCollection(TransactionStatus::all()->loadMissing('transactions'));
    }
}
