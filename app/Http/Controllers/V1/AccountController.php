<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;

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
}
