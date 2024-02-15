<?php

namespace App\Http\Controllers\V1;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function show($fileName) {
        $path = "private/images/$fileName";
        if (Storage::exists($path)) {
            // if ($this->middleware('auth') && $this->middleware('is.admin')) {
                return Storage::download($path);
            // }
            // abort(404);
        }

        abort(404);
    }
}
