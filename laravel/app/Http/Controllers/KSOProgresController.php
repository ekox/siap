<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KSOProgresController extends Controller {

	public function index(Request $request)
	{
		$arr_where = array();
		
		if(isset($_GET['id_kso'])){
			if($_GET['id_kso']!==''){
				$arr_where[] = " a.id_kso='".$_GET['id_kso']."' ";
			}
		}
		
		$arr_where[] = " e.id_kso is not null ";
		
		if(count($arr_where)>0){
			$where = "where ".implode(" and ", $arr_where);
		}
		
		$aColumns = array('id','nama','nomor','tanggal','uraian','jml','status','id_kso','is_ubah');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							b.nama,
							a.nomor,
							to_char(a.tanggal,'dd-mm-yyyy') as tanggal,
							a.uraian,
							nvl(f.jml,0) as jml,
							d.nmstatus as status,
							a.id_kso,
							d.is_ubah
					from d_kso_progres a
					left outer join d_kso b on(a.id_kso=b.id)
					left outer join t_alur c on(a.id_alur=c.id)
					left outer join t_alur_status d on(a.id_alur=d.id_alur and a.status=d.status)
					left outer join(
						select  distinct id_kso
						from d_kso_user
						where id_user=".session('id_user')." 
					) e on(a.id_kso=e.id_kso)
					left outer join(
						select  id_kso_progres,
								count(*) as jml
						from d_kso_progres_dtl
						group by id_kso_progres
					) f on(a.id=f.id_kso_progres)
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
				$sWhere=" where lower(nama) like lower('".$sSearch."%') or lower(nama) like lower('%".$sSearch."%') or
								lower(uraian) like lower('".$sSearch."%') or lower(uraian) like lower('%".$sSearch."%')";
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
			if(session('kdlevel')=='02' && $row->is_ubah=='1'){
				$aksi='<center style="width:50px;">
							<div class="dropdown pull-right">
								<button type="button" class="btn btn-success btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                                <i class="material-icons">done</i>
	                                <span class="caret"></span>
	                            </button>
	                            <ul class="dropdown-menu">
	                                <li><a id="'.$row->id.'" href="javascript:void(0);" class="ubah">Ubah Data</a></li>
	                                <li><a id="'.$row->id.'" href="javascript:void(0);" class="hapus">Hapus Data</a></li>
									<li><a id="'.$row->id_kso.'-'.$row->id.'" href="javascript:void(0);" class="detil">Detil</a></li>
	                            </ul>
	                        </div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->no,
				$row->nama,
				$row->nomor,
				$row->tanggal,
				$row->uraian,
				'<div style="text-align:right;">'.number_format($row->jml).'</div>',
				$row->status,
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function detil(Request $request, $param)
	{
		if($param!=='x-x'){
			
			$arr_param = explode("-", $param);
			$id_kso = $arr_param[0];
			$id_kso_progres = $arr_param[1];
			
			$aColumns = array('id','kode','uraian','satuan','bobot','volume','harga','persentase','keterangan','valid');
			/* Indexed column (used for fast and accurate table cardinality) */
			$sIndexColumn = "id";
			/* DB table to use */
			$sTable = "select  a.id,
								a.lvl1||'.'||a.lvl2||'.'||a.lvl3||'.'||a.lvl4||'.'||a.lvl5||'.'||a.lvl6 as kode,
								a.uraian,
								a.satuan,
								a.bobot,
								a.volume,
								a.harga,
								(a.volume*a.harga) as total,
								nvl(b.valid,'') as valid,
								nvl(b.persentase,0) as persentase,
								nvl(b.keterangan,'') as keterangan
						from(
							select  a.*,
									b.satuan
							from d_kso_teknis a
							left outer join t_satuan b on(a.id_satuan=b.id)
							left outer join d_kso c on(a.id_kso=c.id)
							where a.id_kso=".$id_kso." and a.tagihan='02' and a.lvl=c.lvl
						) a
						left outer join(
							select  a.id_kso_teknis,
									a.persentase,
									a.keterangan,
									a.valid
							from d_kso_progres_dtl a
							where a.id_kso_progres=".$id_kso_progres."
						) b on(a.id=b.id_kso_teknis)
						order by a.lvl1||'.'||a.lvl2||'.'||a.lvl3||'.'||a.lvl4||'.'||a.lvl5||'.'||a.lvl6";
			
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
					$sWhere=" where lower(kode) like lower('".$sSearch."%') or lower(kode) like lower('%".$sSearch."%') or
									lower(uraian) like lower('".$sSearch."%') or lower(uraian) like lower('%".$sSearch."%')";
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
				if(session('kdlevel')=='02'){
					$aksi='<input type="checkbox" id="progres-'.$row->id.'" class="filled-in chk-col-red" value="1" />
                           <label for="progres-'.$row->id.'"></label>';
				}
				
				$valid = '';
				if($row->valid=='1'){
					$valid = '<i class="fa fa-check"></i>';
				}
				elseif($row->valid=='0'){
					$valid = '<i class="fa fa-times"></i>';
				}
				
				$output['aaData'][] = array(
					$row->no,
					$row->kode,
					$row->uraian,
					$row->satuan,
					'<div style="text-align:right;">'.number_format($row->bobot).'</div>',
					'<div style="text-align:right;">'.number_format($row->harga).'</div>',
					'<div style="text-align:right;">'.number_format($row->volume).'</div>',
					$valid,
					$row->keterangan,
					'<input type="text" name="pilih['.$row->id.']" class="val_num" style="text-align:right;width:75% !important;" value="'.$row->persentase.'">'
				);
			}
			
			return response()->json($output);
			
		}
	}
	
	public function proses(Request $request)
	{
		$arr_where = array();
		
		if(isset($_GET['id_kso'])){
			if($_GET['id_kso']!==''){
				$arr_where[] = " a.id_kso='".$_GET['id_kso']."' ";
			}
		}
		
		$arr_where[] = " e.id_kso is not null and g.kdlevel='".session('kdlevel')."' ";
		
		if(count($arr_where)>0){
			$where = "where ".implode(" and ", $arr_where);
		}
		
		$aColumns = array('id','nama','nomor','tanggal','uraian','jml','status','id_kso','is_final');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "	select  a.id,
							b.nama,
							a.nomor,
							to_char(a.tanggal,'dd-mm-yyyy') as tanggal,
							a.uraian,
							nvl(f.jml,0) as jml,
							d.nmstatus as status,
							a.id_kso,
							d.is_final
					from d_kso_progres a
					left outer join d_kso b on(a.id_kso=b.id)
					left outer join t_alur c on(a.id_alur=c.id)
					left outer join t_alur_status d on(a.id_alur=d.id_alur and a.status=d.status)
					left outer join(
						select  distinct id_kso
						from d_kso_user
						where id_user=".session('id_user')." 
					) e on(a.id_kso=e.id_kso)
					left outer join(
						select  id_kso_progres,
								count(*) as jml
						from d_kso_progres_dtl
						group by id_kso_progres
					) f on(a.id=f.id_kso_progres)
					left outer join t_alur_status g on(a.id_alur=g.id_alur and a.status=g.status)
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
				$sWhere=" where lower(nama) like lower('".$sSearch."%') or lower(nama) like lower('%".$sSearch."%') or
								lower(uraian) like lower('".$sSearch."%') or lower(uraian) like lower('%".$sSearch."%')";
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
			$aksi = '';
			if($row->is_final!=='1'){
				$aksi='<center style="width:50px;">
							<div class="dropdown pull-right">
								<button type="button" class="btn btn-success btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									<i class="material-icons">done</i>
									<span class="caret"></span>
								</button>
								<ul class="dropdown-menu">
									<li><a id="'.$row->id_kso.'-'.$row->id.'" href="javascript:void(0);" class="proses">Proses Data</a></li>
								</ul>
							</div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->no,
				$row->nama,
				$row->nomor,
				$row->tanggal,
				$row->uraian,
				'<div style="text-align:right;">'.number_format($row->jml).'</div>',
				$row->status,
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function monitoring(Request $request)
	{
		$arr_where = array();
		
		if(isset($_GET['id_kso'])){
			if($_GET['id_kso']!==''){
				$arr_where[] = " a.id_kso='".$_GET['id_kso']."' ";
			}
		}
		
		$arr_where[] = " e.id_kso is not null ";
		
		if(count($arr_where)>0){
			$where = "where ".implode(" and ", $arr_where);
		}
		
		$aColumns = array('id','nama','nomor','tanggal','uraian','jml','nmlevel','status','id_kso');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "	select  a.id,
							b.nama,
							a.nomor,
							to_char(a.tanggal,'dd-mm-yyyy') as tanggal,
							a.uraian,
							nvl(f.jml,0) as jml,
							h.nmlevel,
							d.nmstatus as status,
							a.id_kso
					from d_kso_progres a
					left outer join d_kso b on(a.id_kso=b.id)
					left outer join t_alur c on(a.id_alur=c.id)
					left outer join t_alur_status d on(a.id_alur=d.id_alur and a.status=d.status)
					left outer join(
						select  distinct id_kso
						from d_kso_user
						where id_user=".session('id_user')." 
					) e on(a.id_kso=e.id_kso)
					left outer join(
						select  id_kso_progres,
								count(*) as jml
						from d_kso_progres_dtl
						group by id_kso_progres
					) f on(a.id=f.id_kso_progres)
					left outer join t_alur_status g on(a.id_alur=g.id_alur and a.status=g.status)
					left outer join t_level h on(g.kdlevel=h.kdlevel)
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
				$sWhere=" where lower(nama) like lower('".$sSearch."%') or lower(nama) like lower('%".$sSearch."%') or
								lower(uraian) like lower('".$sSearch."%') or lower(uraian) like lower('%".$sSearch."%')";
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
			$aksi='<center style="width:50px;">
						<div class="dropdown pull-right">
							<button type="button" class="btn btn-success btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
								<i class="material-icons">done</i>
								<span class="caret"></span>
							</button>
							<ul class="dropdown-menu">
								<li><a id="'.$row->id_kso.'-'.$row->id.'" href="javascript:void(0);" class="proses">Lihat Data</a></li>
							</ul>
						</div>
					</center>';
			
			$output['aaData'][] = array(
				$row->no,
				$row->nama,
				$row->nomor,
				$row->tanggal,
				$row->uraian,
				'<div style="text-align:right;">'.number_format($row->jml).'</div>',
				$row->nmlevel,
				$row->status,
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function proses_detil(Request $request, $param)
	{
		if($param!=='x-x'){
			
			$arr_param = explode("-", $param);
			$id_kso = $arr_param[0];
			$id_kso_progres = $arr_param[1];
			
			$aColumns = array('id','kode','uraian','satuan','bobot','volume','harga','persentase','keterangan','valid');
			/* Indexed column (used for fast and accurate table cardinality) */
			$sIndexColumn = "id";
			/* DB table to use */
			$sTable = "select  a.id,
								a.lvl1||'.'||a.lvl2||'.'||a.lvl3||'.'||a.lvl4||'.'||a.lvl5||'.'||a.lvl6 as kode,
								a.uraian,
								a.satuan,
								a.bobot,
								a.volume,
								a.harga,
								(a.volume*a.harga) as total,
								nvl(b.valid,'') as valid,
								nvl(b.persentase,0) as persentase,
								nvl(b.keterangan,'') as keterangan
						from(
							select  a.*,
									b.satuan
							from d_kso_teknis a
							left outer join t_satuan b on(a.id_satuan=b.id)
							left outer join d_kso c on(a.id_kso=c.id)
							where a.id_kso=".$id_kso." and a.lvl=c.lvl
						) a
						right outer join(
							select  a.id_kso_teknis,
									a.persentase,
									a.keterangan,
									a.valid
							from d_kso_progres_dtl a
							where a.id_kso_progres=".$id_kso_progres."
						) b on(a.id=b.id_kso_teknis)
						order by a.lvl1||'.'||a.lvl2||'.'||a.lvl3||'.'||a.lvl4||'.'||a.lvl5||'.'||a.lvl6";
			
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
					$sWhere=" where lower(kode) like lower('".$sSearch."%') or lower(kode) like lower('%".$sSearch."%') or
									lower(uraian) like lower('".$sSearch."%') or lower(uraian) like lower('%".$sSearch."%')";
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
				if(session('kdlevel')=='02'){
					$aksi='<input type="checkbox" name="pilih['.$row->id.']" id="progres-'.$row->id.'" class="filled-in chk-col-red" value="1" />
                           <label for="progres-'.$row->id.'"></label>';
				}
				
				$valid = '';
				if($row->valid=='1'){
					$valid = '<i class="fa fa-check"></i>';
				}
				elseif($row->valid=='0'){
					$valid = '<i class="fa fa-times"></i>';
				}
				
				$output['aaData'][] = array(
					$row->no,
					$row->kode,
					$row->uraian,
					$row->satuan,
					'<div style="text-align:right;">'.number_format($row->bobot).'</div>',
					'<div style="text-align:right;">'.number_format($row->harga).'</div>',
					'<div style="text-align:right;">'.number_format($row->volume).'</div>',
					'<div style="text-align:right;">'.number_format($row->persentase).'</div>',
					//$row->keterangan,
					//$aksi
				);
			}
			
			return response()->json($output);
			
		}
	}
	
	public function pilih(Request $request, $id)
	{
		try{
			$rows = DB::select("
				select  a.id,
						a.id_kso,
						b.nama,
						b.nopks,
						a.id_user,
						a.nomor,
						to_char(a.tanggal,'yyyy-mm-dd') as tanggal,
						a.uraian
				from d_kso_progres a
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
					from d_kso_progres
					where id_kso=? and nomor=?
				",[
					$request->input('id_kso'),
					$request->input('nomor')
				]);
				
				if($rows[0]->jml==0){
					
					$insert = DB::insert("
						INSERT INTO d_kso_progres(
							id_kso,
							nomor,
							tanggal,
							uraian,
							id_alur,
							status,
							id_user,
							created_at,
							updated_at
						)
						VALUES (?,?,to_date(?,'yyyy-mm-dd'),?,?,?,?,sysdate,sysdate)
					",[
						$request->input('id_kso'),
						$request->input('nomor'),
						$request->input('tanggal'),
						$request->input('uraian'),
						1,
						0,
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
					return 'Nomor ini sudah ada!';
				}
				
			}
			else{
				
				$update = DB::update("
					update d_kso_progres
					set nomor=?,
						tanggal=to_date(?,'yyyy-mm-dd'),
						uraian=?,
						id_user=?,
						updated_at=sysdate
					where id=? and status=0
				",[
					$request->input('nomor'),
					$request->input('tanggal'),
					$request->input('uraian'),
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
				delete from d_kso_progres
				where id=? and status=0
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
	
	public function simpan_detil(Request $request)
	{
		try{
			$arr_kso = explode("-", $request->input('id_kso_progres'));
			$id_kso = $arr_kso[0];
			$id_kso_progres = $arr_kso[1];
			
			$rows = DB::select("
				SELECT	*
				from d_kso_progres
				where id=?
			",[
				$id_kso_progres
			]);
			
			if($rows[0]->status==0){
				
				$arr_pilih = $request->input('pilih');
				
				if(count($arr_pilih)>0){
					
					$arr_key = array_keys($arr_pilih);
					
					for($i=0;$i<count($arr_key);$i++){
						
						$arr_insert[] = "
							select	".$id_kso_progres." as id_kso_progres,
									".$arr_key[$i]." as id_kso_teknis,
									".$arr_pilih[$arr_key[$i]]." as persentase,
									'' as valid,
									'' as keterangan
							from dual
						";
						
					}
					
					$value = implode(" union all ", $arr_insert);
					
					DB::beginTransaction();
				
					$delete = DB::delete("
						delete from d_kso_progres_dtl
						where id_kso_progres=?
					",[
						$id_kso_progres
					]);
					
					$insert = DB::insert("
						INSERT INTO d_kso_progres_dtl(id_kso_progres,id_kso_teknis,persentase,valid,keterangan)
						".$value."
					");
					
					if($insert){
						DB::commit();
						return 'success';
					}
					else{
						return 'Data gagal disimpan!';
					}
					
				}
				else{
					return 'Data dokumen teknis belum diisi!';
				}
				
			}
			else{
				return 'Data sudah diajukan tidak dapat diubah!';
			}			
		}
		catch(\Exception $e){
			return $e;
			return 'Terdapat kesalahan lainnya, hubungi Administrator!';
		}		
	}
	
	public function proses_pilih(Request $request, $id)
	{
		try{
			$rows = DB::select("
				select  a.id,
						a.id_kso,
						b.nama,
						b.nopks,
						a.id_user,
						a.nomor,
						to_char(a.tanggal,'yyyy-mm-dd') as tanggal,
						a.uraian,
						a.id_alur,
						a.status,
						c.id as id_alur_status
				from d_kso_progres a
				left outer join d_kso b on(a.id_kso=b.id)
				left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
				where a.id=?
			",[
				$id
			]);
			
			if(count($rows)>0){
				
				$data['data'] = $rows[0];
				
				$rows = DB::select("
					select  *
					from t_alur_status_dtl
					where id_alur_status=?
					order by nourut asc
				",[
					$data['data']->id_alur_status
				]);
				
				if(count($rows)>0){
					
					$dropdown = '<option value="" style="display:none;">Pilih Status</option>';
					foreach($rows as $row){
						$dropdown .= '<option value="'.$row->id_alur_status_lanjut.'">'.$row->dropdown.'</option>';
					}
					
					$data['dropdown'] = $dropdown;
					$data['error'] = false;
					$data['message'] = '';
					
				}
				else{
					$data['error'] = true;
					$data['message'] = 'Status tidak ditemukan!';
				}
				
			}
			else{
				$data['error'] = true;
				$data['message'] = 'Data tidak ditemukan!';
			}
			
			return response()->json($data);
			
		}
		catch(\Exception $e){
			return $e;
		}
	}
	
	public function proses_simpan(Request $request)
	{
		try{
			$rows = DB::select("
				select	count(*) as jml
				from d_kso_progres
				where id=? and id_alur=? and status=?
			",[
				$request->input('inp-id'),
				$request->input('id_alur'),
				$request->input('status1')
			]);
			
			if($rows[0]->jml==1){
				
				$rows = DB::select("
					select	*
					from t_alur_status
					where id=?
				",[
					$request->input('status')
				]);
				
				if(count($rows)>0){
					
					$lanjut = true;
					
					if($rows[0]->is_valid=='1'){
						
						if(count($request->input('pilih'))>0){
							
							/*$arr_pilih = $request->input('pilih');
							$arr_keys = array_keys($arr_pilih);
							
							$arr_insert = array();
							for($i=0;$i<count($arr_keys);$i++){
								$arr_insert[] = "select	".$request->input('inp-id')." as id_kso_progres,
														".$arr_keys[$i]." as id_kso_teknis,
														'1' as valid
												 
												 ";
							}*/
							
						}
						else{
							$lanjut = false;
						}
						
					}
					
					if($lanjut){
						
						DB::beginTransaction();
						
						$insert = DB::insert("
							insert into h_kso_progres(id_kso_progres,nomor,tanggal,uraian,status,id_user,created_at,updated_at)
							select	id,nomor,tanggal,uraian,status,id_user,created_at,updated_at
							from d_kso_progres
							where id=?
						",[
							$request->input('inp-id')
						]);
						
						if($insert){
							
							$update = DB::update("
								update d_kso_progres
								set id_alur=?,
									status=?,
									id_user=?,
									updated_at=sysdate
								where id=?
							",[
								$rows[0]->id_alur,
								$rows[0]->status,
								session('id_user'),
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
		catch(\Exception $e){
			return $e;
		}
	}
	
}