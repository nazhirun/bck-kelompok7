<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PublicImageController extends Controller
{
    
        
        return response(Storage::get($path))
            ->header('Content-Type', Storage::mimeType($path));
    }
} 