<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AnggaranPaguUnitController extends Controller {

	public function index(Request $request)
	{
		$where = "";
		if(session('kdlevel')=='04'||session('kdlevel')=='05'){
			$where = " and a.kdunit='".session('kdunit')."' ";
		}
		elseif(session('kdlevel')=='06'||session('kdlevel')=='07'||session('kdlevel')=='08'||session('kdlevel')=='11'){
			$where = " and a.kdunit='".substr(session('kdunit'),0,4)."' ";
		}
		
		$aColumns = array('id','thang','nmunit','uraian','kdakun','nilai');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							a.thang,
							c.nmunit,
							b.uraian,
							a.kdakun,
							a.nilai
					from d_pagu a
					left outer join t_output b on(a.id_output=b.id)
					left outer join t_unit c on(a.kdunit=c.kdunit)
					where a.thang='".session('tahun')."' ".$where."
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
				$sWhere=" where lower(nmunit) like lower('".$sSearch."%') or lower(nmunit) like lower('%".$sSearch."%') or
								lower(uraian) like lower('".$sSearch."%') or lower(uraian) like lower('%".$sSearch."%') ";
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
				$row->thang,
				$row->nmunit,
				$row->uraian,
				$row->kdakun,
				'<div style="text-align:right;">'.number_format($row->nilai).'</div>',
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function pilih(Request $request, $id)
	{
		try{
			$rows = DB::select("
				select  id,
						kdunit,
						id_output,
						nilai,
						kdakun
				from d_pagu a
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
				
				$rows = DB::select("
					SELECT	count(*) AS jml
					from d_pagu
					where thang=? and kdunit=? and id_output=? and kdakun=?
				",[
					session('tahun'),
					$request->input('kdunit'),
					$request->input('id_output'),
					$request->input('kdakun'),
				]);
				
				if($rows[0]->jml==0){
					
					$insert = DB::table('d_pagu')->insert([
						'thang' => session('tahun'),
						'kdunit' => $request->input('kdunit'),
						'id_output' => $request->input('id_output'),
						'kdakun' => $request->input('kdakun'),
						'nilai' => str_replace(",", "", $request->input('nilai'))
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
					update d_pagu
					set nilai=?
					where id=?
				",[
					str_replace(",", "", $request->input('nilai')),
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
			return 'Terdapat kesalahan lainnya, hubungi Administrator!';
		}		
	}
	
	public function hapus(Request $request)
	{
		try{
			$delete = DB::delete("
				delete from d_pagu
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
	
	public function sisaPagu()
	{
		$rows = DB::select("
			select  nvl(a.nilai,0) as pagu,
					nvl(b.nilai,0) as realisasi,
					nvl(a.nilai,0)-nvl(b.nilai,0) as sisa
			from(
				select  sum(nilai) as nilai
				from d_pagu
				where kdunit='".session('kdunit')."' and thang='".session('tahun')."'
			) a,
			(
				select  sum(a.nilai) as nilai
				from d_trans_akun a
				left join d_trans b on(a.id_trans=b.id)
				where b.kdunit='".session('kdunit')."' and b.thang='".session('tahun')."' and substr(a.kdakun,1,1) in('5','7') and a.kddk='D'
			) b
		");
		
		if(count($rows)>0) {
			return response()->json($rows[0]);
		}
	}
	
}