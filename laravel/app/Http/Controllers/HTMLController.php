<?php

namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Exception;

class HTMLController extends Controller {
	
	public function __construct()
	{
		
	}
	
	/**
	 * description 
	 */
	public static function css()
	{
		return '<style type="text/css">
			table {width:100%; border:0px solid; border-collapse:collapse;}
			.vb {vertical-align:bottom;}
			.vm {vertical-align:middle;}
			.vt {vertical-align:top;}
			.fwb {font-weight:bold;}
			.tc {text-align:center;}
			.tj {text-align:justify;}
			.tl {text-align:left;}
			.tr {text-align:right;}
			.fz30 {font-size:30%;}
			.fz40 {font-size:40%;}
			.fz50 {font-size:50%;}
			.fz60 {font-size:60%;}
			.fz70 {font-size:70%;}
			.fz75 {font-size:75%;}
			.fz80 {font-size:80%;}
			.fz110 {font-size:110%;}
			.fz115 {font-size:115%;}
			.fz120 {font-size:120%;}
			.fz125 {font-size:125%;}
			.fz130 {font-size:130%;}
			.bd {border:1px solid #000;}
			.bdlr {border-left:1px solid #000; border-right:1px solid #000;}
			.bdl {border-left:1px solid #000;}
			.bdt {border-top:1px solid #000;}
			.bdr {border-right:1px solid #000;}
			.bdb {border-bottom:1px solid #000;}
			.wd2 {width:2%;}
			.wd3 {width:3%;}
			.wd4 {width:4%;}
			.wd5 {width:5%;}
			.wd6 {width:6%;}
			.wd7 {width:7%;}
			.wd8 {width:8%;}
			.wd10 {width:10%;}
			.wd12 {width:12%;}
			.wd15 {width:15%;}
			.wd18 {width:18%;}
			.wd20 {width:20%;}
			.wd25 {width:25%;}
			.wd30 {width:30%;}
			.wd35 {width:35%;}
			.wd40 {width:40%;}
			.wd50 {width:50%;}
			.wd60 {width:60%;}
			.wd70 {width:70%;}
			.wd75 {width:75%;}
			.wd80 {width:80%;}
			.wd85 {width:85%;}
			.wd90 {width:90%;}
			.pd {padding:2px;}
			.cblu {color:#0000FF;}
			.cgre {color:#10AB10;}
			.cpur {color:#F020B9;}
			.cred {color:#FF0000;}
		</style>';
	}
	
	/**
	 * description 
	 */
	public function tglIndo($date) // yyyy-mm-dd
	{
		$tanggal = substr($date,8,2);
		$arr_bulan = [
			'01'=>'Januari', '02'=>'Pebruari', '03'=>'Maret', '04'=>'April',
			'05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus',
			'09'=>'September', '10'=>'Oktober', '11'=>'Nopember', '12'=>'Desember',
		];
		$rom_bulan = [
			'01'=>'I', '02'=>'II', '03'=>'III', '04'=>'IV',
			'05'=>'V', '06'=>'VI', '07'=>'VII', '08'=>'VIII',
			'09'=>'IX', '10'=>'X', '11'=>'XI', '12'=>'II',
		];
		$bln = substr($date,5,2);
		$this->tanggal = $tanggal;
		$this->bulan = $arr_bulan[$bln];
		$this->romawi = $rom_bulan[$bln];
		$this->tahun = substr($date,0,4);
		return $this;
	}
	
	/**
	 * description 
	 */
	public static function terbilang($nilai) 
	{
		function penyebut($nilai) 
		{
			$nilai = abs($nilai);
			$huruf = array("", "satu", "dua", "tiga", "empat", "lima", "enam", "tujuh", "delapan", "sembilan", "sepuluh", "sebelas");
			$temp = "";
			if ($nilai < 12) {
				$temp = " ". $huruf[$nilai];
			} else if ($nilai <20) {
				$temp = penyebut($nilai - 10). " belas";
			} else if ($nilai < 100) {
				$temp = penyebut($nilai/10)." puluh". penyebut($nilai % 10);
			} else if ($nilai < 200) {
				$temp = " seratus" . penyebut($nilai - 100);
			} else if ($nilai < 1000) {
				$temp = penyebut($nilai/100) . " ratus" . penyebut($nilai % 100);
			} else if ($nilai < 2000) {
				$temp = " seribu" . penyebut($nilai - 1000);
			} else if ($nilai < 1000000) {
				$temp = penyebut($nilai/1000) . " ribu" . penyebut($nilai % 1000);
			} else if ($nilai < 1000000000) {
				$temp = penyebut($nilai/1000000) . " juta" . penyebut($nilai % 1000000);
			} else if ($nilai < 1000000000000) {
				$temp = penyebut($nilai/1000000000) . " milyar" . penyebut(fmod($nilai,1000000000));
			} else if ($nilai < 1000000000000000) {
				$temp = penyebut($nilai/1000000000000) . " trilyun" . penyebut(fmod($nilai,1000000000000));
			}     
			return $temp;
		}
		
		if($nilai<0) {
			$hasil = "minus ". trim(penyebut($nilai));
		} else {
			$hasil = trim(penyebut($nilai));
		}
		
		return $hasil;
	}
	
	/**
	 * description 
	 */
	public static function rupiah($angka)
	{
		return number_format($angka, 0, ',', '.');
	}
	
	/**
	 * description 
	 */
	public static function BP()
	{
		$dataBP = DB::select("
			select nama as nmbp, nip as nipbp 
			from   t_user
			where      kddept = '".Session::get('kddept')."' 
			       and kdunit = '".Session::get('kdunit')."' 
			       and kdsatker = '".Session::get('kdsatker')."' 
			       and kdlevel = '04' 
			       and aktif = '1'
		");
		return $dataBP[0];
		//~ self::$nmbp = $dataBP[0]->nmbp;
		//~ self::$nipbp = $dataBP[0]->nipbp;
		//~ return ['nmbp'=>self::$nmbp, 'nipbp'=>self::$nmbp];
	}
	
	/**
	 * description 
	 */
	public static function KPA()
	{
		$dataKPA = DB::select("
			select nama as nmkpa, nip as nipkpa 
			from t_user
			where      kddept = '".Session::get('kddept')."' 
			       and kdunit = '".Session::get('kdunit')."' 
			       and kdsatker = '".Session::get('kdsatker')."' 
			       and kdlevel = '09' 
			       and aktif = '1'
		");
		return $dataKPA[0];
		//~ self::$nmkpa = $dataKPA[0]->nmkpa;
		//~ self::$nipkpa = $dataKPA[0]->nipkpa;
		//~ return ['nmkpa'=>self::$nmkpa, 'nipkpa'=>self::$nipkpa];
	}
	
	/**
	 * description 
	 */
	public static function refBPP()
	{
		$kdsatker = Session::get('kdsatker');
		$kdlevel = Session::get('kdlevel');
		$username = Session::get('username');
		
		$rows = DB::select("
			select a.*, b.nip as nipbpp, b.username, b.kdlevel
			from t_bpp a
			left join t_user b on a.kdsatker=b.kdsatker and b.kdbpp=a.kdbpp
			where b.kdlevel='".$kdlevel."' and username='".$username."' and b.kdsatker='".$kdsatker."'
		");
		
		if(count($rows) > 0) {
			//~ $this->nmbpp = $rows[0]->nmbpp;
			//~ $this->nipbpp = $rows[0]->nipbpp;
			$data = ['nipbpp'=>$rows[0]->nipbpp, 'nmbpp'=>$rows[0]->nmbpp];
		} else {
			//~ $this->nmbpp = '';
			//~ $this->nipbpp = '';
			$data = ['nipbpp'=>'', 'nmbpp'=>''];
		}
		
		return $data;
	}
	
	/**
	 * description 
	 */
	public static function refPPK()
	{
		$kdsatker = Session::get('kdsatker');
		$kdlevel = Session::get('kdlevel');
		$username = Session::get('username');
		
		$rows = DB::select("
			select a.*, b.nip as nipppk, b.username, b.kdlevel
			from t_ppk a
			left join t_user b on a.kdsatker=b.kdsatker and b.kdppk=a.kdppk
			where b.kdlevel='".$kdlevel."' and username='".$username."' and b.kdsatker='".$kdsatker."'
		");
		
		if(count($rows) > 0) {
			//~ $this->nmppk = $rows[0]->nmppk;
			//~ $this->nipppk = $rows[0]->nipppk;
			$data = ['nipppk'=>$rows[0]->nipppk, 'nmppk'=>$rows[0]->nmppk];
		} else {
			//~ $this->nmppk = '';
			//~ $this->nipppk = '';
			$data = ['nipppk'=>'', 'nmppk'=>''];
		}
		
		return $data;
	}
	
	/**
	 * description 
	 */
	public static function today()
	{
		$_today = date('Y-m-d');
		$year = substr($_today,0,4);
		$month = substr($_today,5,2);
		$day = substr($_today,8,2);
		$arr_bulan = [
			'01'=>'Januari', '02'=>'Pebruari', '03'=>'Maret', '04'=>'April',
			'05'=>'Mei', '06'=>'Juni', '07'=>'Juli', '08'=>'Agustus',
			'09'=>'September', '10'=>'Oktober', '11'=>'Nopember', '12'=>'Desember',
		];
		
		return $day.' '.$arr_bulan[$month].' '.$year;
	}
}
