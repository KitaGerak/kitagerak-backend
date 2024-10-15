<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    public function register() {
        return view('auth.register', [
            "title" => "Register New User"
        ]);
    }

    public function register2() {
        return view('auth.register2', [
            "title" => "Register New User"
        ]);
    }

    public function registerAddData(Request $request) {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:5|max:255',
            // 'password-confirm' => 'required|same:password'
        ]);

        $validatedData['password'] = Hash::make($validatedData['password']);

        $user = User::create($validatedData);

        $user->status = 1;
        $user->role_id = 4;
        $user->save();

        // $request->session()->flash('success', 'Registrasi sukses!');
        // return redirect('/halamanrahasialoginhanyauntukadmin');

        return back()->with('success', 'Registrasi sukses.');
    }

    public function login() {
        return view('auth.login', [
            "title" => "Login"
        ]);
    }

    public function authenticate(Request $request) {
        $credentials = $request->validate([
            'email' => 'required|email:dns',
            'password' => 'required'
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/home');
        }

        return back()->with('loginError', 'Login gagal. Periksa kembali email & password Anda');
    }

    public function logout(Request $request) {
        Auth::logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/halamanrahasialoginhanyauntukadmin');
    }
}
