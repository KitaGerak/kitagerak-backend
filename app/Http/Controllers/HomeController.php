<?php

namespace App\Http\Controllers;

use App\Models\SystemWarning;
use App\Models\Transaction;
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
        $systemWarnings = SystemWarning::all();
        
        return view('home', [
            "title" => "Admin Dashboard",
            "transactions" => $transaction,
            "systemWarnings" => $systemWarnings,
        ]);
    }
}
