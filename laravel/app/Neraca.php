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
	public function akunAset()
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
	}

	/**
	 * description 
	 */
	public static function akunAsetDetil($tahun, $kdakun3, $nmakun3)
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
	}

	/**
	 * description 
	 */
 	public static function mainQuery()
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
	}
	
    /**
	 * description 
	 */
	public static function getKasDanSetaraKas($tahun, $periode)
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
	}

}
