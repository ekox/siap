<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;

class TableController extends Controller
{

    //wite code below
    //properties of class
    public static $css_style = '<style>
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

	public static $entitas = "PERUMDA PEMBANGUNAN SARANA JAYA";
    public static $infoLaporan = "(Disajikan dalam jutaan Rupiah)";
    public static $table_open = '<br/><table border="1" cellspacing="0" cellpadding="0" style="border: 1px solid; width:100%">';
    public static $table_open_nb = '<br/><table border="0" cellspacing="0" cellpadding="0" style="border: 0px solid; width:100%">';
    public static $table_open_nb1 = '<br/><table border="0" cellspacing="0" cellpadding="0" style="border: 1px solid; width:100%">';
    public static $table_close = '</table>';
    public static $thead_open = '<thead>';
    public static $thead_close = '</thead>';
    public static $tbody_open = '<tbody>';
    public static $tbody_close = '</tbody>';
	public $reportName;
	public $tahun;

	// methods of class

	/**
	 * constructor 
	 */
	public function __construct()
	{
		//wride code below
		$this->tahun = session('tahun');
	}

	/**
	 * SETTER 
	 */
    public function setReportName($rName)
    {
		$this->reportName = $rName;
    }

    /**
	 * GETTER 
	 */
    public function getReportName()
	{
		return $this->reportName;
	}

	/**
	/**
	 * formatting number 
	 */
	public static function fornum($number)
	{
		$absNumber = abs($number);
		
		if( $number < 0 ) {
			$number = '('.number_format($absNumber, 0, ',' ,'.').')';
		} else {
			$number = number_format($absNumber, 0, ',' ,'.');
		}
		
		return $number;
	}

	/**
	 * check variabel type
	 */
	public static function cFmt($var)
	{
		$value = $var;
		
		if($var == '') {

			//var is empty and var is not numeric type
			$value = $var;
			
		} else {

			//var is not empty and var is not numeric type
			if(is_numeric($var)) {

				//var is numeric type
				$value = self::fornum($var);
				
			} 
		}

		return $value;
	}

	/**
	 * header Of Report 
	 */
	public function headerOfReport(Array $arrParam)
	{
		$tahun = session('tahun');
		$namaLaporan = $arrParam['nmlap'];
		$periode = $arrParam['periode'];
		
		// header of report
		$html_out = self::$css_style;
		$html_out.= self::$table_open_nb;
		$html_out.= self::$thead_open;
		$html_out.= '<tr>
						<th>'.self::$entitas.'</th>
					</tr>
					<tr>
						<th>'.$namaLaporan.'</th>
					</tr>
					<tr>
						<th class="ac">Per '.self::eofLaporan($periode).' '.$tahun.'</th>
					</tr>
					<tr>
						<td class="ac">'.self::$infoLaporan.'</td>
					</tr>
					';
		$html_out.= self::$thead_close;
		$html_out.= self::$table_close;
		
		return $html_out;
	}

	/**
	 * description 
	 */
	public static function rowContent(Array $arr)
	{
		$n = count($arr);
		
		$html_out = '<tr>';

		for($i = 0; $i < $n; $i++) {

			if(is_numeric($arr[$i])) {
				$html_out .= '<td class="pad2 ar">'. self::cFmt($arr[$i]) .'</td>';
			} else {
				$html_out .= '<td class="pad2">'. self::cFmt($arr[$i]) .'</td>';
			}
			
		} 
		
		$html_out .= '</tr>';
		
		return $html_out;
	}

	/**
	 * description 
	 */
	public static function eofLaporan($periode)
	{
		$year = session('tahun');
		$isLeapYear = false;
		$eofFebruary = '28 Februari ';
		$remain = $year % 4;
		if($remain == 0) {
			$isLeapYear = !$isLeapYear;
			$eofFebruary = '29 Februari ';
		}
		
		$arrPeriode = array(
			'01'=>'31 Januari ', '02'=>$eofFebruary, '03'=>' 31 Maret ', '04'=>' 30 April ', '05'=>'31 Mei ', '06'=>'30 Juni ', '07'=>'31 Juli ', '08'=>'31 Agustus ', '09'=>'30 September ', '10'=>'31 Oktober ', '11'=>'30 November ', '12'=>'31 Desember '
		);

		return $arrPeriode[$periode];
	}

}
