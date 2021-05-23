<?php

namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DashboardDepanController extends Controller {

	public function index()
	{
		try{
			$rows = DB::select("
				select	*
				from t_app_version
				where status='1'
			");
			
			return view('dashboard',
				array(
					'app_versi'=>$rows[0]->versi,
					'app_nama'=>$rows[0]->nama,
					'app_ket'=>$rows[0]->ket
				)
			);
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function spp_spm_sp2d()
	{
		try{
			$rows = DB::select("
				SELECT	a.status,
					a.nmstatus,
					a.warna,
					a.icon,
					IFNULL(b.jml,0) AS jml,
					round(IFNULL(b.nilai,0)/1000000,2) AS nilai
				FROM t_status_beranda a
				LEFT OUTER JOIN(
					SELECT	b.status_beranda,
						COUNT(*) AS jml,
						SUM(c.totnilmak) as nilai
					FROM d_spp a
					LEFT OUTER JOIN t_alur_status b ON(a.id_alur=b.id_alur AND a.status=b.status)
					LEFT OUTER JOIN d_data_induk c ON(a.kdsatker=c.kdsatker AND a.thang=c.thang AND a.nospp=c.nospp)
					WHERE a.kdsatker=? AND a.thang=?
					GROUP BY b.status_beranda
				) b ON(a.status=b.status_beranda)
				ORDER BY a.status ASC
			",[
				session('kdsatker'),
				session('tahun')
			]);
			
			$data['spp'] = $rows[0]->jml;
			$data['nilspp'] = $rows[0]->nilai.'jt';
			$data['spp1'] = $rows[1]->jml;
			$data['nilspp1'] = $rows[1]->nilai.'jt';
			$data['spm'] = $rows[2]->jml;
			$data['nilspm'] = $rows[2]->nilai.'jt';
			$data['sp2d'] = $rows[3]->jml;
			$data['nilsp2d'] = $rows[3]->nilai.'jt';
			
			return response()->json($data);
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function bulan()
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
					
					$grafik = "<script>	
								Highcharts.chart('realisasivs', {
									chart: {
										type: 'areaspline',
										backgroundColor:'rgba(255, 255, 255, 0.0)',
									},
									title: {
										text: 'Realisasi vs Rencana',
										align: 'left'
									},
									subtitle: {
										text: 'perbandingan realisasi dengan rencana anggaran',
										align: 'left'
									},
									xAxis: {
										categories: [
											'".implode("','", $arr_jenis)."'
										]
									},
									yAxis: {
										title: {
											text: ''
										}
									},
									tooltip: {
										shared: true,
										valueSuffix: ' jt'
									},
									credits: {
										enabled: false
									},
									plotOptions: {
										areaspline: {
											fillOpacity: 0.5,
											dataLabels: {
												enabled: true
											}
										}
									},
									series: [{
										name: 'Rencana',
										data: [".implode(",", $arr_rencana)."],
										color: {
											linearGradient: {
												x1: 0,
												x2: 0,
												y1: 0,
												y2: 1
											},
											stops: [
												[0, 'rgba(177, 13, 201, 0.8)'],
												[1, 'rgba(101, 8, 227, 0.2)']
											]
										},
									}, {
										name: 'Realisasi',
										data: [".implode(",", $arr_realisasi)."],
										color: {
											linearGradient: {
												x1: 0,
												x2: 0,
												y1: 0,
												y2: 1
											},
											stops: [
												[0, 'rgba(255, 215, 0, 0.8)'],
												[1, 'rgba(255, 215, 0, 0.2)']
											]
										},
									}],
									exporting: {
										enabled: false,
										buttons: {
											contextButton: {
												symbol: 'download',
												symbolFill: 'rgba(255, 255, 255, 0.0)',
												symbolStroke: '#b10dc9',
												theme: {
													fill: 'rgba(255, 255, 255, 0.0)',
													stroke: 'rgba(255, 255, 255, 0.0)',
												}
											}
										}
									},
									navigation: {
										menuItemStyle: {
											fontWeight: 'normal',
											background: 'none'
										},
										menuItemHoverStyle: {
											fontWeight: 'bold',
											background: '#ffd700',
											color: 'black'
										}
									}
								});
								</script>";
					
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
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function ppk()
	{
		try{
			$rows = DB::select("
				SELECT	kdppk,
						SUM(totnilmak) AS nilai
				FROM d_data_induk
				WHERE kdsatker=? AND thang=? AND kdppk IS NOT NULL AND kdppk<>''
				GROUP BY kdppk
			",[
				session('kdsatker'),
				session('tahun')
			]);
			
			foreach($rows as $row){
				$arr_data[] = "{
									name: '".$row->kdppk."',
									y: ".$row->nilai."
								}";
			}
			
			$grafik = "<script>	
						Highcharts.chart('realisasippk', {
							chart: {
								plotBackgroundColor: null,
								plotBorderWidth: null,
								plotShadow: false,
								backgroundColor:'rgba(255, 255, 255, 0.0)',
								type: 'pie'
							},
							title: {
								text: 'Realisasi',
								align: 'left'
							},
							subtitle: {
								text: 'per PPK',
								align: 'left'
							},
							tooltip: {
								pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
							},
							plotOptions: {
								pie: {
									allowPointSelect: true,
									cursor: 'pointer',
									dataLabels: {
										enabled: true,
										format: '<b>{point.name}</b>: {point.percentage:.1f} %',
										style: {
											color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
										}
									},
								}
							},
							/*plotOptions: {
								pie: {
									allowPointSelect: true,
									cursor: 'pointer',
									dataLabels: {
										enabled: true
									},
									showInLegend: true,
									colors: ['#f5b2ff', '#db44f1', '#b10dc9'],
									borderWidth: 0 // < set this option
								}
							},
							legend: {
								align: 'right',
								verticalAlign: 'top',
								layout: 'vertical',
								padding: 3,
								itemMarginTop: 3,
								itemMarginBottom: 3,
								x: 0,
								y: 100
							},*/
							series: [{
								name: 'Realisasi',
								colorByPoint: true,
								data: [".implode(",", $arr_data)."],
								innerSize: '50%',
								/*showInLegend:true,
								dataLabels: {
									enabled: false
								}*/
							}],
							exporting: {
								enabled: false,
								buttons: {
									contextButton: {
										symbol: 'download',
										symbolFill: 'rgba(255, 255, 255, 0.0)',
										symbolStroke: '#b10dc9',
										theme: {
											fill: 'rgba(255, 255, 255, 0.0)',
											stroke: 'rgba(255, 255, 255, 0.0)',
										}
									}
								}
							},
							navigation: {
								menuItemStyle: {
									fontWeight: 'normal',
									background: 'none'
								},
								menuItemHoverStyle: {
									fontWeight: 'bold',
									background: '#ffd700',
									color: 'black'
								}
							}
						});
						</script>";
			
			return $grafik;
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function jnsbelanja()
	{
		try{
			$rows = DB::select("
				SELECT	substr(kdakun,1,2) as jnsbelanja,
					SUM(nilmak) AS nilai
				FROM d_data_mak
				WHERE kdsatker=? AND thang=?
				GROUP BY substr(kdakun,1,2)
			",[
				session('kdsatker'),
				session('tahun')
			]);
			
			foreach($rows as $row){
				$arr_data[] = "{
									name: '".$row->jnsbelanja."',
									y: ".$row->nilai."
								}";
			}
			
			$grafik = "<script>	
						Highcharts.chart('realisasibelanja', {
							chart: {
								plotBackgroundColor: null,
								plotBorderWidth: null,
								plotShadow: false,
								backgroundColor:'rgba(255, 255, 255, 0.0)',
								type: 'pie'
							},
							title: {
								text: 'Realisasi',
								align: 'left'
							},
							subtitle: {
								text: 'per Jenis Belanja',
								align: 'left'
							},
							tooltip: {
								pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
							},
							plotOptions: {
								pie: {
									allowPointSelect: true,
									cursor: 'pointer',
									dataLabels: {
										distance: 5,
										enabled: true,
										format: '<b>{point.name}</b>: {point.percentage:.1f} %',
										style: {
											color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
										}
									},
								}
							},
							/*legend: {
								align: 'right',
								verticalAlign: 'top',
								layout: 'vertical',
								padding: 3,
								itemMarginTop: 3,
								itemMarginBottom: 3,
								x: 0,
								y: 100
							},*/
							series: [{
								name: 'Realisasi',
								colorByPoint: true,
								data: [".implode(",", $arr_data)."],
								innerSize: '50%',
								/*showInLegend:true,
								dataLabels: {
									enabled: false
								}*/
							}],
							exporting: {
								enabled: false,
								buttons: {
									contextButton: {
										symbol: 'download',
										symbolFill: 'rgba(255, 255, 255, 0.0)',
										symbolStroke: '#b10dc9',
										theme: {
											fill: 'rgba(255, 255, 255, 0.0)',
											stroke: 'rgba(255, 255, 255, 0.0)',
										}
									}
								}
							},
							navigation: {
								menuItemStyle: {
									fontWeight: 'normal',
									background: 'none'
								},
								menuItemHoverStyle: {
									fontWeight: 'bold',
									background: '#ffd700',
									color: 'black'
								}
							}
						});
						</script>";
			
			return $grafik;
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function ketepatan_spp()
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
				
				$grafik = "<script>	
								Highcharts.chart('ketepatanspp', {
									chart: {
										type: 'column',
										backgroundColor:'rgba(255, 255, 255, 0.0)'
									},
									title: {
										text: 'Ketepatan',
										align: 'left'
									},
									subtitle: {
										text: 'Pembuatan SPP',
										align: 'left'
									},
									yAxis: {
										min: 0,
										title: {
											text: 'SPP'
										}
									},
									xAxis: {
										categories: [
											'".implode("','", $arr_jenis)."'
										],
										crosshair: true,
										title: {
											text: 'Jumlah Hari'
										}
									},
									tooltip: {
										headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
										pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name}: </td>' +
											'<td style=\"padding:0\"><b>{point.y:.1f}</b></td></tr>',
										footerFormat: '</table>',
										shared: true,
										useHTML: true
									},
									plotOptions: {
										column: {
											pointPadding: 0.2,
											borderWidth: 0,
											dataLabels: {
												enabled: true
											},
										}
									},
									series: [{
										name: 'Jumlah',
										data: [".implode(",", $arr_nilai)."],
										color: {
											linearGradient: {
												x1: 0,
												x2: 0,
												y1: 0,
												y2: 1
											},
											stops: [
												[0, 'rgba(156, 39, 176, 0.58)'],
												[1, '#673ab7']
											]
										},
										showInLegend: false
									}],
									exporting: {
										enabled: false,
										buttons: {
											contextButton: {
												symbol: 'download',
												symbolFill: 'rgba(255, 255, 255, 0.0)',
												symbolStroke: '#b10dc9',
												theme: {
													fill: 'rgba(255, 255, 255, 0.0)',
													stroke: 'rgba(255, 255, 255, 0.0)',
												}
											}
										}
									},
									navigation: {
										menuItemStyle: {
											fontWeight: 'normal',
											background: 'none'
										},
										menuItemHoverStyle: {
											fontWeight: 'bold',
											background: '#ffd700',
											color: 'black'
										}
									}
								});
							</script>";
				
				return $grafik;
				
			}
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function ketepatan_spm()
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
				
				$grafik = "<script>	
								Highcharts.chart('ketepatanspm', {
									chart: {
										type: 'column',
										backgroundColor:'rgba(255, 255, 255, 0.0)'
									},
									title: {
										text: 'Ketepatan',
										align: 'left'
									},
									subtitle: {
										text: 'Pembuatan SPM',
										align: 'left'
									},
									yAxis: {
										min: 0,
										title: {
											text: 'SPM'
										}
									},
									xAxis: {
										categories: [
											'".implode("','", $arr_jenis)."'
										],
										crosshair: true,
										title: {
											text: 'Jumlah Hari'
										}
									},
									tooltip: {
										headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
										pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name}: </td>' +
											'<td style=\"padding:0\"><b>{point.y:.1f}</b></td></tr>',
										footerFormat: '</table>',
										shared: true,
										useHTML: true
									},
									plotOptions: {
										column: {
											pointPadding: 0.2,
											borderWidth: 0,
											dataLabels: {
												enabled: true
											},
										}
									},
									series: [{
										name: 'Jumlah',
										data: [".implode(",", $arr_nilai)."],
										color: {
											linearGradient: {
												x1: 0,
												x2: 0,
												y1: 0,
												y2: 1
											},
											stops: [
												[0, 'rgba(156, 39, 176, 0.58)'],
												[1, '#673ab7']
											]
										},
										showInLegend: false
									}],
									exporting: {
										enabled: false,
										buttons: {
											contextButton: {
												symbol: 'download',
												symbolFill: 'rgba(255, 255, 255, 0.0)',
												symbolStroke: '#b10dc9',
												theme: {
													fill: 'rgba(255, 255, 255, 0.0)',
													stroke: 'rgba(255, 255, 255, 0.0)',
												}
											}
										}
									},
									navigation: {
										menuItemStyle: {
											fontWeight: 'normal',
											background: 'none'
										},
										menuItemHoverStyle: {
											fontWeight: 'bold',
											background: '#ffd700',
											color: 'black'
										}
									}
								});
							</script>";
				
				return $grafik;
				
			}
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function index_ikpa()
	{
		try{
			$rows = DB::select("
				select	*
				from t_app_version
				where status='1'
			");
			
			return view('dashboard-ikpa',
				array(
					'app_versi'=>$rows[0]->versi,
					'app_nama'=>$rows[0]->nama,
					'app_ket'=>$rows[0]->ket
				)
			);
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function ikpa_uptup()
	{
		try{
			$rows = DB::select("
				SELECT	ROUND((ROUND(SUM(a.persen)/(COUNT(a.periode)*100)*100,2)+ROUND(SUM(b.persen)/(COUNT(b.periode)*100)*100,2))/2,2) AS nilai
				FROM(
					SELECT	a.*,
						IFNULL(b.totnilmak,0) AS nilai_up,
						ROUND(IFNULL(a.nilai/IFNULL(b.totnilmak,0),0)*100) AS persen
					FROM(
						SELECT	nosppu,
							DATE_FORMAT(tgspm,'%m') AS periode,
							COUNT(*) AS jml,
							SUM(totnilmak) AS nilai
						FROM d_data_induk
						WHERE kdsatker=? AND thang=? AND kdsifspm IN('3','5')
						GROUP BY nosppu,DATE_FORMAT(tgspm,'%m')
					) a
					LEFT OUTER JOIN d_data_induk b ON(a.nosppu=b.nospp AND b.kdsatker=? AND b.thang=?)
				) a,
				(
					SELECT	a.*,
						IFNULL(b.totnilmak,0) AS nilai_up,
						ROUND(IFNULL(a.nilai/IFNULL(b.totnilmak,0),0)*100) AS persen,
						IF((DATEDIFF(a.tgspm,b.tgspm)-30)>0,CONCAT('Terlambat ',DATEDIFF(a.tgspm,b.tgspm)-30,' hari'),'Tepat waktu') AS hari
					FROM(
						SELECT	nosppu,
							tgspm,
							DATE_FORMAT(tgspm,'%m') AS periode,
							COUNT(*) AS jml,
							SUM(totnilmak) AS nilai
						FROM d_data_induk
						WHERE kdsatker=? AND thang=? AND kdsifspm='6'
						GROUP BY nosppu,tgspm,DATE_FORMAT(tgspm,'%m')
					) a
					LEFT OUTER JOIN d_data_induk b ON(a.nosppu=b.nospp AND b.kdsatker=? AND b.thang=?)
				) b
			",[
				session('kdsatker'),
				session('tahun'),
				session('kdsatker'),
				session('tahun'),
				session('kdsatker'),
				session('tahun'),
				session('kdsatker'),
				session('tahun')
			]);
			
			return $rows[0]->nilai;
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function ikpa_lpj()
	{
		try{
			$rows = DB::select("
				SELECT	ROUND(SUM(IF(DATEDIFF(b.tgkirim,b.tglpj)<=10,1,IF(b.tgkirim IS NULL,1,0)))/12*100,2) AS nilai
				FROM t_periode a
				LEFT OUTER JOIN(
					SELECT	*
					FROM d_lpj_kirim
					WHERE kdsatker=? AND thang=?
				) b ON(a.periode=b.periode)
				ORDER BY a.periode ASC
			",[
				session('kdsatker'),
				session('tahun')
			]);
			
			return $rows[0]->nilai;
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function ikpa_tolak()
	{
		try{
			$rows = DB::select("
				SELECT	ROUND((a.jml-b.jml)/a.jml*100,2) AS nilai
				FROM(
					SELECT	COUNT(*) AS jml
					FROM d_spp
					WHERE kdsatker=? AND thang=?
				) a,
				(
					SELECT	COUNT(*) AS jml
					FROM(
						SELECT	DISTINCT kdsatker,thang,nospp
						FROM(
							SELECT	DISTINCT kdsatker,thang,nospp
							FROM d_spp
							WHERE kdsatker=? AND thang=? AND id_alur=1 AND STATUS=13
							UNION ALL
							SELECT	DISTINCT kdsatker,thang,nospp
							FROM d_spp_histori
							WHERE kdsatker=? AND thang=? AND id_alur=1 AND STATUS=13
						) a
					) a
				) b
			",[
				session('kdsatker'),
				session('tahun'),
				session('kdsatker'),
				session('tahun'),
				session('kdsatker'),
				session('tahun')
			]);
			
			return $rows[0]->nilai;
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function ikpa_capaian()
	{
		try{
			$rows = DB::select("
				SELECT	a.*,
					IFNULL(b.jml,0) AS jml
				FROM t_status_capaian a
				LEFT OUTER JOIN(
					SELECT a.nilai,
						COUNT(*) AS jml
					FROM(
						SELECT	a.periode,
							IF(b.rencana>a.realisasi,
								'Dibawah target',
								IF(IFNULL(b.rencana,0)=a.realisasi,
									'Sesuai target',
									IF(b.rencana<a.realisasi,
										'Melebihi Target',
										'Dibawah target'
									)
								)
							) AS nilai
						FROM(
							SELECT	DATE_FORMAT(a.tgspm,'%m') AS periode,
								SUM(a.totnilmak) AS realisasi
							FROM d_data_induk a
							WHERE a.kdsatker=? AND a.thang=? AND DATE_FORMAT(a.tgspm,'%m')<>'00'
							GROUP BY DATE_FORMAT(a.tgspm,'%m')
						) a
						LEFT OUTER JOIN(
							SELECT	periode,
								SUM(jml) AS rencana
							FROM d_hal3dipa_ppk
							WHERE kdsatker=? AND thang=?
						) b ON(a.periode=b.periode)
					) a
					GROUP BY a.nilai
				) b ON(a.status=b.nilai)
				ORDER BY a.id ASC
			",[
				session('kdsatker'),
				session('tahun'),
				session('kdsatker'),
				session('tahun'),
			]);
			
			foreach($rows as $row){
				$arr_data[] = "{
									name: '".$row->status."',
									y: ".$row->jml."
								}";
			}
			
			$grafik = "<script>	
						Highcharts.chart('capaian', {
							chart: {
								backgroundColor:'rgba(255, 255, 255, 0.0)',
								type: 'pie'
							},
							title: {
								text: 'Status Capaian Kinerja',
								align: 'left'
							},
							subtitle: {
								text: 'Realisasi terhadap target',
								align: 'left'
							},
							tooltip: {
								pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
							},
							plotOptions: {
								pie: {
									allowPointSelect: true,
									cursor: 'pointer',
									dataLabels: {
										distance: 0,
										enabled: true,
										format: '<b>{point.name}</b>: {point.percentage:.1f} %',
										style: {
											color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
										}
									},
								}
							},
							legend: {
								align: 'right',
								verticalAlign: 'top',
								layout: 'vertical',
								padding: 3,
								itemMarginTop: 3,
								itemMarginBottom: 3,
								x: 0,
								y: 100
							},
							series: [{
								name: 'Realisasi',
								colorByPoint: true,
								data: [".implode(",", $arr_data)."],
								innerSize: '50%',
								/*showInLegend:true,
								dataLabels: {
									enabled: false
								}*/
							}],
							exporting: {
								enabled: false,
								buttons: {
									contextButton: {
										symbol: 'download',
										symbolFill: 'rgba(255, 255, 255, 0.0)',
										symbolStroke: '#b10dc9',
										theme: {
											fill: 'rgba(255, 255, 255, 0.0)',
											stroke: 'rgba(255, 255, 255, 0.0)',
										}
									}
								}
							},
							navigation: {
								menuItemStyle: {
									fontWeight: 'normal',
									background: 'none'
								},
								menuItemHoverStyle: {
									fontWeight: 'bold',
									background: '#ffd700',
									color: 'black'
								}
							}
						});
						</script>";
			
			return $grafik;
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function ikpa_renkas()
	{
		try{
			$rows = DB::select("
				SELECT	a.*,
					IFNULL(b.jml,0) AS jml
				FROM t_status_renkas a
				LEFT OUTER JOIN(
					SELECT	a.nilai,
						COUNT(*) AS jml
					FROM(
						SELECT	IF(a.status2 BETWEEN -15 AND 15, 'Tepat',
								IF(a.status2>15, 'Dibawah', 'Diatas')
							) AS nilai
						FROM(
							SELECT	a.*,
								IF(a.jmlrenkas=0,'Dispensasi SPM','-') AS status1,
								IF(a.jmlrenkas=0,0,ROUND(a.selisih/a.jmlrenkas*100)) AS status2
							FROM(
								SELECT	a.*,
									IFNULL(b.jmlrenkas,0) AS jmlrenkas,
									IFNULL(b.jmlrenkas,0)-a.nilai AS selisih
								FROM(
									SELECT	a.tgsp2d,
										SUBSTR(b.kdakun,1,2) AS jnsbelanja,
										SUM(b.nilmak) AS nilai
									FROM d_data_induk a
									LEFT OUTER JOIN d_data_mak b ON(a.code_id=b.code_id)
									WHERE a.kdsatker=? AND a.thang=? AND SUBSTR(b.kdakun,1,1)='5' AND a.nosp2d IS NOT NULL AND a.nosp2d<>''
									GROUP BY a.tgsp2d,SUBSTR(b.kdakun,1,2)
								) a
								LEFT OUTER JOIN d_renkas b ON(a.tgsp2d=b.tgrenkas AND a.jnsbelanja=b.kdjenbel)
							) a
						) a
					) a
					GROUP BY a.nilai
				) b ON(a.status=b.nilai)
			",[
				session('kdsatker'),
				session('tahun')
			]);
			
			foreach($rows as $row){
				$arr_data[] = "{
									name: '".$row->status."',
									y: ".$row->jml."
								}";
			}
			
			$grafik = "<script>	
						Highcharts.chart('renkas', {
							chart: {
								plotBackgroundColor: null,
								plotBorderWidth: null,
								plotShadow: false,
								backgroundColor:'rgba(255, 255, 255, 0.0)',
								type: 'pie'
							},
							title: {
								text: 'Ketepatan Renkas',
								align: 'left'
							},
							subtitle: {
								text: 'Deviasi terhadap rencana ~15%',
								align: 'left'
							},
							tooltip: {
								pointFormat: '{series.name}: <b>{point.percentage:.1f}%</b>'
							},
							plotOptions: {
								pie: {
									allowPointSelect: true,
									cursor: 'pointer',
									dataLabels: {
										distance: 0,
										enabled: true,
										format: '<b>{point.name}</b>: {point.percentage:.1f} %',
										style: {
											color: (Highcharts.theme && Highcharts.theme.contrastTextColor) || 'black'
										}
									},
								}
							},
							legend: {
								align: 'right',
								verticalAlign: 'top',
								layout: 'vertical',
								padding: 3,
								itemMarginTop: 3,
								itemMarginBottom: 3,
								x: 0,
								y: 100
							},
							series: [{
								name: 'Realisasi',
								colorByPoint: true,
								data: [".implode(",", $arr_data)."],
								innerSize: '50%',
								/*showInLegend:true,
								dataLabels: {
									enabled: false
								}*/
							}],
							exporting: {
								enabled: false,
								buttons: {
									contextButton: {
										symbol: 'download',
										symbolFill: 'rgba(255, 255, 255, 0.0)',
										symbolStroke: '#b10dc9',
										theme: {
											fill: 'rgba(255, 255, 255, 0.0)',
											stroke: 'rgba(255, 255, 255, 0.0)',
										}
									}
								}
							},
							navigation: {
								menuItemStyle: {
									fontWeight: 'normal',
									background: 'none'
								},
								menuItemHoverStyle: {
									fontWeight: 'bold',
									background: '#ffd700',
									color: 'black'
								}
							}
						});
						</script>";
			
			return $grafik;
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function ikpa_indikator()
	{
		try{
			$rows = DB::select("
				SELECT	a.*
				FROM(
					SELECT	'UP/TUP' AS jenis,
						ROUND((ROUND(SUM(a.persen)/(COUNT(a.periode)*100)*100,2)+ROUND(SUM(b.persen)/(COUNT(b.periode)*100)*100,2))/2,2) AS nilai,
						ROUND(ROUND((ROUND(SUM(a.persen)/(COUNT(a.periode)*100)*100,2)+ROUND(SUM(b.persen)/(COUNT(b.periode)*100)*100,2))/2,2)*10/100,2) AS poin
					FROM(
						SELECT	a.*,
							IFNULL(b.totnilmak,0) AS nilai_up,
							ROUND(IFNULL(a.nilai/IFNULL(b.totnilmak,0),0)*100) AS persen
						FROM(
							SELECT	nosppu,
								DATE_FORMAT(tgspm,'%m') AS periode,
								COUNT(*) AS jml,
								SUM(totnilmak) AS nilai
							FROM d_data_induk
							WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."' AND kdsifspm IN('3','5')
							GROUP BY nosppu,DATE_FORMAT(tgspm,'%m')
						) a
						LEFT OUTER JOIN d_data_induk b ON(a.nosppu=b.nospp AND b.kdsatker='".session('kdsatker')."' AND b.thang='".session('tahun')."')
					) a,
					(
						SELECT	a.*,
							IFNULL(b.totnilmak,0) AS nilai_up,
							ROUND(IFNULL(a.nilai/IFNULL(b.totnilmak,0),0)*100) AS persen,
							IF((DATEDIFF(a.tgspm,b.tgspm)-30)>0,CONCAT('Terlambat ',DATEDIFF(a.tgspm,b.tgspm)-30,' hari'),'Tepat waktu') AS hari
						FROM(
							SELECT	nosppu,
								tgspm,
								DATE_FORMAT(tgspm,'%m') AS periode,
								COUNT(*) AS jml,
								SUM(totnilmak) AS nilai
							FROM d_data_induk
							WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."' AND kdsifspm='6'
							GROUP BY nosppu,tgspm,DATE_FORMAT(tgspm,'%m')
						) a
						LEFT OUTER JOIN d_data_induk b ON(a.nosppu=b.nospp AND b.kdsatker='".session('kdsatker')."' AND b.thang='".session('tahun')."')
					) b
				) a

				UNION ALL

				SELECT	a.*
				FROM(
					SELECT	'Rekon LPJ' AS jenis,
						ROUND(SUM(IF(DATEDIFF(b.tgkirim,b.tglpj)<=10,1,IF(b.tgkirim IS NULL,1,0)))/12*100,2) AS nilai,
						ROUND(ROUND(SUM(IF(DATEDIFF(b.tgkirim,b.tglpj)<=10,1,IF(b.tgkirim IS NULL,1,0)))/12*100,2)*5/100,2) AS poin
					FROM t_periode a
					LEFT OUTER JOIN(
						SELECT	*
						FROM d_lpj_kirim
						WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."'
					) b ON(a.periode=b.periode)
					ORDER BY a.periode ASC
				) a

				UNION ALL

				SELECT	a.*
				FROM(
					SELECT	'SPM Salah' AS jenis,
						ROUND((a.jml-b.jml)/a.jml*100,2) AS nilai,
						ROUND(ROUND((a.jml-b.jml)/a.jml*100,2)*5/100,2) AS poin
					FROM(
						SELECT	COUNT(*) AS jml
						FROM d_spp
						WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."'
					) a,
					(
						SELECT	COUNT(*) AS jml
						FROM(
							SELECT	DISTINCT kdsatker,thang,nospp
							FROM(
								SELECT	DISTINCT kdsatker,thang,nospp
								FROM d_spp
								WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."' AND id_alur=1 AND STATUS=13
								UNION ALL
								SELECT	DISTINCT kdsatker,thang,nospp
								FROM d_spp_histori
								WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."' AND id_alur=1 AND STATUS=13
							) a
						) a
					) b
				) a

				UNION ALL

				SELECT	a.*
				FROM(
					SELECT	'Kontrak' AS jenis,
						ROUND(SUM(IF(b.tgkirim <= IF(DAYNAME(a.tgkontrak)='Monday',DATE_SUB(a.tgkontrak, INTERVAL 5 DAY),DATE_SUB(a.tgkontrak, INTERVAL 7 DAY)),
							1,
							IF(b.tgkirim IS NULL,
								1,
								0
							)
						))/COUNT(*)*100,2) AS nilai,	
						ROUND(ROUND(SUM(IF(b.tgkirim <= IF(DAYNAME(a.tgkontrak)='Monday',DATE_SUB(a.tgkontrak, INTERVAL 5 DAY),DATE_SUB(a.tgkontrak, INTERVAL 7 DAY)),
							1,
							IF(b.tgkirim IS NULL,
								1,
								0
							)
						))/COUNT(*)*100,2)*10/100,2) AS poin
					FROM d_kontrak_header a
					LEFT OUTER JOIN d_kontrak_kirim b ON(a.kdsatker=b.kdsatker AND a.thang=b.thang AND a.nourut=b.nourut)
					WHERE a.kdsatker='".session('kdsatker')."' AND a.thang='".session('tahun')."'
				) a

				UNION ALL

				SELECT	'Retur' AS jenis,
					ROUND((b.jmlspm-a.jmlretur)/b.jmlspm*100,2) AS nilai,
					ROUND(ROUND((b.jmlspm-a.jmlretur)/b.jmlspm*100,2)*5/100,2) AS poin
				FROM(
					SELECT	SUM(jmlretur) AS jmlretur
					FROM d_lpj_kirim
					WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."'
				) a,
				(
					SELECT	COUNT(*) AS jmlspm
					FROM d_data_induk
					WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."'
				) b
					
				UNION ALL

				SELECT	'Hal III' AS jenis,
					ROUND(SUM(a.nilai)/COUNT(*),2) AS nilai,
					ROUND(ROUND(SUM(a.nilai)/COUNT(*),2)*5/100,2) AS poin
				FROM(
					SELECT	a.realisasi,
						b.rencana,
						IF(b.rencana>a.realisasi,
							ROUND(a.realisasi/b.rencana*100,2),
							IF(IFNULL(b.rencana,0)=a.realisasi,
								100,
								IF(b.rencana<a.realisasi,
									ROUND(IFNULL(b.rencana,0)/a.realisasi*100,2),
									0
								)
							)
						) AS nilai
					FROM(
						SELECT	DATE_FORMAT(a.tgspm,'%m') AS periode,
							SUM(a.totnilmak) AS realisasi
						FROM d_data_induk a
						WHERE a.kdsatker='".session('kdsatker')."' AND a.thang='".session('tahun')."' AND DATE_FORMAT(a.tgspm,'%m')<>'00'
						GROUP BY DATE_FORMAT(a.tgspm,'%m')
					) a
					LEFT OUTER JOIN(
						SELECT	periode,
							SUM(jml) AS rencana
						FROM d_hal3dipa_ppk
						WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."'
					) b ON(a.periode=b.periode)
				) a

				UNION ALL

				SELECT	'Revisi' AS jenis,
					100.00 AS nilai,
					5.00 AS poin
					
				UNION ALL

				SELECT	'Tagihan' AS jenis,
					ROUND(SUM(a.selisih)/COUNT(*)*100,2) AS nilai,
					ROUND(ROUND(SUM(a.selisih)/COUNT(*)*100,2)*20/100,2) AS poin
				FROM(
					SELECT	a.nospp,
						a.uraiben1,
						a.uraian1,
						a.tgbast,
						a.tginvoice,
						a.tgspp,
						a.tgspm,
						a.tgsp2d,
						IF(a.selisih1>10,
							0,
							IF(a.selisih2>5,
								0,
								IF(a.selisih3>2,
									0,
									1
								)
							)
						) AS selisih
					FROM(
						SELECT	a.nospp,
							b.uraiben1,
							b.uraian1,
							DATE_FORMAT(a.tgbast,'%d-%m-%Y') AS tgbast,
							DATE_FORMAT(a.tginvoice,'%d-%m-%Y') AS tginvoice,
							DATE_FORMAT(b.tgspp,'%d-%m-%Y') AS tgspp,
							DATE_FORMAT(b.tgspm,'%d-%m-%Y') AS tgspm,
							DATE_FORMAT(b.tgsp2d,'%d-%m-%Y') AS tgsp2d,
							IFNULL(DATEDIFF(b.tgspp, a.tgbast),0) AS selisih1,
							IFNULL(DATEDIFF(b.tgspm, b.tgspp),0) AS selisih2,
							IFNULL(DATEDIFF(b.tgsp2d, b.tgspm),0) AS selisih3
						FROM d_spp a
						LEFT OUTER JOIN d_data_induk b ON(a.kdsatker=b.kdsatker AND a.thang=b.thang AND a.nospp=b.nospp)
						WHERE a.kdsatker='".session('kdsatker')."' AND a.thang='".session('tahun')."'
					) a
				) a

				UNION ALL

				SELECT	'Renkas' AS jenis,
					IFNULL(ROUND(SUM(a.nilai)/COUNT(*)*100,2),0.00) AS nilai,
					ROUND(IFNULL(ROUND(SUM(a.nilai)/COUNT(*)*100,2),0.00)*5/100,2) AS poin
				FROM(
					SELECT	IF(a.status2 BETWEEN -15 AND 15, 1,0) AS nilai
					FROM(
						SELECT	a.*,
							IF(a.jmlrenkas=0,'Dispensasi SPM','-') AS status1,
							IF(a.jmlrenkas=0,0,ROUND(a.selisih/a.jmlrenkas*100)) AS status2
						FROM(
							SELECT	a.*,
								IFNULL(b.jmlrenkas,0) AS jmlrenkas,
								IFNULL(b.jmlrenkas,0)-a.nilai AS selisih
							FROM(
								SELECT	a.tgsp2d,
									SUBSTR(b.kdakun,1,2) AS jnsbelanja,
									SUM(b.nilmak) AS nilai
								FROM d_data_induk a
								LEFT OUTER JOIN d_data_mak b ON(a.code_id=b.code_id)
								WHERE a.kdsatker='".session('kdsatker')."' AND a.thang='".session('tahun')."' AND SUBSTR(b.kdakun,1,1)='5' AND a.nosp2d IS NOT NULL AND a.nosp2d<>''
								GROUP BY a.tgsp2d,SUBSTR(b.kdakun,1,2)
							) a
							LEFT OUTER JOIN d_renkas b ON(a.tgsp2d=b.tgrenkas AND a.jnsbelanja=b.kdjenbel)
						) a
					) a
				) a

				UNION ALL

				SELECT	'Realisasi' AS jenis,
					ROUND(IFNULL(b.realisasi,0)/IFNULL(a.paguakhir,0)*100,2) AS nilai,
					ROUND(ROUND(IFNULL(b.realisasi,0)/IFNULL(a.paguakhir,0)*100,2)*20/100,2) AS poin
				FROM(
					SELECT	SUM(paguakhir) AS paguakhir
					FROM d_pagu
					WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."' AND lvl='1'
				) a,
				(
					SELECT	SUM(totnilmak) AS realisasi
					FROM d_data_induk
					WHERE kdsatker='".session('kdsatker')."' AND thang='".session('tahun')."'
				) b

				UNION ALL

				SELECT	'Pagu Minus' AS jenis,
					ROUND(SUM(IF(a.realisasi>b.paguakhir,0,1))/COUNT(*)*100,2) AS nilai,
					ROUND(ROUND(SUM(IF(a.realisasi>b.paguakhir,0,1))/COUNT(*)*100,2)*5/100,2) AS poin
				FROM(
					SELECT	a.kdprogram,
						a.kdgiat,
						a.kdoutput,
						a.kdakun,
						SUM(a.nilmak) AS realisasi
					FROM d_data_mak a
					WHERE a.kdsatker='".session('kdsatker')."' AND a.thang='".session('tahun')."' AND SUBSTR(a.kdakun,1,1)='5'
					GROUP BY a.kdprogram,
						a.kdgiat,
						a.kdoutput,
						a.kdakun
				) a
				LEFT OUTER JOIN(
					SELECT	a.kdprogram,
						a.kdgiat,
						a.kdoutput,
						a.kdakun,
						SUM(a.paguakhir) AS paguakhir
					FROM d_pagu a
					WHERE a.kdsatker='".session('kdsatker')."' AND a.thang='".session('tahun')."' AND SUBSTR(a.kdakun,1,1)='5' AND a.lvl='7'
					GROUP BY a.kdprogram,
						a.kdgiat,
						a.kdoutput,
						a.kdakun
				) b ON(a.kdprogram=b.kdprogram AND a.kdgiat=b.kdgiat AND a.kdoutput=b.kdoutput AND a.kdakun=b.kdakun)

				UNION ALL

				SELECT	'Dispensasi' AS jenis,
					IFNULL(ROUND(SUM(IF(b.tgrenkas IS NULL,0,1))/COUNT(*)*100,2),0) AS nilai,
					IFNULL(ROUND(ROUND(SUM(IF(b.tgrenkas IS NULL,0,1))/COUNT(*)*100,2)*5/100,2),0) AS poin
				FROM(
					SELECT	DATE_FORMAT(a.tgsp2d,'%d-%m-%Y') AS tgsp2d,
						SUBSTR(b.kdakun,1,2) AS jnsbelanja
					FROM d_data_induk a
					LEFT OUTER JOIN d_data_mak b ON(a.code_id=b.code_id)
					WHERE a.kdsatker='".session('kdsatker')."' AND a.thang='".session('tahun')."' AND a.nosp2d IS NOT NULL AND a.nosp2d<>''
					GROUP BY a.tgsp2d,SUBSTR(b.kdakun,1,2)
				) a
				LEFT OUTER JOIN d_renkas b ON(a.tgsp2d=b.tgrenkas AND a.jnsbelanja=b.kdjenbel)
			");
			
			$ikpa1 = 0;
			foreach($rows as $row){
				$arr_jenis[] = "'".$row->jenis."'";
				$arr_nilai[] = $row->nilai;
				$arr_poin[] = $row->poin;
				$ikpa1 += $row->poin;
			}
			
			if($ikpa1>=80){
				$ikpa2 = 'Baik';
			}
			elseif($ikpa1>=60 && $ikpa1<80){
				$ikpa2 = 'Cukup';
			}
			else{
				$ikpa2 = 'Buruk';
			}
			
			$grafik = "<script>	
						Highcharts.chart('indikator', {
							chart: {
								zoomType: 'xy',
								backgroundColor:'rgba(255, 255, 255, 0.0)',
							},
							title: {
								text: 'Penilaian Kinerja Keuangan',
								align: 'left'
							},
							legend: {
								enabled: false
							},
							xAxis: {
								categories: [".implode(",", $arr_jenis)."],
								crosshair: true,
								enabled: true
							},
							yAxis: [{ // Primary yAxis
								title: {
									text: 'Nilai PKK'
								}
							}, { // Secondary yAxis
								title: {
									text: 'Poin PKK'
								},
								opposite: true
							}],
							tooltip: {
								shared: true,
							},
							credits: {
								enabled: false
							},
							plotOptions: {
								areaspline: {
									fillOpacity: 0.5,
									dataLabels: {
										enabled: true
									}
								}
							},
							series: [{
								name: 'Poin PKK',
								type: 'areaspline',
								yAxis: 1,
								data: [".implode(",", $arr_poin)."],
								color: {
									linearGradient: {
										x1: 0,
										x2: 0,
										y1: 0,
										y2: 1
									},
									stops: [
										[0, 'rgba(177, 13, 201, 0.8)'],
										[1, 'rgba(101, 8, 227, 0.2)']
									]
								},
							}, {
								name: 'Nilai PKK',
								type: 'areaspline',
								data: [".implode(",", $arr_nilai)."],
								color: {
									linearGradient: {
										x1: 0,
										x2: 0,
										y1: 0,
										y2: 1
									},
									stops: [
										[0, 'rgba(255, 215, 0, 0.8)'],
										[1, 'rgba(255, 215, 0, 0.2)']
									]
								},
							}],
							exporting: {
								enabled: false,
								buttons: {
									contextButton: {
										symbol: 'download',
										symbolFill: 'rgba(255, 255, 255, 0.0)',
										symbolStroke: '#b10dc9',
										theme: {
											fill: 'rgba(255, 255, 255, 0.0)',
											stroke: 'rgba(255, 255, 255, 0.0)',
										}
									}
								}
							},
							navigation: {
								menuItemStyle: {
									fontWeight: 'normal',
									background: 'none'
								},
								menuItemHoverStyle: {
									fontWeight: 'bold',
									background: '#ffd700',
									color: 'black'
								}
							}
						});
						</script>";
			
			$data['grafik'] = $grafik;
			$data['ikpa1'] = $ikpa1;
			$data['ikpa2'] = $ikpa2;
			
			return response()->json($data);
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function ikpa_kontrak()
	{
		try{
			$rows = DB::select("
				SELECT	a.*,
					IFNULL(b.jml,0) AS jml
				FROM t_status_ketepatan a
				LEFT OUTER JOIN(
					SELECT	a.nilai,
						COUNT(*) AS jml
					FROM(
						SELECT	a.nourut,
							IF(a.selisih<=5,'s/d 5',
								IF(a.selisih>5 AND a.selisih<=7,'s/d 7',
									IF(a.selisih>7 AND a.selisih<=10,'s/d 10',
										IF(a.selisih>7 AND a.selisih<=10,'> 10',
											'N/A'
										)
									)
								)
							) AS nilai
						FROM(
							SELECT	a.nourut,
								IFNULL(DATEDIFF(b.tgkirim,a.tgkontrak),'N/A') AS selisih
							FROM d_kontrak_header a
							LEFT OUTER JOIN d_kontrak_kirim b ON(a.kdsatker=b.kdsatker AND a.thang=b.thang AND a.nourut=b.nourut)
							WHERE a.kdsatker=? AND a.thang=?
						) a
					) a
					GROUP BY a.nilai
				) b ON(a.status=b.nilai)
				ORDER BY a.id ASC
			",[
				session('kdsatker'),
				session('tahun')
			]);
			
			foreach($rows as $row){
				$arr_kolom[] = "'".$row->status."'";
				$arr_nilai[] = $row->jml;
			}
			
			$grafik = "<script>	
						Highcharts.chart('kontrak', {
							chart: {
								type: 'column',
								backgroundColor:'rgba(255, 255, 255, 0.0)'
							},
							title: {
								text: 'Ketepatan',
								align: 'left'
							},
							subtitle: {
								text: 'Penyampaian data kontrak',
								align: 'left'
							},
							yAxis: {
								min: 0,
								title: {
									text: 'Kontrak'
								}
							},
							xAxis: {
								categories: [
									".implode(",", $arr_kolom)."
								],
								crosshair: true,
								title: {
									text: 'Jumlah Hari'
								}
							},
							tooltip: {
								headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
								pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name}: </td>' +
									'<td style=\"padding:0\"><b>{point.y:.1f}</b></td></tr>',
								footerFormat: '</table>',
								shared: true,
								useHTML: true
							},
							plotOptions: {
								column: {
									pointPadding: 0.2,
									borderWidth: 0,
									dataLabels: {
										enabled: true
									}
								}
							},
							series: [{
								name: 'Jumlah',
								data: [".implode(",", $arr_nilai)."],
								color: {
									linearGradient: {
										x1: 0,
										x2: 0,
										y1: 0,
										y2: 1
									},
									stops: [
										[0, 'rgba(156, 39, 176, 0.58)'],
										[1, '#673ab7']
									]
								},
								showInLegend: false
							}],
							exporting: {
								enabled: false,
								buttons: {
									contextButton: {
										symbol: 'download',
										symbolFill: 'rgba(255, 255, 255, 0.0)',
										symbolStroke: '#b10dc9',
										theme: {
											fill: 'rgba(255, 255, 255, 0.0)',
											stroke: 'rgba(255, 255, 255, 0.0)',
										}
									}
								}
							},
							navigation: {
								menuItemStyle: {
									fontWeight: 'normal',
									background: 'none'
								},
								menuItemHoverStyle: {
									fontWeight: 'bold',
									background: '#ffd700',
									color: 'black'
								}
							}
						});
						</script>";
			
			return $grafik;
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function ikpa_tagihan()
	{
		try{
			$rows = DB::select("
				SELECT	a.*,
					IFNULL(b.jml,0) AS jml
				FROM t_status_ketepatan1 a
				LEFT OUTER JOIN(
					SELECT	a.status,
						COUNT(*) AS jml
					FROM(
						SELECT	a.nospp,
							a.uraiben1,
							a.uraian1,
							a.tgbast,
							a.tginvoice,
							a.tgspp,
							a.tgspm,
							a.tgsp2d,
							IF(a.selisih1>10,
								'Tagihan Terlambat',
								IF(a.selisih2>5,
									'SPM Terlambat',
									IF(a.selisih3>2,
										'SP2D Terlambat',
										'Tepat Waktu'
									)
								)
							) AS STATUS
						FROM(
							SELECT	a.nospp,
								b.uraiben1,
								b.uraian1,
								DATE_FORMAT(a.tgbast,'%d-%m-%Y') AS tgbast,
								DATE_FORMAT(a.tginvoice,'%d-%m-%Y') AS tginvoice,
								DATE_FORMAT(b.tgspp,'%d-%m-%Y') AS tgspp,
								DATE_FORMAT(b.tgspm,'%d-%m-%Y') AS tgspm,
								DATE_FORMAT(b.tgsp2d,'%d-%m-%Y') AS tgsp2d,
								IFNULL(DATEDIFF(b.tgspp, a.tgbast),0) AS selisih1,
								IFNULL(DATEDIFF(b.tgspm, b.tgspp),0) AS selisih2,
								IFNULL(DATEDIFF(b.tgsp2d, b.tgspm),0) AS selisih3
							FROM d_spp a
							LEFT OUTER JOIN d_data_induk b ON(a.kdsatker=b.kdsatker AND a.thang=b.thang AND a.nospp=b.nospp)
							WHERE a.kdsatker=? AND a.thang=?
						) a
					) a
					GROUP BY a.status
				) b ON(a.status=b.status)
				ORDER BY a.id ASC
			",[
				session('kdsatker'),
				session('tahun')
			]);
			
			foreach($rows as $row){
				$arr_kolom[] = "'".$row->status."'";
				$arr_nilai[] = $row->jml;
			}
			
			$grafik = "<script>	
						Highcharts.chart('tagihan', {
							chart: {
								type: 'column',
								backgroundColor:'rgba(255, 255, 255, 0.0)'
							},
							title: {
								text: 'Ketepatan',
								align: 'left'
							},
							subtitle: {
								text: 'Penyelesaian tagihan',
								align: 'left'
							},
							yAxis: {
								min: 0,
								title: {
									text: null
								}
							},
							xAxis: {
								categories: [
									".implode(",", $arr_kolom)."
								],
								crosshair: true,
								title: {
									text: null
								}
							},
							tooltip: {
								headerFormat: '<span style=\"font-size:10px\">{point.key}</span><table>',
								pointFormat: '<tr><td style=\"color:{series.color};padding:0\">{series.name}: </td>' +
									'<td style=\"padding:0\"><b>{point.y:.1f}</b></td></tr>',
								footerFormat: '</table>',
								shared: true,
								useHTML: true
							},
							plotOptions: {
								column: {
									pointPadding: 0.2,
									borderWidth: 0,
									dataLabels: {
										enabled: true
									}
								}
							},
							series: [{
								name: 'Jumlah',
								data: [".implode(",", $arr_nilai)."],
								color: {
									linearGradient: {
										x1: 0,
										x2: 0,
										y1: 0,
										y2: 1
									},
									stops: [
										[0, 'rgba(156, 39, 176, 0.58)'],
										[1, '#673ab7']
									]
								},
								showInLegend: false
							}],
							exporting: {
								enabled: false,
								buttons: {
									contextButton: {
										symbol: 'download',
										symbolFill: 'rgba(255, 255, 255, 0.0)',
										symbolStroke: '#b10dc9',
										theme: {
											fill: 'rgba(255, 255, 255, 0.0)',
											stroke: 'rgba(255, 255, 255, 0.0)',
										}
									}
								}
							},
							navigation: {
								menuItemStyle: {
									fontWeight: 'normal',
									background: 'none'
								},
								menuItemHoverStyle: {
									fontWeight: 'bold',
									background: '#ffd700',
									color: 'black'
								}
							}
						});
						</script>";
			
			return $grafik;
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya!';
		}
	}
	
}