<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function show($folder, $fileName = null) {
        if ($fileName == null) {
            $path = "private/images/$folder";
        } else {
            $path = "private/images/$folder/$fileName";
        }
        

        if (Storage::exists($path)) {
            // if ($this->middleware('auth') && $this->middleware('is.admin')) {
                return Storage::download($path);
            // }
            // abort(404);
        }

        abort(404);
    }
}
