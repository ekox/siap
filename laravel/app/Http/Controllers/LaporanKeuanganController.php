<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Mpdf\Mpdf;

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
		$arrParam = [];
		$periode = '01';
		$tahun = $this->tahun;
		
		if(isset($_GET['periode'])) {
			$periode = htmlentities($_GET['periode']);
		}
		
		$this->setReportName('LAPORAN LABA RUGI'); //REALISASI RKAP').strtoupper(self::getPeriode($periode)).' TAHUN 2019');
		$namaLaporan = $this->getReportName();
		$arrParamLaporan = ['nmlap'=>$namaLaporan, 'periode'=>$periode, 'jnslap'=>'lbr'];
		$html_out = $this->headerOfReport($arrParamLaporan);
		$nbsp = $this->nbsp;
		
		// content of report
		$html_out.= self::$table_open;
		$html_out.= self::$thead_open;

		//header of content
		$html_out.= '<tr>
			<th>URAIAN</th>
			<th style="width:25%;">s.d '.self::getPeriode($periode).' '.$tahun.'</th>
			<th style="width:25%;">'.self::getPeriode($periode).' '.$tahun.'</th>
		</tr>';

		//column info
		$html_out.= '<tr>
			<th>1</th>
			<th>2</th>
			<th>3</th>
		</tr>';
		
		$html_out.= self::$thead_close;

		//get content
		$rows = array(
			'pendapatan'=> \App\Labarugi::getPendapatan($this->tahun, $periode),
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
		
		$pendapatan = \App\Labarugi::getPendapatan($this->tahun, $periode);
		$beban_pokok_pendapatan = \App\Labarugi::getBebanPokokPendapatan($this->tahun, $periode);
		$beban_pemasaran = \App\Labarugi::getBebanPemasaran($this->tahun, $periode);
		$beban_adum = \App\Labarugi::getBebanAdministrasiUmum($this->tahun, $periode);
		$pendapatan_lain = \App\Labarugi::getPendapatanLainLain($this->tahun, $periode);
		$beban_lain = \App\Labarugi::getBebanLainLain($this->tahun, $periode);

		//generate row of table body content
		function isiBaris($uraian, $nilai, $nilai1)
		{
			$obj = new LaporanKeuanganController();
			
			return '<tr>
						<td class="pad2">'.$obj->cFmt($uraian).'</td>
						<td class="pad2 ar">'.$obj->cFmt($nilai).'</td>
						<td class="pad2 ar">'.$obj->cFmt($nilai1).'</td>
					</tr>';
		}
	
		//body of content
		$html_out.= self::$tbody_open;

		$html_out.= isiBaris('PENDAPATAN', abs($pendapatan->nilai), abs($pendapatan->nilai1));
		$html_out.= isiBaris('&nbsp;', '', '');
		$html_out.= isiBaris('BEBAN POKOK PENDAPATAN', ($beban_pokok_pendapatan->nilai*-1), ($beban_pokok_pendapatan->nilai1*-1));
		$html_out.= isiBaris('&nbsp;', '', '');
		$laba_kotor = (abs($pendapatan->nilai) + (abs($beban_pokok_pendapatan->nilai)*-1));
		$laba_kotor1 = (abs($pendapatan->nilai1) + (abs($beban_pokok_pendapatan->nilai1)*-1));
		$html_out.= isiBaris('<span class="bo">LABA KOTOR</span>', $laba_kotor, $laba_kotor1);
		$html_out.= isiBaris('&nbsp;', '', '');
		$html_out.= isiBaris('BEBAN USAHA', '', '');
		$html_out.= isiBaris($nbsp.'Beban Pemasaran', (abs($beban_pemasaran->nilai)*-1), (abs($beban_pemasaran->nilai1)*-1));
		$html_out.= isiBaris($nbsp.'Beban Administrasi Umum', (abs($beban_adum->nilai)*-1), (abs($beban_adum->nilai1)*-1));
		$jml_beban_usaha = (abs($beban_pemasaran->nilai) + abs($beban_adum->nilai)) * -1;
		$jml_beban_usaha1 = (abs($beban_pemasaran->nilai1) + abs($beban_adum->nilai1)) * -1;
		$html_out.= isiBaris('Jumlah Beban Usaha', $jml_beban_usaha, $jml_beban_usaha1);
		$html_out.= isiBaris('&nbsp;', '', '');
		$laba_usaha = $laba_kotor + ( abs($jml_beban_usaha) * -1);
		$laba_usaha1 = $laba_kotor1 + ( abs($jml_beban_usaha1) * -1);
		$html_out.= isiBaris('<span class="bo">LABA (RUGI) USAHA</span>', $laba_usaha, $laba_usaha1);
		$html_out.= isiBaris('&nbsp;', '', '');
		$html_out.= isiBaris('<span class="bo">PENDAPATAN (BEBAN) LAIN-LAIN</span>', '', '');
		$html_out.= isiBaris($nbsp.'Pendapatan Lain-lain', $pendapatan_lain->nilai, $pendapatan_lain->nilai1);
		$html_out.= isiBaris($nbsp.'Beban Lain-lain', $beban_lain->nilai, $beban_lain->nilai1);
		$jml_pb_lain = $pendapatan_lain->nilai + $beban_lain->nilai;
		$jml_pb_lain1 = $pendapatan_lain->nilai1 + $beban_lain->nilai1;
		$html_out.= isiBaris('<span class="bo">Jumlah Pendapatan (Beban) Lain-lain Bersih</span>', $jml_pb_lain, $jml_pb_lain1);
		$html_out.= isiBaris('&nbsp;', '', '');
		$laba_bersih = $laba_usaha+$jml_pb_lain;
		$laba_bersih1 = $laba_usaha1+$jml_pb_lain1;
		$html_out.= isiBaris('<span class="bo">LABA SEBELUM BEBAN PAJAK PENGHASILAN<br/>DAN LABA (RUGI) ANAK PERUSAHAAN</span>', $laba_bersih, $laba_bersih1);
		$html_out.= isiBaris('&nbsp;', '', '');
		$html_out.= isiBaris('Manfaat (Beban) Pajak Penghasilan', '', '');
		$html_out.= isiBaris($nbsp.'Pajak Kini', $rows['pajak_kini'], 0, 0);
		$html_out.= isiBaris($nbsp.'Pajak Badan', $rows['pajak_badan'], 0, 0);
		$html_out.= isiBaris('&nbsp;', '', '');
		$html_out.= isiBaris('<span class="bo">Jumlah Manfaat (Beban) Pajak Penghasilan</span>', $rows['manfaat_bpp'], 0);
		$html_out.= isiBaris('&nbsp;', '', '');
		$html_out.= isiBaris('LABA ANAK PERUSAHAAN', 0, 0);
		$html_out.= isiBaris('&nbsp;', '', '');
		$html_out.= isiBaris('LABA BERSIH', $laba_bersih, $laba_bersih1);
		
		$html_out.= self::$tbody_close;
		$html_out.= self::$table_close;
		
		//~ return $html_out;

		//render html to PDF format
		require_once 'laravel/vendor/autoload.php';
		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4-P',
			'margin_left' => 15,
			'margin_right' => 15,
			'margin_top' => 18,
			'margin_bottom' => 18,
		]);

		//mode portrait or landscape
		$mpdf->AddPage('L');

		//write content to PDF
		$mpdf->writeHTML($html_out);
		$mpdf->Output('Laporan Laba (Rugi).pdf', 'I');
		exit;
	}

	//LAPORAN NERACA
	public function balanceSheet()
	{
		$arrParam = [];
		$periode = '01';
		$tahun = $this->tahun;
		$arrParam['tahun'] = $tahun;
		
		if(isset($_GET['periode'])) {
			$periode = htmlentities($_GET['periode']);
			$arrParam['periode'] = $periode;

			$rkey = md5($_GET['periode'].csrf_token());

			if(isset($_GET['rkey'])) {
				if($rkey != $_GET['rkey']) {
					return '<script>alert("periode tidak valid"); window.close();</script>';
				}
			}
		}
		
		$this->setReportName('LAPORAN REALISASI POSISI KEUANGAN');
		$namaLaporan = $this->getReportName();
		$arrParamLaporan = ['nmlap'=>$namaLaporan, 'periode'=>$periode, 'jnslap'=>'nrc'];
		$html_out = $this->headerOfReport($arrParamLaporan);
		$nbsp = str_repeat('&nbsp;', 4);

		// content of report
		$html_out.= self::$table_open;
		$html_out.= self::$thead_open;
		
		$html_out.= '<tr>
			<th>URAIAN</th>
			<th>s.d '.self::getPeriode($periode).' '.$tahun.'</th>
		</tr>';
		
		$html_out.= self::$thead_close;

		// get data of content
		$data = [
			'ca' => \App\Neraca::getAkun2('11', $arrParam), // return value : kdakun2, nmakun, saldo
			'fa' => \App\Neraca::getAkun2('12', $arrParam),
			'stl' => \App\Neraca::getAkun2('21', $arrParam),
			'ltl' => \App\Neraca::getAkun2('22', $arrParam),
			'sc' => \App\Neraca::getAkun2('31', $arrParam),
			're' => \App\Neraca::getAkun2('32', $arrParam),
		];

		// content of report
		$html_out.= self::$tbody_open; // <tbody>
		
		// list of account
		$nrc = array(
			'Z' => array( '&nbsp;', '' ),
			'11' => array( $nbsp.'JUMLAH '.$data['ca']->nmakun, $data['ca']->saldo ),
			'12' => array( $nbsp.'JUMLAH '.$data['fa']->nmakun, $data['fa']->saldo ),
			'A'  => array( $nbsp.'JUMLAH ASET ', $data['ca']->saldo + $data['fa']->saldo ),
			'21' => array( $nbsp.'JUMLAH '.$data['stl']->nmakun, $data['stl']->saldo ),
			'22' => array( $nbsp.'JUMLAH '.$data['ltl']->nmakun, $data['ltl']->saldo ),
			'L'  => array( $nbsp.'JUMLAH KEWAJIBAN ', $data['stl']->saldo + $data['ltl']->saldo ),
			'31' => array( $nbsp.'JUMLAH '.$data['sc']->nmakun, $data['sc']->saldo ),
			'32' => array( $nbsp.'JUMLAH '.$data['re']->nmakun, $data['re']->saldo ),
			'E'  => array( $nbsp.'JUMLAH EKUITAS ', $data['sc']->saldo + $data['re']->saldo ),
			'LE'  => array( $nbsp.'JUMLAH KEWAJIBAN DAN EKUITAS', ($data['stl']->saldo + $data['ltl']->saldo + $data['sc']->saldo + $data['re']->saldo) ),
		);

		// CURRENT ASSETS
		$html_out.= self::rowContent(['ASET', '']);
		$html_out.= self::rowContent([$nbsp.'ASET LANCAR', '']);
		$akun11 = \App\Neraca::getAkun3($data['ca']->kdakun2, $arrParam);
		foreach($akun11 as $a11) {
			if($a11->saldo != 0) {
				$html_out.= self::rowContent([$nbsp.$nbsp.$a11->nmakun, $a11->saldo]);
			} 
		}
		
		$html_out.= self::rowContent($nrc['11']);

		$html_out.= self::rowContent($nrc['Z']);

		// FIXED ASSETS
		$html_out.= self::rowContent([$nbsp.'ASET TIDAK LANCAR', '']);
		$akun12 = \App\Neraca::getAkun3($data['fa']->kdakun2, $arrParam);
		foreach($akun12 as $a12) {
			if( $a12->saldo != 0) {
				$html_out.= self::rowContent([$nbsp.$nbsp.$a12->nmakun, $a12->saldo]);
			}
		}
		$html_out.= self::rowContent($nrc['12']);
		
		// TOTAL ASSETS
		$html_out.= self::rowContent($nrc['A']);
		$html_out.= self::rowContent($nrc['Z']);
		
		// LIABILITIES & EQUITY
		// LIABILITIES
		// SHORT TERM LIABILITIES
		$html_out.= self::rowContent(['KEWAJIBAN DAN EKUITAS', '']);
		$html_out.= self::rowContent(['KEWAJIBAN', '']);
		$html_out.= self::rowContent([$nbsp.'KEWAJIBAN JANGKA PENDEK', '']);
		$akun21 = \App\Neraca::getAkun3($data['stl']->kdakun2, $arrParam);
		foreach($akun21 as $a21) {
			if( $a21->saldo != 0) {
				$html_out.= self::rowContent([$nbsp.$nbsp.$a21->nmakun, $a21->saldo]);
			}
		}
		$html_out.= self::rowContent($nrc['21']);
		$html_out.= self::rowContent($nrc['Z']);

		// LONG TERM LIABILITIES
		$html_out.= self::rowContent([$nbsp.'KEWAJIBAN JANGKA PANJANG', '']);
		$akun22 = \App\Neraca::getAkun3($data['ltl']->kdakun2, $arrParam);
		foreach($akun22 as $a22) {
			if( $a22->saldo != 0) {
				$html_out.= self::rowContent([$nbsp.$nbsp.$a22->nmakun, $a22->saldo]);
			}
		}
		$html_out.= self::rowContent($nrc['22']);
		$html_out.= self::rowContent($nrc['L']);
		$html_out.= self::rowContent($nrc['Z']);

		// EQUITY
		// SHARE CAPITAL
		$html_out.= self::rowContent(['EKUITAS', '']);
		$html_out.= self::rowContent([$nbsp.'MODAL SAHAM DISETOR', '']);
		$akun31 = \App\Neraca::getAkun3($data['sc']->kdakun2, $arrParam);
		foreach($akun31 as $a31) {
			if( $a31->saldo != 0) {
				$html_out.= self::rowContent([$nbsp.$nbsp.$a31->nmakun,$a31->saldo]);
			}
		}
		$html_out.= self::rowContent($nrc['31']);
		$html_out.= self::rowContent($nrc['Z']);
		
		// RETAINED EARNINGS
		$html_out.= self::rowContent([$nbsp.'SALDO LABA', '']);
		$akun32 = \App\Neraca::getAkun3($data['re']->kdakun2, $arrParam);
		foreach($akun32 as $a32) {
			if( $a32->saldo != 0) {
				$html_out.= self::rowContent([$nbsp.$nbsp.$a32->nmakun, $a32->saldo]);
			}
		}
		$html_out.= self::rowContent($nrc['32']);
		$html_out.= self::rowContent($nrc['E']);
		$html_out.= self::rowContent($nrc['Z']);
		$html_out.= self::rowContent($nrc['LE']);

		// closed <tbody>
		$html_out.= self::$tbody_close; // </tbody>
		$html_out.= self::$table_close; // </table>

		//~ return $html_out;

		//render html to PDF format
		require_once 'laravel/vendor/autoload.php';
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
		$mpdf->Output('Laporan Posisi Keuangan.pdf', 'I');
		exit;
	}

	/**
	 * description 
	 */
	public function changeOnEquity()
	{
		$arrParam = [];
		$periode = '01';
		$tahun = $this->tahun;
		$arrParam['tahun'] = $tahun;
		if(isset($_GET['periode'])) {
			$periode = htmlentities($_GET['periode']);
			$arrParam['periode'] = $periode;
		}
		
		$this->setReportName('LAPORAN PERUBAHAN EKUITAS '.self::getPeriode($periode).' TAHUN 2019');
		$namaLaporan = $this->getReportName();
		$arrParamLaporan = ['nmlap'=>$namaLaporan, 'periode'=>$periode, 'jnslap'=>'prb'];
		$html_out = $this->headerOfReport($arrParamLaporan);
		$nbsp = str_repeat('&nbsp;', 4);

		// content of report
		$html_out.= self::$table_open;
		$html_out.= self::$thead_open;

		$html_out.= '<tr>
			<th style="padding-top:1em;padding-bottom:1em;">URAIAN</th>
			<th style="padding-top:1em;padding-bottom:1em;">RKAP 2019</th>
			<th style="padding-top:1em;padding-bottom:1em;">RKAP '.self::getPeriode($periode).' '.$tahun.'</th>
			<th style="padding-top:1em;padding-bottom:1em;">Realisasi '.self::getPeriode($periode).' '.$tahun.'</th>
			<th style="padding-top:1em;padding-bottom:1em;">%</th>
			<th style="padding-top:1em;padding-bottom:1em;">%</th>
		</tr>';

		$html_out.= '<tr>
			<th>1</th>
			<th>2</th>
			<th>3</th>
			<th>4</th>
			<th>5=4:3</th>
			<th>6=4:2</th>
		</tr>';

		$html_out.= self::$thead_close;
		
		$data = array(
			'SLTL' => \App\Prbekuitas::ekuitasAwal('3', $arrParam),
			'LBTB' => \App\Labarugi::getLabaBersih($tahun, $periode),
		);
		
		$prb = array(
			'Z' => array('&nbsp;', '', '', '', '', ''),
			'M' => array('MODAL ', '', '', '', '', ''),
			'311' => array($this->nbsp.'Modal Dasar Perda Nomor 11 Tahun 2018', 0, 0, 0, 0, 0),
			'312' => array($this->nbsp.'Modal yang berlum disetor', 0, 0, 0, 0, 0),
			'JM' => array($this->nbsp.'Jumlah Modal ditempatkan dan disetor ', 0, 0, 0, 0, 0),
			'SL' => array('SALDO LABA ', '', '', '', '', ''),
			'321' => array($this->nbsp.'Saldo Laba Tahun Lalu ', $data['SLTL']->saldo, 0, 0, 0, 0),
			'322' => array($this->nbsp.'Laba Bersih Tahun Berjalan ', $data['LBTB']->nilai, 0, 0, 0, 0),
			'323' => array($this->nbsp.'Kewajiban Imbalan Kerja ', 0, 0, 0, 0, 0),
			'324' => array($this->nbsp.'Kewajiban Pendapatan Anggaran Daerah ', 0, 0, 0, 0, 0),
			'325' => array($this->nbsp.'Dana Sosial dan Dana Pensiun ', 0, 0, 0, 0, 0),
			'JSL' => array($this->nbsp.'Jumlah Saldo Laba ', 0, 0, 0, 0, 0),
			'JE' => array($this->nbsp.'JUMLAH EKUITAS ', 0, 0, 0, 0, 0),
		);

		$html_out.= self::$tbody_open;

		$html_out.= self::rowContent($prb['Z']);
		$html_out.= self::rowContent($prb['M']);
		$html_out.= self::rowContent($prb['311']);
		$html_out.= self::rowContent($prb['312']);
		$html_out.= self::rowContent($prb['JM']);
		$html_out.= self::rowContent($prb['Z']);
		$html_out.= self::rowContent($prb['SL']);
		$html_out.= self::rowContent($prb['321']);
		$html_out.= self::rowContent($prb['322']);
		$html_out.= self::rowContent($prb['323']);
		$html_out.= self::rowContent($prb['324']);
		$html_out.= self::rowContent($prb['325']);
		$html_out.= self::rowContent($prb['JSL']);
		$html_out.= self::rowContent($prb['Z']);
		$html_out.= self::rowContent($prb['JE']);
		$html_out.= self::rowContent($prb['Z']);

		$html_out.= self::$tbody_close;
		$html_out.= self::$table_close;

		//~ return $html_out;

		//render html to PDF format
		require_once 'laravel/vendor/autoload.php';
		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4-P',
			'margin_left' => 15,
			'margin_right' => 15,
			'margin_top' => 18,
			'margin_bottom' => 18,
		]);

		//mode portrait or landscape
		$mpdf->AddPage('L');

		//write content to PDF
		$mpdf->writeHTML($html_out);
		$mpdf->Output('Laporan Perubahan Ekuitas.pdf', 'I');
		exit;
	}

	/**
	 * description 
	 */
	public function cashFlow()
	{
		$arrParam = [];
		$periode = '01';
		$tahun = $this->tahun;
		if(isset($_GET['periode'])) {
			$periode = htmlentities($_GET['periode']);
		}
		
		$this->setReportName('LAPORAN ARUS KAS'); //.strtoupper(self::getPeriode($periode)).' TAHUN 2019');
		$namaLaporan = $this->getReportName();
		$arrParamLaporan = ['nmlap'=>$namaLaporan, 'periode'=>$periode, 'jnslap'=>'cfl'];
		$html_out = $this->headerOfReport($arrParamLaporan);
		$nbsp = str_repeat('&nbsp;', 4);

		// content of report
		$html_out.= self::$table_open;
		$html_out.= self::$thead_open;

		$html_out.= '<tr>
			<th>URAIAN</th>
			<th style="width:25%;">s.d '.self::getPeriode($periode).' '.$tahun.'</th>
			<th style="width:25%;">'.self::getPeriode($periode).' '.$tahun.'</th>
		</tr>';

		$html_out.= '<tr>
			<th>1</th>
			<th>2</th>
			<th>3</th>
		</tr>';

		$html_out.= self::$thead_close;

		$cfl = array(
			'Z' => array('&nbsp;', '', ''),
			'AO' => array('ARUS KAS DARI AKTIVITAS OPERASI ', '', ''),
			'JAO' => array($this->nbsp.'JUMLAH KAS BERSIH DARI AKTIVITAS OPERASI ', 0, 0),
			'AI' => array('ARUS KAS DARI AKTIVITAS INVESTASI ', '', ''),
			'JAI' => array($this->nbsp.'JUMLAH KAS BERSIH DARI DARI AKTIVITAS INVESTASI ',0, 0),
			'AP' => array('ARUS KAS DARI AKTIVITAS PENDANAAN ', '', ''),
			'JAP' => array($this->nbsp.'JUMLAH KAS BERSIH DARI DARI AKTIVITAS PENDANAAN ',0, 0),
			'NT' => array('KENAIKAN (PENURUNAN) BERSIH KAS DAN SETARA KAS ', 0, 0),
			'KAW' => array('KAS DAN SETARA KAS PADA AWAL PERIODE ', 0, 0),
			'KAK' => array('KAS DAN SETARA KAS PADA AKHIR PERIODE ', 0, 0),
		);

		$opr = array(
			'MDB' => array($this->nbsp.'Penerimaan dari Pengembangan Lingkungan', 0, 0),
			'MPK' => array($this->nbsp.'Penerimaan dari Pengelolaan Aktiva', 0, 0),
			'MPJ' => array($this->nbsp.'Penerimaan dari Jasa', 0, 0),
			'MPL' => array($this->nbsp.'Penerimaan Lainnya', 0, 0),
			'KBO' => array($this->nbsp.'Pembayaran Biaya Operasional', 0, 0),
			'KBP' => array($this->nbsp.'Pembayaran Biaya Pemasaan, Kantor, dan Pemeliharaan Umum', 0, 0),
			'KBJ' => array($this->nbsp.'Pembayaran Biaya Jasa Produksi', 0, 0),
			'KDS' => array($this->nbsp.'Pembayaran Dana Pensiun, THT, Pendidikan, dan Sosial', 0, 0),
			'KDG' => array($this->nbsp.'Pembayaran kepada Pegawai', 0, 0),
		);

		$inv = array(
			'MDE' => array($this->nbsp.'Penerimaan Dividen dari entitas asosiasi', 0, 0),
			'MIV' => array($this->nbsp.'Penempatan Investasi Ventura Bersama', 0, 0),
			'MAR' => array($this->nbsp.'Penambahan Aset Real Estate', 0, 0),
			'MPP' => array($this->nbsp.'Penempatan Properti Investasi', 0, 0),
			'MPA' => array($this->nbsp.'Perolehan Aset Tetap', 0, 0),
			'MPM' => array($this->nbsp.'Penempatan Modal pada entitas asosiasi', 0, 0),
		);

		$fnd = array(
			'PMD' => array($this->nbsp.'Penyertaan Modal Daerah/Pinjaman',0 ,0),
			'PAD' => array($this->nbsp.'Pembayaran PAD',0 ,0),
		);

		$html_out.= self::$tbody_open;

		$html_out.= self::rowContent($cfl['Z']);
		
		$html_out.= self::rowContent($cfl['AO']);
		$html_out.= self::rowContent($opr['MDB']);
		$html_out.= self::rowContent($opr['MPK']);
		$html_out.= self::rowContent($opr['MPJ']);
		$html_out.= self::rowContent($opr['MPL']);
		$html_out.= self::rowContent($opr['KBO']);
		$html_out.= self::rowContent($opr['KBP']);
		$html_out.= self::rowContent($opr['KBJ']);
		$html_out.= self::rowContent($opr['KDS']);
		$html_out.= self::rowContent($opr['KDG']);
		$html_out.= self::rowContent($cfl['JAO']);
		$html_out.= self::rowContent($cfl['Z']);
		
		$html_out.= self::rowContent($cfl['AI']);
		$html_out.= self::rowContent($inv['MDE']);
		$html_out.= self::rowContent($inv['MIV']);
		$html_out.= self::rowContent($inv['MAR']);
		$html_out.= self::rowContent($inv['MPP']);
		$html_out.= self::rowContent($inv['MPA']);
		$html_out.= self::rowContent($inv['MPM']);
		$html_out.= self::rowContent($cfl['JAI']);
		$html_out.= self::rowContent($cfl['Z']);
		
		$html_out.= self::rowContent($cfl['AP']);
		$html_out.= self::rowContent($fnd['PMD']);
		$html_out.= self::rowContent($fnd['PAD']);
		$html_out.= self::rowContent($cfl['JAP']);
		$html_out.= self::rowContent($cfl['Z']);
		$html_out.= self::rowContent($cfl['NT']);
		$html_out.= self::rowContent($cfl['Z']);
		$html_out.= self::rowContent($cfl['KAW']);
		$html_out.= self::rowContent($cfl['Z']);
		$html_out.= self::rowContent($cfl['KAK']);

		$html_out.= self::$tbody_close;
		$html_out.= self::$table_close;

		//~ return $html_out;

		//render html to PDF format
		require_once 'laravel/vendor/autoload.php';
		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4-P',
			'margin_left' => 15,
			'margin_right' => 15,
			'margin_top' => 18,
			'margin_bottom' => 18,
		]);

		//mode portrait or landscape
		$mpdf->AddPage('L');

		//write content to PDF
		$mpdf->writeHTML($html_out);
		$mpdf->Output('Laporan Arus Kas.pdf', 'I');
		exit;
	}

	/**
	 * description 
	 */
	public function realisasi()
	{
		return response()->json(['error' => true, 'message' => 'under construction']);
	}

	/**
	 * description 
	 */
	public function rKey()
	{
		$rKey = md5(csrf_token());
		
		if(isset($_GET['periode'])) {
			$rKey = md5($_GET['periode'].csrf_token());
		}

		return $rKey;
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
