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
        $pendapatan = [
			['uraian' => 'Pengembangan Aset', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Pengelolaan Aset', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
        ];
        
        $data = [
            'tahun' => session('tahun'),
            'rows' => $pendapatan,
        ];

        $html_out = view('realisasi.pendapatan', $data);

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
		$mpdf->Output('rincian realisasi pendapatan.pdf', 'I');
		exit;
    }

    /**
     * description 
     */
    public function pendapatanPengembangan()
    {
        $rows = [
			['uraian' => 'Penjualan Tanah/Bangunan CBD Pulo Jahe', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Penyertaan Tanah Mitra - Proyek Ujung Menteng', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Penyertaan Tanah Mitra - Proyek Situ Gintung 2', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Penyertaan Tanah Mitra - Tanah 15.6 Ha', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Pendapatan Kerjasama - Cibubur Junction', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Pendapatan Kerjasama - Palma Citra', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Pendapatan Kerjasama - Gedung Sarana Jaya', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Pendapatan Kerjasama - Mall Ikan Pejompongan', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Pendapatan Kerjasama - Apartemen Zam-Zam Margonda', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Pendapatan Kerjasama - Hotel Mercure Cikini', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Pendapatan Kerjasama - Klapa Village', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Pendapatan Kerjasama - Lebak Bulus', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Pendapatan Kerjasama - SPTA', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Jasa Rekomendasi', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
        ];
        
        $data = [
            'tahun' => session('tahun'),
            'rows' => $rows,
        ];

        $html_out = view('realisasi.pendapatan-pengembangan', $data);

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
		$mpdf->Output('rincian realisasi pendapatan pengembangan.pdf', 'I');
		exit;
    }

    /**
     * description 
     */
    public function pendapatanPengelolaan()
    {
		$rows1 = [
			['uraian' => 'Lantai IV Gedung Sarana Jaya', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Fasilitas STS', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Gedung Eks. Jaya Gas', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Gedung Cik\'s', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Gedung Sarana Jaya 3', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Pondok Kelapa Town Square (POKETS)', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Gedung Sarana Jaya Tebet', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Plaza Atrium', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Hotel Veranda', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Jembatan JPM', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
		];
		
		$rows2 = [
			['uraian' => 'Margin Deposito', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Jasa Giro', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Fee Marketing', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Lain-lain', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
		];

		$data = [
            'tahun' => session('tahun'),
            'rows1' => $rows1,
            'rows2' => $rows2,
        ];

        $html_out = view('realisasi.pendapatan-pengelolaan', $data);
        $html_out_add = view('realisasi.pendapatan-pengelolaan-tambahan', $data);

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

		//mode portrait or landscape
		$mpdf->AddPage('L');

		//write content to PDF
		$mpdf->writeHTML($html_out_add);
		
		$mpdf->Output('rincian realisasi pendapatan pengelolaan.pdf', 'I');
		exit;
    }

    /**
     * description 
     */
    public function beban()
    {
        $beban = [
			['uraian' => 'Beban Pokok Penjualan', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Beban Usaha', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
        ];
        
        $data = [
            'tahun' => session('tahun'),
            'rows' => $beban,
        ];

        $html_out = view('realisasi.beban', $data);

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
		$mpdf->Output('rincian realisasi beban.pdf', 'I');
		exit;
    }

    /**
     * description 
     */
    public function bebanPokokPenjualan()
    {
		$BPP1 = [
			['uraian' => 'BPP Tanah - CBD Pulo Jahe', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'BPP Tanah Situ Gintung', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'BPP Tanah Ujung Menteng', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'BPP Tanah 15.6 Ha', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'BPP Gedung JPM', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
		];
		
		$BPP2 = [
			['uraian' => 'BPP Gedung Sarana Jaya Tebet', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'BPP Pondok Kelapa Town Square', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'BPP Gedung Ciks', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'BPP Ciks Mansion', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'BPP Gedung Sarana Jaya 3', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'BPP Plaza Atrium', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'BPP Hotel Veranda', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
		];
		
        $data = [
            'tahun' => session('tahun'),
            'rows1' => $BPP1,
            'rows2' => $BPP2, 
        ];

        $html_out = view('realisasi.beban-pokok-penjualan', $data);

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
		$mpdf->Output('rincian realisasi beban pokok penjualan.pdf', 'I');
		exit;
    }

    /**
     * description 
     */
    public function bebanUsaha()
    {
		$BU1 = [
			['uraian' => 'Biaya Pemasaran', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Biaya Pegawai', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Biaya Kantor', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Biaya Pemeliharaan', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Biaya Penyusutan/Amortisasi', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Biaya Umum', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
		];
		
		$BU2 = [
			['uraian' => 'Beban Adm. Bank & Pajak Jasa Giro', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'PPh Final', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
			['uraian' => 'Lain-lain', 'rkap' => 0, 'rctw' => 0, 'rltw' => 0, 'psn1' => 0, 'rcsdtw' => 0, 'rlsdtw' => 0, 'psn2' => 0, 'psn3' => 0],
		];
		
        $data = [
            'tahun' => session('tahun'),
            'rows1' => $BU1,
            'rows2' => $BU2,
        ];

        $html_out = view('realisasi.beban-usaha', $data);
        $html_out_add = view('realisasi.beban-usaha-tambahan', $data);

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
		
		//mode portrait or landscape
		$mpdf->AddPage('L');

		//write content to PDF
		$mpdf->writeHTML($html_out_add);
		
		$mpdf->Output('rincian realisasi beban usaha.pdf', 'I');
		exit;
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
