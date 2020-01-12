<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Mpdf\Mpdf;
use DB;

class LaporanRealisasiController extends Controller
{
    //
    /**
     * description 
     */
    public function pendapatan()
    {
		$periode = '';
		if(isset($_GET['periode'])){
			if($_GET['periode']!==''){
				$periode = $_GET['periode'];
			}
		}
		
		$periode1 = "";
		$periode2 = "";
		
		if($periode!==''){
			
			if($periode=='03'){
				$periode1 = "'01','02','03'";
				$periode2 = "nilai03";
			}
			elseif($periode=='06'){
				$periode1 = "'04','05','06'";
				$periode2 = "nilai03+nilai06";
			}
			elseif($periode=='09'){
				$periode1 = "'07','08','09'";
				$periode2 = "nilai03+nilai06+nilai09";
			}
			elseif($periode=='12'){
				$periode1 = "'10','11','12'";
				$periode2 = "nilai03+nilai06+nilai09+nilai12";
			}
			
		}
		
		$query = "
			select  g.nmtriwulan1 as triwulan,
					upper(a.nmakun) as uraian,
					nvl(b.nilai,0) as rkap,
					nvl(c.nilai,0) as rcsdtw,
					abs(nvl(d.nilai,0)) as rlsdtw,
					decode(nvl(c.nilai,0),0,0,round(abs(nvl(d.nilai,0))/c.nilai*100)) as psn2,
					nvl(e.nilai,0) as rctw,
					abs(nvl(f.nilai,0)) as rltw,
					decode(nvl(e.nilai,0),0,0,round(abs(nvl(f.nilai,0))/e.nilai*100)) as psn1,
					decode(nvl(b.nilai,0),0,0,round(abs(nvl(d.nilai,0))/b.nilai*100)) as psn3
			from t_akun a
			left join(
				
				/* cari RKAP tahunan */
				select  substr(kdakun,1,2) as kdakun,
						sum(nilai) as nilai
				from d_pagu_proyek
				where thang='".session('tahun')."'
				group by substr(kdakun,1,2)
				
			) b on(substr(a.kdakun,1,2)=b.kdakun)
			left join(
				
				/* cari RKAP sd triwulanan */
				select  substr(kdakun,1,2) as kdakun,
						sum(".$periode2.") as nilai
				from d_rencana
				where thang='".session('tahun')."'
				group by substr(kdakun,1,2)
				
			) c on(substr(a.kdakun,1,2)=c.kdakun)
			left join(
				
				/* cari realisasi sd triwulanan */
				select  substr(a.kdakun,1,2) as kdakun,
						sum(a.debet-a.kredit) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode<='".$periode."'
				group by substr(a.kdakun,1,2)
				
			) d on(substr(a.kdakun,1,2)=d.kdakun)
			left join(
				
				/* cari RKAP triwulanan */
				select  substr(kdakun,1,2) as kdakun,
						sum(nilai".$periode.") as nilai
				from d_rencana
				where thang='".session('tahun')."'
				group by substr(kdakun,1,2)
				
			) e on(substr(a.kdakun,1,2)=e.kdakun)
			left join(
				
				/* cari realisasi triwulanan */
				select  substr(a.kdakun,1,2) as kdakun,
						sum(a.debet-a.kredit) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode in(".$periode1.")
				group by substr(a.kdakun,1,2)
				
			) f on(substr(a.kdakun,1,2)=f.kdakun),
			(
				select  nmtriwulan1
				from t_triwulan
				where kdtriwulan='".$periode."'
			) g
			where a.kdlap='LR' and a.kddk='K' and a.lvl=2
			order by a.kdakun
		";
		
		$rows = DB::select($query);
		
		$rows_tot = DB::select("
			select	sum(a.rkap) as rkap,
					sum(a.rcsdtw) as rcsdtw,
					sum(a.rlsdtw) as rlsdtw,
					decode(sum(a.rcsdtw),0,0,round(sum(a.rlsdtw)/sum(a.rcsdtw)*100)) as psn2,
					sum(a.rctw) as rctw,
					sum(a.rltw) as rltw,
					decode(sum(a.rctw),0,0,round(sum(a.rltw)/sum(a.rctw)*100)) as psn1,
					decode(sum(a.rkap),0,0,round(sum(a.rlsdtw)/sum(a.rkap)*100)) as psn3
			from(
				".$query."
			) a
		");
		
		$rows = json_decode(json_encode($rows), true);
		$rows_tot = json_decode(json_encode($rows_tot[0]), true);
        
        $data = [
            'tahun' => session('tahun'),
			'periode' => $rows[0]['triwulan'],
            'rows' => $rows,
			'total' => $rows_tot,
        ];

        $html_out = view('realisasi.pendapatan', $data);

		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4-P',
			'margin_left' => 8,
			'margin_right' => 8,
			'margin_top' => 18,
			'margin_bottom' => 18,
		]);

		//mode portrait or landscape
		$mpdf->AddPage('L');

