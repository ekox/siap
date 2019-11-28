<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\TableController as TBL;

class BuktiTransaksiController extends Controller
{
    //
    public static $perusahaan = 'PERUSAHAAN DAERAH PEMBANGUNAN<br>SARANA JAYA';

    public function __construct()
	{
		$this->thead_open = TBL::$thead_open;
		$this->thead_close = TBL::$thead_close;
		$this->tbody_open = TBL::$tbody_open;
		$this->tbody_close = TBL::$tbody_close;
	}

	public function penerimaan()
	{
		return '';
	}

	public function pengeluaran()
	{
		return '';
	}

    public function pengeluaranUangMukaKerja()
    {
		$namaBerkas = 'Pengeluaran Kas/Bank untuk Uang Muka Kerja';
		$noDokumen = 'No.:    PSJ/FM/DKA/MRI/01';
		$tglBerlaku = '';

		$html_out = '';
		$html_out.= '<table border="0" cellspacing="0" cellpadding="1" width="100%">';
		$html_out.= $this->tbody_open;

		$html_out.= '<tr>
			<td colspan="5" class="">Sudah diterima dari P.D. Pembangunan Sarana Jaya :</td>
		</tr>';

		$html_out.= '<tr>
			<td colspan="5">&nbsp;</td>
		</tr>';

		$html_out.= '<tr>
			<td width="15%" class="">Sebesar</td>
			<td width="2%" class="">:</td>
			<td colspan="3" class="">&nbsp;</td>
		</tr>';

		$html_out.= '<tr>
			<td>Terbilang</td>
			<td>:</td>
			<td colspan="3">&nbsp;</td>
		</tr>';

		$html_out.= '<tr>
			<td>Untuk Keperluan</td>
			<td>:</td>
			<td colspan="3">&nbsp;</td>
		</tr>';

		$html_out.= '<tr>
			<td>Nomor Mata Anggaran</td>
			<td>:</td>
			<td colspan="3">&nbsp;</td>
		</tr>';

		$html_out.= '<tr>
			<td>&nbsp</td>
			<td></td>
			<td width="10%">RKAP</td>
			<td width="2%">:</td>
			<td></td>
		</tr>';

		$html_out.= '<tr>
			<td>&nbsp</td>
			<td></td>
			<td width="10%">Realisasi</td>
			<td width="2%">:</td>
			<td></td>
		</tr>';

		$html_out.= '<tr>
			<td>&nbsp</td>
			<td></td>
			<td width="10%">Sisa</td>
			<td width="2%">:</td>
			<td></td>
		</tr>';

		$html_out.= '<tr>
			<td colspan="5">&nbsp;</td>
		</tr>';

		$html_out.= '<tr>
			<td colspan="4">Uang tersebut kami pertanggungjawabkan pada tanggal :</td>
			<td colspan="1"></td>
		</tr>';

		$html_out.= $this->tbody_close;	
		$html_out.= '</table>';

		$html_out.= '<br><br>';
		
		$html_out.= '<table width="100%">';
		$html_out.= $this->tbody_open;
		$html_out.= '<tr>
			<td colspan="2" width="70%">&nbsp;</td>
			<td width="" style="padding-right:1em;">Jakarta, ..................... 20..</td>
		</tr>';

		$html_out.= '<tr>
			<td colspan="3">&nbsp;</td>
		</tr>';

		$html_out.= '<tr>
			<td>Menyetujui,</td>
			<td>Pemohon</td>
			<td>Penerima,</td>
		</tr>';
		
		$html_out.= '<tr>
			<td>SM Divisi Keuangan & Akt.</td>
			<td>SM Divisi Umum & SDM</td>
			<td>&nbsp;</td>
		</tr>';
		$html_out.= $this->tbody_close;
		$html_out.= '</table>';

		return $html_out;
    }

}
