<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TagihanProsesController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','nmunit','nama','nmtrans','pks','tgjtempo','uraian','nilai','status');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select    a.*
					from(
						select  a.id,
								d.nmunit,
								e.nama,
								h.nmtrans,
								a.nopks||'<br>'||to_char(a.tgpks,'dd-mm-yyyy') as pks,
								to_char(a.tgjtempo,'dd-mm-yyyy') as tgjtempo,
								a.uraian,
								a.nilai,
								b.nmalur||'<br>'||g.nmlevel||'<br>'||c.nmstatus as status,
								decode(c.is_unit,null,
									1,
									decode(substr(a.kdunit,1,c.is_unit),'".session('kdlevel')."',
										1,
										0
									)
								) as akses
						from d_tagih a
						left outer join t_alur b on(a.id_alur=b.id)
						left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
						left outer join t_unit d on(a.kdunit=d.kdunit)
						left outer join t_pelanggan e on(a.id_pelanggan=e.id)
						left outer join t_level g on(c.kdlevel=g.kdlevel)
						left outer join t_trans h on(a.kdtran=h.id)
						where a.thang='".session('tahun')."' and c.kdlevel='".session('kdlevel')."'
					) a
					where a.akses=1
					order by a.id desc
					";
		
		/*
		 * Paging
		 */ 
		$sLimit = " ";
		if((isset($_GET['iDisplayStart']))&&(isset($_GET['iDisplayLength']))){
			$iDisplayStart=$_GET['iDisplayStart']+1;
			$iDisplayLength=$_GET['iDisplayLength'];
			$sSearch=$_GET['sSearch'];
			if ((isset( $iDisplayStart )) &&  ($iDisplayLength != '-1' )) 
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
				$sWhere=" where lower(nopks) like lower('".$sSearch."%') or lower(nopks) like lower('%".$sSearch."%') or
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
			if(session('kdlevel')=='12'){
				$aksi='<center>
							<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
							<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
								<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Proses Data</a>
							</div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->no,
				$row->nmunit,
				$row->nama,
				$row->nmtrans,
				$row->pks,
				$row->tgjtempo,
				$row->uraian,
				'<div style="text-align:right;">'.number_format($row->nilai).'</div>',
				$row->status,
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function monitoring(Request $request)
	{
		$aColumns = array('id','nmunit','nama','nmtrans','pks','tgjtempo','uraian','nilai','status');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							d.nmunit,
							e.nama,
							h.nmtrans,
							a.nopks||'<br>'||to_char(a.tgpks,'dd-mm-yyyy') as pks,
							to_char(a.tgjtempo,'dd-mm-yyyy') as tgjtempo,
							a.uraian,
							a.nilai,
							b.nmalur||'<br>'||g.nmlevel||'<br>'||c.nmstatus as status
					from d_tagih a
					left outer join t_alur b on(a.id_alur=b.id)
					left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
					left outer join t_unit d on(a.kdunit=d.kdunit)
					left outer join t_pelanggan e on(a.id_pelanggan=e.id)
					left outer join t_level g on(c.kdlevel=g.kdlevel)
					left outer join t_trans h on(a.kdtran=h.id)
					where a.thang='".session('tahun')."'
					order by a.id desc
					";
		
		/*
		 * Paging
		 */ 
		$sLimit = " ";
		if((isset($_GET['iDisplayStart']))&&(isset($_GET['iDisplayLength']))){
			$iDisplayStart=$_GET['iDisplayStart']+1;
			$iDisplayLength=$_GET['iDisplayLength'];
			$sSearch=$_GET['sSearch'];
			if ((isset( $iDisplayStart )) &&  ($iDisplayLength != '-1' )) 
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
				$sWhere=" where lower(nopks) like lower('".$sSearch."%') or lower(nopks) like lower('%".$sSearch."%') or
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
			if(session('kdlevel')=='12'){
				$aksi='<center>
							<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
							<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
								<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Lihat Data</a>
							</div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->no,
				$row->nmunit,
				$row->nama,
				$row->nmtrans,
				$row->pks,
				$row->tgjtempo,
				$row->uraian,
				'<div style="text-align:right;">'.number_format($row->nilai).'</div>',
				$row->status,
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function pilih(Request $request, $id)
	{
		$rows = DB::select("
			select  a.id,
					b.nmalur,
					c.nmunit,
					d.nama as nmpelanggan,
					f.nmtrans,
					a.nopks,
					to_char(a.tgpks,'yyyy-mm-dd') as tgpks,
					to_char(a.tgjtempo,'yyyy-mm-dd') as tgjtempo,
					a.uraian,
					a.nilai,
					a.id_alur,
					a.status
			from d_tagih a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_unit c on(a.kdunit=c.kdunit)
			left outer join t_pelanggan d on(a.id_pelanggan=d.id)
			left outer join t_trans f on(a.kdtran=f.id)
			where a.id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$id_alur = $rows[0]->id_alur;
			$detil = $rows[0];
			$data['error'] = false;
			$data['message'] = $detil;
			
			$rows = DB::select("
				select  *
				from t_alur_status_dtl
				where id_alur_status=(
					select id
					from t_alur_status
					where id_alur=? and status=1
				)
				order by nourut asc
			",[
				$id_alur
			]);
			
			$status = '<option value="">Pilih Data</option>';
			foreach($rows as $row){
				$status .= '<option value="'.$row->id_alur_status_lanjut.'">'.$row->dropdown.'</option>';
			}
			
			$data['dropdown'] = $status;
			
		}
		else{
			$data['error'] = true;
			$data['message'] = 'Data header tidak ditemukan!';
		}
		
		return response()->json($data);
	}
	
	public function simpan(Request $request)
	{
		$rows = DB::select("
			select	count(*) as jml
			from d_tagih
			where id=? and id_alur=? and status=?
		",[
			$request->input('inp-id'),
			$request->input('id_alur'),
			$request->input('status')
		]);
		
		if($rows[0]->jml==1){
			
			$rows = DB::select("
				select	*
				from t_alur_status
				where id=?
			",[
				$request->input('status1')
			]);
			
			if(count($rows)>0){
				
				$lanjut = true;
				
				if($rows[0]->is_valid=='1'){
					
					if(count($request->input('pilih'))>0){
						
						
						
					}
					else{
						$lanjut = false;
					}
					
				}
				
				if($lanjut){
					
					DB::beginTransaction();
					
					$insert = DB::insert("
						insert into d_tagih_histori(id_tagih,id_alur,status,ket,id_user,created_at,updated_at)
						select	id,id_alur,status,ket,id_user,created_at,updated_at
						from d_tagih
						where id=?
					",[
						$request->input('inp-id')
					]);
					
					if($insert){
						
						$update = DB::update("
							update d_tagih
							set id_alur=?,
								status=?,
								id_user=?,
								updated_at=sysdate,
								ket=?
							where id=?
						",[
							$rows[0]->id_alur,
							$rows[0]->status,
							session('id_user'),
							$request->input('ket'),
							$request->input('inp-id')
						]);
						
						if($update){
							DB::commit();
							return 'success';
						}
						else{
							return 'Status gagal diupdate!';
						}
						
					}
					else{
						return 'Data histori gagal disimpan!';
					}
					
				}
				else{
					return 'Anda belum melakukan validasi data!';
				}
				
			}
			else{
				return 'Status tidak ditemukan!';
			}
			
		}
		else{
			return 'Data tidak ditemukan!';
		}		
	}
	
}