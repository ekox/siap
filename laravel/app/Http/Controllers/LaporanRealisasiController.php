<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class LaporanRealisasiController extends Controller
{
    //
    /**
     * description 
     */
    public function pendapatan()
    {
        $data = [
            'tahun' => session('tahun'),
        ];

        return view('realisasi.pendapatan', $data);
    }

    /**
     * description 
     */
    public function pendapatanPengembangan()
    {
        $data = [
            'tahun' => session('tahun'),
        ];

        return view('realisasi.pendapatan-pengembangan', $data);
    }

    /**
     * description 
     */
    public function pendapatanPengelolaan()
    {
        $data = [
            'tahun' => session('tahun'),
        ];

        return view('realisasi.pendapatan-pengelolaan', $data);
    }

    /**
     * description 
     */
    public function beban()
    {
        $data = [
            'tahun' => session('tahun'),
        ];

        return view('realisasi.beban', $data);
    }

    /**
     * description 
     */
    public function bebanPokokPenjualan()
    {
        $data = [
            'tahun' => session('tahun'),
        ];

        return view('realisasi.beban-pokok-penjualan', $data);
    }

    /**
     * description 
     */
    public function bebanUsaha()
    {
        $data = [
            'tahun' => session('tahun'),
        ];

        return view('realisasi.beban-usaha', $data);
    }
}
