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
	/*public function akunAset()
	{
		$rows = DB::select("
			  SELECT SUBSTR (kdakun, 1, 2) kdakun2,
					 SUBSTR (kdakun, 1, 3) kdakun3,
					 kdakun,
					 nmakun,
					 kdlap,
					 kddk,
					 DECODE (
						SUBSTR (kdakun, 2, 1),
						0, 1,
						DECODE (SUBSTR (kdakun, 3, 1),
								0, 2,
								DECODE (SUBSTR (kdakun, 4, 1), 0, 3, 6)))
						kdlv
				FROM t_akun a
			   WHERE kdlap = 'NR' AND SUBSTR (kdakun, 1, 1) = 1
			ORDER BY 1, 2, 3
		");
		
		foreach($rows as $row) {
			$this->kdakun2[] = array($row->kdakun2);
			$this->kdakun3[] = array($row->kdakun3);
			$this->nmakun[] = array($row->nmakun);
		}
		
		return $this;
	}*/

	/**
	 * description 
	 */
	/*public static function akunAsetDetil($tahun, $kdakun3, $nmakun3)
	{
		$rows = DB::select("
				  SELECT '".htmlentities($kdakun3)."' AS kdakun, '".htmlentities($nmakun3)."' AS nmakun, SUM (saldo) AS saldo
					FROM (SELECT thang AS tahun,
								 periode,
								 kdakun,
								 debet,
								 kredit,
								 (debet - kredit) AS saldo
							FROM d_buku_besar
						   WHERE thang = ? AND kdakun LIKE '".htmlentities($kdakun3)."%')
				GROUP BY '111', 'KAS DAN SETARA KAS'
			", [$tahun]);

		if(count($rows)==0) {
			$rows = DB::select("
				SELECT '".htmlentities($kdakun3)."' AS kdakun, '".htmlentities($nmakun3)."' AS nmakun, 0 AS saldo
				  FROM DUAL
			");
		} 

		return array($rows[0]->kdakun, $rows[0]->nmakun, $rows[0]->saldo);
	}*/

	/**
	 * description 
	 */
 	/*public static function mainQuery()
	{
		return "
			  SELECT b.thang AS tahun,
					 b.periode,
					 SUBSTR (b.kdakun, 1, 1) kdakun1,
					 SUBSTR (b.kdakun, 1, 2) kdakun2,
					 SUBSTR (b.kdakun, 1, 3) kdakun3,
					 SUBSTR (b.kdakun, 1, 4) kdakun4,
					 b.kdakun,
					 k.nmakun,
					 b.debet,
					 b.kredit,
					 (b.debet - b.kredit) AS saldo
				FROM d_buku_besar b LEFT JOIN t_akun K ON b.kdakun = k.kdakun
			   WHERE SUBSTR (b.kdakun, 1, 1) = '1'
		";
	}*/
	
    /**
	 * description 
	 */
	/*public static function getKasDanSetaraKas($tahun, $periode)
	{
		$query = DB::select("
			  SELECT kdakun3 AS akun, 'KAS DAN SETARA KAS' AS nmakun, SUM (saldo) AS saldo
				FROM (".self::mainQuery().")
			   WHERE kdakun3 = '111' AND tahun = ?
			GROUP BY kdakun3, 'KAS DAN SETARA KAS'
		", [$tahun]);

		if(count($query) == 0) {
			$query = $this->emptyRows();
		} 

		return $query[0];
	}*/

	/**
	 * description 
	 */
	public static function nrcAkun2($kdakun2, $tahun)
	{
		if(substr($kdakun2, 0, 1) == '1') {
			$dk_saldo = " debet - kredit ";
		} else {
			$dk_saldo = " kredit - debet ";
		}

		$kdakun2 = htmlentities($kdakun2);
		$tahun = htmlentities($tahun);
		
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
	}

	/**
	 * description 
	 */
	public static function nrcAkun3($kdakun2, $tahun)
	{
		if(substr($kdakun2, 0, 1) == '1') {
			$dk_saldo = " debet - kredit ";
		} else {
			$dk_saldo = " kredit - debet ";
		}
		
		$kdakun2 = htmlentities($kdakun2);
		$tahun = htmlentities($tahun);
		
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
							WHERE kdakun LIKE '".$kdakun2."%' AND thang = ?
						 GROUP BY SUBSTR (kdakun, 1, 3)) b
					 ON (a.kdakun3 = b.kdakun3)
			ORDER BY 1
		", [$kdakun2, $tahun]);

		if(count($rows) < 1) {
			$rows = DB::select("
				SELECT '' AS kdakun3, '' nmakun, 0 saldo FROM DUAL	
			", []);
		} 

		return $rows;
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
}
