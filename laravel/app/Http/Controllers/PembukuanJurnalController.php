<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PembukuanJurnalController extends Controller {

	public function index(Request $request)
	{
		$rows = DB::select("
			select  a.kdakun,
					b.nmakun,
					sum(decode(a.kddk,'D',a.nilai,0)) as debet,
					sum(decode(a.kddk,'K',a.nilai,0)) as kredit
			from(
				select  to_char(tgdok,'yyyy') as thang,
						to_char(tgdok,'mm') as periode,
						'D' as kddk,
						debet as kdakun,
						sum(nilai) as nilai
				from d_trans a
				left join t_alur b on(a.id_alur=b.id)
				where thang=? and debet is not null and b.neraca1=1
				group by to_char(tgdok,'yyyy'),
						 to_char(tgdok,'mm'),
						 debet
						 
				union all

				select  to_char(tgdok,'yyyy') as thang,
						to_char(tgdok,'mm') as periode,
						'K' as kddk,
						kredit as kdakun,
						sum(nilai) as nilai
				from d_trans a
				left join t_alur b on(a.id_alur=b.id)
				where thang=? and kredit is not null and b.neraca1=1
				group by to_char(tgdok,'yyyy'),
						 to_char(tgdok,'mm'),
						 kredit
			) a
			left join t_akun b on(a.kdakun=b.kdakun)
			group by a.kdakun,b.nmakun
			order by a.kdakun,b.nmakun
		",[
			session('tahun'),
			session('tahun')
		]);
		
		$data = '';
		$total_debet = 0;
		$total_kredit = 0;
		foreach($rows as $row){
			$data .= '<tr>
						<td>'.$row->kdakun.'</td>
						<td>'.$row->nmakun.'</td>
						<td style="text-align:right;">'.number_format($row->debet,0).'</td>
						<td style="text-align:right;">'.number_format($row->kredit,0).'</td>
					  </tr>';
			$total_debet += $row->debet;
			$total_kredit += $row->kredit;
		}
		
		return response()->json(array(
			'data' => $data,
			'total_debet' => number_format($total_debet,0),
			'total_kredit' => number_format($total_kredit,0)
		));
	}
	
	public function neracaPenyesuaian(Request $request)
	{
		$rows = DB::select("
			select  a.kdakun,
					b.nmakun,
					a.debet,
					a.kredit,
					a.debet1,
					a.kredit1,
					a.debet2,
					a.kredit2
			from(
				select  nvl(a.kdakun,b.kdakun) as kdakun,
						nvl(a.debet,0) as debet,
						nvl(a.kredit,0) as kredit,
						nvl(b.debet,0) as debet1,
						nvl(b.kredit,0) as kredit1,
						nvl(a.debet,0)+nvl(b.debet,0) as debet2,
						nvl(a.kredit,0)+nvl(b.kredit,0) as kredit2
				from(
					/* neraca saldo */
					select  a.kdakun,
							sum(decode(a.kddk,'D',a.nilai,0)) as debet,
							sum(decode(a.kddk,'K',a.nilai,0)) as kredit
					from(
						select  to_char(tgdok,'yyyy') as thang,
								to_char(tgdok,'mm') as periode,
								'D' as kddk,
								debet as kdakun,
								sum(nilai) as nilai
						from d_trans a
						left join t_alur b on(a.id_alur=b.id)
						where thang=? and debet is not null and b.neraca1=1
						group by to_char(tgdok,'yyyy'),
								 to_char(tgdok,'mm'),
								 debet
								 
						union all

						select  to_char(tgdok,'yyyy') as thang,
								to_char(tgdok,'mm') as periode,
								'K' as kddk,
								kredit as kdakun,
								sum(nilai) as nilai
						from d_trans a
						left join t_alur b on(a.id_alur=b.id)
						where thang=? and kredit is not null and b.neraca1=1
						group by to_char(tgdok,'yyyy'),
								 to_char(tgdok,'mm'),
								 kredit
					) a
					group by a.kdakun
				) a
				full outer join(
					/* penyesuaian */
					select  a.kdakun,
							sum(decode(a.kddk,'D',a.nilai,0)) as debet,
							sum(decode(a.kddk,'K',a.nilai,0)) as kredit
					from(
						select  to_char(tgdok,'yyyy') as thang,
								to_char(tgdok,'mm') as periode,
								c.kddk,
								c.kdakun,
								sum(c.nilai) as nilai
						from d_trans a
						left join t_alur b on(a.id_alur=b.id)
						left join d_trans_akun c on(a.id=c.id_trans)
						where thang=? and b.neraca1=0
						group by to_char(tgdok,'yyyy'),
								 to_char(tgdok,'mm'),
								 c.kddk,
								 c.kdakun
					) a
					group by a.kdakun
				) b on(a.kdakun=b.kdakun)
			) a
			left join t_akun b on(a.kdakun=b.kdakun)
			order by a.kdakun
		",[
			session('tahun'),
			session('tahun'),
			session('tahun')
		]);
		
		$data = '';
		$total_debet = 0;
		$total_kredit = 0;
		$total_debet1 = 0;
		$total_kredit1 = 0;
		$total_debet2 = 0;
		$total_kredit2 = 0;
		foreach($rows as $row){
			$data .= '<tr>
						<td>'.$row->kdakun.'</td>
						<td>'.$row->nmakun.'</td>
						<td style="text-align:right;">'.number_format($row->debet,0).'</td>
						<td style="text-align:right;">'.number_format($row->kredit,0).'</td>
						<td style="text-align:right;">'.number_format($row->debet1,0).'</td>
						<td style="text-align:right;">'.number_format($row->kredit1,0).'</td>
						<td style="text-align:right;">'.number_format($row->debet2,0).'</td>
						<td style="text-align:right;">'.number_format($row->kredit2,0).'</td>
					  </tr>';
			$total_debet += $row->debet;
			$total_kredit += $row->kredit;
			$total_debet1 += $row->debet1;
			$total_kredit1 += $row->kredit1;
			$total_debet2 += $row->debet2;
			$total_kredit2 += $row->kredit2;
		}
		
		return response()->json(array(
			'data' => $data,
			'total_debet' => number_format($total_debet,0),
			'total_kredit' => number_format($total_kredit,0),
			'total_debet1' => number_format($total_debet1,0),
			'total_kredit1' => number_format($total_kredit1,0),
			'total_debet2' => number_format($total_debet2,0),
			'total_kredit2' => number_format($total_kredit2,0)
		));
	}
	
}