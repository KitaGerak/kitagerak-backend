<?php

// namespace App\Http\Controllers;

// use App\Models\User;
// use Illuminate\Http\Request;

// class VerifyEmailController extends Controller
// {
//     public function __invoke(Request $request)
//     {
//         $user = User::findOrfail($request->route('id'));
//         if (! hash_equals((string) $user->getKey(), (string) $request->route('id'))) {
//             return false;
//         }

//         if (! hash_equals(sha1($user->getEmailForVerification()), (string) $request->route('hash'))) {
//             return false;
//         }

//         return true;
//     }
// }
