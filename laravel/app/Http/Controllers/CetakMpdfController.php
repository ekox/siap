<?php
namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use mPDF;

class CetakMpdfController extends Controller {

	protected $tahun,$kdsatker,$kdppk;

	public function __construct()
	{
		$this->tahun = session('tahun');
		$this->kdsatker = session('kdsatker');
		$this->nmsatker = session('nmsatker');
		$this->kdppk = session('kdppk');
	}

	protected function css_tabel()
	{
		$style = '<style>
					#tbl-header {
						border: 0px;
						border-collapse: collapse;
						font-size:90%;
						vertical-align: middle;
						width:100%;
					}
					#tbl-content, th {
						border: 0px solid black;
						border-collapse: collapse;
						font-size:90%;
						vertical-align: middle;
					}
					#tbl-content, td {
						border: 0px solid black;
						border-collapse: collapse;
						font-size:80%;
						vertical-align: top;
					}
				</style>';

		return $style;
	}
	
	public function routingSlipSPP($param)
	{
		$rows = DB::select("
			SELECT 	c.nmlevel,
					d.nama,
					a.updated_at as tanggal,
					ifnull(a.catatan,'-') as catatan,
					b.nmstatus,
					b.id_alur_status
			FROM d_spp a
			LEFT OUTER JOIN t_alur_status b ON(a.status=b.status)
			LEFT OUTER JOIN t_level c ON(b.kdlevel=c.kdlevel)
			LEFT OUTER JOIN t_user d ON(a.id_user=d.id)
			WHERE a.id_spp=?

			UNION ALL

			SELECT 	c.nmlevel,
					d.nama,
					a.created_at as tanggal,
					ifnull(a.catatan,'-') as catatan,
					b.nmstatus,
					b.id_alur_status
			FROM d_spp_histori a
			LEFT OUTER JOIN t_alur_status b ON(a.status=b.status)
			LEFT OUTER JOIN t_level c ON(b.kdlevel=c.kdlevel)
			LEFT OUTER JOIN t_user d ON(a.id_user=d.id)
			WHERE a.id_spp=?

			ORDER BY tanggal DESC,id_alur_status DESC
		", [
			$param,$param
		]);

		if(count($rows) > 0) {
			
			$row_header = DB::select("
				SELECT a.kdppk,b.nmppk,a.nospp,a.tgspp,a.created_at
				FROM d_spp a
				LEFT OUTER JOIN t_ppk b ON(a.kdppk=b.kdppk)
				WHERE a.id_spp=?
			",[
				$param
			]);

			$html_out = $this->css_tabel();

			$html_out .= '
				<p style="font-size:90%; font-weight:bold; text-align:center;">Routing Slip SPP</p>
				<br>
				<table id="tbl-header">
					<tbody>
						<tr>
							<td>Nama Satker</td>
							<td>:</td>
							<td>'.$this->nmsatker.'</td>
						</tr>
						<tr>
							<td>Kode PPK</td>
							<td>:</td>
							<td>'.$row_header[0]->kdppk.'</td>
						</tr>
						<tr>
							<td>Nama PPK</td>
							<td>:</td>
							<td>'.$row_header[0]->nmppk.'</td>
						</tr>
						<tr>
							<td>No SPP</td>
							<td>:</td>
							<td>'.$row_header[0]->nospp.'</td>
						</tr>
						<tr>
							<td>Tanggal SPP</td>
							<td>:</td>
							<td>'.$this->tanggal($row_header[0]->tgspp).'</td>
						</tr>
						<tr>
							<td>Tanggal Rekam</td>
							<td>:</td>
							<td>'.$this->tanggal($row_header[0]->created_at).'</td>
						</tr>
					</tbody>
				</table>
				<br>
			';
			
			$html_out .= '
				<table id="tbl-content" style="border:1px solid #000;border-collapse:collapse; width:100%">';
				
			/*$html_out .= '<table style="border:0px solid #000;border-collapse:collapse; width:100%; font-size:80%;">';*/
			$html_out .= '
					<thead>
						<tr>
							<th style="border:1px solid #000;border-collapse:collapse; text-align:center; padding:4px;">No</th>
							<th style="border:1px solid #000;border-collapse:collapse; text-align:center; padding:4px;">Level</th>
							<th style="border:1px solid #000;border-collapse:collapse; text-align:center; padding:4px;">Nama</th>
							<th style="border:1px solid #000;border-collapse:collapse; text-align:center; padding:4px;">Tanggal Proses</th>
							<th style="border:1px solid #000;border-collapse:collapse; text-align:center; padding:4px;">Catatan</th>
							<th style="border:1px solid #000;border-collapse:collapse; text-align:center; padding:4px;">Status</th>
						</tr>
					</thead>
			';
			
			$html_out .= '
					<tbody>';
			
			$no=1;
			for ($i=0; $i < count($rows); $i++) {
				$html_out .= '
						<tr>
							<td style="border:1px solid #000;border-collapse:collapse; text-align:right; padding:4px;">
								'.$no.'.
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px;">
								'.$rows[$i]->nmlevel.'
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px;">
								'.$rows[$i]->nama.'
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px;">
								'.date_format(date_create($rows[$i]->tanggal), "d-m-Y, H:i:s").'
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px;">
								'.$rows[$i]->catatan.'
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px;">
								'.$rows[$i]->nmstatus.'
							</td>
						</tr>
				';
				$no++;
			}
			
			$html_out .= '
					</tbody>
				</table>';
			
			//render PDF
			$mpdf = new mPDF("en", "A4", "15");
			$mpdf->AddPage('P');
			$mpdf->writeHTML($html_out);
			$mpdf->Output('Routing Slip SPP.pdf', 'I');
			exit; 
		}
		else {
			return "<script>
					alert('Routing Slip tidak dapat dicetak!');
					window.close();
				</script>";
		}
	}
	
	private function bulan($x)
	{
		if($x=='01')return 'Januari';
		elseif ($x=='02')return 'Februari';
		elseif ($x=='03')return 'Maret';
		elseif ($x=='04')return 'April';
		elseif ($x=='05')return 'Mei';
		elseif ($x=='06')return 'Juni';
		elseif ($x=='07')return 'Juli';
		elseif ($x=='08')return 'Agustus';
		elseif ($x=='09')return 'September';
		elseif ($x=='10')return 'Oktober';
		elseif ($x=='11')return 'November';
		elseif ($x=='12')return 'Desember';
		else return '';
	}
	
	private function tanggal($param){
		$x=substr($param,8,2).' '.$this->bulan(substr($param,5,2)).' '.substr($param,0,4);
		return $x;
	}

	public function pelaksanaanPerAkun($bulan, $dok)
	{
		if(session('kdlevel')=='02' || session('kdlevel')=='06'){
			
			$where_bulan="";
			$dok_header="";
			if($dok==1){ //SPP
				/*$where_bulan=" AND (
								(tgspp <> '' AND MONTH(tgspp)=".$bulan.") OR
								(tgspm <> '' AND MONTH(tgspm)=".$bulan.") OR
								(tgsp2d <> '' AND MONTH(tgsp2d)=".$bulan.")
							) ";*/
				$where_bulan=" AND (tgspp <> '' AND MONTH(tgspp)=".$bulan.") ";
				$dok_header="SPP";
			}
			elseif($dok==2){ //SPM
				/*$where_bulan=" AND (
								(tgspm <> '' AND MONTH(tgspm)=".$bulan.") OR
								(tgsp2d <> '' AND MONTH(tgsp2d)=".$bulan.")
							) ";*/
				$where_bulan=" AND (tgspm <> '' AND MONTH(tgspm)=".$bulan.") ";
				$dok_header="SPM";
			}
			elseif($dok==3){ //SP2D
				$where_bulan=" AND (tgsp2d <> '' AND MONTH(tgsp2d)=".$bulan.") ";
				$dok_header="SP2D";
			}

			$rows = DB::select("
				select 	concat(a.kdakun, ' ', a.uraian) AS akun_uraian,
						a.pagu,
						ifnull(b.sum_nilmak,0) AS realisasi_bulan,
						ifnull(c.sum_nilmak,0) AS realisasi_total,
						(a.pagu - ifnull(c.sum_nilmak,0)) AS sisa,
						round(ifnull(c.sum_nilmak,0)/a.pagu*100,2) AS persen_realisasi
				from(
					SELECT 	kdakun, uraian, SUM(paguakhir)-SUM(nilblokir)+SUM(kembel) AS pagu
					FROM d_pagu
					WHERE lvl='7' AND thang=".$this->tahun." AND kdsatker=".$this->kdsatker." AND kdppk=".$this->kdppk."
					GROUP BY kdakun,uraian
				) a
				left outer join(
					SELECT b.kdakun,sum(a.totnilmak) as sum_totnilmak,sum(b.nilmak) as sum_nilmak
					FROM d_data_induk a
					LEFT OUTER JOIN d_data_mak b ON(a.code_id=b.code_id AND a.nospp=b.nospp)
					WHERE a.thang=".$this->tahun." AND a.kdsatker=".$this->kdsatker." AND a.kdppk=".$this->kdppk."
					".$where_bulan."
					GROUP BY b.kdakun					
				) b on a.kdakun=b.kdakun
				left outer join(
					SELECT b.kdakun,sum(a.totnilmak) as sum_totnilmak,sum(b.nilmak) as sum_nilmak
					FROM d_data_induk a
					LEFT OUTER JOIN d_data_mak b ON(a.code_id=b.code_id AND a.nospp=b.nospp)
					WHERE a.thang=".$this->tahun." AND a.kdsatker=".$this->kdsatker." AND a.kdppk=".$this->kdppk."
					GROUP BY b.kdakun
				) c on a.kdakun=c.kdakun
			");

			$rows_jml = DB::select("
				select 	a.kdppk,
						a.jml_pagu,
						ifnull(b.sum_totnilmak,0) AS jml_realisasi_bulan,
						ifnull(c.sum_totnilmak,0) AS jml_realisasi_total,
						(a.jml_pagu - ifnull(c.sum_totnilmak,0)) AS jml_sisa,
						round(ifnull(c.sum_totnilmak,0)/a.jml_pagu*100,2) AS persen_realisasi
				from(
					SELECT kdppk, SUM(paguakhir)-SUM(nilblokir)+SUM(kembel) AS jml_pagu
					FROM d_pagu
					WHERE lvl='7' AND thang=".$this->tahun." AND kdsatker=".$this->kdsatker." AND kdppk=".$this->kdppk."
					GROUP BY kdppk
				) a
				LEFT OUTER JOIN(
					SELECT kdppk,SUM(totnilmak) AS sum_totnilmak
					FROM d_data_induk
					WHERE thang=".$this->tahun." AND kdsatker=".$this->kdsatker." AND kdppk=".$this->kdppk."
					".$where_bulan."
					GROUP BY kdppk
				) b ON a.kdppk=b.kdppk
				LEFT OUTER JOIN(
					SELECT kdppk,sum(totnilmak) AS sum_totnilmak
					FROM d_data_induk
					WHERE thang=".$this->tahun." AND kdsatker=".$this->kdsatker." AND kdppk=".$this->kdppk."
					GROUP BY kdppk
				) c ON a.kdppk=c.kdppk
			");

		/*if(count($rows) > 0) {*/
			
			/*$row_header = DB::select("
				SELECT a.kdppk,b.nmppk,a.nospp,a.tgspp,a.created_at
				FROM d_spp a
				LEFT OUTER JOIN t_ppk b ON(a.kdppk=b.kdppk)
				WHERE a.id_spp=?
			",[
				$param
			]);*/

			$html_out = $this->css_tabel();

			$html_out .= '
				<p style="font-size:90%; font-weight:bold; text-align:center;">
					LAPORAN PELAKSANAAN ANGGARAN BERDASARKAN PENERBITAN '.$dok_header.'<br>
					'.$this->nmsatker.'<br>
					PER TANGGAL '.date("d").' '.strtoupper($this->bulan(date("m"))).' '.date("Y").'
				</p>
				<br>
			';
			
			$html_out .= '
				<table id="tbl-content" style="border:1px solid #000;border-collapse:collapse; width:100%">';
				
			/*$html_out .= '<table style="border:0px solid #000;border-collapse:collapse; width:100%; font-size:80%;">';*/
			$html_out .= '
					<thead>
						<tr>
							<th style="border:1px solid #000;border-collapse:collapse; text-align:center; padding:4px;">URAIAN</th>
							<th style="border:1px solid #000;border-collapse:collapse; text-align:center; padding:4px;">PAGU</th>
							<th style="border:1px solid #000;border-collapse:collapse; text-align:center; padding:4px;">
								REALISASI BULAN<br>'.strtoupper($this->bulan($bulan)).'
							</th>
							<th style="border:1px solid #000;border-collapse:collapse; text-align:center; padding:4px;">
								REALISASI<br>TOTAL
							</th>
							<th style="border:1px solid #000;border-collapse:collapse; text-align:center; padding:4px;">SISA</th>
							<th style="border:1px solid #000;border-collapse:collapse; text-align:center; padding:4px;">
								REALISASI<br>(%)
							</th>
						</tr>
					</thead>
			';
			
			$html_out .= '
					<tbody>';
			
			for ($i=0; $i < count($rows); $i++) {
				$html_out .= '
						<tr>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px;">
								'.$rows[$i]->akun_uraian.'
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px; text-align:right;">
								'.number_format($rows[$i]->pagu,2).'
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px; text-align:right;">
								'.number_format($rows[$i]->realisasi_bulan,2).'
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px; text-align:right;">
								'.number_format($rows[$i]->realisasi_total,2).'
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px; text-align:right;">
								'.number_format($rows[$i]->sisa,2).'
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px; text-align:right;">
								'.$rows[$i]->persen_realisasi.'
							</td>
						</tr>
				';
			}
			
			$html_out .= '
						<tr>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px;">
								<strong>JUMLAH TOTAL LEMHANAS RI</strong>
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px; text-align:right;">
								<strong>'.number_format($rows_jml[0]->jml_pagu,2).'</strong>
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px; text-align:right;">
								<strong>'.number_format($rows_jml[0]->jml_realisasi_bulan,2).'</strong>
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px; text-align:right;">
								<strong>'.number_format($rows_jml[0]->jml_realisasi_total,2).'</strong>
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px; text-align:right;">
								<strong>'.number_format($rows_jml[0]->jml_sisa,2).'</strong>
							</td>
							<td style="border:1px solid #000;border-collapse:collapse; padding:4px; text-align:right;">
								<strong>'.$rows_jml[0]->persen_realisasi.'</strong>
							</td>
						</tr>
					</tbody>
				</table>';
			
			//render PDF
			$mpdf = new mPDF("en", "A4", "15");
			$mpdf->AddPage('L');
			$mpdf->writeHTML($html_out);
			$mpdf->Output('Laporan Pelaksanaan Anggaran.pdf', 'I');
			exit; 
		/*}
		else {
			return "<script>
					alert('Laporan tidak dapat dicetak!');
					window.close();
				</script>";
		}*/
		}
		else{
			return 'Anda tidak memiliki akses ini!';
		}
	}

}
