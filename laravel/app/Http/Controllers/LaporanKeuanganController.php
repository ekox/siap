<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class LaporanKeuanganController extends TableController
{
    //
	public $tahun;
    
	// methods of class
	public function __construct()
	{
		parent::__construct();
		$this->tahun = session('tahun');
	}

	//LAPORAN LABA(RUGI)
	public function incomeStatement()
	{
		$periode = '01';
		if(isset($_GET['periode'])) {
			$periode = htmlentities($_GET['periode']);
		}
		
		$this->setReportName('LABA (RUGI) REALISASI RKAP TRIWULAN III TAHUN 2019');
		$namaLaporan = $this->getReportName();
		$html_out = $this->headerOfReport($namaLaporan);
		$nbsp = str_repeat('&nbsp;', 4);
		
		// content of report
		$html_out.= self::$table_open;
		$html_out.= self::$thead_open;

		//header of content
		$html_out.= '<tr>
			<th rowspan="2">URAIAN</th>
			<th rowspan="2">RKAP 2019</th>
			<th colspan="3">TRIWULAN III</th>
			<th colspan="3">s.d TRIWULAN III</th>
			<th rowspan="2">% thd RKAP 2019</th>
		</tr>';
		$html_out.= '<tr>
			<th>Rencana</th>
			<th>Realisasi</th>
			<th>%</th>
			<th>Rencana</th>
			<th>Realisasi</th>
			<th>%</th>
		</tr>';

		//column info
		$html_out.= '<tr>
			<th>1</th>
			<th>2</th>
			<th>3</th>
			<th>4</th>
			<th>5=4:3</th>
			<th>6</th>
			<th>7</th>
			<th>8=7:6</th>
			<th>9=7:2</th>
		</tr>';
		$html_out.= self::$thead_close;

		//get content
		$rows = array(
			'pendapatan'=> \App\Labarugi::getPendapatan($this->tahun, $periode)->nilai,
			'beban_pokok_pendapatan' => \App\Labarugi::getBebanPokokPendapatan($this->tahun, $periode)->nilai,
			'laba_kotor' => \App\Labarugi::getLabaKotor($this->tahun, $periode)->nilai,
			'beban_pemasaran' => \App\Labarugi::getBebanPemasaran($this->tahun, $periode)->nilai,
			'beban_adum' => \App\Labarugi::getBebanAdministrasiUmum($this->tahun, $periode)->nilai,
			'jml_beban_usaha' => \App\Labarugi::getBebanUsaha($this->tahun, $periode)->nilai,
			'laba_usaha' => \App\Labarugi::getLabaUsaha($this->tahun, $periode)->nilai,
			'pendapatan_lain' => \App\Labarugi::getPendapatanLainLain($this->tahun, $periode)->nilai,
			'beban_lain' => \App\Labarugi::getBebanLainLain($this->tahun, $periode)->nilai,
			'jml_pb_lain' => \App\Labarugi::getPendapatanBebanLainLain($this->tahun, $periode)->nilai,
			'pajak_kini' => \App\Labarugi::getPajakKini($this->tahun, $periode)->nilai,
			'pajak_badan' => \App\Labarugi::getPajakBadan($this->tahun, $periode)->nilai,
			'laba_anak_usaha' => \App\Labarugi::getLabaAnakPerusahaan($this->tahun, $periode)->nilai,
			'manfaat_bpp' => \App\Labarugi::getManfaatBebanPajakPenghasilan($this->tahun, $periode)->nilai,
			'laba_bersih' => \App\Labarugi::getLabaBersih($this->tahun, $periode)->nilai,
		);

		//generate row of table body content
		function isiBaris($uraian, $nilai, $tw_rc, $tw_rl, $tw_p, $sdtw_rc, $sdtw_rl, $sdtw_p, $thd)
		{
			$obj = new LaporanKeuanganController();
			
			return '<tr>
						<td class="pad2">'.$obj->cFmt($uraian).'</td>
						<td class="pad2 ar">'.$obj->cFmt($nilai).'</td>
						<td class="pad2 ar">'.$obj->cFmt($tw_rc).'</td>
						<td class="pad2 ar">'.$obj->cFmt($tw_rl).'</td>
						<td class="pad2 ac">'.$obj->cFmt($tw_p).'</td>
						<td class="pad2 ar">'.$obj->cFmt($sdtw_rc).'</td>
						<td class="pad2 ar">'.$obj->cFmt($sdtw_rl).'</td>
						<td class="pad2 ar">'.$obj->cFmt($sdtw_p).'</td>
						<td class="pad2 ac">'.$obj->cFmt($thd).'</td>
					</tr>';
		}
	
		//body of content
		$html_out.= self::$tbody_open;

		$html_out.= isiBaris('PENDAPATAN', $rows['pendapatan'], 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris('&nbsp;', '', '', '', '', '', '', '', '');
		$html_out.= isiBaris('BEBAN POKOK PENDAPATAN', $rows['beban_pokok_pendapatan'], 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris('&nbsp;', '', '', '', '', '', '', '', '');
		$html_out.= isiBaris('<span class="bo">LABA KOTOR</span>', $rows['laba_kotor'], 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris('&nbsp;', '', '', '', '', '', '', '', '');
		$html_out.= isiBaris('BEBAN USAHA', '', '', '', '', '', '', '', '');
		$html_out.= isiBaris($nbsp.'Beban Pemasaran', $rows['beban_pemasaran'], 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris($nbsp.'Beban Administrasi Umum', $rows['beban_adum'], 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris('Jumlah Beban Usaha', $rows['jml_beban_usaha'], 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris('&nbsp;', '', '', '', '', '', '', '', '');
		$html_out.= isiBaris('<span class="bo">LABA (RUGI) USAHA</span>', $rows['laba_usaha'], 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris('&nbsp;', '', '', '', '', '', '', '', '');
		$html_out.= isiBaris('<span class="bo">PENDAPATAN (BEBAN) LAIN-LAIN</span>', '', '', '', '', '', '', '', '');
		$html_out.= isiBaris($nbsp.'Pendapatan Lain-lain', $rows['pendapatan_lain'], 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris($nbsp.'Beban Lain-lain', $rows['beban_lain'], 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris('<span class="bo">Jumlah Pendapatan (Beban) Lain-lain Bersih</span>', $rows['jml_pb_lain'], 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris('&nbsp;', '', '', '', '', '', '', '', '');
		$html_out.= isiBaris('<span class="bo">LABA SEBELUM BEBAN PAJAK PENGHASILAN<br/>DAN LABA (RUGI) ANAK PERUSAHAAN</span>', 0, 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris('&nbsp;', '', '', '', '', '', '', '', '');
		$html_out.= isiBaris('Manfaat (Beban) Pajak Penghasilan', '', '', '', '', '', '', '', '');
		$html_out.= isiBaris($nbsp.'Pajak Kini', $rows['pajak_kini'], 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris($nbsp.'Pajak Badan', $rows['pajak_badan'], 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris('&nbsp;', '', '', '', '', '', '', '', '');
		$html_out.= isiBaris('<span class="bo">Jumlah Manfaat (Beban) Pajak Penghasilan</span>', $rows['manfaat_bpp'], 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris('&nbsp;', '', '', '', '', '', '', '', '');
		$html_out.= isiBaris('LABA ANAK PERUSAHAAN', $rows['beban_pokok_pendapatan'], 0, 0, 0, 0, 0, 0, 0);
		$html_out.= isiBaris('&nbsp;', '', '', '', '', '', '', '', '');
		$html_out.= isiBaris('LABA BERSIH', $rows['beban_pokok_pendapatan'], 0, 0, 0, 0, 0, 0, 0);
		
		$html_out.= self::$tbody_close;

		// end of table
		$html_out.= self::$table_close;
		return $html_out;
	}

	//LAPORAN NERACA
	public function balanceSheet()
	{
		$periode = '01';
		if(isset($_GET['periode'])) {
			$periode = htmlentities($_GET['periode']);
		}
		
		$this->setReportName('LAPORAN REALISASI POSISI KEUANGAN');
		$namaLaporan = $this->getReportName();
		$html_out = $this->headerOfReport($namaLaporan);
		$nbsp = str_repeat('&nbsp;', 4);

		
		// content of report
		$html_out.= self::$table_open;
		$html_out.= self::$thead_open;
		$html_out.= '<tr>
			<th rowspan="2">URAIAN</th>
			<th rowspan="2">RKAP '.$this->tahun.'</th>
			<th colspan="3">s.d Triwulan III '.$this->tahun.'</th>
			<th rowspan="2">%</th>
		</tr>';
		$html_out.= '<tr>
			<th>Rencana</th>
			<th>Realisasi</th>
			<th>%</th>
		</tr>';
		$html_out.= self::$thead_close;

		// get data of content
		$data = [
			'ca' => \App\Neraca::nrcAkun2('11', $this->tahun), // return kdakun2, nmakun, saldo
			'fa' => \App\Neraca::nrcAkun2('12', $this->tahun),
			'stl' => \App\Neraca::nrcAkun2('21', $this->tahun),
			'ltl' => \App\Neraca::nrcAkun2('22', $this->tahun),
			'sc' => \App\Neraca::nrcAkun2('31', $this->tahun),
			're' => \App\Neraca::nrcAkun2('32', $this->tahun),
		];

		// content of report
		$html_out.= self::$tbody_open; // <tbody>

		// list of account
		$nrc = array(
			'11' => array( $nbsp.'JUMLAH '.$data['ca']->nmakun, $data['ca']->saldo, 0, 0, 0, 0 ),
			'12' => array( $nbsp.'JUMLAH '.$data['fa']->nmakun, $data['fa']->saldo, 0, 0, 0, 0 ),
			'A'  => array( $nbsp.'JUMLAH ASET ', $data['ca']->saldo + $data['fa']->saldo, 0, 0, 0, 0 ),
			'21' => array( $nbsp.'JUMLAH '.$data['stl']->nmakun, $data['stl']->saldo, 0, 0, 0, 0 ),
			'22' => array( $nbsp.'JUMLAH '.$data['ltl']->nmakun, $data['ltl']->saldo, 0, 0, 0, 0 ),
			'L'  => array( $nbsp.'JUMLAH KEWAJIBAN ', $data['stl']->saldo + $data['ltl']->saldo, 0, 0, 0, 0 ),
			'31' => array( $nbsp.'JUMLAH '.$data['sc']->nmakun, $data['sc']->saldo, 0, 0, 0, 0 ),
			'32' => array( $nbsp.'JUMLAH '.$data['re']->nmakun, $data['re']->saldo, 0, 0, 0, 0 ),
			'E'  => array( $nbsp.'JUMLAH EKUITAS ', $data['sc']->saldo + $data['re']->saldo, 0, 0, 0, 0 ),
			'LE'  => array( $nbsp.'JUMLAH EKUITAS ', ($data['stl']->saldo + $data['ltl']->saldo + $data['sc']->saldo + $data['re']->saldo), 0, 0, 0, 0 ),
		);

		// CURRENT ASSETS
		$html_out.= self::rowContent(['ASET', '', '', '', '', '']);
		$html_out.= self::rowContent(['ASET LANCAR', '', '', '', '', '']);
		$akun11 = \App\Neraca::nrcAkun3($data['ca']->kdakun2, $this->tahun);
		foreach($akun11 as $a11) {
			if((int) $a11->saldo != 0) {
				$html_out.= self::rowContent([$nbsp.$a11->nmakun, $a11->saldo, 0, 0, 0, 0]);
			} 
		}
		$html_out.= self::rowContent($nrc['11']);

		$html_out.= self::rowContent(['&nbsp;', '', '', '', '', '']);

		// FIXED ASSETS
		$html_out.= self::rowContent(['ASET TIDAK LANCAR', '', '', '', '', '']);
		$akun12 = \App\Neraca::nrcAkun3($data['fa']->kdakun2, $this->tahun);
		foreach($akun12 as $a12) {
			if((int) $a12->saldo != 0) {
				$html_out.= self::rowContent([$nbsp.$a12->nmakun, $a12->saldo, 0, 0, 0, 0]);
			}
		}
		$html_out.= self::rowContent($nrc['12']);
		
		// TOTAL ASSETS
		$html_out.= self::rowContent($nrc['A']);
		$html_out.= self::rowContent(['&nbsp;', '', '', '', '', '']);
		
		// LIABILITIES & EQUITY
		// LIABILITIES
		// SHORT TERM LIABILITIES
		$html_out.= self::rowContent(['KEWAJIBAN DAN EKUITAS', '', '', '', '', '']);
		$html_out.= self::rowContent(['KEWAJIBAN', '', '', '', '', '']);
		$html_out.= self::rowContent(['KEWAJIBAN JANGKA PENDEK', '', '', '', '', '']);
		$akun21 = \App\Neraca::nrcAkun3($data['stl']->kdakun2, $this->tahun);
		foreach($akun21 as $a21) {
			if((int) $a21->saldo != 0) {
				$html_out.= self::rowContent([$nbsp.$a21->nmakun, $a21->saldo, 0, 0, 0, 0]);
			}
		}
		$html_out.= self::rowContent($nrc['21']);
		$html_out.= self::rowContent(['&nbsp;', '', '', '', '', '']);

		// LONG TERM LIABILITIES
		$html_out.= self::rowContent(['KEWAJIBAN JANGKA PANJANG', '', '', '', '', '']);
		$akun22 = \App\Neraca::nrcAkun3($data['ltl']->kdakun2, $this->tahun);
		foreach($akun22 as $a22) {
			if((int) $a22->saldo != 0) {
				$html_out.= self::rowContent([$nbsp.$a22->nmakun, $a22->saldo, 0, 0, 0, 0]);
			}
		}
		$html_out.= self::rowContent($nrc['22']);
		$html_out.= self::rowContent($nrc['L']);
		$html_out.= self::rowContent(['&nbsp;', '', '', '', '', '']);

		// EQUITY
		// SHARE CAPITAL
		$html_out.= self::rowContent(['EKUITAS', '', '', '', '', '']);
		$html_out.= self::rowContent(['MODAL SAHAM DISETOR', '', '', '', '', '']);
		$akun31 = \App\Neraca::nrcAkun3($data['sc']->kdakun2, $this->tahun);
		foreach($akun31 as $a31) {
			if((int) $a31->saldo != 0) {
				$html_out.= self::rowContent([$nbsp.$a31->nmakun, $a31->saldo, 0, 0, 0, 0]);
			}
		}
		$html_out.= self::rowContent($nrc['31']);
		$html_out.= self::rowContent(['&nbsp;', '', '', '', '', '']);
		
		// RETAINED EARNINGS
		$html_out.= self::rowContent(['SALDO LABA', '', '', '', '', '']);
		$akun32 = \App\Neraca::nrcAkun3($data['re']->kdakun2, $this->tahun);
		foreach($akun32 as $a32) {
			if((int) $a32->saldo != 0) {
				$html_out.= self::rowContent([$nbsp.$a32->nmakun, $a32->saldo, 0, 0, 0, 0]);
			}
		}
		$html_out.= self::rowContent($nrc['32']);
		$html_out.= self::rowContent(['&nbsp;', '', '', '', '', '']);
		$html_out.= self::rowContent($nrc['LE']);

		// closed <tbody>
		$html_out.= self::$tbody_close; // </tbody>
		$html_out.= self::$table_close; // </table>
		return $html_out;
	}

	/**
	 * description 
	 */
	public function testing()
	{
		$data = array(
			'111' => \App\Neraca::akunAsetDetil($this->tahun, '111', 'KAS DAN SETARA KAS'),
			'112' => \App\Neraca::akunAsetDetil($this->tahun, '111', 'PIUTANG'),
			'113' => \App\Neraca::akunAsetDetil($this->tahun, '111', 'PAJAK DIBAYAR DIMUKA'),
			'114' => \App\Neraca::akunAsetDetil($this->tahun, '111', 'BEBAN DIBAYAR DIMUKA'),
			'115' => \App\Neraca::akunAsetDetil($this->tahun, '111', 'JAMINAN'),
			'116' => \App\Neraca::akunAsetDetil($this->tahun, '111', 'ASET REAL ESTATE'),
		);

		return $data;
	}
}
