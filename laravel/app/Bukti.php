<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Bukti extends Model
{
    //
    /**
	 * description 
	 */
	public static function queryUangMukaKerja($id)
	{
		$rows = DB::select("
            SELECT a.id,
                   a.kdunit,
                   a.thang,
                   a.id_alur,
                   a.id_penerima,
                   a.nodok,
                   TO_CHAR (a.tgdok, 'dd-mm-yyyy') tgdok,
                   a.uraian,
                   a.notrans,
                   a.kdtran,
                   b.nmalur,
                   b.menu,
                   b.batas1,
                   b.batas2,
                   c.id AS id_trans_akun,
                   c.kdakun,
                   c.nilai,
                   c.kddk,
                   d.nmakun
              FROM d_trans a
                   LEFT JOIN t_alur b
                      ON (a.id_alur = b.id)
                   LEFT JOIN d_trans_akun c
                      ON (a.id = c.id_trans)
                   LEFT JOIN t_akun d
                      ON (c.kdakun = d.kdakun)
             WHERE a.thang = '2019' AND b.menu = 3 AND c.kddk = 'K' AND c.id_trans = ?
		",
		[$id]);

		return $rows[0];
	}

    /**
     * description 
     */
    public static function queryUangMasuk()
    {
        
    }
}
