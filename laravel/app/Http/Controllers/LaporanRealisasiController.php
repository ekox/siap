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

    /**
	 * description 
	 */
	public static function investasi()
	{
		$arrItem = [
			['idx' => '1', 'val' => 'Menggunakan Dana PD. PSJ', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['idx' => '2', 'val' => 'Menggunakan Dana PMD', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
		];
		
		$arrPSJ = [
			['no' => '1', 'kode' => 'a', 'uraian' => 'Pembangunan Sarana Square', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'b', 'uraian' => 'KSO Proyek Zam-Zam Apartemen - Margonda', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'c', 'uraian' => 'KSO Proyek Anami Klapa Village', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'd', 'uraian' => 'KSO Proyek Lebak Bulus Pembangunan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'e', 'uraian' => 'Pondok Kelapa Town Square (POKETS)', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'f', 'uraian' => 'Gedung Cik\'s', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'g', 'uraian' => 'Gedung Sarana Jaya III', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'h', 'uraian' => 'Situ Gintung II - Pembebasan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'i', 'uraian' => 'Lebak Bulus - Pembangunan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'j', 'uraian' => 'Lebak Bulus - Tanah', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'k', 'uraian' => 'Gedung Sarana Jaya Tebet', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'l', 'uraian' => 'Investasi Lainnya', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'm', 'uraian' => 'Griya Cik\'s', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'n', 'uraian' => 'Alat Produksi Lainnya', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
		];
		
		$arrPMD = [
			['no' => '1', 'kode' => 'a', 'uraian' => 'Proyek Klapa Village Tower A & B', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'b', 'uraian' => 'Pembangunan Lebak Bulus - Pembangunan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'c', 'uraian' => 'Pengembangan SPTA - Pembebasan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'd', 'uraian' => 'Pengembangan SPTA - Pembangunan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'e', 'uraian' => 'Pengembangan DP 0 Lokasi Baru - Pembebasan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'f', 'uraian' => 'Pengembangan DP 0 Lokasi Baru - Pembangunan (Cilangkap & Ujung Menteng)', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'g', 'uraian' => 'Cik\'s Mansion/Griya Cik\'s', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'h', 'uraian' => 'Alat Produksi Baru', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
		];

		$arrBinv = [
			['idx' => '1', 'val' => 'Peralatan/Perabot Kantor', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['idx' => '2', 'val' => 'Komputer', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['idx' => '3', 'val' => 'Kendaraan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
		];
		
		$data = [
			'tahun' => session('tahun'),
			'itm' => $arrItem,
			'rows1' => $arrPSJ,
			'rows2' => $arrPMD,
			'rows3' => $arrBinv,
		];

		$html_out = view('realisasi.investasi', $data);

		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4-P',
			'margin_left' => 8,
			'margin_right' => 8,
			'margin_top' => 18,
			'margin_bottom' => 18,
		]);

		//mode portrait or landscape
		$mpdf->AddPage('L');

		//write content to PDF
		$mpdf->writeHTML($html_out);
		$mpdf->Output('Bukti Uang Masuk.pdf', 'I');
		exit;
	}
}
