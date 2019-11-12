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
		//get content
		/*$rows = array(
			'kas_dan_setara_kas' => \App\Neraca::getKasDanSetaraKas($this->tahun, $periode),
			'piutang_usaha' => \App\Neraca::getKasDanSetaraKas($this->tahun, $periode),
			'pajak_dimuka' => \App\Neraca::getKasDanSetaraKas($this->tahun, $periode),
			'biaya_dimuka' => \App\Neraca::getKasDanSetaraKas($this->tahun, $periode),
			'aset_real_estate' => \App\Neraca::getKasDanSetaraKas($this->tahun, $periode),
			'aset_lancar' => \App\Neraca::getKasDanSetaraKas($this->tahun, $periode),
			'invest_ventura' => 0,
			'invest_asosiasi' => 0,
			'aset_ventura' => 0,
			'properti_inves' => 0,
			'aset_tetap_susut' => 0,
			'aset_lain_lain' => 0,
			'aset_tidak_lancar' => \App\Neraca::getKasDanSetaraKas($this->tahun, $periode),
		);*/

		$arr = array(
			'a111' => \App\Neraca::akunAsetDetil($this->tahun, '111', 'KAS DAN SETARA KAS')[0],
			'n111' => \App\Neraca::akunAsetDetil($this->tahun, '111', 'KAS DAN SETARA KAS')[1],
			's111' => \App\Neraca::akunAsetDetil($this->tahun, '111', 'KAS DAN SETARA KAS')[2],

			'a112' => \App\Neraca::akunAsetDetil($this->tahun, '112', 'PIUTANG')[0],
			'n112' => \App\Neraca::akunAsetDetil($this->tahun, '112', 'PIUTANG')[1],
			's112' => \App\Neraca::akunAsetDetil($this->tahun, '112', 'PIUTANG')[2],
			
			'113' => \App\Neraca::akunAsetDetil($this->tahun, '113', 'PAJAK DIBAYAR DIMUKA'),
			'114' => \App\Neraca::akunAsetDetil($this->tahun, '114', 'BEBAN DIBAYAR DIMUKA'),
			'115' => \App\Neraca::akunAsetDetil($this->tahun, '115', 'JAMINAN'),
			'116' => \App\Neraca::akunAsetDetil($this->tahun, '116', 'ASET REAL ESTATE'),
		);
		
		$dummy['111'] = array( $arr['n111'],$arr['s111'], 0, 0, 0, 0);
		// content of report
		$html_out.= self::$tbody_open;
		$html_out.= self::rowContent($dummy['111']);
		$html_out.= self::rowContent($dummy['112']);
		$html_out.= self::$tbody_close;
		$html_out.= self::$table_close;
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
