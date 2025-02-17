<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\V1\UpdateUserRequest;
use App\Http\Resources\V1\UserCollection;
use App\Http\Resources\V1\UserResource;
use App\Mail\SendOtpCode;
use App\Models\User;
use App\Models\VerificationCode;
use DateInterval;
use DateTime;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class AccountController extends Controller
{

    private function createMessage($statusCode, $message, $data, $httpCode = 200) {
        $status = true;
        if ($statusCode != 200) {
            $status = false;
        }
        return response()->json([
            'status' => $status,
            'statusCode' => $statusCode,
            'message' => $message,
            'data' => $data,
        ], $httpCode);
    }

    public function register(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
            'email' => 'required|email',
            'phone_number' => 'required|string',
            'password' => 'required',
            'confirm_password' => 'required|same:password',
            'role_id' => 'required|exists:roles,id',
        ]);

        if ($validator->fails()) {
            return $this->createMessage(422, $validator->errors()->first(), null, 422);
        }

        $user = User::where('email', $request->email)->first(); 
        
        if ($user->count() > 0) {
            return $this->createMessage(422, "Akun Anda sudah terdaftar. Harap lakukan login", null, 422);
        } else if ($user->status != 1) {
            return $this->createMessage(422, "Akun Anda telah di-nonaktifkan. Silakan gunakan email lain atau hubungi admin.", null, 422);
        }

        $input = $request->all();

        $input['password'] = bcrypt($input['password']);

        $user = User::create($input);

        return $this->generateOtpCode("account_activation", [
            'is' => 'phone_number',
            'data' => $validator['phone_number']
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




    public function handleOtpCode(Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'email|nullable',
            'phoneNumber' => 'string|nullable',
            'verificationType' => 'string|required'
        ]);

        if ($validator->fails()) {
            return $this->createMessage(422, $validator->errors()->first(), null, 422);
        }

        if ($request->email != null) {
            $identifier = [
                'is' => 'email',
                'data' => $request->email
            ];
        } else if ($request->phoneNumber != null) {
            $identifier = [
                'is' => 'phone_number',
                'data' => $request->phoneNumber
            ];
        } else {
            return $this->createMessage(500, 'Email atau nomor HP wajib terisi salah satu.', null, 500);
        }
        
        return $this->generateOtpCode($request->verificationType, $identifier);
    }

    private function generateOtpCode($verificationType, $identifier) {
        $verificationTypes = ["account_activation", "change_password", "login"];

        if (!in_array($verificationType, $verificationTypes)) {
            return $this->createMessage(500, "Parameter 'Verification Type' tidak ditemukan", null, 500);
        }

        try {
            $user = User::where($identifier['is'], $identifier['data'])->first();
        } catch (Exception $e) {
            return $this->createMessage(500, $e->getMessage(), null, 500);
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
                'verification_for' => $verificationType
            ]);

            // Kirim otp ke platform
            $msg = "OTP telah berhasil dikirimkan melalui ";
            if ($identifier['is'] == "email") {
                Mail::to($user->email)->send(new SendOtpCode($code));
                $msg .= "email " . $identifier['data'];
            } else if ($identifier['is'] == "phone_number") {
                //TODO kirim ke WA
                $msg .= "nomor WhatsApp " . $identifier['data'];
            }

            
            if ($verificationType == "account_activation") {
                $msg = "Berhasil melakukan registrasi user. Silakan masukkan kode OTP yang telah terkirim ke nomor WhatsApp terdaftar untuk melakukan aktivasi akun.";
            }

            return $this->createMessage(200, $msg, new UserResource($user));

        } else {
            return $this->createMessage(500, "User tidak ditemukan", null, 500);
        }
    }



    public function verifyCode(Request $request) {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string',
            'verificationFor' => 'required|string',
            'email' => 'email|nullable',
            'phoneNumber' => 'string|nullable',
        ]);

        if ($validator->fails()) {
            return $this->createMessage(422, $validator->errors()->first(), null, 422);
        }

        if ($request->email != null) {
            $user = User::where('email', $request->email)->first();   
        } else if ($request->phoneNumber != null) {
            $user = User::where('phone_number', $request->phoneNumber)->first();
        } else {
            return $this->createMessage(500, "Email / nomor HP wajib terisi salah satu.", null, 500);
        }
            
        if ($user != null && $user->count() > 0) {
            $verificationCode = VerificationCode::where('user_id', $user->id)->where('is_valid', 1)->where('verification_code', $request->code)->where('verification_for', $request->verificationFor)->whereRaw("valid_until >= NOW()")->first();
            
            if ($verificationCode != null && $verificationCode->count() > 0) {

                if ($verificationCode->verification_for != "change_password") {
                    $verificationCode->is_valid = 0;
                    $verificationCode->save();
                }

                if ($verificationCode->verification_for == "login") {
                    DB::statement("UPDATE `users` SET `status` = 1, `email_verified_at` = NOW(), `last_accessing` = NOW() WHERE `id` = $user->id");
                    
                    return $this->login($user);
                }
                return $this->createMessage(200, "Kode berhasil terverifikasi.", null);

            } else {
                return $this->createMessage(422, "Kode Verifikasi salah.", null, 422);
            }
        } else {
            return $this->createMessage(422, "Alamat e-mail tidak terdaftar", null, 422);
        }
    }



    public function changePassword(Request $request) {
        $validator = Validator::make($request->all(), [
            'phoneNumber' => 'string|nullable',
            'email' => 'email|nullable',
            'password' => 'required',
            'confirmPassword' => 'required|same:password',
            'code' => 'string|required'
        ]);

        if ($validator->fails()) {
            return $this->createMessage(422, $validator->errors()->first(), null, 422);
        }

        if ($request->email != null) {
            $user = User::where('email', $request->email)->first();    
        } else if ($request->phoneNumber != null) {
            $user = User::where('phone_number', $request->phoneNumber)->first();
        } else {
            return $this->createMessage(422, "Email / nomor telepon wajib diisi salah satu.", null, 422);
        }

        if ($user != null && $user->count() > 0) {
            $verificationCode = VerificationCode::where('user_id', $user->id)->where('is_valid', 1)->where('verification_code', $request->code)->where('verification_for', "change_password")->whereRaw("valid_until >= NOW()")->first();
            
            if ($verificationCode != null && $verificationCode->count() > 0) {

                $verificationCode->is_valid = 0;
                $verificationCode->save();

                $newPassword = bcrypt($request->newPassword);
                $user->password = $newPassword;
                $user->save();

                return $this->createMessage(200, "Ganti kata sandi berhasil. Silakan lakukan login ulang", new UserResource($user));
            }
            return $this->createMessage(422, "Kode verifikasi salah.", null, 422);
        } else {
            return $this->createMessage(422, "Alamat E-mail tidak terdaftar.", null, 422);
        }
    }


    

    private function login($auth) {

        if($auth->email_verified_at == null && $auth->phone_verified_at == null) {
            return $this->createMessage(2, "Email atau nomor HP Anda belum terverifikasi.", null, 422);
        }

        // && $auth->last_accessing lebih dari 30 hari
        if ($auth->status != 1) {
            return $this->createMessage(2, "Akun telah dinonaktifkan. Silakan login/register menggunakan akun berbeda.", null, 422);
        }

        DB::statement("UPDATE users SET `last_accessing` = NOW(), `status` = 1 WHERE id = ?", [$auth->id]);

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

        return $this->createMessage(200, "Login Sukses", $success);
    }
    
    private function loginWithOtp(Request $request) {
        $validated = $request->validate([
            'email' => 'email|nullable',
            'phoneNumber' => 'string|nullable'
        ]);

        $user = User::where('email', $validated['email'])->orWhere('phone_number', $validated['phoneNumber'])->first();

        $loginMethod = "";

        if ($validated['email'] != null) {
            $loginMethod = "Email";
        } else if ($validated['phoneNumber'] != null) {
            $loginMethod = "Nomor HP";
        } else {
            return $this->createMessage(0, "Email / nomor telepon harus terisi salah satu.", null, 422);
        }

        if ($user == null) {
            return $this->createMessage(4, "$loginMethod belum terdaftar. Silakan coba menggunakan metode lain", null, 422);
        }


        if ($loginMethod == "Email") {
            $this->generateOtpCode("login",[
                'is' => 'email',
                'data' => $validated['email']
            ]);
        } else if ($loginMethod == "Nomor HP") {
            $this->generateOtpCode("login",[
                'is' => 'phone_number',
                'data' => $validated['phoneNumber']
            ]);
        }

        return $this->createMessage(200, "Kode OTP telah terkirim ke $loginMethod.", null);
    }


    private function loginWithPassword(Request $request) {
        
        $validated = $request->validate([
            'email' => 'email|nullable',
            'phoneNumber' => 'string|nullable',
            'password' => 'required'
        ]);

        if ((Auth::attempt(['email' => $validated['email'], 'password' => $validated['password']])) || (Auth::attempt(['phone_number' => $validated['phoneNumber'], 'password' => $validated['password']]))) {
            $auth = Auth::user();

            if ($auth->login_method != 1) {
                return $this->createMessage(1, "Harap lakukan login menggunakan Google", null, 422);
            }

            $this->login($auth);
        }

        return $this->createMessage(3, "Email, nomor telepon, atau password salah.", null, 422);
    }

    private function loginWithGoogle(Request $request) {

        $validated = $request->validate([
            'email' => 'required|email',
            'name' => 'required',
            'googleId' => 'required',
            'photoUrl' => 'required',
            'roleId' => 'required'
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($user == null) {
            //insert akun baru
            $auth = User::create([
                'email' => $validated['email'],
                'name' => $validated['name'],
                'email_verified_at' => DB::raw(NOW()),
                'role_id' => $validated['roleId'], //TODO Chage
                'status' => 1,
                'login_method_id' => 2,
                'photo_url' => $validated['photoUrl'],
            ]);
        } else {
            $auth = $user;
            if ($user->login_method_id != 2) { //login with google
                return $this->createMessage(1, "Harap login menggunakan metode manual / OTP", null, 422);
            } 
        }

        //lakukan login
        return $this->login($auth);
    }

    public function handleLogin(Request $request) {
        if (isset($request["loginMethod"])) {
            if ($request["loginMethod"] == "password") {
                return $this->loginWithPassword($request);
            } else if ($request["loginMethod"] == "google") {
                return $this->loginWithGoogle($request);
            } else if ($request["loginMethod"] == "otp") {
                return $this->loginWithOtp($request);
            }
            
            return $this->createMessage(500, "login method?", null, 500);
        }

        return $this->createMessage(500, "login method?", null, 500);
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

    public function generateGuestToken() {
        $auth = User::where('email', "guest@kitagerak.com")->first();
        return response()->json([
            "token" => $auth->createToken('guest_token'.$auth->id, ['view'])->plainTextToken,
        ]);
    }

}
