<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function register(Request $request) {
        try {
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

            
            if($request->hasFile('ktpImage'))
            {
                $ktpImage = $request->ktpImage;
                
                $extension = $ktpImage->getClientOriginalExtension();

                $filename = time() . '.' . $ktpImage->getClientOriginalExtension();
                $path = Storage::disk('public')->put('ktp_images', $ktpImage);

                $input['photo_url'] = $path;
            }

            $user = new User();
            $user->name = $input['name'];
            $user->email = $input['email'];
            $user->password = $input['password'];
            $user->role_id = $input['role_id'];
            $user->photo_url = $input['photo_url'];

            if(isset($request->owner_id))
            {
                $user->owner_id = $request->owner_id;
            }
            $user->status = 1;

            $user->save();
            // $user = User::create($input);
            
            $success['token'] = $user->createToken('basic_token', ['view','make_transaction'])->plainTextToken;
            $success['name'] = $user->name;
            $success['id'] = $user->id;
            $success['roleId'] = $user->role_id;
    
            event(new Registered($user));
    
            return response()->json([
                'status' => true,
                'message' => 'Register Sukses',
                'data' => $success,
            ]);
        } catch (\Exception $e)
        {
            return response()->json([
                'status' => false,
                'message' => 'Register Gagal',
                'data' => $e->getMessage(),
            ]);
        }

        $input = $request->all();

        $input['password'] = bcrypt($input['password']);

        if($request->hasFile('ktpImage'))
        {
            $ktpImage = $request->ktpImage;
            
            $extension = $ktpImage->getClientOriginalExtension();

            $filename = time() . '.' . $ktpImage->getClientOriginalExtension();
            $path = Storage::disk('public')->put('ktp_images', $ktpImage);

            $input['photo_url'] = $path;
        }

        $user = User::create($input);

        $success['token'] = $user->createToken('basic_token', ['view','make_transaction'])->plainTextToken;
        $success['nik'] = $user->nik;
        $success['name'] = $user->name;
        $success['id'] = $user->id;
        $success['roleId'] = $user->role_id;
        $success['phoneNumber'] = $user->phone_number;
        $success['emailAddress'] = $user->email;
        $success['profilePicture'] = $user->photo_url;

        return response()->json([
            'status' => true,
            'message' => 'Register Sukses',
            'data' => $success,
        ]);

    }

    public function login(Request $request, $loginWithGoogle = false) {
        if ((Auth::attempt(['email' => $request->email, 'password' => $request->password])) || $loginWithGoogle == true) {
            $auth = Auth::user();

            if ($auth == null && $loginWithGoogle == true) {
                $auth = User::where('email', $request->email)->where('status', 1)->first();
            }

            if($auth->email_verified_at == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Autentikasi Gagal. Email anda belum terverifikasi!',
                    'data' => null,
                ], 422);
            }

            if ($auth->status != 1) {
                return response()->json([
                    'status' => false,
                    'message' => 'Akun telah dinonaktifkan',
                    'data' => null,
                ], 422);
            }

            if ($auth->role_id == 2) {
                $success['token'] = $auth->createToken('venue_owner_token'.$auth->id, ['view', 'create', 'update', 'delete'])->plainTextToken;
            } else {
                $success['token'] = $auth->createToken('basic_token'.$auth->id, ['view','make_transaction'])->plainTextToken;
            }
            $success['name'] = $auth->name;
            $success['id'] = $auth->id;
            $success['roleId'] = $auth->role_id;
            $success['phoneNumber'] = $auth->phone_number;
            $success['emailAddress'] = $auth->email;
            $success['profilePicture'] = $auth->photo_url;

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

    public function loginWithGoogle(Request $request) {

        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required',
            'googleId' => 'required',
            'photoUrl' => 'required'
        ]);

        $userRes = User::where('email', $validated['email'])->where('status', 1)->first();
        if ($userRes == null) {
            //insert akun baru
            User::create([
                'email' => $validated['email'],
                'name' => $validated['name'],
                'password' => null,
                'email_verified_at' => DB::raw(NOW()),
                'role_id' => 1,
                'status' => 1,
                'login_method_id' => 2,
                'photo_url' => $validated['photoUrl'],
            ]);
            return $this->login($request, true);
        } else {
            if ($userRes->login_method_id == 2) { //login with google
                //lakukan login
                return $this->login($request, true);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Akun Anda sudah terdaftar menggunakan E-mail',
                    'data' => null,
                ], 422);
            }
        }
    }

    public function changeLoginMethod(Request $request) {
        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required',
            'googleId' => 'required',
            'photoUrl' => 'required'
        ]);

        $userRes = User::where('email', $validated['email'])->where('status', 1)->where('login_method_id', 1)->first();
        if ($userRes != null) {
            $userRes->password = null;
            $userRes->login_method_id = 2;
            $userRes->save();
            return $this->login($request, true);
        }

        return response()->json([
            'status' => false,
            'message' => 'Akun tidak ditemukan',
            'data' => null,
        ], 422);
    }
}
