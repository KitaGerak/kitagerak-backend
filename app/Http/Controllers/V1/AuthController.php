<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => $validator->errors(),
                'data' => null,
            ], 422);
        }

        $input = $request->all();

        $input['password'] = bcrypt($input['password']);
        $user = User::create($input);

        $success['token'] = $user->createToken('basic_token', ['view'])->plainTextToken;
        $success['name'] = $user->name;
        $success['id'] = $user->id;
        $success['roleId'] = $user->role_id;

        return response()->json([
            'status' => true,
            'message' => 'Register Sukses',
            'data' => $success,
        ]);

    }

    public function login(Request $request) {
        if (Auth::attempt(['email' => $request->email, 'password' => $request->password])) {
            $auth = Auth::user();

            if ($auth->role_id == 2) {
                $success['token'] = $auth->createToken('venue_owner_token'.$auth->id, ['view', 'create', 'update', 'delete'])->plainTextToken;
            } else {
                $success['token'] = $auth->createToken('basic_token'.$auth->id, ['view','make_transaction'])->plainTextToken;
            }
            $success['name'] = $auth->name;
            $success['id'] = $auth->id;
            $success['roleId'] = $auth->role_id;

            return response()->json([
                'status' => true,
                'message' => 'Autentikasi Sukses',
                'data' => $success,
            ], 200);
        }

        return response()->json([
            'status' => false,
            'message' => 'Autentikasi Gagal',
            'data' => null,
        ], 422);
    }
}
