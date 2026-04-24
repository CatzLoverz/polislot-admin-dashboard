<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class IotStreamViewerController extends Controller
{
    /**
     * Menampilkan halaman khusus untuk uji coba WebSockets (Reverb)
     */
    public function index()
    {
        // Target MAC Address untuk uji coba
        $targetMac = "00:1A:2B:3C:4D:5E";
        
        return view('Contents.IotStream.viewer', compact('targetMac'));
    }
}
