<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class AdminController extends Controller
{
    /**
     * Display admin dashboard
     * 
     * @return \Illuminate\View\View
     */
    public function dashboard()
    {
        // Check if user is admin
        if (Auth::user()->role !== 'admin') {
            return redirect('/')->with('error', 'Anda tidak memiliki akses sebagai admin.');
        }
        
        // Get user count
        $userCount = User::count();
        
        // Example data for dashboard
        $pendapatan = "Rp 8,5 Juta";
        $pesananBaru = 28;
        $pengunjung = 2856;
        
        // Get recent users
        $recentUsers = User::latest()->take(3)->get();
        
        return view('admin.dashboard', compact('userCount', 'pendapatan', 'pesananBaru', 'pengunjung', 'recentUsers'));
    }
} 