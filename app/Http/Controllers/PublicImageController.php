<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicImageController extends Controller
{
       public function show($path)
    {
        $path = 'public/barang/' . $path;

        if (!Storage::exists($path)) {
            return response('Image not found', 404);
        }

        return response(Storage::get($path))
            ->header('Content-Type', Storage::mimeType($path));
    }

}
