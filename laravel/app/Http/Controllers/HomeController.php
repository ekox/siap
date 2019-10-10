<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class HomeController extends Controller {

	//index
	public function total()
	{
		$rows = DB::select("
			select  a.jml1,
					round(a.nilai1/1000000) as nilai1,
					b.jml2,
					round(b.nilai2/1000000) as nilai2,
					c.jml3,
					round(c.nilai3/1000000) as nilai3,
					d.jml4,
					round(d.nilai4/1000000) as nilai4
			from(
				select  count(a.id) as jml1,
						sum(b.nilai) as nilai1
				from d_trans a
				left outer join(
					select  id_trans,
							sum(nilai) as nilai
					from d_trans_akun
					where kddk='D'
					group by id_trans
				) b on(a.id=b.id_trans)
				left outer join t_alur c on(a.id_alur=c.id)
				where a.thang='".session('tahun')."' and c.menu=1
			) a,
			(
				select  count(a.id) as jml2,
						sum(b.nilai) as nilai2
				from d_trans a
				left outer join(
					select  id_trans,
							sum(nilai) as nilai
					from d_trans_akun
					where kddk='D'
					group by id_trans
				) b on(a.id=b.id_trans)
				left outer join t_alur c on(a.id_alur=c.id)
				where a.thang='".session('tahun')."' and c.menu=2
			) b,
			(
				select  count(a.id) as jml3,
						sum(b.nilai) as nilai3
				from d_trans a
				left outer join(
					select  id_trans,
							sum(nilai) as nilai
					from d_trans_akun
					where kddk='D'
					group by id_trans
				) b on(a.id=b.id_trans)
				left outer join t_alur c on(a.id_alur=c.id)
				where a.thang='".session('tahun')."' and c.menu=3
			) c,

			(
				select  count(a.id) as jml4,
						sum(b.nilai) as nilai4
				from d_trans a
				left outer join(
					select  id_trans,
							sum(nilai) as nilai
					from d_trans_akun
					where kddk='D'
					group by id_trans
				) b on(a.id=b.id_trans)
				left outer join t_alur c on(a.id_alur=c.id)
				where a.thang='".session('tahun')."' and c.menu=4
			) d
		");
		
		return response()->json($rows[0]);
	}

}