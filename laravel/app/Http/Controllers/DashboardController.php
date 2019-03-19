<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DashboardController extends Controller {

	//index
	public function index()
	{
		try{
			return view('dashboard-baru');
		}
		catch(\Exception $e){
			return 'Koneksi terputus!';
		}
	}
	
	public function grafik1()
	{
		try{
			$rows = DB::select("
				select	status
				from d_status_api
				where jenis='01'
			");
			
			$tabel = "d_data_induk_temp";
			if($rows[0]->status=='1'){
				$tabel = "d_data_induk";
			}
			
			$and="";
			if(session('kdlevel')=='02' || session('kdlevel')=='06'){
				$and=" AND kdppk='".session('kdppk')."' ";
			}
			
			$rows = DB::select("
				SELECT	a.jenis,
						IFNULL(a.jml,0) AS jml,
						IFNULL(round(a.nilai/1000000),0) AS nilai
				FROM(
					SELECT	'SPP' AS jenis,
							COUNT(nospp) AS jml,
							SUM(totnilmak) AS nilai
					FROM ".$tabel."
					WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."' AND nospp<>'' AND nospp IS NOT NULL ".$and."
					
					UNION ALL
					
					SELECT	'SPM' AS jenis,
							COUNT(nospp) AS jml,
							SUM(totnilmak) AS nilai
					FROM ".$tabel."
					WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."' AND nospm<>'' AND nospm IS NOT NULL ".$and."
					
					UNION ALL
					
					SELECT	'SP2D' AS jenis,
							COUNT(nospp) AS jml,
							SUM(totnilmak) AS nilai
					FROM ".$tabel."
					WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."' AND nosp2d<>'' AND nosp2d IS NOT NULL ".$and."
				) a
			");
			
			if(count($rows)>0){
				
				foreach($rows as $row){
					$arr_jenis[] = $row->jenis;
					$arr_jml[] = $row->jml;
					$arr_nilai[] = $row->nilai;
				}
				
				$grafik = "
					<script>
						Highcharts.chart('grafik1', {
							chart: {
								plotBackgroundColor: null,
								plotBorderWidth: null,
								plotShadow: false,
								backgroundColor:'rgba(255, 255, 255, 0.0)',
								zoomType: 'xy'
							},
							title: {
								text: 'Realisasi (Status)'
							},
							xAxis: {
								categories: ['".implode("','", $arr_jenis)."'],
								crosshair: true,
								labels: {
									style: {
										color: 'white'
									}
								}
							},
							yAxis: [{ // Primary yAxis
								labels: {
									format: '{value}jt',
									style: {
										color: 'white'
									}
								},
								title: {
									text: 'Nilai',
									style: {
										color: 'white'
									}
								}
							}, { // Secondary yAxis
								title: {
									text: 'Jumlah',
									style: {
										color: 'white'
									}
								},
								labels: {
									format: '{value}',
									style: {
										color: 'white'
									}
								},
								opposite: true
							}],
							tooltip: {
								shared: true
							},
							legend: {
								enabled: false
							},
							series: [{
								name: 'Jumlah',
								type: 'column',
								yAxis: 1,
								data: [".implode(",", $arr_jml)."],
								tooltip: {
									valueSuffix: ''
								}

							}, {
								name: 'Nilai',
								type: 'spline',
								data: [".implode(",", $arr_nilai)."],
								tooltip: {
									valueSuffix: 'jt'
								}
							}]
						});
					</script>
				";
				
				return $grafik;
				
			}
			else{
				return 'Data tidak ditemukan!';
			}
			
		}
		catch(\Exception $e){
			return 'Koneksi terputus!';
		}
	}
	
	public function grafik2()
	{
		try{
			$rows = DB::select("
				select	status
				from d_status_api
				where jenis='02'
			");
			
			$tabel1 = "d_data_mak_temp";
			if($rows[0]->status=='1'){
				$tabel1 = "d_data_mak";
			}
			
			$rows = DB::select("
				select	status
				from d_status_api
				where jenis='01'
			");
			
			$tabel2 = "d_data_induk_temp";
			if($rows[0]->status=='1'){
				$tabel2 = "d_data_induk";
			}
			
			$and="";
			if(session('kdlevel')=='02' || session('kdlevel')=='06'){
				$and=" AND b.kdppk='".session('kdppk')."' ";
			}
			
			$rows = DB::select("
				SELECT	a.dashboard AS jenis,
						IFNULL(b.jml,0) AS jml,
						IFNULL(ROUND(b.nilai/1000000),0) AS nilai
				FROM t_jnsbelanja a
				LEFT OUTER JOIN(
					
					SELECT	SUBSTR(a.kdakun,1,2) AS jnsbelanja,
							COUNT(*) AS jml,
							SUM(a.nilmak) AS nilai
					FROM ".$tabel1." a
					LEFT OUTER JOIN ".$tabel2." b ON(a.kdsatker=b.kdsatker AND a.thang=b.thang AND a.code_id=b.code_id)
					WHERE a.kdsatker='".session('kdsatker')."' AND a.thang='".session('tahun')."' ".$and."
					GROUP BY SUBSTR(a.kdakun,1,2)
					
				) b ON(a.jnsbelanja=b.jnsbelanja)
			");
			
			if(count($rows)>0){
				
				foreach($rows as $row){
					$arr_jenis[] = $row->jenis;
					$arr_jml[] = $row->jml;
					$arr_nilai[] = $row->nilai;
				}
				
				$grafik = "
					<script>
						Highcharts.chart('grafik2', {
							chart: {
								plotBackgroundColor: null,
								plotBorderWidth: null,
								plotShadow: false,
								backgroundColor:'rgba(255, 255, 255, 0.0)',
								zoomType: 'xy'
							},
							title: {
								text: 'Realisasi (Jenis Belanja)'
							},
							xAxis: {
								categories: ['".implode("','", $arr_jenis)."'],
								crosshair: true,
								labels: {
									style: {
										color: 'white'
									}
								}
							},
							yAxis: [{ // Primary yAxis
								labels: {
									format: '{value}jt',
									style: {
										color: 'white'
									}
								},
								title: {
									text: 'Nilai',
									style: {
										color: 'white'
									}
								}
							}, { // Secondary yAxis
								title: {
									text: 'Jumlah',
									style: {
										color: 'white'
									}
								},
								labels: {
									format: '{value}',
									style: {
										color: 'white'
									}
								},
								opposite: true
							}],
							tooltip: {
								shared: true
							},
							legend: {
								enabled: false
							},
							series: [{
								name: 'Jumlah',
								type: 'column',
								yAxis: 1,
								data: [".implode(",", $arr_jml)."],
								tooltip: {
									valueSuffix: ''
								}

							}, {
								name: 'Nilai',
								type: 'spline',
								data: [".implode(",", $arr_nilai)."],
								tooltip: {
									valueSuffix: 'jt'
								}
							}]
						});
					</script>
				";
				
				return $grafik;
				
			}
			else{
				return 'Data tidak ditemukan!';
			}
			
		}
		catch(\Exception $e){
			return 'Koneksi terputus!';
		}
	}
	
	public function grafik3()
	{
		try{
			$rows = DB::select("
				select	status
				from d_status_api
				where jenis='01'
			");
			
			$tabel = "d_data_induk_temp";
			if($rows[0]->status=='1'){
				$tabel = "d_data_induk";
			}
			
			$rows = DB::select("
				SELECT	periode
				FROM t_periode
				ORDER BY periode ASC
			");
			
			if(count($rows)>0){
				
				foreach($rows as $row){
					
					$arr_sum[] = "SUM(jml".$row->periode.")";
					
					$query[] = "
							SELECT	'".$row->periode."' AS periode,
									".implode("+", $arr_sum)." AS nilai
							FROM d_hal3dipa
							WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."' AND kdtrktrm='1'
						";
						
					$query1[] = "
							SELECT	'".$row->periode."' AS periode,
									SUM(totnilmak) AS nilai
							FROM ".$tabel."
							WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."' AND DATE_FORMAT(tgspm,'%mm')<='".$row->periode."'
						";
				
				}
										
				$rows = DB::select("
					SELECT	a.periode,
							a.kdbulan,
							ROUND(b.nilai/1000000) AS rencana,
							IFNULL(ROUND(c.nilai/1000000),0) AS realisasi
					FROM t_periode a
					LEFT OUTER JOIN(
						".implode(" union all ", $query)."
					) b ON(a.periode=b.periode)
					LEFT OUTER JOIN(
						".implode(" union all ", $query1)."
					) c ON(a.periode=c.periode)
				");
						
				if(count($rows)>0){
				
					foreach($rows as $row){
						$arr_jenis[] = $row->kdbulan;
						$arr_rencana[] = $row->rencana;
						$arr_realisasi[] = $row->realisasi;
					}
					
					$grafik = "
						<script>
							Highcharts.chart('grafik3', {
								chart: {
									plotBackgroundColor: null,
									plotBorderWidth: null,
									plotShadow: false,
									backgroundColor:'rgba(255, 255, 255, 0.0)',
									zoomType: 'line'
								},
								title: {
									text: 'Realisasi vs Rencana'
								},
								xAxis: {
									categories: ['".implode("','", $arr_jenis)."'],
									crosshair: true,
									labels: {
										style: {
											color: 'white'
										}
									}
								},
								yAxis: {
									labels: {
										format: '{value}jt',
										style: {
											color: 'white'
										}
									},
									title: {
										text: 'Nilai',
										style: {
											color: 'white'
										}
									}
								},
								tooltip: {
									shared: true
								},
								legend: {
									enabled: false
								},
								series: [{
									name: 'Perencanaan',
									data: [".implode(",", $arr_rencana)."]
								}, {
									name: 'Realisasi',
									data: [".implode(",", $arr_realisasi)."]
								}]
							});
						</script>
					";
					
					return $grafik;
					
				}
				else{
					return 'Data tidak ditemukan!';
				}
				
			}
			else{
				return 'Data periode tidak ditemukan!';
			}
			
		}
		catch(\Exception $e){
			return 'Koneksi terputus!';
		}
	}
	
	public function grafik4()
	{
		try{
			$rows = DB::select("
				select	status
				from d_status_api
				where jenis='01'
			");
			
			$tabel = "d_data_induk_temp";
			if($rows[0]->status=='1'){
				$tabel = "d_data_induk";
			}
			
			$rows = DB::select("
				SELECT	a.*,
					IFNULL(b.nilai,0) AS nilai
				FROM t_norma_waktu a
				LEFT OUTER JOIN(
					SELECT	a.*,
						COUNT(b.nospp) AS nilai
					FROM t_norma_waktu a
					LEFT OUTER JOIN(
						SELECT	a.nospp,
							DATEDIFF(a.max_id,a.tgspp) AS jmlhari
						FROM(
							SELECT	a.nospp,
								c.tgspp,
								MAX(a.created_at) AS max_id
							FROM d_spp_histori a
							LEFT OUTER JOIN t_alur_status b ON(a.id_alur=b.id_alur)
							LEFT OUTER JOIN ".$tabel." c ON(a.kdsatker=c.kdsatker AND a.thang=c.thang AND a.nospp=c.nospp)
							WHERE a.kdsatker='".session('kdsatker')."' AND a.thang='".session('tahun')."' AND b.is_spp='1'
							GROUP BY a.nospp,c.tgspp
						) a
					) b ON(b.jmlhari BETWEEN a.range1 AND a.range2)
					WHERE a.jenis='01'
				) b ON(a.id=b.id)
				WHERE a.jenis='01'	
			");
						
			if(count($rows)>0){
			
				foreach($rows as $row){
					$arr_jenis[] = $row->uraian;
					$arr_nilai[] = $row->nilai;
				}
				
				$grafik = "
					<script>
						Highcharts.chart('grafik4', {
							chart: {
								plotBackgroundColor: null,
								plotBorderWidth: null,
								plotShadow: false,
								backgroundColor:'rgba(255, 255, 255, 0.0)',
								type: 'column'
							},
							title: {
								text: 'Ketepatan (SPP)'
							},
							xAxis: {
								categories: ['".implode("','", $arr_jenis)."'],
								crosshair: true,
								labels: {
									style: {
										color: 'white'
									}
								}
							},
							yAxis: {
								labels: {
									format: '{value}',
									style: {
										color: 'white'
									}
								},
								title: {
									text: 'Jumlah',
									style: {
										color: 'white'
									}
								}
							},
							tooltip: {
								shared: true
							},
							legend: {
								enabled: false
							},
							series: [{
								name: 'Jumlah',
								data: [".implode(",", $arr_nilai)."]
							}]
						});
					</script>
				";
				
				return $grafik;
				
			}
			else{
				return 'Data tidak ditemukan!';
			}
			
		}
		catch(\Exception $e){
			return 'Koneksi terputus!';
		}
	}
	
	public function grafik5()
	{
		try{
			$rows = DB::select("
				select	status
				from d_status_api
				where jenis='01'
			");
			
			$tabel = "d_data_induk_temp";
			if($rows[0]->status=='1'){
				$tabel = "d_data_induk";
			}
			
			$rows = DB::select("
				SELECT	a.*,
					IFNULL(b.nilai,0) AS nilai
				FROM t_norma_waktu a
				LEFT OUTER JOIN(
					SELECT	a.*,
						COUNT(b.nospp) AS nilai
					FROM t_norma_waktu a
					LEFT OUTER JOIN(
						SELECT	a.nospp,
							DATEDIFF(a.max_id,a.tgspp) AS jmlhari
						FROM(
							SELECT	a.nospp,
								c.tgspp,
								MAX(a.created_at) AS max_id
							FROM d_spp_histori a
							LEFT OUTER JOIN t_alur_status b ON(a.id_alur=b.id_alur)
							LEFT OUTER JOIN ".$tabel." c ON(a.kdsatker=c.kdsatker AND a.thang=c.thang AND a.nospp=c.nospp)
							WHERE a.kdsatker='".session('kdsatker')."' AND a.thang='".session('tahun')."' AND b.is_spm='1'
							GROUP BY a.nospp,c.tgspp
						) a
					) b ON(b.jmlhari BETWEEN a.range1 AND a.range2)
					WHERE a.jenis='02'
				) b ON(a.id=b.id)
				WHERE a.jenis='02'	
			");
						
			if(count($rows)>0){
			
				foreach($rows as $row){
					$arr_jenis[] = $row->uraian;
					$arr_nilai[] = $row->nilai;
				}
				
				$grafik = "
					<script>
						Highcharts.chart('grafik5', {
							chart: {
								plotBackgroundColor: null,
								plotBorderWidth: null,
								plotShadow: false,
								backgroundColor:'rgba(255, 255, 255, 0.0)',
								type: 'column'
							},
							title: {
								text: 'Ketepatan (SPM)'
							},
							xAxis: {
								categories: ['".implode("','", $arr_jenis)."'],
								crosshair: true,
								labels: {
									style: {
										color: 'white'
									}
								}
							},
							yAxis: {
								labels: {
									format: '{value}',
									style: {
										color: 'white'
									}
								},
								title: {
									text: 'Jumlah',
									style: {
										color: 'white'
									}
								}
							},
							tooltip: {
								shared: true
							},
							legend: {
								enabled: false
							},
							series: [{
								name: 'Jumlah',
								data: [".implode(",", $arr_nilai)."]
							}]
						});
					</script>
				";
				
				return $grafik;
				
			}
			else{
				return 'Data tidak ditemukan!';
			}
			
		}
		catch(\Exception $e){
			return 'Koneksi terputus!';
		}
	}
	
	public function dataRealRKO()
	{
		$rows = DB::select("
			SELECT	ROUND(IFNULL(SUM(a.nilai)/1000000,0),1) AS JML_REAL_RKO
			FROM d_rko_pagu2 a
			LEFT OUTER JOIN d_rko b ON(a.id_rko=b.id)
			LEFT OUTER JOIN(
				SELECT	a.id_rko,
					b.nourut
				FROM(
					SELECT	id_rko,
						MAX(id) AS max_id
					FROM d_rko_status
					GROUP BY id_rko
				) a
				LEFT OUTER JOIN d_rko_status b ON(a.max_id=b.id)
			) c ON(a.id_rko=c.id_rko)
			WHERE b.kdsatker=? AND b.thang=? AND c.nourut>=4
		",[
			session('kdsatker'),
			session('tahun')
		]);
			
		return $rows[0]->JML_REAL_RKO;
		
	}
	
	public function dataRealTransaksi()
	{
		$rows = DB::select("
			SELECT	ROUND(IFNULL(SUM(a.nilai)/1000000,0),1) AS JML_REAL_RKO
			FROM d_rko_pagu2 a
			LEFT OUTER JOIN d_rko b ON(a.id_rko=b.id)
			LEFT OUTER JOIN(
				SELECT	a.id_rko,
					b.nourut
				FROM(
					SELECT	id_rko,
						MAX(id) AS max_id
					FROM d_rko_status
					GROUP BY id_rko
				) a
				LEFT OUTER JOIN d_rko_status b ON(a.max_id=b.id)
			) c ON(a.id_rko=c.id_rko)
			WHERE b.kdsatker=? AND b.thang=? AND c.nourut>=4
		",[
			session('kdsatker'),
			session('tahun')
		]);
			
		return $rows[0]->JML_REAL_RKO;
		
	}
	
	public function dataJmlRKO()
	{
		
		$rows = DB::select("
				SELECT 
				  IFNULL(COUNT(*), 0) JML_RKO 
				FROM
				  d_rko");
			
		return $rows[0]->JML_RKO;
		
	}
	
	public function dataJmlTransaksi()
	{
		
		$rows = DB::select("
				SELECT 
				  IFNULL(COUNT(*), 0) JML_TRAN 
				FROM
				  d_transaksi");
			
		return $rows[0]->JML_TRAN;
		
	}
	
	public function dataJmlEvaluasi()
	{
		
		$rows = DB::select("
				SELECT 
				  IFNULL(COUNT(*), 0) JML_EVAL 
				FROM
				  d_evaluasi");
			
		return $rows[0]->JML_EVAL;
		
	}
	
	public function capaian()
	{
		$rows = DB::select("
			SELECT	a.*,
					IF(a.persen_realisasi>=a.persen_target,
						'1',
						IF((a.persen_target-a.persen_realisasi)<=10,
							'2',
							'3'
						)
					) AS status_anggaran,
					IF(a.realisasi1>=a.target1,
						'1',
						IF((a.target1-a.realisasi1)<=10,
							'2',
							'3'
						)
					) AS status_kinerja
				FROM(
					SELECT	a.*,
						b.target,
						ROUND(b.target/a.paguakhir*100) AS persen_target,
						b.realisasi,
						ROUND(b.realisasi/a.paguakhir*100) AS persen_realisasi,
						b.target1,
						b.realisasi1
					FROM(
						SELECT	kode,
							uraian,
							paguakhir
						FROM d_pagu
						WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."' AND lvl='3'
					) a
					LEFT OUTER JOIN(
						SELECT	kode,
							target,
							realisasi,
							target1,
							realisasi1
						FROM d_capaian
						WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."'
					) b ON(a.kode=b.kode)
				) a
		");
		
		$data = '';
		foreach($rows as $row){
			$data .= '<tr>
						<td>'.$row->kode.'</td>
						<td>'.$row->uraian.'</td>
						<td style="text-align:right;">'.number_format($row->paguakhir).'</td>
						<td style="text-align:right;">'.number_format($row->target).'</td>
						<td style="text-align:right;">'.number_format($row->realisasi).'</td>
						<td style="text-align:center;"><div class="circleBase type'.number_format($row->status_anggaran).'"></div></td>
						<td style="text-align:right;">'.number_format($row->target1).'</td>
						<td style="text-align:right;">'.number_format($row->realisasi1).'</td>
						<td style="text-align:center;"><div class="circleBase type'.number_format($row->status_kinerja).'"></div></td>
					  </tr>';
		}
			
		return $data;	
	}

}