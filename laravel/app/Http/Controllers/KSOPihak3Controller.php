<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KSOPihak3Controller extends Controller {

	public function index(Request $request)
	{
		$arr_where = array();
		
		if(isset($_GET['id_kso'])){
			if($_GET['id_kso']!==''){
				$arr_where[] = " a.id_kso='".$_GET['id_kso']."' ";
			}
		}
		
		$where = "";
		if(session('kdlevel')!=='00'){
			$arr_where[] = " f.id_kso is not null ";
		}
		
		if(count($arr_where)>0){
			$where = "where ".implode(" and ", $arr_where);
		}
		
		$aColumns = array('id','nama_kso','nama_mk','nama_qs','nama_kon');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							b.nama as nama_kso,
							c.nama as nama_mk,
							d.nama as nama_qs,
							e.nama as nama_kon
					from d_kso_pihak3 a
					left outer join d_kso b on(a.id_kso=b.id)
					left outer join t_perusahaan c on(a.id_mk=c.id)
					left outer join t_perusahaan d on(a.id_qs=d.id)
					left outer join t_perusahaan e on(a.id_kon=e.id)
					left outer join(
						select distinct id_kso
						from d_kso_user
						where id_user=".session('id_user')."
					) f on(a.id_kso=f.id_kso)
					".$where."
					order by a.id desc";
		
		/*
		 * Paging
		 */ 
		$sLimit = " ";
		if((isset($_GET['start']))&&(isset($_GET['length']))){
			$iDisplayStart=$_GET['start']+1;
			$iDisplayLength=$_GET['length'];
			$sSearch=$_GET['search'];
			if ((isset($sSearch)) && (isset( $iDisplayStart )) &&  ($iDisplayLength != '-1' )) 
			{
				$iDisplayEnd=$iDisplayStart+$iDisplayLength-1;
				$sLimit = " WHERE NO BETWEEN '$iDisplayStart' AND '$iDisplayEnd'";
			}
		}
		 
		 
		/*
		 * Ordering
		 */
		$sOrder = " ";
		if((isset($_GET['order'][0]['column']))&&(isset($_GET['order'][0]['dir']))){
			$iSortCol_0=$_GET['order'][0]['column'];
			$iSortDir_0=$_GET['order'][0]['dir'];
			if ( isset($iSortCol_0  ) )
			{
				//modified ordering
				for($i=0;$i<count($aColumns);$i++){
					if($iSortCol_0==$i){
						if($iSortDir_0=='asc'){
							$sOrder = " ORDER BY ".$aColumns[$i-1]." ASC ";
						}
						else{
							$sOrder = " ORDER BY ".$aColumns[$i-1]." DESC ";
						}
					}
				}
			}
		}
		
		/*
		 * Filtering
		 */
		//modified filtering
		$sWhere="";
		if(isset($_GET['search']['value'])){
			$sSearch=$_GET['search']['value'];
			if((isset($sSearch))&&($sSearch!='')){
				$sWhere=" where lower(nama_kso) like lower('".$sSearch."%') or lower(nama_kso) like lower('%".$sSearch."%') or
								lower(nama_mk) like lower('".$sSearch."%') or lower(nama_mk) like lower('%".$sSearch."%') or
								lower(nama_qs) like lower('".$sSearch."%') or lower(nama_qs) like lower('%".$sSearch."%') or
								lower(nama_kon) like lower('".$sSearch."%') or lower(nama_kon) like lower('%".$sSearch."%')";
			}
		}

		/* Data set length after filtering */
		$iFilteredTotal = 0;
		$rows = DB::select("
			SELECT COUNT(*) as jumlah FROM (".$sTable.") a
		");
		$result = (array)$rows[0];
		if($result){
			$iFilteredTotal = $result['jumlah'];
		}
		
		/* Total data set length */
		$iTotal = 0;
		$rows = DB::select("
			SELECT COUNT(".$sIndexColumn.") as jumlah FROM (".$sTable.") a
		");
		$result = (array)$rows[0];
		if($result){
			$iTotal = $result['jumlah'];
		}
	   
		/*
		 * Format Output
		 */
		$output = array(
			"sEcho" => intval($request->input('sEcho')),
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
				$aksi='<center style="width:50px;">
							<div class="dropdown pull-right">
								<button type="button" class="btn btn-success btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                                <i class="material-icons">done</i>
	                                <span class="caret"></span>
	                            </button>
	                            <ul class="dropdown-menu">
	                                <li><a id="'.$row->id.'" href="javascript:void(0);" class="ubah">Ubah Data</a></li>
	                                <li><a id="'.$row->id.'" href="javascript:void(0);" class="hapus">Hapus Data</a></li>
	                            </ul>
	                        </div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->no,
				$row->nama_kso,
				$row->nama_mk,
				$row->nama_qs,
				$row->nama_kon,
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function pilih(Request $request, $id)
	{
		try{
			$rows = DB::select("
				select  a.id,
						a.id_kso,
						b.nama,
						b.nopks,
						a.id_mk,
						a.id_qs,
						a.id_kon
				from d_kso_pihak3 a
				left outer join d_kso b on(a.id_kso=b.id)
				where a.id=?
			",[
				$id
			]);
			
			if(count($rows)>0){
				
				return response()->json($rows[0]);
				
			}
			
		}
		catch(\Exception $e){
			return $e;
		}
	}
	
	public function simpan(Request $request)
	{
		try{
			if($request->input('inp-rekambaru')=='1'){
				
				$rows = DB::select("
					SELECT	count(*) AS jml
					from d_kso_pihak3
					where id_kso=?
				",[
					$request->input('id_kso')
				]);
				
				if($rows[0]->jml==0){
					
					$insert = DB::insert("
						INSERT INTO d_kso_pihak3(
							id_kso,id_mk,id_qs,id_kon,id_user,created_at,updated_at
						)
						VALUES (?,?,?,?,?,sysdate,sysdate)
					",[
						$request->input('id_kso'),
						$request->input('id_mk'),
						$request->input('id_qs'),
						$request->input('id_kon'),
						session('id_user')
					]);
					
					if($insert){
						return 'success';
					}
					else{
						return 'Data gagal disimpan!';
					}
					
				}
				else{
					return 'KSO ini sudah ada!';
				}
				
			}
			else{
				
				$update = DB::update("
					update d_kso_pihak3
					set id_mk=?,
						id_qs=?,
						id_kon=?,
						id_user=?,
						updated_at=sysdate
					where id=?
				",[
					$request->input('id_mk'),
					$request->input('id_qs'),
					$request->input('id_kon'),
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
			return $e;
			return 'Terdapat kesalahan lainnya, hubungi Administrator!';
		}		
	}
	
	public function hapus(Request $request)
	{
		try{
			$delete = DB::delete("
				delete from d_kso_pihak3
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