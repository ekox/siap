<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Mpdf\Mpdf;

class LaporanRealisasiController extends Controller
{
    //
    /**
     * description 
     */
    public function pendapatan()
    {
        $realisasi = [
            ['uraian'=>'Pengembangan Aset', 'rkap'=>2500, 'rc'=>2500, 'rl'=>2450, 'rcsd'=>2450, 'rlsd'=>2350],
            ['uraian'=>'Pengelolaan Aset', 'rkap'=>3500, 'rc'=>3000, 'rl'=>2750, 'rcsd'=>2650, 'rlsd'=>2500],
        ];

        $data = [
            'tahun' => session('tahun'),
            'rows' => $realisasi
        ];

        return view('realisasi.pendapatan', $data);
    }

    /**
     * description 
     */
    public function pendapatanPengembangan()
    {
        $realisasi = [

        ];
        
        $data = [
            'tahun' => session('tahun'),
            'realisasi' => $realisasi
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
        $realisasi = [
            ['uraian'=>'Beban Pokok Penjualan', 'rkap'=>2500, 'rc'=>2500, 'rl'=>2450, 'rcsd'=>2450, 'rlsd'=>2350],
            ['uraian'=>'Beban Usaha', 'rkap'=>3500, 'rc'=>3000, 'rl'=>2750, 'rcsd'=>2650, 'rlsd'=>2500],
        ];
        
        $data = [
            'tahun' => session('tahun'),
            'rows' => $realisasi
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
