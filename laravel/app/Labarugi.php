<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Labarugi extends Model
{
    //

    /**
	 * description 
	 */
	public static function emptyRows()
	{
		return DB::select("
			SELECT '' AS kdakun, '' AS nmakun, 0 AS nilai FROM DUAL
		");
	}
	
	/**
	 * description 
	 */
	public static function getPendapatan($tahun, $periode)
	{
		$query = DB::select("
			  SELECT periode,
					 kdakun1,
					 'PENDAPATAN' AS nmakun1,
					 SUM (debet) AS debet,
					 SUM (kredit) AS kredit,
					 SUM ((debet-kredit)) AS nilai
				FROM (  SELECT b.thang AS tahun,
							   b.periode,
							   SUBSTR (b.kdakun, 1, 1) kdakun1,
							   SUBSTR (b.kdakun, 1, 2) kdakun2,
							   SUBSTR (b.kdakun, 1, 3) kdakun3,
							   b.kdakun,
							   k.nmakun,
							   b.debet,
							   b.kredit
						  FROM d_buku_besar b LEFT JOIN t_akun K ON b.kdakun = k.kdakun
						 WHERE SUBSTR (b.kdakun, 1, 1) = '4'
					  ORDER BY kdakun1,
							   kdakun2,
							   kdakun3, 
							   kdakun)
			   WHERE tahun = ?
			GROUP BY periode, kdakun1, 'PENDAPATAN'
		", [$tahun]);

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 

		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getBebanPokokPendapatan($tahun, $periode)
	{
		$query = DB::select("
			  SELECT SUBSTR (kdakun, 1, 2) AS kdakun3,
					 'Beban Pokok Pendapatan' AS nmakun3,
					 SUM (debet) AS nilai
				FROM (SELECT a.kdakun,
							 a.nmakun,
							 b.tahun,
							 b.periode,
							 b.debet,
							 b.kredit
						FROM    t_akun a
							 INNER JOIN
								(SELECT kdakun,
										debet,
										kredit,
										thang AS tahun,
										periode
								   FROM d_buku_besar
								  WHERE SUBSTR (kdakun, 1, 2) = '51') b
							 ON b.kdakun = a.kdakun)
			   WHERE tahun = ?
			GROUP BY SUBSTR (kdakun, 1, 2), 'Beban Pokok Pendapatan'
		", [$tahun]);

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 

		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getBebanUsaha($tahun, $periode)
	{
		$query = DB::select("
			  SELECT SUBSTR (kdakun, 1, 2) AS kdakun3,
					 'Beban Usaha' AS nmakun3,
					 SUM (debet) AS nilai
				FROM (SELECT a.kdakun,
							 a.nmakun,
							 b.tahun,
							 b.periode,
							 b.debet,
							 b.kredit
						FROM    t_akun a
							 INNER JOIN
								(SELECT kdakun,
										debet,
										kredit,
										thang AS tahun,
										periode
								   FROM d_buku_besar
								  WHERE SUBSTR (kdakun, 1, 2) = '52') b
							 ON b.kdakun = a.kdakun)
			   WHERE tahun = ?
			GROUP BY SUBSTR (kdakun, 1, 2), 'Beban Usaha'
		", [$tahun]);

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 

		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getBebanPemasaran($tahun, $periode)
	{
		$query = DB::select("
			  SELECT SUBSTR (kdakun, 1, 3) AS kdakun3,
					 'Beban Pemasaran' AS nmakun3,
					 SUM (debet) AS nilai
				FROM (SELECT a.kdakun,
							 a.nmakun,
							 b.tahun,
							 b.periode,
							 b.debet,
							 b.kredit
						FROM    t_akun a
							 INNER JOIN
								(SELECT kdakun,
										debet,
										kredit,
										thang AS tahun,
										periode
								   FROM d_buku_besar
								  WHERE SUBSTR (kdakun, 1, 3) = '521') b
							 ON b.kdakun = a.kdakun)
			   WHERE tahun = ?
			GROUP BY SUBSTR (kdakun, 1, 3), 'Beban Pemasaran'
		", [$tahun]);
		
		if(count($query) == 0) {
			$query = self::emptyRows();
		} 

		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getBebanAdministrasiUmum($tahun, $periode)
	{
		$query = DB::select("
			  SELECT SUBSTR (kdakun, 1, 3) AS kdakun3,
					 'Beban Administrasi dan Umum' AS nmakun3,
					 SUM (debet) AS nilai
				FROM (SELECT a.kdakun,
							 a.nmakun,
							 b.tahun,
							 b.periode,
							 b.debet,
							 b.kredit
						FROM    t_akun a
							 INNER JOIN
								(SELECT kdakun,
										debet,
										kredit,
										thang AS tahun,
										periode
								   FROM d_buku_besar
								  WHERE SUBSTR (kdakun, 1, 3) = '522') b
							 ON b.kdakun = a.kdakun)
			   WHERE tahun = ?
			GROUP BY SUBSTR (kdakun, 1, 3), 'Beban Administrasi dan Umum'
		", [$tahun]);

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 

		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getLabaKotor($tahun, $periode)
	{
		$labaKotor = (int) self::getPendapatan($tahun, $periode)->nilai - self::getBebanPokokPendapatan($tahun, $periode)->nilai;

		$query = DB::select("
			SELECT ".htmlentities($labaKotor)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 

		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getLabaUsaha($tahun, $periode)
	{
		$labaUsaha = (int) self::getLabaKotor($tahun, $periode)->nilai - (int) self::getBebanUsaha($tahun, $periode)->nilai;

		$query = DB::select("
			SELECT ".htmlentities($labaUsaha)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getPendapatanLainLain($tahun, $periode)
	{
		$pendapatanLainLain = 0;
	
		$query = DB::select("
			SELECT ".htmlentities($pendapatanLainLain)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getBebanLainLain($tahun, $periode)
	{
		$bebanLainLain = 0;
	
		$query = DB::select("
			SELECT ".htmlentities($bebanLainLain)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getPendapatanBebanLainLain($tahun, $periode)
	{
		$pendapatanBebanLainLain = (int) self::getPendapatanLainLain($tahun, $periode)->nilai - (int) self::getBebanLainLain($tahun, $periode)->nilai;
	
		$query = DB::select("
			SELECT ".htmlentities($pendapatanBebanLainLain)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getManfaatBebanPajakPenghasilan($tahun, $periode)
	{
		$manfaatBebanPajakPenghasilan = 0;
	
		$query = DB::select("
			SELECT ".htmlentities($manfaatBebanPajakPenghasilan)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

	/**
	 * description 
	 */
	
	public static function getPajakKini($tahun, $periode)
	{
		$pajakKini = 0;
	
		$query = DB::select("
			SELECT ".htmlentities($pajakKini)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getPajakBadan($tahun, $periode)
	{
		$pajakBadan = 0;
	
		$query = DB::select("
			SELECT ".htmlentities($pajakBadan)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getLabaAnakPerusahaan($tahun, $periode)
	{
		$labaAnakPerusahaan = (int) self::getPajakKini($tahun, $periode)->nilai + (int) self::getPajakBadan($tahun, $periode)->nilai ;
	
		$query = DB::select("
			SELECT ".htmlentities($labaAnakPerusahaan)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

	/**
	 * description 
	 */
	public static function getLabaBersih($tahun, $periode)
	{
		$labaBersih = 0;
	
		$query = DB::select("
			SELECT ".htmlentities($labaBersih)." AS nilai
			FROM DUAL
		");

		if(count($query) == 0) {
			$query = self::emptyRows();
		} 
		
		return $query[0];
	}

}
