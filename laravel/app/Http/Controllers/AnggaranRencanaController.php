<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnggaranRencanaController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','nmproyek','kdakun','nmakun','t01','t02','t03','t04');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							b.nmproyek,
							a.kdakun,
							c.nmakun,
							a.nilai03 as t01,
							a.nilai06 as t02,
							a.nilai09 as t03,
							a.nilai12 as t04
					from d_rencana a
					left join t_proyek b on(a.id_proyek=b.id)
					left join t_akun c on(a.kdakun=c.kdakun)
					where a.thang='".session('tahun')."'
					order by a.id desc";
		
		/*
		 * Paging
		 */ 
		$sLimit = " ";
		if((isset($_GET['iDisplayStart']))&&(isset($_GET['iDisplayLength']))){
			$iDisplayStart=$_GET['iDisplayStart']+1;
			$iDisplayLength=$_GET['iDisplayLength'];
			$sSearch=$_GET['sSearch'];
			if (($sSearch=='') && (isset( $iDisplayStart )) &&  ($iDisplayLength != '-1' )) 
			{
				$iDisplayEnd=$iDisplayStart+$iDisplayLength-1;
				$sLimit = " WHERE NO BETWEEN '$iDisplayStart' AND '$iDisplayEnd'";
			}
		}
		
		/*
		 * Ordering
		 */
		$sOrder = " ";
		if((isset($_GET['iSortCol_0']))&&(isset($_GET['sSortDir_0']))){
			$iSortCol_0=$_GET['iSortCol_0'];
			$iSortDir_0=$_GET['sSortDir_0'];
			if ( isset($iSortCol_0  ) )
			{		
				//modified ordering
				for($i=0;$i<count($aColumns);$i++){
					if($iSortCol_0==$i){
						if($iSortDir_0=='asc'){
							$sOrder = " ORDER BY ".$aColumns[$i]." DESC ";
						}
						else{
							$sOrder = " ORDER BY ".$aColumns[$i]." ASC ";
						}
					}
				}
			}
		}
		
		//modified filtering
		$sWhere="";
		if(isset($_GET['sSearch'])){
			$sSearch=$_GET['sSearch'];
			if((isset($sSearch))&&($sSearch!='')){
				$sWhere=" where lower(nmproyek) like lower('".$sSearch."%') or lower(nmproyek) like lower('%".$sSearch."%') or
								lower(kdakun) like lower('".$sSearch."%') or lower(kdakun) like lower('%".$sSearch."%') or
								lower(nmakun) like lower('".$sSearch."%') or lower(nmakun) like lower('%".$sSearch."%') ";
			}
		}
		
		/* Data set length after filtering */
		$iFilteredTotal = 0;
		$rows = DB::select("
			SELECT COUNT(*) as JUMLAH FROM (".$sTable.") qry
		");
		$result = (array)$rows[0];
		if($result){
			$iFilteredTotal = $result['jumlah'];
		}
		
		/* Total data set length */
		$iTotal = 0;
		$rows = DB::select("
			SELECT COUNT(".$sIndexColumn.") as JUMLAH FROM (".$sTable.") qry
		");
		$result = (array)$rows[0];
		if($result){
			$iTotal = $result['jumlah'];
		}

		/*
		 * Format Output
		 */
		$sEcho="";
		if(isset($_GET['sEcho'])){
			$sEcho=$_GET['sEcho'];
		}
		$output = array(
			"sEcho" => intval($sEcho),
			"iTotalRecords" => $iTotal,
			"iTotalDisplayRecords" => $iFilteredTotal,
			"aaData" => array()
		);
		
		$str=str_replace(" , ", " ", implode(", ", $aColumns));
		
		$sQuery = "SELECT * FROM ( SELECT ROWNUM AS NO,".$str." FROM ( SELECT * FROM (".$sTable.") ".$sOrder.") ".$sWhere." ) a ".$sLimit." ";
		
		$rows = DB::select($sQuery);
		
		foreach( $rows as $row )
		{
			$aksi='';
			if(session('kdlevel')=='00'){
				$aksi='<center>
							<button type="button" class="btn btn-raised btn-sm btn-icon btn-success dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
							<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
								<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
								<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
							</div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->no,
				$row->nmproyek,
				$row->kdakun,
				$row->nmakun,
				'<div style="text-align:right;">'.number_format($row->t01).'</div>',
				'<div style="text-align:right;">'.number_format($row->t02).'</div>',
				'<div style="text-align:right;">'.number_format($row->t03).'</div>',
				'<div style="text-align:right;">'.number_format($row->t04).'</div>',
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function pilih(Request $request, $id)
	{
		try{
			$rows = DB::select("
				select  a.*,
						b.id_penerima
				from d_rencana a
				left join t_proyek b on(a.id_proyek=b.id)
				where a.id=?
			",[
				$id
			]);
			
			if(count($rows)>0){
				return response()->json($rows[0]);
			}
			
		}
		catch(\Exception $e){
			return 'Kesalahan lainnya!';
		}
	}
	
	public function simpan(Request $request)
	{
		try{
			if($request->input('inp-rekambaru')=='1'){
				
				$arr_proyek = explode('-', $request->input('id_proyek'));
				$id_proyek = $arr_proyek[0];
				
				$rows = DB::select("
					SELECT	count(*) AS jml
					from d_rencana
					where thang=? and id_proyek=? and kdakun=?
				",[
					session('tahun'),
					$id_proyek,
					$request->input('kdakun')
				]);
				
				if($rows[0]->jml==0){
					
					$insert = DB::table('d_rencana')->insert([
						'thang' => session('tahun'),
						'id_proyek' => $id_proyek,
						'kdakun' => $request->input('kdakun'),
						'nilai03' => str_replace(",", "", $request->input('nilai03')),
						'nilai06' => str_replace(",", "", $request->input('nilai06')),
						'nilai09' => str_replace(",", "", $request->input('nilai09')),
						'nilai12' => str_replace(",", "", $request->input('nilai12')),
						'id_user' => session('id_user')
					]);
					
					if($insert){
						return 'success';
					}
					else{
						return 'Data gagal disimpan!';
					}
					
				}
				else{
					return 'Duplikasi data!';
				}
				
			}
			else{
				
				$update = DB::update("
					update d_rencana
					set nilai03=?,
						nilai06=?,
						nilai09=?,
						nilai12=?,
						id_user=?,
						updated_at=sysdate
					where id=?
				",[
					str_replace(",", "", $request->input('nilai03')),
					str_replace(",", "", $request->input('nilai06')),
					str_replace(",", "", $request->input('nilai09')),
					str_replace(",", "", $request->input('nilai12')),
					session('id_user'),
					$request->input('inp-id')
				]);
				
				if($update){
					return 'success';
				}
				else{
					return 'Data gagal diubah!';
				}
				
			}			
		}
		catch(\Exception $e){
			return $e->getMessage();
		}		
	}
	
	public function hapus(Request $request)
	{
		try{
			$delete = DB::delete("
				delete from d_rencana
				where id=?
			",[
				$request->input('id')
			]);
			
			if($delete==true) {
				return 'success';
			}
			else {
				return 'Proses hapus gagal. Hubungi Administrator.';
			}
			
		}
		catch(\Exception $e){
			return 'Terdapat kesalahan lainnya, hubungi Administrator!';
		}		
	}
	
}