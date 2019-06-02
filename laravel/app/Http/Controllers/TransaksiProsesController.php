<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TransaksiProsesController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','notrans','nmalur','nama','nobukti','uraian','nilai','status');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select	a.*
					from(
						select  a.id as id_trans,
								a.id,
								a.notrans,
								a.thang,
								d.nmunit,
								b.nmalur,
								e.nama,
								a.nobukti,
								to_char(a.tgbukti,'dd-mm-yyyy') as tgbukti,
								h.nmtrans as uraian,
								nvl(f.nilai,0) as nilai,
								g.nmlevel||'<br>'||c.nmstatus as status,
								decode(c.is_unit,null,
									1,
									decode(substr(a.kdunit,1,c.is_unit),'".session('kdunit')."',
										1,
										0
									)
								) as akses
						from d_trans a
						left outer join t_alur b on(a.id_alur=b.id)
						left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
						left outer join t_unit d on(a.kdunit=d.kdunit)
						left outer join t_penerima e on(a.id_penerima=e.id)
						left outer join t_level g on(c.kdlevel=g.kdlevel)
						left outer join t_trans h on(a.kdtran=h.id)
						left outer join(
							select  id_trans,
									sum(nilai) as nilai
							from d_trans_akun
							where kddk='D'
							group by id_trans
						) f on(a.id=f.id_trans)
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
				$sWhere=" where lower(nobukti) like lower('".$sSearch."%') or lower(nobukti) like lower('%".$sSearch."%') or
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
			if(session('kdlevel')=='01'){
				$aksi='<center>
							<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
							<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
								<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Proses Data</a>
							</div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->id,
				$row->notrans,
				$row->nmalur,
				$row->nama,
				$row->nobukti,
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
		$aColumns = array('id','notrans','nmalur','nama','nobukti','uraian','nilai','status');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id as id_trans,
							a.id,
							a.notrans,
							a.thang,
							d.nmunit,
							b.nmalur,
							e.nama,
							a.nobukti,
							to_char(a.tgbukti,'dd-mm-yyyy') as tgbukti,
							h.nmtrans as uraian,
							nvl(f.nilai,0) as nilai,
							g.nmlevel||'<br>'||c.nmstatus as status,
							decode(c.is_unit,null,
								1,
								decode(substr(a.kdunit,1,c.is_unit),'".session('kdunit')."',
									1,
									0
								)
							) as akses
					from d_trans a
					left outer join t_alur b on(a.id_alur=b.id)
					left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
					left outer join t_unit d on(a.kdunit=d.kdunit)
					left outer join t_penerima e on(a.id_penerima=e.id)
					left outer join t_level g on(c.kdlevel=g.kdlevel)
					left outer join t_trans h on(a.kdtran=h.id)
					left outer join(
						select  id_trans,
								sum(nilai) as nilai
						from d_trans_akun
						where kddk='D'
						group by id_trans
					) f on(a.id=f.id_trans)
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
				$sWhere=" where lower(nobukti) like lower('".$sSearch."%') or lower(nobukti) like lower('%".$sSearch."%') or
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
			if(session('kdlevel')=='01'){
				$aksi='<center>
							<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
							<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
								<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Lihat Data</a>
							</div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->id,
				$row->notrans,
				$row->nmalur,
				$row->nama,
				$row->nobukti,
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
					a.notrans,
					b.nmalur,
					c.uraian as nmoutput,
					d.nama as nmpenerima,
					e.uraian as nmgiat,
					f.nmtrans,
					a.nobukti,
					to_char(a.tgbukti,'yyyy-mm-dd') as tgbukti,
					a.uraian,
					a.id_alur,
					a.status
			from d_trans a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_output c on(a.id_output=c.id)
			left outer join t_penerima d on(a.id_penerima=d.id)
			left outer join t_kegiatan e on(a.id_giat=e.id)
			left outer join t_trans f on(a.kdtran=f.id)
			where a.id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$detil = $rows[0];
			
			$rows = DB::select("
				select	*
				from d_trans_dok
				where id_trans=?
			",[
				$id
			]);
			
			$data['dok'] = '';
			if(count($rows)>0){
				$data['dok'] = $rows[0]->nmfile;
			}
			
			$rows = DB::select("
				select	a.kdakun,
						b.nmakun,
						a.kddk,
						a.nilai
				from d_trans_akun a
				left outer join t_akun b on(a.kdakun=b.kdakun)
				where a.id_trans=?
				order by a.kddk,a.kdakun asc
			",[
				$id
			]);
			
			if(count($rows)>0){
				
				$data['akun'] = $rows;
				$data['x'] = count($rows);
				$data['error'] = false;
				$data['message'] = $detil;
				
				$rows = DB::select("
					select  *
					from t_alur_status_dtl
					where id_alur_status=(
						select id
						from t_alur_status
						where id_alur=1 and status=1
					)
					order by nourut asc
				");
				
				$status = '<option value="">Pilih Data</option>';
				foreach($rows as $row){
					$status .= '<option value="'.$row->id_alur_status_lanjut.'">'.$row->dropdown.'</option>';
				}
				
				$data['dropdown'] = $status;
				
			}
			else{
				$data['error'] = true;
				$data['message'] = 'Data akun tidak ditemukan!';
			}
			
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
			from d_trans
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
						insert into d_trans_histori(id_trans,id_alur,status,ket,id_user,created_at,updated_at)
						select	id,id_alur,status,ket,id_user,created_at,updated_at
						from d_trans
						where id=?
					",[
						$request->input('inp-id')
					]);
					
					if($insert){
						
						$update = DB::update("
							update d_trans
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