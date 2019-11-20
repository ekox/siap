<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;
use \App\Labarugi;

class Neraca extends Model
{
    //
	/**
	 * description 
	 */
	public function __construct()
	{
		$obj = new Labarugi();
		$this->emptyRows = Labarugi::emptyRows();
	}

	/**
	 * description 
	 */
	public static function nmAkun2($param)
	{
		$arr = [
			'11' => 'ASET LANCAR',
			'12' => 'ASET TIDAK LANCAR',
			'21' => 'KEWAJIBAN JANGKA PENDEK',
			'22' => 'KEWAJIBAN JANGKA PANJANG',
			'31' => 'MODAL SAHAM DISETOR',
			'32' => 'SALDO LABA',
		];

		return $arr[$param];
	}	

	/**
	 * description 
	 */
	public static function getParam($kode, Array $arrParam)
	{
		if(isset($arrParam['tahun'])) {
			$tahun = htmlentities($arrParam['tahun']);
			$arrWhere['tahun'] = " AND thang = '".$tahun."' ";
		}
		 
		if(isset($arrParam['periode'])) {
			$periode = htmlentities($arrParam['periode']);
			$arrWhere['periode'] = " AND periode <= '".$periode."' ";
		}
		
		$kdakun2 = htmlentities($kode);
		$nmakun2 = htmlentities(self::nmAkun2($kode));

		$andWhere = implode("", $arrWhere);

		return $andWhere;
	}

	/**
	 * description 
	 */
	public static function getAkun2($kdakun2, Array $arrParam)
	{
		$andWhere = self::getParam($kdakun2, $arrParam);
		$nmakun2 = self::nmAkun2($kdakun2);

		if(substr($kdakun2, 0, 1) == '1') {
			$dk_saldo = " debet - kredit ";
		} else {
			$dk_saldo = " kredit - debet ";
		}
		
		$rows = DB::select("
			SELECT '".$kdakun2."' AS kdakun2, '".$nmakun2."' AS nmakun, NVL (SUM (".$dk_saldo."), 0) AS saldo
			  FROM d_buku_besar
			 WHERE SUBSTR (kdakun, 1, 2) = ? ".$andWhere."
		", [$kdakun2]);

		return $rows[0];
	}

	/**
	 * description 
	 *//*
	public static function nrcAkun2($kdakun2, $tahun)
	{
		$kdakun2 = htmlentities($kdakun2);
		$tahun = htmlentities($tahun);

		if(substr($kdakun2, 0, 1) == '1') {
			$dk_saldo = " debet - kredit ";
		} else {
			$dk_saldo = " kredit - debet ";
		}
		
		$rows = DB::select("
			  SELECT a.kdakun2, nmakun, NVL (saldo, 0) saldo
				FROM    (SELECT SUBSTR (kdakun, 1, 2) AS kdakun2, nmakun, 2 AS lvl
						   FROM t_akun
						  WHERE     kdlap = 'NR'
								AND SUBSTR (kdakun, 1, 2) = ?
								AND SUBSTR (kdakun, 4, 1) = '0'
								AND SUBSTR (kdakun, 3, 1) = '0'
								AND SUBSTR (kdakun, 2, 1) != '0') a
					 RIGHT JOIN
						(  SELECT SUBSTR (kdakun, 1, 2) AS kdakun2,
								  SUM ( (".$dk_saldo.")) AS saldo
							 FROM d_buku_besar
							WHERE kdakun LIKE '".$kdakun2."%' AND thang = ?
						 GROUP BY SUBSTR (kdakun, 1, 2)) b
					 ON (a.kdakun2 = b.kdakun2)
			ORDER BY 1
		", [$kdakun2, $tahun]);

		if(count($rows) < 1) {
			$nmakun2 = self::nmAkun2($kdakun2);
			$rows = DB::select("
				SELECT '".$kdakun2."' AS kdakun2, '".$nmakun2."' nmakun, 0 saldo FROM DUAL
			", []);
		} 
		
		return $rows[0];
	}*/

	/**
	 * description 
	 */
	public static function getAkun3($kdakun2, Array $arrParam)
	{
		$kdakun2 = htmlentities($kdakun2);
		$tahun = htmlentities($arrParam['tahun']);

		if(substr($kdakun2, 0, 1) == '1') {
			$dk_saldo = " debet - kredit ";
		} else {
			$dk_saldo = " kredit - debet ";
		}

		$andWhere = self::getParam($kdakun2, $arrParam);
		
		$rows = DB::select("
			  SELECT a.kdakun3, nmakun, NVL (saldo, 0) saldo
				FROM    (SELECT SUBSTR (kdakun, 1, 3) AS kdakun3, nmakun, 3 AS lvl
						   FROM t_akun
						  WHERE     kdlap = 'NR'
								AND SUBSTR (kdakun, 1, 2) = ?
								AND SUBSTR (kdakun, 4, 1) = '0'
								AND SUBSTR (kdakun, 3, 1) != '0'
								AND SUBSTR (kdakun, 2, 1) != '0') a
					 RIGHT JOIN
						(  SELECT SUBSTR (kdakun, 1, 3) AS kdakun3,
								  SUM ( (".$dk_saldo.")) AS saldo
							 FROM d_buku_besar
							WHERE kdakun LIKE '".$kdakun2."%' ".$andWhere."
						 GROUP BY SUBSTR (kdakun, 1, 3)) b
					 ON (a.kdakun3 = b.kdakun3)
			ORDER BY 1
		", [$kdakun2]);

		if(count($rows) < 1) {
			$rows = DB::select("
				SELECT '' AS kdakun3, '' nmakun, 0 saldo FROM DUAL	
			", []);
		} 

		return $rows;
	}

}
