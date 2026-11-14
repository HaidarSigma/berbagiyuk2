<?php

namespace App\Http\Controllers;

use App\Models\KebutuhanMendesak;

class KebutuhanController extends Controller
{
    public function index()
    {
        return view('kebutuhan', [
            'terbuka' => KebutuhanMendesak::where('status', 'mendukung')->latest()->get(),
            'selesai' => KebutuhanMendesak::where('status', 'selesai')->latest()->take(12)->get(),
        ]);
    }
}