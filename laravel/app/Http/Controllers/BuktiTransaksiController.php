<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ConvertController as KNV;
use Mpdf\Mpdf;
use App\Bukti;

class BuktiTransaksiController extends TableController
{
    //
    public static $style = '<style>
		.ac {text-align:center;}
		.al {text-align:left;}
		.aj {text-align:justify;}
		.ar {text-align:right;}
		.ball {border:1px solid;}
		.blr {border-left:1px solid; border-right:1px solid;}
		.btb {border-top:1px solid; border-bottom:1px solid;}
		.bl {border-left:1px solid;}
		.bt {border-top:1px solid;}
		.br {border-right:1px solid;}
		.bb {border-bottom:1px solid;}
		.bo {font-weight:bold;}
		.pad2 {padding:2px 2px 2px 2px;}
	</style>';
	
    public static $perusahaan = 'PERUSAHAAN DAERAH PEMBANGUNAN<br>SARANA JAYA';

    public function __construct()
	{
		//
	}

	/**
	 * description 
	 */
	public function uangMasuk()
	{
		$data = [
			'tahun' => session('tahun'),
			'perusahaan1' => 'PERUSAHAAN DAERAH PEMBANGUNAN SARANA JAYA',
			'perusahaan2' => 'PEMERINTAH KHUSUS DKI JAKARTA',
			'nomor' => '',
			'nomorbukti' => '',
		];

		//~ return view('bukti.uang-masuk', $data);
		$html_out = view('bukti.uang-masuk', $data);

		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4-P',
			'margin_left' => 8,
			'margin_right' => 8,
			'margin_top' => 18,
			'margin_bottom' => 18,
		]);

		//mode portrait or landscape
		$mpdf->AddPage('P');

		//write content to PDF
		$mpdf->writeHTML($html_out);
		$mpdf->Output('Bukti Uang Masuk.pdf', 'I');
		exit;
	}

	/**
	 * description 
	 */
	public function uangKeluar()
	{
		$data = [
			'tahun' => session('tahun'),
			'perusahaan1' => 'PERUSAHAAN DAERAH PEMBANGUNAN SARANA JAYA',
			'perusahaan2' => 'PEMERINTAH KHUSUS DKI JAKARTA',
			'nomor' => '',
			'nomorbukti' => '',
		];
		
		//~ return view('bukti.uang-keluar', $data);
		$html_out = view('bukti.uang-keluar', $data);

		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4-P',
			'margin_left' => 8,
			'margin_right' => 8,
			'margin_top' => 18,
			'margin_bottom' => 18,
		]);

		//mode portrait or landscape
		$mpdf->AddPage('P');

		//write content to PDF
		$mpdf->writeHTML($html_out);
		$mpdf->Output('Bukti Uang Keluar.pdf', 'I');
		exit;
	}

    /**
	 * description 
	 */
	public function uangMukaKerja()
    {
		$tahun = session('tahun');
		if(!isset($_GET['id'])) {
			return '<script type="text/javascript">alert(\'ID tidak ditemukan\');window.open(\'/siap\', \'_blank\')</script>';
		} else {
			$id = htmlentities($_GET['id']);
		
			$namaBerkas = 'Pengeluaran Kas/Bank untuk Uang Muka Kerja';
			$noDokumen = 'No.:    PSJ/FM/DKA/MRI/01';
			$tglBerlaku = '';

			$data = Bukti::queryUangMukaKerja($tahun, $id);

			$html_out = self::$style;
			$html_out.= '<table border="0" cellspacing="0" cellpadding="1" width="100%" style="font-size:10px;">';
			$html_out.= self::$tbody_open;

			$html_out.= '<tr>
				<td colspan="7" class="">Sudah diterima dari P.D. Pembangunan Sarana Jaya :</td>
			</tr>';

			$html_out.= '<tr>
				<td colspan="7">&nbsp;</td>
			</tr>';

			$html_out.= '<tr>
				<td width="20%" class="">Sebesar</td>
				<td width="2%" class="">:</td>
				<td colspan="5" class="">'.self::cFmt($data->nilai).'</td>
			</tr>';

			$html_out.= '<tr>
				<td>Terbilang</td>
				<td>:</td>
				<td colspan="5">'.KNV::terbilang($data->nilai).' rupiah'.'</td>
			</tr>';

			$html_out.= '<tr>
				<td>Untuk Keperluan</td>
				<td>:</td>
				<td colspan="5">'.$data->uraian.'</td>
			</tr>';

			$html_out.= '<tr>
				<td>Nomor Mata Anggaran</td>
				<td>:</td>
				<td colspan="5">'.$data->kdakun.' ('.$data->nmakun.')</td>
			</tr>';

			$html_out.= '<tr>
				<td>&nbsp;</td>
				<td></td>
				<td width="10%">RKAP</td>
				<td width="2%">:</td>
				<td colspan="3"></td>
			</tr>';

			$html_out.= '<tr>
				<td>&nbsp;</td>
				<td></td>
				<td width="10%">Realisasi</td>
				<td width="2%">:</td>
				<td colspan="3"></td>
			</tr>';

			$html_out.= '<tr>
				<td>&nbsp;</td>
				<td></td>
				<td width="10%">Sisa</td>
				<td width="2%">:</td>
				<td colspan="3"></td>
			</tr>';

			$html_out.= '<tr>
				<td colspan="7">&nbsp;</td>
			</tr>';

			$html_out.= '<tr>
				<td colspan="5">Uang tersebut kami pertanggungjawabkan pada tanggal :</td>
				<td colspan="2"></td>
			</tr>';

			$html_out.= self::$tbody_close;	
			$html_out.= '</table>';

			$html_out.= '<br><br>';
			
			$html_out.= '<table border="0" cellspacing="0" cellpadding="3" width="100%" style="font-size:10px;">';
			$html_out.= self::$tbody_open;
			$html_out.= '<tr>
				<td colspan="2" width="70%">&nbsp;</td>
				<td width="30%" style="padding-right:1em;" class="ac">Jakarta, ..................... '.$data->thang.'</td>
			</tr>';

			$html_out.= '<tr>
				<td colspan="3">&nbsp;</td>
			</tr>';

			$html_out.= '<tr>
				<td class="ac">Menyetujui,</td>
				<td class="ac">Pemohon</td>
				<td class="ac">Penerima,</td>
			</tr>';
			
			$html_out.= '<tr>
				<td class="ac">SM. Divisi Keuangan & Akt.</td>
				<td class="ac">SM. Divisi Umum & SDM</td>
				<td class="ac">&nbsp;</td>
			</tr>';
			
			$html_out.= '<tr>
				<td colspan="3">&nbsp;</td>
			</tr>';

			$html_out.= '<tr>
				<td colspan="3">&nbsp;</td>
			</tr>';

			$html_out.= '<tr>
				<td colspan="3">&nbsp;</td>
			</tr>';
			
			$seniorMgrKeu = Bukti::getSeniorManagerKeuangan()->nama;
			$seniorDivUmum = Bukti::getDivisiUmumSDM()->nama;
			$penerimaUMK = Bukti::getPenerimaUangMuka($id)->nama;
			
			$html_out.= '<tr>
				<td class="ac">'.$seniorMgrKeu.'</td>
				<td class="ac">'.$seniorDivUmum.'</td>
				<td class="ac">'.$penerimaUMK.'</td>
			</tr>';
			
			$html_out.= self::$tbody_close;
			$html_out.= '</table>';

			//~ return $html_out;
			//~ require_once 'laravel/vendor/autoload.php';
			$mpdf = new Mpdf([
				'mode' => 'utf-8',
				'format' => 'A4-P',
				'margin_left' => 15,
				'margin_right' => 15,
				'margin_top' => 18,
				'margin_bottom' => 18,
			]);

			//mode portrait or landscape
			$mpdf->AddPage('P');

			//write content to PDF
			$mpdf->writeHTML($html_out);
			$mpdf->Output('Bukti Uang Muka Kerja.pdf', 'I');
			exit;
		}
    }

}
