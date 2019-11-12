<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class TableController extends Controller
{
    //
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
    public static $info_laporan = "(Disajikan dalam jutaan Rupiah)";
    public static $table_open = '<br/><table border="1" cellspacing="0" cellpadding="0" style="border: 1px solid; width:100%">';
    public static $table_open_nb = '<br/><table border="0" cellspacing="0" cellpadding="0" style="border: 0px solid; width:100%">';
    public static $table_close = '</table>';
    public static $thead_open = '<thead>';
    public static $thead_close = '</thead>';
    public static $tbody_open = '<tbody>';
    public static $tbody_close = '</tbody>';
	public $reportName;
	public $tahun;

	// methods of class
	public function __construct()
	{
		
	}

    public function setReportName($rName)
    {
		$this->reportName = $rName;
    }
    
    public function getReportName()
	{
		return $this->reportName;
	}

	//formatting number
	/**
	 * description 
	 */
	public static function fornum($number)
	{
		$absNumber = abs($number);
		
		if((int) $number < 0) {
			$number = '('.number_format($absNumber, 0, ',' ,'.').')';
		} else {
			$number = number_format($absNumber, 0, ',' ,'.');
		}
		return $number;
	}

	//check variabel type
	/**
	 * description 
	 */
	public static function cFmt($var)
	{
		if($var == '') {

			//var is empty
			$value = $var;
		} else {

			//var is not empy
			$obj = new LaporanKeuanganController();
			$value = $var;

			if(is_numeric($var)) {

				//var is numeric
				$value = $obj::fornum($var);
			}
		}

		return $value;
	}

	/**
	 * description 
	 */
	public function headerOfReport($namaLaporan)
	{
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
						<td class="ac">'.self::$info_laporan.'</td>
					</tr>
					';
		$html_out.= self::$thead_close;
		$html_out.= self::$table_close;
		
		return $html_out;
	}
}
