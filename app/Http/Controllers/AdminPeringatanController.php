<?php

namespace App\Http\Controllers;

use App\Models\LogAktivitas;
use App\Models\PeringatanKeamanan;

class AdminPeringatanController extends Controller
{
    public function index()
    {
        return view('admin.peringatan', ['daftar' => PeringatanKeamanan::latest()->paginate(20)]);
    }

    public function baca()
    {
        PeringatanKeamanan::whereNull('dibaca_at')->update(['dibaca_at' => now()]);
        LogAktivitas::catat('peringatan_dibaca');

        return back()->with('status', 'Semua peringatan ditandai sudah dibaca.');
    }
}