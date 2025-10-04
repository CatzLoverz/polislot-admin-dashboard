<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        Log::info('[DashboardController@index] Menampilkan dashboard.', ['user_id' => $user->user_id]);
        return view('Contents.Dashboard.index', compact('user'));
    }
}