		//write content to PDF
		$mpdf->writeHTML($html_out);
		$mpdf->Output('rincian realisasi pendapatan.pdf', 'I');
		exit;
    }

    /**
     * description 
     */
    public function pendapatanPengembangan()
    {
        $periode = '';
		if(isset($_GET['periode'])){
			if($_GET['periode']!==''){
				$periode = $_GET['periode'];
			}
		}
		
		$periode1 = "";
		$periode2 = "";
		
		if($periode!==''){
			
			if($periode=='03'){
				$periode1 = "'01','02','03'";
				$periode2 = "nilai03";
			}
			elseif($periode=='06'){
				$periode1 = "'04','05','06'";
				$periode2 = "nilai03+nilai06";
			}
			elseif($periode=='09'){
				$periode1 = "'07','08','09'";
				$periode2 = "nilai03+nilai06+nilai09";
			}
			elseif($periode=='12'){
				$periode1 = "'10','11','12'";
				$periode2 = "nilai03+nilai06+nilai09+nilai12";
			}
			
		}
		
		$query = "
			select  f.nmtriwulan1 as triwulan,
					a.nmakun||' - '||a.nmproyek as uraian,
					nvl(e.nilai,0) as rkap,
					nvl(b.nilai,0) as rcsdtw,
					nvl(a.nilai,0) as rlsdtw,
					decode(nvl(b.nilai,0),0,0,round(nvl(a.nilai,0)/b.nilai*100)) as psn2,
					nvl(d.nilai,0) as rctw,
					nvl(c.nilai,0) as rltw,
					decode(nvl(d.nilai,0),0,0,round(nvl(c.nilai,0)/d.nilai*100)) as psn1,
					decode(nvl(e.nilai,0),0,0,round(nvl(a.nilai,0)/e.nilai*100)) as psn3
			from(
				select  nvl(a.id_proyek,0) as id_proyek,
						c.nmproyek,
						substr(a.kdakun,1,3) as kdakun,
						b.nmakun,
						sum(a.nilai) as nilai
				from(
					select  decode(a.parent_id,null,a.id_proyek,b.id_proyek) as id_proyek,
							decode(a.parent_id,null,a.kredit,b.kredit) as kdakun,
							sum(a.nilai) as nilai
					from d_trans a
					left join d_trans b on(a.parent_id=b.id)
					left join t_alur c on(a.id_alur=c.id)
					where a.thang='".session('tahun')."' and to_char(a.tgdok,'mm')<='".$periode."' and c.menu in(1,2)
					group by decode(a.parent_id,null,a.id_proyek,b.id_proyek),
							 decode(a.parent_id,null,a.kredit,b.kredit)
				) a
				left join t_akun b on(substr(a.kdakun,1,3)||'000'=b.kdakun)
				left join t_proyek c on(a.id_proyek=c.id)
				where substr(a.kdakun,1,2)='41'
				group by a.id_proyek,c.nmproyek,substr(a.kdakun,1,3),b.nmakun
			) a
			left join(
				select  id_proyek,
						substr(kdakun,1,3) as kdakun,
						sum(".$periode2.") as nilai
				from d_rencana
				where thang='".session('tahun')."'
				group by id_proyek,substr(kdakun,1,3)
			) b on(a.id_proyek=b.id_proyek and a.kdakun=b.kdakun)
			left join(
				select  nvl(a.id_proyek,0) as id_proyek,
						c.nmproyek,
						substr(a.kdakun,1,3) as kdakun,
						b.nmakun,
						sum(a.nilai) as nilai
				from(
					select  decode(a.parent_id,null,a.id_proyek,b.id_proyek) as id_proyek,
							decode(a.parent_id,null,a.kredit,b.kredit) as kdakun,
							sum(a.nilai) as nilai
					from d_trans a
					left join d_trans b on(a.parent_id=b.id)
					left join t_alur c on(a.id_alur=c.id)
					where a.thang='".session('tahun')."' and to_char(a.tgdok,'mm') in(".$periode1.") and c.menu in(1,2)
					group by decode(a.parent_id,null,a.id_proyek,b.id_proyek),
							 decode(a.parent_id,null,a.kredit,b.kredit)
				) a
				left join t_akun b on(substr(a.kdakun,1,3)||'000'=b.kdakun)
				left join t_proyek c on(a.id_proyek=c.id)
				where substr(a.kdakun,1,2)='41'
				group by a.id_proyek,c.nmproyek,substr(a.kdakun,1,3),b.nmakun
			) c on(a.id_proyek=c.id_proyek and a.kdakun=c.kdakun)
			left join(
				select  id_proyek,
						substr(kdakun,1,3) as kdakun,
						sum(nilai".$periode.") as nilai
				from d_rencana
				where thang='".session('tahun')."'
				group by id_proyek,substr(kdakun,1,3)
			) d on(a.id_proyek=d.id_proyek and a.kdakun=d.kdakun)
			left join(
				select  id_proyek,
						substr(kdakun,1,3) as kdakun,
						sum(nilai) as nilai
				from d_pagu_proyek
				where thang='".session('tahun')."'
				group by id_proyek,substr(kdakun,1,3)
			) e on(a.id_proyek=e.id_proyek and a.kdakun=e.kdakun),
			(
				select  nmtriwulan1
				from t_triwulan
				where kdtriwulan='".$periode."'
			) f
			order by a.kdakun,a.id_proyek
		";
		
		$rows = DB::select($query);
		
		if(count($rows)>0){
			
			$rows_tot = DB::select("
				select	sum(a.rkap) as rkap,
						sum(a.rcsdtw) as rcsdtw,
						sum(a.rlsdtw) as rlsdtw,
						decode(sum(a.rcsdtw),0,0,round(sum(a.rlsdtw)/sum(a.rcsdtw)*100)) as psn2,
						sum(a.rctw) as rctw,
						sum(a.rltw) as rltw,
						decode(sum(a.rctw),0,0,round(sum(a.rltw)/sum(a.rctw)*100)) as psn1,
						decode(sum(a.rkap),0,0,round(sum(a.rlsdtw)/sum(a.rkap)*100)) as psn3
				from(
					".$query."
				) a
			");
			
			$rows = json_decode(json_encode($rows), true);
			$rows_tot = json_decode(json_encode($rows_tot[0]), true);
			
			$data = [
				'tahun' => session('tahun'),
				'periode' => $rows[0]['triwulan'],
				'rows' => $rows,
				'total' => $rows_tot,
			];

			$html_out = view('realisasi.pendapatan-pengembangan', $data);

			$mpdf = new Mpdf([
				'mode' => 'utf-8',
				'format' => 'A4-P',
				'margin_left' => 8,
				'margin_right' => 8,
				'margin_top' => 18,
				'margin_bottom' => 18,
			]);

			//mode portrait or landscape
			$mpdf->AddPage('L');

			//write content to PDF
			$mpdf->writeHTML($html_out);		
			$mpdf->Output('rincian realisasi pendapatan pengembangan.pdf', 'I');
			exit;
			
		}
		else{
			return 'Data tidak ditemukan!';
		}
		
    }

    /**
     * description 
     */
    public function pendapatanPengelolaan()
    {
		$periode = '';
		if(isset($_GET['periode'])){
			if($_GET['periode']!==''){
				$periode = $_GET['periode'];
			}
		}
		
		$periode1 = "";
		$periode2 = "";
		
		if($periode!==''){
			
			if($periode=='03'){
				$periode1 = "'01','02','03'";
				$periode2 = "nilai03";
			}
			elseif($periode=='06'){
				$periode1 = "'04','05','06'";
				$periode2 = "nilai03+nilai06";
			}
			elseif($periode=='09'){
				$periode1 = "'07','08','09'";
				$periode2 = "nilai03+nilai06+nilai09";
			}
			elseif($periode=='12'){
				$periode1 = "'10','11','12'";
				$periode2 = "nilai03+nilai06+nilai09+nilai12";
			}
			
		}
		
		$query = "
			select  f.nmtriwulan1 as triwulan,
					a.nmakun||' - '||a.nmproyek as uraian,
					nvl(e.nilai,0) as rkap,
					nvl(b.nilai,0) as rcsdtw,
					nvl(a.nilai,0) as rlsdtw,
					decode(nvl(b.nilai,0),0,0,round(nvl(a.nilai,0)/b.nilai*100)) as psn2,
					nvl(d.nilai,0) as rctw,
					nvl(c.nilai,0) as rltw,
					decode(nvl(d.nilai,0),0,0,round(nvl(c.nilai,0)/d.nilai*100)) as psn1,
					decode(nvl(e.nilai,0),0,0,round(nvl(a.nilai,0)/e.nilai*100)) as psn3
			from(
				select  nvl(a.id_proyek,0) as id_proyek,
						c.nmproyek,
						substr(a.kdakun,1,3) as kdakun,
						b.nmakun,
						sum(a.nilai) as nilai
				from(
					select  decode(a.parent_id,null,a.id_proyek,b.id_proyek) as id_proyek,
							decode(a.parent_id,null,a.kredit,b.kredit) as kdakun,
							sum(a.nilai) as nilai
					from d_trans a
					left join d_trans b on(a.parent_id=b.id)
					left join t_alur c on(a.id_alur=c.id)
					where a.thang='".session('tahun')."' and to_char(a.tgdok,'mm')<='".$periode."' and c.menu in(1,2)
					group by decode(a.parent_id,null,a.id_proyek,b.id_proyek),
							 decode(a.parent_id,null,a.kredit,b.kredit)
				) a
				left join t_akun b on(substr(a.kdakun,1,3)||'000'=b.kdakun)
				left join t_proyek c on(a.id_proyek=c.id)
				where substr(a.kdakun,1,2)<>'41' and b.kdlap='LR'
				group by a.id_proyek,c.nmproyek,substr(a.kdakun,1,3),b.nmakun
			) a
			left join(
				select  id_proyek,
						substr(kdakun,1,3) as kdakun,
						sum(".$periode2.") as nilai
				from d_rencana
				where thang='".session('tahun')."'
				group by id_proyek,substr(kdakun,1,3)
			) b on(a.id_proyek=b.id_proyek and a.kdakun=b.kdakun)
			left join(
				select  nvl(a.id_proyek,0) as id_proyek,
						c.nmproyek,
						substr(a.kdakun,1,3) as kdakun,
						b.nmakun,
						sum(a.nilai) as nilai
				from(
					select  decode(a.parent_id,null,a.id_proyek,b.id_proyek) as id_proyek,
							decode(a.parent_id,null,a.kredit,b.kredit) as kdakun,
							sum(a.nilai) as nilai
					from d_trans a
					left join d_trans b on(a.parent_id=b.id)
					left join t_alur c on(a.id_alur=c.id)
					where a.thang='".session('tahun')."' and to_char(a.tgdok,'mm') in(".$periode1.") and c.menu in(1,2)
					group by decode(a.parent_id,null,a.id_proyek,b.id_proyek),
							 decode(a.parent_id,null,a.kredit,b.kredit)
				) a
				left join t_akun b on(substr(a.kdakun,1,3)||'000'=b.kdakun)
				left join t_proyek c on(a.id_proyek=c.id)
				where substr(a.kdakun,1,2)<>'41' and b.kdlap='LR'
				group by a.id_proyek,c.nmproyek,substr(a.kdakun,1,3),b.nmakun
			) c on(a.id_proyek=c.id_proyek and a.kdakun=c.kdakun)
			left join(
				select  id_proyek,
						substr(kdakun,1,3) as kdakun,
						sum(nilai".$periode.") as nilai
				from d_rencana
				where thang='".session('tahun')."'
				group by id_proyek,substr(kdakun,1,3)
			) d on(a.id_proyek=d.id_proyek and a.kdakun=d.kdakun)
			left join(
				select  id_proyek,
						substr(kdakun,1,3) as kdakun,
						sum(nilai) as nilai
				from d_pagu_proyek
				where thang='".session('tahun')."'
				group by id_proyek,substr(kdakun,1,3)
			) e on(a.id_proyek=e.id_proyek and a.kdakun=e.kdakun),
			(
				select  nmtriwulan1
				from t_triwulan
				where kdtriwulan='".$periode."'
			) f
			order by a.kdakun,a.id_proyek
		";
		
		$rows = DB::select($query);
		
		if(count($rows)>0){
			
			$rows_tot = DB::select("
				select	sum(a.rkap) as rkap,
						sum(a.rcsdtw) as rcsdtw,
						sum(a.rlsdtw) as rlsdtw,
						decode(sum(a.rcsdtw),0,0,round(sum(a.rlsdtw)/sum(a.rcsdtw)*100)) as psn2,
						sum(a.rctw) as rctw,
						sum(a.rltw) as rltw,
						decode(sum(a.rctw),0,0,round(sum(a.rltw)/sum(a.rctw)*100)) as psn1,
						decode(sum(a.rkap),0,0,round(sum(a.rlsdtw)/sum(a.rkap)*100)) as psn3
				from(
					".$query."
				) a
			");
			
			$rows = json_decode(json_encode($rows), true);
			$rows_tot = json_decode(json_encode($rows_tot[0]), true);
			
			$data = [
				'tahun' => session('tahun'),
				'periode' => $rows[0]['triwulan'],
				'rows' => $rows,
				'total' => $rows_tot,
			];

			$html_out = view('realisasi.pendapatan-pengelolaan', $data);

			$mpdf = new Mpdf([
				'mode' => 'utf-8',
				'format' => 'A4-P',
				'margin_left' => 8,
				'margin_right' => 8,
				'margin_top' => 18,
				'margin_bottom' => 18,
			]);

			//mode portrait or landscape
			$mpdf->AddPage('L');

			//write content to PDF
			$mpdf->writeHTML($html_out);
			
			$mpdf->Output('rincian realisasi pendapatan pengelolaan.pdf', 'I');
			exit;
			
		}
		else{
			return 'Data tidak ditemukan!';
		}
		
    }

    /**
     * description 
     */
    public function beban()
    {
        $periode = '';
		if(isset($_GET['periode'])){
			if($_GET['periode']!==''){
				$periode = $_GET['periode'];
			}
		}
		
		$periode1 = "";
		$periode2 = "";
		
		if($periode!==''){
			
			if($periode=='03'){
				$periode1 = "'01','02','03'";
				$periode2 = "nilai03";
			}
			elseif($periode=='06'){
				$periode1 = "'04','05','06'";
				$periode2 = "nilai03+nilai06";
			}
			elseif($periode=='09'){
				$periode1 = "'07','08','09'";
				$periode2 = "nilai03+nilai06+nilai09";
			}
			elseif($periode=='12'){
				$periode1 = "'10','11','12'";
				$periode2 = "nilai03+nilai06+nilai09+nilai12";
			}
			
		}
		
		$query = "
			select  g.nmtriwulan1 as triwulan,
					upper(a.nmakun) as uraian,
					nvl(b.nilai,0) as rkap,
					nvl(c.nilai,0) as rcsdtw,
					abs(nvl(d.nilai,0)) as rlsdtw,
					decode(nvl(c.nilai,0),0,0,round(abs(nvl(d.nilai,0))/c.nilai*100)) as psn2,
					nvl(e.nilai,0) as rctw,
					abs(nvl(f.nilai,0)) as rltw,
					decode(nvl(e.nilai,0),0,0,round(abs(nvl(f.nilai,0))/e.nilai*100)) as psn1,
					decode(nvl(b.nilai,0),0,0,round(abs(nvl(d.nilai,0))/b.nilai*100)) as psn3
			from t_akun a
			left join(
				
				/* cari RKAP tahunan */
				select  substr(kdakun,1,2) as kdakun,
						sum(nilai) as nilai
				from d_pagu_proyek
				where thang='".session('tahun')."'
				group by substr(kdakun,1,2)
				
			) b on(substr(a.kdakun,1,2)=b.kdakun)
			left join(
				
				/* cari RKAP sd triwulanan */
				select  substr(kdakun,1,2) as kdakun,
						sum(".$periode2.") as nilai
				from d_rencana
				where thang='".session('tahun')."'
				group by substr(kdakun,1,2)
				
			) c on(substr(a.kdakun,1,2)=c.kdakun)
			left join(
				
				/* cari realisasi sd triwulanan */
				select  substr(a.kdakun,1,2) as kdakun,
						sum(a.debet-a.kredit) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode<='".$periode."'
				group by substr(a.kdakun,1,2)
				
			) d on(substr(a.kdakun,1,2)=d.kdakun)
			left join(
				
				/* cari RKAP triwulanan */
				select  substr(kdakun,1,2) as kdakun,
						sum(nilai".$periode.") as nilai
				from d_rencana
				where thang='".session('tahun')."'
				group by substr(kdakun,1,2)
				
			) e on(substr(a.kdakun,1,2)=e.kdakun)
			left join(
				
				/* cari realisasi triwulanan */
				select  substr(a.kdakun,1,2) as kdakun,
						sum(a.debet-a.kredit) as nilai
				from d_buku_besar a
				where a.thang='".session('tahun')."' and a.periode in(".$periode1.")
				group by substr(a.kdakun,1,2)
				
			) f on(substr(a.kdakun,1,2)=f.kdakun),
			(
				select  nmtriwulan1
				from t_triwulan
				where kdtriwulan='".$periode."'
			) g
			where a.kdlap='LR' and a.kddk='D' and a.lvl=2
			order by a.kdakun
		";
		
		$rows = DB::select($query);
		
		$rows_tot = DB::select("
			select	sum(a.rkap) as rkap,
					sum(a.rcsdtw) as rcsdtw,
					sum(a.rlsdtw) as rlsdtw,
					decode(sum(a.rcsdtw),0,0,round(sum(a.rlsdtw)/sum(a.rcsdtw)*100)) as psn2,
					sum(a.rctw) as rctw,
					sum(a.rltw) as rltw,
					decode(sum(a.rctw),0,0,round(sum(a.rltw)/sum(a.rctw)*100)) as psn1,
					decode(sum(a.rkap),0,0,round(sum(a.rlsdtw)/sum(a.rkap)*100)) as psn3
			from(
				".$query."
			) a
		");
		
		$rows = json_decode(json_encode($rows), true);
		$rows_tot = json_decode(json_encode($rows_tot[0]), true);
        
        $data = [
            'tahun' => session('tahun'),
			'periode' => $rows[0]['triwulan'],
            'rows' => $rows,
			'total' => $rows_tot,
        ];

        $html_out = view('realisasi.beban', $data);

		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4-P',
			'margin_left' => 8,
			'margin_right' => 8,
			'margin_top' => 18,
			'margin_bottom' => 18,
		]);

		//mode portrait or landscape
		$mpdf->AddPage('L');

		//write content to PDF
		$mpdf->writeHTML($html_out);
		$mpdf->Output('rincian realisasi beban.pdf', 'I');
		exit;
    }

    /**
     * description 
     */
    public function bebanPokokPenjualan()
    {
		$periode = '';
		if(isset($_GET['periode'])){
			if($_GET['periode']!==''){
				$periode = $_GET['periode'];
			}
		}
		
		$periode1 = "";
		$periode2 = "";
		
		if($periode!==''){
			
			if($periode=='03'){
				$periode1 = "'01','02','03'";
				$periode2 = "nilai03";
			}
			elseif($periode=='06'){
				$periode1 = "'04','05','06'";
				$periode2 = "nilai03+nilai06";
			}
			elseif($periode=='09'){
				$periode1 = "'07','08','09'";
				$periode2 = "nilai03+nilai06+nilai09";
			}
			elseif($periode=='12'){
				$periode1 = "'10','11','12'";
				$periode2 = "nilai03+nilai06+nilai09+nilai12";
			}
			
		}
		
		$query = "
			select  f.nmtriwulan1 as triwulan,
					a.nmakun||' - '||a.nmproyek as uraian,
					nvl(e.nilai,0) as rkap,
					nvl(b.nilai,0) as rcsdtw,
					nvl(a.nilai,0) as rlsdtw,
					decode(nvl(b.nilai,0),0,0,round(nvl(a.nilai,0)/b.nilai*100)) as psn2,
					nvl(d.nilai,0) as rctw,
					nvl(c.nilai,0) as rltw,
					decode(nvl(d.nilai,0),0,0,round(nvl(c.nilai,0)/d.nilai*100)) as psn1,
					decode(nvl(e.nilai,0),0,0,round(nvl(a.nilai,0)/e.nilai*100)) as psn3
			from(
				select  nvl(a.id_proyek,0) as id_proyek,
						c.nmproyek,
						substr(a.kdakun,1,3) as kdakun,
						b.nmakun,
						sum(a.nilai) as nilai
				from(
					select  a.id_proyek,
							a.debet as kdakun,
							sum(a.nilai_bersih) as nilai
					from d_trans a
					left join d_trans b on(a.parent_id=b.id)
					left join t_alur c on(a.id_alur=c.id)
					where a.thang='".session('tahun')."' and to_char(a.tgdok,'mm')<='".$periode."' and c.menu in(4)
					group by a.id_proyek,
							 a.debet
				) a
				left join t_akun b on(substr(a.kdakun,1,3)||'000'=b.kdakun)
				left join t_proyek c on(a.id_proyek=c.id)
				where substr(a.kdakun,1,2)='51'
				group by a.id_proyek,c.nmproyek,substr(a.kdakun,1,3),b.nmakun
			) a
			left join(
				select  id_proyek,
						substr(kdakun,1,3) as kdakun,
						sum(".$periode2.") as nilai
				from d_rencana
				where thang='".session('tahun')."'
				group by id_proyek,substr(kdakun,1,3)
			) b on(a.id_proyek=b.id_proyek and a.kdakun=b.kdakun)
			left join(
				select  nvl(a.id_proyek,0) as id_proyek,
						c.nmproyek,
						substr(a.kdakun,1,3) as kdakun,
						b.nmakun,
						sum(a.nilai) as nilai
				from(
					select  a.id_proyek,
							a.debet as kdakun,
							sum(a.nilai_bersih) as nilai
					from d_trans a
					left join d_trans b on(a.parent_id=b.id)
					left join t_alur c on(a.id_alur=c.id)
					where a.thang='".session('tahun')."' and to_char(a.tgdok,'mm') in(".$periode1.") and c.menu in(4)
					group by a.id_proyek,
							 a.debet
				) a
				left join t_akun b on(substr(a.kdakun,1,3)||'000'=b.kdakun)
				left join t_proyek c on(a.id_proyek=c.id)
				where substr(a.kdakun,1,2)='51'
				group by a.id_proyek,c.nmproyek,substr(a.kdakun,1,3),b.nmakun
			) c on(a.id_proyek=c.id_proyek and a.kdakun=c.kdakun)
			left join(
				select  id_proyek,
						substr(kdakun,1,3) as kdakun,
						sum(nilai".$periode.") as nilai
				from d_rencana
				where thang='".session('tahun')."'
				group by id_proyek,substr(kdakun,1,3)
			) d on(a.id_proyek=d.id_proyek and a.kdakun=d.kdakun)
			left join(
				select  id_proyek,
						substr(kdakun,1,3) as kdakun,
						sum(nilai) as nilai
				from d_pagu_proyek
				where thang='".session('tahun')."'
				group by id_proyek,substr(kdakun,1,3)
			) e on(a.id_proyek=e.id_proyek and a.kdakun=e.kdakun),
			(
				select  nmtriwulan1
				from t_triwulan
				where kdtriwulan='".$periode."'
			) f
			order by a.kdakun,a.id_proyek
		";
		
		$rows = DB::select($query);
		
		if(count($rows)>0){
			
			$rows_tot = DB::select("
				select	sum(a.rkap) as rkap,
						sum(a.rcsdtw) as rcsdtw,
						sum(a.rlsdtw) as rlsdtw,
						decode(sum(a.rcsdtw),0,0,round(sum(a.rlsdtw)/sum(a.rcsdtw)*100)) as psn2,
						sum(a.rctw) as rctw,
						sum(a.rltw) as rltw,
						decode(sum(a.rctw),0,0,round(sum(a.rltw)/sum(a.rctw)*100)) as psn1,
						decode(sum(a.rkap),0,0,round(sum(a.rlsdtw)/sum(a.rkap)*100)) as psn3
				from(
					".$query."
				) a
			");
			
			$rows = json_decode(json_encode($rows), true);
			$rows_tot = json_decode(json_encode($rows_tot[0]), true);
			
			$data = [
				'tahun' => session('tahun'),
				'periode' => $rows[0]['triwulan'],
				'rows' => $rows,
				'total' => $rows_tot,
			];

			$html_out = view('realisasi.beban-pokok-penjualan', $data);

			$mpdf = new Mpdf([
				'mode' => 'utf-8',
				'format' => 'A4-P',
				'margin_left' => 8,
				'margin_right' => 8,
				'margin_top' => 18,
				'margin_bottom' => 18,
			]);

			//mode portrait or landscape
			$mpdf->AddPage('L');

			//write content to PDF
			$mpdf->writeHTML($html_out);
			$mpdf->Output('rincian realisasi beban pokok penjualan.pdf', 'I');
			exit;
			
		}
		else{
			return 'Data tidak ditemukan!';
		}
		
    }

    /**
     * description 
     */
    public function bebanUsaha()
    {
		$periode = '';
		if(isset($_GET['periode'])){
			if($_GET['periode']!==''){
				$periode = $_GET['periode'];
			}
		}
		
		$periode1 = "";
		$periode2 = "";
		
		if($periode!==''){
			
			if($periode=='03'){
				$periode1 = "'01','02','03'";
				$periode2 = "nilai03";
			}
			elseif($periode=='06'){
				$periode1 = "'04','05','06'";
				$periode2 = "nilai03+nilai06";
			}
			elseif($periode=='09'){
				$periode1 = "'07','08','09'";
				$periode2 = "nilai03+nilai06+nilai09";
			}
			elseif($periode=='12'){
				$periode1 = "'10','11','12'";
				$periode2 = "nilai03+nilai06+nilai09+nilai12";
			}
			
		}
		
		$query = "
			select  f.nmtriwulan1 as triwulan,
					a.nmakun||' - '||a.nmproyek as uraian,
					nvl(e.nilai,0) as rkap,
					nvl(b.nilai,0) as rcsdtw,
					nvl(a.nilai,0) as rlsdtw,
					decode(nvl(b.nilai,0),0,0,round(nvl(a.nilai,0)/b.nilai*100)) as psn2,
					nvl(d.nilai,0) as rctw,
					nvl(c.nilai,0) as rltw,
					decode(nvl(d.nilai,0),0,0,round(nvl(c.nilai,0)/d.nilai*100)) as psn1,
					decode(nvl(e.nilai,0),0,0,round(nvl(a.nilai,0)/e.nilai*100)) as psn3
			from(
				select  nvl(a.id_proyek,0) as id_proyek,
						c.nmproyek,
						substr(a.kdakun,1,3) as kdakun,
						b.nmakun,
						sum(a.nilai) as nilai
				from(
					select  a.id_proyek,
							a.debet as kdakun,
							sum(a.nilai_bersih) as nilai
					from d_trans a
					left join d_trans b on(a.parent_id=b.id)
					left join t_alur c on(a.id_alur=c.id)
					where a.thang='".session('tahun')."' and to_char(a.tgdok,'mm')<='".$periode."' and c.menu in(4)
					group by a.id_proyek,
							 a.debet
				) a
				left join t_akun b on(substr(a.kdakun,1,3)||'000'=b.kdakun)
				left join t_proyek c on(a.id_proyek=c.id)
				where substr(a.kdakun,1,2)='52'
				group by a.id_proyek,c.nmproyek,substr(a.kdakun,1,3),b.nmakun
			) a
			left join(
				select  id_proyek,
						substr(kdakun,1,3) as kdakun,
						sum(".$periode2.") as nilai
				from d_rencana
				where thang='".session('tahun')."'
				group by id_proyek,substr(kdakun,1,3)
			) b on(a.id_proyek=b.id_proyek and a.kdakun=b.kdakun)
			left join(
				select  nvl(a.id_proyek,0) as id_proyek,
						c.nmproyek,
						substr(a.kdakun,1,3) as kdakun,
						b.nmakun,
						sum(a.nilai) as nilai
				from(
					select  a.id_proyek,
							a.debet as kdakun,
							sum(a.nilai_bersih) as nilai
					from d_trans a
					left join d_trans b on(a.parent_id=b.id)
					left join t_alur c on(a.id_alur=c.id)
					where a.thang='".session('tahun')."' and to_char(a.tgdok,'mm') in(".$periode1.") and c.menu in(4)
					group by a.id_proyek,
							 a.debet
				) a
				left join t_akun b on(substr(a.kdakun,1,3)||'000'=b.kdakun)
				left join t_proyek c on(a.id_proyek=c.id)
				where substr(a.kdakun,1,2)='52'
				group by a.id_proyek,c.nmproyek,substr(a.kdakun,1,3),b.nmakun
			) c on(a.id_proyek=c.id_proyek and a.kdakun=c.kdakun)
			left join(
				select  id_proyek,
						substr(kdakun,1,3) as kdakun,
						sum(nilai".$periode.") as nilai
				from d_rencana
				where thang='".session('tahun')."'
				group by id_proyek,substr(kdakun,1,3)
			) d on(a.id_proyek=d.id_proyek and a.kdakun=d.kdakun)
			left join(
				select  id_proyek,
						substr(kdakun,1,3) as kdakun,
						sum(nilai) as nilai
				from d_pagu_proyek
				where thang='".session('tahun')."'
				group by id_proyek,substr(kdakun,1,3)
			) e on(a.id_proyek=e.id_proyek and a.kdakun=e.kdakun),
			(
				select  nmtriwulan1
				from t_triwulan
				where kdtriwulan='".$periode."'
			) f
			order by a.kdakun,a.id_proyek
		";
		
		$rows = DB::select($query);
		
		if(count($rows)>0){
			
			$rows_tot = DB::select("
				select	sum(a.rkap) as rkap,
						sum(a.rcsdtw) as rcsdtw,
						sum(a.rlsdtw) as rlsdtw,
						decode(sum(a.rcsdtw),0,0,round(sum(a.rlsdtw)/sum(a.rcsdtw)*100)) as psn2,
						sum(a.rctw) as rctw,
						sum(a.rltw) as rltw,
						decode(sum(a.rctw),0,0,round(sum(a.rltw)/sum(a.rctw)*100)) as psn1,
						decode(sum(a.rkap),0,0,round(sum(a.rlsdtw)/sum(a.rkap)*100)) as psn3
				from(
					".$query."
				) a
			");
			
			$rows = json_decode(json_encode($rows), true);
			$rows_tot = json_decode(json_encode($rows_tot[0]), true);
			
			$data = [
				'tahun' => session('tahun'),
				'periode' => $rows[0]['triwulan'],
				'rows' => $rows,
				'total' => $rows_tot,
			];

        $html_out = view('realisasi.beban-usaha', $data);

			$mpdf = new Mpdf([
				'mode' => 'utf-8',
				'format' => 'A4-P',
				'margin_left' => 8,
				'margin_right' => 8,
				'margin_top' => 18,
				'margin_bottom' => 18,
			]);

			//mode portrait or landscape
			$mpdf->AddPage('L');

			//write content to PDF
			$mpdf->writeHTML($html_out);
			
			$mpdf->Output('rincian realisasi beban usaha.pdf', 'I');
			exit;
			
		}
		else{
			return 'Data tidak ditemukan!';
		}
    }

    /**
	 * description 
	 */
	public static function investasi()
	{
		$arrItem = [
			['idx' => '1', 'val' => 'Menggunakan Dana PD. PSJ', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['idx' => '2', 'val' => 'Menggunakan Dana PMD', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
		];
		
		$arrPSJ = [
			['no' => '1', 'kode' => 'a', 'uraian' => 'Pembangunan Sarana Square', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'b', 'uraian' => 'KSO Proyek Zam-Zam Apartemen - Margonda', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'c', 'uraian' => 'KSO Proyek Anami Klapa Village', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'd', 'uraian' => 'KSO Proyek Lebak Bulus Pembangunan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'e', 'uraian' => 'Pondok Kelapa Town Square (POKETS)', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'f', 'uraian' => 'Gedung Cik\'s', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'g', 'uraian' => 'Gedung Sarana Jaya III', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'h', 'uraian' => 'Situ Gintung II - Pembebasan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'i', 'uraian' => 'Lebak Bulus - Pembangunan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'j', 'uraian' => 'Lebak Bulus - Tanah', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'k', 'uraian' => 'Gedung Sarana Jaya Tebet', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'l', 'uraian' => 'Investasi Lainnya', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'm', 'uraian' => 'Griya Cik\'s', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'n', 'uraian' => 'Alat Produksi Lainnya', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
		];
		
		$arrPMD = [
			['no' => '1', 'kode' => 'a', 'uraian' => 'Proyek Klapa Village Tower A & B', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'b', 'uraian' => 'Pembangunan Lebak Bulus - Pembangunan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'c', 'uraian' => 'Pengembangan SPTA - Pembebasan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'd', 'uraian' => 'Pengembangan SPTA - Pembangunan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'e', 'uraian' => 'Pengembangan DP 0 Lokasi Baru - Pembebasan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'f', 'uraian' => 'Pengembangan DP 0 Lokasi Baru - Pembangunan (Cilangkap & Ujung Menteng)', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'g', 'uraian' => 'Cik\'s Mansion/Griya Cik\'s', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['no' => '1', 'kode' => 'h', 'uraian' => 'Alat Produksi Baru', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
		];

		$arrBinv = [
			['idx' => '1', 'val' => 'Peralatan/Perabot Kantor', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['idx' => '2', 'val' => 'Komputer', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
			['idx' => '3', 'val' => 'Kendaraan', 'rkap' => 0, 'tw' => 0, 'rtw' => 0, 'sdtw' => 0, 'psn1' => 0, 'psn2' => 0],
		];
		
		$data = [
			'tahun' => session('tahun'),
			'itm' => $arrItem,
			'rows1' => $arrPSJ,
			'rows2' => $arrPMD,
			'rows3' => $arrBinv,
		];

		$html_out = view('realisasi.investasi', $data);

		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4-P',
			'margin_left' => 8,
			'margin_right' => 8,
			'margin_top' => 18,
			'margin_bottom' => 18,
		]);

		//mode portrait or landscape
		$mpdf->AddPage('L');

		//write content to PDF
		$mpdf->writeHTML($html_out);
		$mpdf->Output('Bukti Uang Masuk.pdf', 'I');
		exit;
	}
}
