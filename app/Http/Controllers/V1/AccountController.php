<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateUserRequest;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Models\VerificationCode;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
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

        try {
            $this->generateCode($request, $user->email, "account_activation");
        } catch (Exception $e) {
            return response()->json([
                'status' => false,
                'message' => "Gagal membuat kode verifikasi. Harap menunggu dalam beberapa saat"
            ]);
        }

        return response()->json([
            'status' => true,
            'message' => "Berhasil melakukan registrasi user. Silakan cek e-mail terdaftar untuk melakukan aktivasi akun.",
            'data' => new UserResource($user)
        ]);
    }

    public function show(User $user) {
        if (auth('sanctum')->check()){
            $userAuth = auth('sanctum')->user();
            if ($userAuth->role_id != 3) { //bukan admin  
                if ($user->id != $userAuth->id) {
                    return response()->json([
                        "status" => 0,
                        "message" => "Dilarang mengambil data user lain"
                    ]);
                }
            }
        }
        // return new UserResource($user);
        if ($user['photo_url'] != null) {
            $user['photo_url'] = str_replace("private", env("APP_URL"), $user['photo_url']);
        }
        return response()->json([
            'data' => $user
        ]);
    }

    public function updateData(UpdateUserRequest $request, User $user) {
        $user->update($request->all());

        if ($request->has('image')) {
            $image = $request->image;

            $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            $extension = $image->getClientOriginalExtension();
            if (in_array(strtolower($extension), $allowedImageExtensions)) {
                $fileName = $image->store("private/images/user_profiles/$user->id");
                $user->photo_url = $fileName;
                $user->save();
            }
        }
        if ($user['photo_url'] != null) {
            $user['photo_url'] = str_replace("private", env("APP_URL"), $user['photo_url']);
        }
        return response()->json([
            "data" => $user,
        ]);
    }

    public function generateCode(Request $request, $email = null, $verificationFor = null) {
        $verificationTypes = ["account_activation", "change_password"];

        if ((isset($request->email) && $request != null) || $email != null) {
            if (in_array($verificationFor, $verificationTypes) || (isset($request->verificationType) && in_array($request->verificationType, $verificationTypes))) {
                if ($email != null) {
                    $user = User::where('email', $email)->first();
                } else {
                    $user = User::where('email', $request->email)->first();
                }
    
                if ($user != null && $user->count() > 0) {
                    $now = new DateTime(); //now
    
                    $hours = 5; // hours amount (integer) you want to add
                    $expired = (clone $now)->add(new DateInterval("PT{$hours}H")); // use clone to avoid modification of $now object
    
                    $digits = 4;
                    $code = str_pad(rand(0, pow(10, $digits)-1), $digits, '0', STR_PAD_LEFT);
    
                    VerificationCode::where('user_id', $user->id)->where('is_valid', 1)->update([
                        'is_valid' => 0
                    ]);
    
                    VerificationCode::create([
                        'user_id' => $user->id,
                        'verification_code' => $code,
                        'is_valid' => 1,
                        'valid_until' => $expired,
                        'verification_for' => $verificationFor == null ? $request->verificationType : $verificationFor
                    ]);
    
                    return 1;

                } else {
                    if (isset($request) && $request != null) {
                        return response()->json([
                            "status" => false,
                            "message" => "User tidak ditemukan."
                        ]);
                    }
                    throw ("User tidak ditemukan.");
                }
            } else {
                if (isset($request) && $request != null) {
                    return response()->json([
                        "status" => false,
                        "message" => "Tipe verifikasi invalid."
                    ]);
                }
                throw ("Tipe verifikasi invalid.");
            }
        } else {
            if (isset($request) && $request != null) {
                return response()->json([
                    "status" => false,
                    "message" => "Parameter tidak valid."
                ]);
            }
            throw ("Parameter tidak valid");
        }
    }

    public function verifyCode(Request $request) {
        if (isset($request->email) && isset($request->code)) {
            $user = User::where('email', $request->email)->first();
            
            if ($user != null && $user->count() > 0) {
                $verificationCode = VerificationCode::where('user_id', $user->id)->where('is_valid', 1)->where('verification_code', $request->code)->whereRaw("valid_until >= NOW()")->get()->first();
                if ($verificationCode != null && $verificationCode->count() > 0) {
                    return response()->json([
                        'status' => true,
                        'message' => '',
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Kode verifikasi salah / sudah expired',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Alamat E-mail tidak terdaftar',
                ]);    
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid parameter(s)',
            ]);
        }
    }


    public function activateAccount(Request $request) {
        if (isset($request->email) && isset($request->code)) {
            $user = User::where('email', $request->email)->first();
            
            if ($user != null && $user->count() > 0) {
                if ($user->status == -1) {
                    $verificationCode = VerificationCode::where('user_id', $user->id)->where('is_valid', 1)->where('verification_code', $request->code)->whereRaw("valid_until >= NOW()")->where('verification_for', 'account_activation')->get()->first();
                    if ($verificationCode != null && $verificationCode->count() > 0) {
                        VerificationCode::where('verification_code', $request->code)->update(['is_valid' => 0]);
                        DB::statement("UPDATE `users` SET `status` = 1, `email_verified_at` = NOW(), `last_accessing` = NOW() WHERE `id` = $user->id");

                        //auto login
                        $auth = User::where('email', $request->email)->where('status', 1)->first();
                        if ($auth->role_id == 2) {
                            $success['token'] = $auth->createToken('venue_owner_token'.$auth->id, ['view', 'create', 'update', 'delete'])->plainTextToken;
                        } else {
                            $success['token'] = $auth->createToken('basic_token'.$auth->id, ['view','make_transaction'])->plainTextToken;
                        }
                        $success['name'] = $auth->name;
                        $success['roleId'] = $auth->role_id;
                        $success['emailAddress'] = $auth->email;
                        $success['profilePicture'] = $auth->photo_url;

                        return response()->json([
                            'status' => true,
                            'message' => 'Aktivasi akun sukses.',
                            'data' => $success,
                        ]);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Kode verifikasi salah / sudah expired',
                        ]);
                    }
                } else if ($user->status == 1) {
                    return response()->json([
                        'status' => false,
                        'message' => 'Akun sudah aktif',
                    ]);
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Akun sudah dinonaktifkan. Silakan gunakan akun lain / pergi ke menu pulihkan akun',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Alamat E-mail tidak terdaftar',
                ]);    
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Invalid parameter(s)',
            ]);
        }
    }

    public function changePassword(Request $request) {
        if (isset($request->email) && isset($request->code) && isset($request->newPassword) && isset($request->confirmNewPassword)) {
            if ($request->newPassword == $request->confirmNewPassword) {
                $user = User::where('email', $request->email)->first();
                if ($user != null && $user->count() > 0) {
                    $verificationCode = VerificationCode::where('user_id', $user->id)->where('is_valid', 1)->where('verification_code', $request->code)->whereRaw("valid_until >= NOW()")->where('verification_for', 'change_password')->get()->first();
                    if ($verificationCode != null && $verificationCode->count() > 0) {
                        $newPassword = bcrypt($request->newPassword);
                        $user->password = $newPassword;
                        $user->save();
                        $verificationCode->is_valid = 0;
                        $verificationCode->save();
                        return response()->json([
                            'status' => true,
                            'message' => 'Ganti kata sandi berhasil. Silakan lakukan login ulang',
                        ]);
                    } else {
                        return response()->json([
                            'status' => false,
                            'message' => 'Kode verifikasi salah',
                        ]);
                    }
                } else {
                    return response()->json([
                        'status' => false,
                        'message' => 'Alamat E-mail tidak terdaftar',
                    ]);
                }
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Kata sandi dan konfirmasi kata sandi tidak sesuai',
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Parameter tidak lengkap',
            ]);
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



    public function login(Request $request, $loginWithGoogle = false) {
        if ((Auth::attempt(['email' => $request->email, 'password' => $request->password])) || (Auth::attempt(['phone_number' => $request->phone, 'password' => $request->password])) || $loginWithGoogle == true) {
            $auth = Auth::user();

            if ($auth == null && $loginWithGoogle == true) {
                $auth = User::where('email', $request->email)->where('status', 1)->first();
            }

            if($auth->email_verified_at == null) {
                return response()->json([
                    'status' => false,
                    'message' => 'Login gagal. Email Anda belum terverifikasi!',
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

            DB::statement("UPDATE FROM users SET last_accessing = NOW() WHERE id = ?", [$auth->id]);

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
            ]);
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


    public function getEmployees($ownerId) {
        if (auth('sanctum')->check()) {
            $userAuth = auth('sanctum')->user();
            if ($userAuth->id == $ownerId) {
                return new UserCollection(User::where('id', $ownerId)->with("employees")->get());
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Unauthenticated'
        ], 422);
    }

}
