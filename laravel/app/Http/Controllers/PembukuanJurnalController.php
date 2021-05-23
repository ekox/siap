<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PembukuanJurnalController extends Controller {

	public function index(Request $request, $tgawal, $tgakhir)
	{
		$where = "";
		$where1 = "";
		if($tgawal!=='xxx' && $tgakhir!=='xxx'){
			$where = " and a.tgsawal between to_date('".$tgawal." 00:00:00','yyyy-mm-dd hh24:mi:ss') and to_date('".$tgakhir." 23:59:59','yyyy-mm-dd hh24:mi:ss') ";
			$where1 = " and b.tgdok between to_date('".$tgawal." 00:00:00','yyyy-mm-dd hh24:mi:ss') and to_date('".$tgakhir." 23:59:59','yyyy-mm-dd hh24:mi:ss') ";
		}
		
		$rows = DB::select("
			select  a.kdakun,
					b.nmakun,
					sum(decode(a.kddk,'D',a.nilai,0)) as debet,
					sum(decode(a.kddk,'K',a.nilai,0)) as kredit
			from(
				/* saldo awal */
				select  to_char(a.tgsawal,'YYYY') as thang,
						to_char(a.tgsawal,'MM') as periode,
						a.kddk,
						a.kdakun,
						sum(a.nilai) as nilai
				from d_sawal a
				where a.thang=? ".$where."
				group by to_char(a.tgsawal,'YYYY'),
						to_char(a.tgsawal,'MM'),
						a.kdakun,
						a.kddk
				
				union all
				
				/* transaksi berjalan termasuk pajak */
				select  to_char(b.tgdok,'yyyy') as thang,
						to_char(b.tgdok,'mm') as periode,
						a.kddk,
						a.kdakun,
						sum(a.nilai) as nilai
				from d_trans_akun a
				left join d_trans b on(a.id_trans=b.id)
				left join t_alur c on(b.id_alur=c.id)
				where b.thang=? and c.neraca1=1 ".$where1."
				group by to_char(b.tgdok,'yyyy'),
						 to_char(b.tgdok,'mm'),
						 a.kddk,
						 a.kdakun
				
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
		$where = "";
		if(isset($_GET['periode'])){
			if($_GET['periode']!==''){
				$where = "where a.periode<='".$_GET['periode']."'";
			}
		}
		
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
						
						/* saldo awal */
						select  to_char(a.tgsawal,'YYYY') as thang,
								to_char(a.tgsawal,'MM') as periode,
								a.kddk,
								a.kdakun,
								sum(a.nilai) as nilai
						from d_sawal a
						where a.thang=?
						group by to_char(a.tgsawal,'YYYY'),
								to_char(a.tgsawal,'MM'),
								a.kdakun,
								a.kddk
						
						union all
						
						/* transaksi berjalan termasuk pajak */
						select  to_char(b.tgdok,'yyyy') as thang,
								to_char(b.tgdok,'mm') as periode,
								a.kddk,
								a.kdakun,
								sum(a.nilai) as nilai
						from d_trans_akun a
						left join d_trans b on(a.id_trans=b.id)
						left join t_alur c on(b.id_alur=c.id)
						where b.thang=? and to_char(b.tgdok,'yyyy')=? and c.neraca1=1
						group by to_char(b.tgdok,'yyyy'),
								 to_char(b.tgdok,'mm'),
								 a.kddk,
								 a.kdakun
						
					) a
					".$where."
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
					".$where."
					group by a.kdakun
				) b on(a.kdakun=b.kdakun)
			) a
			left join t_akun b on(a.kdakun=b.kdakun)
			order by a.kdakun
		",[
			session('tahun'),
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
	
	public function neracaLajur(Request $request, $periode)
	{
		$rows = DB::select("
			select  a.*,
					d.nmakun,
					nvl(b.debet,0) as debet1,
					nvl(b.kredit,0) as kredit1,
					nvl(b.saldo,0) as saldo,
					nvl(c.debet,0) as debet2,
					nvl(c.kredit,0) as kredit2
			from(
				select  a.kdakun,
						sum(a.debet) as debet,
						sum(a.kredit) as kredit
				from d_buku_besar a
				where a.thang=? and a.periode<=?
				group by a.kdakun
			) a
			left join(
				select  a.kdakun,
						sum(a.debet) as debet,
						sum(a.kredit) as kredit,
						decode(substr(a.kdakun,1,1),'1',sum(a.debet)-sum(a.kredit),0) as saldo
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode<=? and b.kdlap='NR'
				group by a.kdakun
			) b on(a.kdakun=b.kdakun)
			left join(
				select  a.kdakun,
						sum(a.debet) as debet,
						sum(a.kredit) as kredit
				from d_buku_besar a
				left join t_akun b on(a.kdakun=b.kdakun)
				where a.thang=? and a.periode<=? and b.kdlap='LR'
				group by a.kdakun
			) c on(a.kdakun=c.kdakun)
			left join t_akun d on(a.kdakun=d.kdakun)
			order by a.kdakun
		",[
			session('tahun'),
			$periode,
			session('tahun'),
			$periode,
			session('tahun'),
			$periode
		]);
		
		$data = '';
		$total_debet = 0;
		$total_kredit = 0;
		$total_debet1 = 0;
		$total_kredit1 = 0;
		$total_saldo = 0;
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
						<td style="text-align:right;">'.number_format($row->saldo,0).'</td>
						<td style="text-align:right;">'.number_format($row->debet2,0).'</td>
						<td style="text-align:right;">'.number_format($row->kredit2,0).'</td>
					  </tr>';
			$total_debet += $row->debet;
			$total_kredit += $row->kredit;
			$total_debet1 += $row->debet1;
			$total_kredit1 += $row->kredit1;
			$total_debet2 += $row->debet2;
			$total_kredit2 += $row->kredit2;
			$total_saldo += $row->saldo;
			
		}
		
		//hitung rugi laba
		if($total_debet1>$total_kredit1){
			$total_kredit3 = $total_debet1-$total_kredit1;
			$total_debet3 = 0;
		}
		else{
			$total_debet3 = $total_kredit1-$total_debet1;
			$total_kredit3 = 0;
		}
		
		if($total_debet2>$total_kredit2){
			$total_kredit4 = $total_debet2-$total_kredit2;
			$total_debet4 = 0;
		}
		else{
			$total_debet4 = $total_kredit2-$total_debet2;
			$total_kredit4 = 0;
		}
		
		//hitung total akhir
		$total_debet5 = $total_debet1 + $total_debet3;
		$total_kredit5 = $total_kredit1 + $total_kredit3;
		$total_debet6 = $total_debet2 + $total_debet4;
		$total_kredit6 = $total_kredit2 + $total_kredit4;
		
		return response()->json(array(
			'data' => $data,
			'total_debet' => number_format($total_debet,0),
			'total_kredit' => number_format($total_kredit,0),
			'total_debet1' => number_format($total_debet1,0),
			'total_kredit1' => number_format($total_kredit1,0),
			'total_debet2' => number_format($total_debet2,0),
			'total_kredit2' => number_format($total_kredit2,0),
			'total_debet3' => number_format($total_debet3,0),
			'total_kredit3' => number_format($total_kredit3,0),
			'total_debet4' => number_format($total_debet4,0),
			'total_kredit4' => number_format($total_kredit4,0),
			'total_debet5' => number_format($total_debet5,0),
			'total_kredit5' => number_format($total_kredit5,0),
			'total_debet6' => number_format($total_debet6,0),
			'total_kredit6' => number_format($total_kredit6,0),
			'total_saldo' => number_format($total_saldo,0),
		));
	}
	
}