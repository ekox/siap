<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Bukubesar;
use clsTinyButStrong;
use DB;
use Mpdf\Mpdf;

class BukuBesarController extends Controller
{
    /**
	 * description 
	 */
	public function excel(Request $request)
	{		
		if(isset($_GET['kdakun'])){
			if(count($_GET['kdakun'])==1){
				
				$tahun = session('tahun');
				$kdakun = $_GET['kdakun'][0];
				
				$arr_where = array();
				
				$tgawal = '';
				$tgakhir = '';
				$where1 = " and to_char(a.tgsawal,'yyyy-mm-dd')='".$tahun."-01-01' ";
				$where2 = "  ";
				if(isset($_GET['tgawal']) && isset($_GET['tgakhir'])){
					if($_GET['tgawal']!=='' && $_GET['tgakhir']!==''){
						
						$arr_where[] = " b.tgdok between to_date('".$_GET['tgawal']." 00:00:01','yyyy-mm-dd hh24:mi:ss') and to_date('".$_GET['tgakhir']." 23:59:59','yyyy-mm-dd hh24:mi:ss') ";
						
						$where1 = " and a.tgsawal < to_date('".$_GET['tgawal']." 00:00:01','yyyy-mm-dd hh24:mi:ss') ";
						
						$where2 = " 
							union all
						
							select  a.kdakun,
									a.kddk,
									a.nilai
							from d_trans_akun a
							left join d_trans b on(a.id_trans=b.id)
							where b.thang='".$tahun."' and a.KDAKUN='".$kdakun."' and b.tgdok < to_date('".$_GET['tgawal']." 00:00:01','yyyy-mm-dd hh24:mi:ss')
						";
						
						$tgawal = $_GET['tgawal'];
						$tgakhir = $_GET['tgakhir'];
						
					}
				}
				
				$and = "";
				if(count($arr_where)>0){
					$and = " and ".implode(" and ", $arr_where);
				}
				
				$sawal_debet = 0;
				$sawal_kredit = 0;
				$sawal_saldo = 0;
				
				//cari saldo awal
				$rows = DB::select("
					select  SUM(DECODE(a.kddk, 'D', a.nilai, 0)) AS debet,
							SUM(DECODE(a.kddk, 'K', a.nilai, 0)) AS kredit,
							SUM(decode(a.kddk,'D',a.nilai,'K',-a.nilai)) as saldo
					from(
						select  a.kdakun,
								a.kddk,
								a.nilai
						from d_sawal a
						where a.thang='".$tahun."' and a.KDAKUN='".$kdakun."' ".$where1."
						
						".$where2."
						
					) a
				");
				
				if(count($rows)>0){
					
					$sawal_debet = $rows[0]->debet;
					$sawal_kredit = $rows[0]->kredit;
					$sawal_saldo = $rows[0]->saldo;
					
				}
				
				$rows = DB::select("
					SELECT a.kdakun,
						 d.nmakun,
						 to_char(last_day(b.tgdok),'dd')||' '||e.nmbulan as bulan,
						 TO_CHAR (b.tgdok, 'dd-mm-yyyy') AS tanggal,
						 b.nodok||' | '||c.nmunit AS no_voucher,
						 substr(b.kdunit,1,4) as kd_pc,
						 f.nama||' | '||b.uraian AS remark,
						 a.kddk,
						 a.nilai
					FROM d_trans_akun a
					 LEFT JOIN d_trans b
						ON (a.id_trans = b.id)
					 LEFT JOIN t_unit c
						ON (SUBSTR (b.kdunit, 1, 4) = c.kdunit)
					 LEFT JOIN t_akun d
						ON (a.kdakun = d.kdakun)
					 LEFT JOIN t_penerima f
						ON (b.id_penerima=f.id)
					 LEFT JOIN t_bulan e on(to_char(b.tgdok,'mm')=e.bulan)
				   WHERE  b.thang = '".$tahun."' and a.kdakun='".$kdakun."' ".$and."
					ORDER BY b.tgdok,a.id
				");

				if(count($rows) > 0) {		
				
					$tot_debet = $sawal_debet;
					$tot_kredit = $sawal_kredit;
					$saldo = $sawal_saldo;
					foreach($rows as $row) {
						
						$debet = 0;
						$kredit = 0;
						
						if($row->kddk=='D'){
							$debet = $row->nilai;
							$saldo += $row->nilai;
						}
						else{
							$kredit = $row->nilai;
							$saldo -= $row->nilai;
						}
						
						$val = (object) array(
							'tahun' => $tahun,
							'kdakun' => $row->kdakun,
							'nmakun' => $row->nmakun,
							'tanggal' => $row->tanggal,
							'no_voucher' => $row->no_voucher,
							'kd_pc' => $row->kd_pc,
							'remark' => $row->remark,
							'debet' => number_format($debet,2),
							'kredit' => number_format($kredit,2),
							'saldo' => number_format($saldo,2),
						);

						$values[] = $val;

						$tot_debet += $debet;
						$tot_kredit += $kredit;
						
					}

					$param[] = array(
						'tahun' => $tahun,
						'kdakun' => $kdakun,
						'nmakun' => $rows[0]->nmakun,
						'tgawal'=> $tgawal,
						'tgakhir'=> $tgakhir,
						'debet' => number_format($tot_debet,2),
						'kredit' => number_format($tot_kredit,2),
						'saldo' => number_format($saldo,2),
						'sawal_debet' => number_format($sawal_debet,2),
						'sawal_kredit' => number_format($sawal_kredit,2),
						'sawal_saldo' => number_format($sawal_saldo,2)
					);

					$TBS = new clsTinyButStrong();
					$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);	
					
					//load template in folder /doc
					$TBS->LoadTemplate('tbs_template/'.'template_buku_besar.xlsx');
					
					$TBS->Plugin(OPENTBS_SELECT_SHEET,'Sheet1');
					$TBS->MergeBlock('p', $param);
					$TBS->MergeBlock('v', $values);
					
					//download file
					header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
					$TBS->Show(OPENTBS_DOWNLOAD,'Buku_besar_'.$kdakun.'.xlsx');
					
				}
				else {
					return 'Data tidak ditemukan!';
				}
				
			}
			else{
				return 'Cetakan excel hanya dapat digunakan untuk 1 jenis akun!';
			}
			
		}
		else{
			return 'Cetakan excel wajib memilih kode akun!';
		}
		
	}
	
	public function pdf(Request $request)
	{	
		$where = "";
		if(isset($_GET['kdakun'])){
			if(count($_GET['kdakun'])>0){
				$where = " and kdakun in('".implode("','", $_GET['kdakun'])."') ";
			}
		}
		
		$rows = DB::select("
			select	kdakun,
					nmakun
			from t_akun
			where lvl=6 ".$where."
			order by kdakun
		");
		
		$tahun = session('tahun');
		
		$periode = '';
		if(isset($_GET['tgawal']) && isset($_GET['tgakhir'])){
			if($_GET['tgawal']!=='' && $_GET['tgakhir']!==''){
				$periode = '<tr>
								<th colspan="3">Periode '.$_GET['tgawal'].' s.d. '.$_GET['tgakhir'].'</th>
							</tr>';
			}
		}
		
		$data = '<style>
					table { border-collapse: collapse; font-size:10px; border: 1px solid #000;}
					/*td {
					  border-right: solid 1px #000; 
					  border-left: solid 1px #000;
					}*/
				 </style>
				 <table width="100%" border="0" cellspacing="0" cellpadding="10" style="border: 0px solid #000;font-size:10px;">
					<thead>
						<tr>
							<th colspan="3">PERUMDA PEMBANGUNAN SARANA JAYA</th>
						</tr>
						<tr>
							<th colspan="3">Laporan Buku Besar TA. '.$tahun.'</th>
						</tr>
						'.$periode.'
					</thead>
				</table>
				<br>';
		
		foreach($rows as $row){
			
			$kdakun = $row->kdakun;
			$nmakun = $row->nmakun;
			
			$arr_where = array();
		
			$tgawal = '';
			$tgakhir = '';
			$where1 = " and to_char(a.tgsawal,'yyyy-mm-dd')='".$tahun."-01-01' ";
			$where2 = "  ";
			if(isset($_GET['tgawal']) && isset($_GET['tgakhir'])){
				if($_GET['tgawal']!=='' && $_GET['tgakhir']!==''){
					
					$arr_where[] = " b.tgdok between to_date('".$_GET['tgawal']." 00:00:01','yyyy-mm-dd hh24:mi:ss') and to_date('".$_GET['tgakhir']." 23:59:59','yyyy-mm-dd hh24:mi:ss') ";
					
					$where1 = " and a.tgsawal < to_date('".$_GET['tgawal']." 00:00:01','yyyy-mm-dd hh24:mi:ss') ";
					
					$where2 = " 
						union all
					
						select  a.kdakun,
								a.kddk,
								a.nilai
						from d_trans_akun a
						left join d_trans b on(a.id_trans=b.id)
						where b.thang='".$tahun."' and a.KDAKUN='".$kdakun."' and b.tgdok < to_date('".$_GET['tgawal']." 00:00:01','yyyy-mm-dd hh24:mi:ss')
					";
					
					$tgawal = $_GET['tgawal'];
					$tgakhir = $_GET['tgakhir'];
					
				}
			}
			
			$and = "";
			if(count($arr_where)>0){
				$and = " and ".implode(" and ", $arr_where);
			}
			
			$sawal_debet = 0;
			$sawal_kredit = 0;
			$sawal_saldo = 0;
			
			//cari saldo awal
			$rows = DB::select("
				select  SUM(DECODE(a.kddk, 'D', a.nilai, 0)) AS debet,
						SUM(DECODE(a.kddk, 'K', a.nilai, 0)) AS kredit,
						SUM(decode(a.kddk,'D',a.nilai,'K',-a.nilai)) as saldo
				from(
					select  a.kdakun,
							a.kddk,
							a.nilai
					from d_sawal a
					where a.thang='".$tahun."' and a.KDAKUN='".$kdakun."' ".$where1."
					
					".$where2."
					
				) a
			");
			
			if(count($rows)>0){
				
				$sawal_debet = $rows[0]->debet;
				$sawal_kredit = $rows[0]->kredit;
				$sawal_saldo = $rows[0]->saldo;
				
			}
			
			$data .= '<table width="100%" cellspacing="0" cellpadding="3">
						<thead>
							<tr>
								<th style="border: 1px solid #000;">Tanggal</th>
								<th style="border: 1px solid #000;">No Voucher</th>
								<th style="border: 1px solid #000;">Kd P/C</th>
								<th style="border: 1px solid #000;">Remark</th>
								<th style="border: 1px solid #000;">Debet</th>
								<th style="border: 1px solid #000;">Kredit</th>
								<th style="border: 1px solid #000;">Saldo</th>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td colspan="2" style="text-align:left;"><b>Akun : '.$kdakun.' - '.$nmakun.'</b></td>
								<td></td>
								<td style="text-align:right"><b><i>Saldo Awal : </i></b></td>
								<td style="text-align:right">'.number_format($sawal_debet,2).'</td>
								<td style="text-align:right">'.number_format($sawal_kredit,2).'</td>
								<td style="text-align:right">'.number_format($sawal_saldo,2).'</td>
							</tr>';
			
			$rows = DB::select("
				SELECT a.kdakun,
					 d.nmakun,
					 to_char(last_day(b.tgdok),'dd')||' '||e.nmbulan as bulan,
					 TO_CHAR (b.tgdok, 'dd-mm-yyyy') AS tanggal,
					 b.nodok||' | '||c.nmunit AS no_voucher,
					 substr(b.kdunit,1,4) as kd_pc,
					 f.nama||' | '||b.uraian AS remark,
					 a.kddk,
					 a.nilai
				FROM d_trans_akun a
				 LEFT JOIN d_trans b
					ON (a.id_trans = b.id)
				 LEFT JOIN t_unit c
					ON (SUBSTR (b.kdunit, 1, 4) = c.kdunit)
				 LEFT JOIN t_akun d
					ON (a.kdakun = d.kdakun)
				 LEFT JOIN t_penerima f
					ON (b.id_penerima=f.id)
				 LEFT JOIN t_bulan e on(to_char(b.tgdok,'mm')=e.bulan)
			   WHERE  b.thang = '".$tahun."' and a.kdakun='".$kdakun."' ".$and."
				ORDER BY b.tgdok,a.id
			");
			
			$tot_debet = $sawal_debet;
			$tot_kredit = $sawal_kredit;
			$saldo = $sawal_saldo;
			
			foreach($rows as $row) {
				
				$debet = 0;
				$kredit = 0;
				
				if($row->kddk=='D'){
					$debet = $row->nilai;
					$saldo += $row->nilai;
				}
				else{
					$kredit = $row->nilai;
					$saldo -= $row->nilai;
				}
				
				$data .= '<tr>
							<td>'.$row->tanggal.'</td>
							<td>'.$row->no_voucher.'</td>
							<td>'.$row->kd_pc.'</td>
							<td>'.$row->remark.'</td>
							<td style="text-align:right">'.number_format($debet,2).'</td>
							<td style="text-align:right">'.number_format($kredit,2).'</td>
							<td style="text-align:right">'.number_format($saldo,2).'</td>
						</tr>';
								
			}
			
			$data .= '</tbody></table><br><br>';
			
		}
		
		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4-L',
			'margin_left' => 8,
			'margin_right' => 8,
			'margin_top' => 8,
			'margin_bottom' => 8,
		]);

		//mode portrait or landscape
		$mpdf->AddPage('L');

		//write content to PDF
		$mpdf->writeHTML($data);
		$mpdf->Output('Laporan_Buku_Besar.pdf', 'I');
		exit;
		
	}
	
}
