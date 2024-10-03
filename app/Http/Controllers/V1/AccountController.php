<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\V1\UserResource;
use App\Models\User;
use App\Models\VerificationCode;
use DateInterval;
use DateTime;

class AccountController extends Controller
{
    public function index() {
    }

    public function show(User $user) {
        return new UserResource($user);
    }

    public function updateData(Request $request, User $user) {
        $user->update($request->all());

        if ($request->has('image')) {
            $image = $request->image;

            $allowedImageExtensions = ['jpg', 'jpeg', 'png', 'webp'];

            $extension = $image->getClientOriginalExtension();
            if (in_array(strtolower($extension), $allowedImageExtensions)) {
                $fileName = $image->store('private/images/user_profiles');
                $user->photo_url = $fileName;
                $user->save();
            }
        }

        return response()->json([
            "data" => $user,
        ]);
    }

    public function generateCode(Request $request) {
        if (isset($request->email)) {
            $user = User::where('email', $request->email)->first();
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
                ]);

                return response()->json([
                    'status' => true,
                    'message' => $code,
                ]);
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Alamat E-mail tidak terdaftar',
        ]);
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
                        'message' => 'Kode verifikasi salah',
                    ]);
                }
            }
        }

        return response()->json([
            'status' => false,
            'message' => 'Alamat E-mail tidak terdaftar',
        ]);
    }

    public function changePassword(Request $request) {
        if (isset($request->email) && isset($request->code) && isset($request->newPassword) && isset($request->confirmNewPassword)) {
            if ($request->newPassword == $request->confirmNewPassword) {
                $user = User::where('email', $request->email)->first();
                if ($user != null && $user->count() > 0) {
                    $verificationCode = VerificationCode::where('user_id', $user->id)->where('is_valid', 1)->where('verification_code', $request->code)->whereRaw("valid_until >= NOW()")->get()->first();
                    if ($verificationCode != null && $verificationCode->count() > 0) {
                        $newPassword = bcrypt($request->newPassword);
                        $user->password = $newPassword;
                        $user->save();
                        $verificationCode->is_valid = 0;
                        $verificationCode->save();
                        return response()->json([
                            'status' => true,
                            'message' => 'Ganti kata sandi berhasil. Silakan lakukan login',
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
        }

        return response()->json([
            'status' => false,
            'message' => 'Error',
        ]);
        
    }

}
