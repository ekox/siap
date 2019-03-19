<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KSOTeknisBaruController extends Controller {

	public function lvl1(Request $request, $id_kso)
	{
		if($id_kso!=='x'){
			
			$aColumns = array('id','nmtagihan','lvl1','uraian','satuan','harga','volume','bobot','total');
			/* Indexed column (used for fast and accurate table cardinality) */
			$sIndexColumn = "id";
			/* DB table to use */
			$sTable = "select  a.id,
								e.nmtagihan,
								a.lvl1,
								a.uraian,
								b.satuan,
								a.harga,
								a.volume,
								a.bobot,
								nvl(c.total,0) as total
						from d_kso_teknis a
						left outer join t_satuan b on(a.id_satuan=b.id)
						left outer join(
							select  lvl1,
									sum(harga*volume) as total
							from d_kso_teknis
							where id_kso=".$id_kso."
							group by lvl1
						) c on(a.lvl1=c.lvl1)
						left outer join(
							select	distinct
									id_kso
							from d_kso_user
							where id_user=".session('id_user')."
						) d on(a.id_kso=d.id_kso)
						left outer join t_tagihan e on(a.tagihan=e.kdtagihan)
						where a.id_kso=".$id_kso." and a.lvl=1 and d.id_kso is not null
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
					$sWhere=" where lower(lvl1) like lower('".$sSearch."%') or lower(lvl1) like lower('%".$sSearch."%') or
									lower(uraian) like lower('".$sSearch."%') or lower(uraian) like lower('%".$sSearch."%') ";
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
					$row->nmtagihan,
					$row->lvl1,
					$row->uraian,
					$row->satuan,
					'<div style="text-align:right;">'.number_format($row->harga).'</div>',
					'<div style="text-align:right;">'.number_format($row->volume).'</div>',
					'<div style="text-align:right;">'.number_format($row->bobot).'</div>',
					'<div style="text-align:right;">'.number_format($row->total).'</div>',
					$aksi
				);
			}
			
			return response()->json($output);
			
		}
	}
	
	public function lvl2(Request $request, $id_kso)
	{
		if($id_kso!=='x'){
			
			$aColumns = array('id','nmtagihan','lvl2','uraian','satuan','harga','volume','bobot','total');
			/* Indexed column (used for fast and accurate table cardinality) */
			$sIndexColumn = "id";
			/* DB table to use */
			$sTable = "select  a.id,
								e.nmtagihan,
								a.lvl1||'.'||a.lvl2 as lvl2,
								a.uraian,
								b.satuan,
								a.harga,
								a.volume,
								a.bobot,
								nvl(c.total,0) as total
						from d_kso_teknis a
						left outer join t_satuan b on(a.id_satuan=b.id)
						left outer join(
							select  lvl1,
									lvl2,
									sum(harga*volume) as total
							from d_kso_teknis
							where id_kso=".$id_kso."
							group by lvl1,lvl2
						) c on(a.lvl1=c.lvl1 and a.lvl2=c.lvl2)
						left outer join(
							select	distinct
									id_kso
							from d_kso_user
							where id_user=".session('id_user')."
						) d on(a.id_kso=d.id_kso)
						left outer join t_tagihan e on(a.tagihan=e.kdtagihan)
						where a.id_kso=".$id_kso." and a.lvl=2 and d.id_kso is not null
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
					$sWhere=" where lower(lvl2) like lower('".$sSearch."%') or lower(lvl2) like lower('%".$sSearch."%') or
									lower(uraian) like lower('".$sSearch."%') or lower(uraian) like lower('%".$sSearch."%') ";
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
					$row->nmtagihan,
					$row->lvl2,
					$row->uraian,
					$row->satuan,
					'<div style="text-align:right;">'.number_format($row->harga).'</div>',
					'<div style="text-align:right;">'.number_format($row->volume).'</div>',
					'<div style="text-align:right;">'.number_format($row->bobot).'</div>',
					'<div style="text-align:right;">'.number_format($row->total).'</div>',
					$aksi
				);
			}
			
			return response()->json($output);
			
		}
	}
	
	public function lvl3(Request $request, $id_kso)
	{
		if($id_kso!=='x'){
			
			$aColumns = array('id','nmtagihan','lvl3','uraian','satuan','harga','volume','bobot','total');
			/* Indexed column (used for fast and accurate table cardinality) */
			$sIndexColumn = "id";
			/* DB table to use */
			$sTable = "select  a.id,
								e.nmtagihan,
								a.lvl1||'.'||a.lvl2||'.'||a.lvl3 as lvl3,
								a.uraian,
								b.satuan,
								a.harga,
								a.volume,
								a.bobot,
								nvl(c.total,0) as total
						from d_kso_teknis a
						left outer join t_satuan b on(a.id_satuan=b.id)
						left outer join(
							select  lvl1,
									lvl2,
									lvl3,
									sum(harga*volume) as total
							from d_kso_teknis
							where id_kso=".$id_kso."
							group by lvl1,lvl2,lvl3
						) c on(a.lvl1=c.lvl1 and a.lvl2=c.lvl2 and a.lvl3=c.lvl3)
						left outer join(
							select	distinct
									id_kso
							from d_kso_user
							where id_user=".session('id_user')."
						) d on(a.id_kso=d.id_kso)
						left outer join t_tagihan e on(a.tagihan=e.kdtagihan)
						where a.id_kso=".$id_kso." and a.lvl=3 and d.id_kso is not null
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
					$sWhere=" where lower(lvl3) like lower('".$sSearch."%') or lower(lvl3) like lower('%".$sSearch."%') or
									lower(uraian) like lower('".$sSearch."%') or lower(uraian) like lower('%".$sSearch."%') ";
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
					$row->nmtagihan,
					$row->lvl3,
					$row->uraian,
					$row->satuan,
					'<div style="text-align:right;">'.number_format($row->harga).'</div>',
					'<div style="text-align:right;">'.number_format($row->volume).'</div>',
					'<div style="text-align:right;">'.number_format($row->bobot).'</div>',
					'<div style="text-align:right;">'.number_format($row->total).'</div>',
					$aksi
				);
			}
			
			return response()->json($output);
			
		}
	}
	
	public function lvl4(Request $request, $id_kso)
	{
		if($id_kso!=='x'){
			
			$aColumns = array('id','nmtagihan','lvl4','uraian','satuan','harga','volume','bobot','total');
			/* Indexed column (used for fast and accurate table cardinality) */
			$sIndexColumn = "id";
			/* DB table to use */
			$sTable = "select  a.id,
								e.nmtagihan,
								a.lvl1||'.'||a.lvl2||'.'||a.lvl3||'.'||a.lvl4 as lvl4,
								a.uraian,
								b.satuan,
								a.harga,
								a.volume,
								a.bobot,
								nvl(c.total,0) as total
						from d_kso_teknis a
						left outer join t_satuan b on(a.id_satuan=b.id)
						left outer join(
							select  lvl1,
									lvl2,
									lvl3,
									lvl4,
									sum(harga*volume) as total
							from d_kso_teknis
							where id_kso=".$id_kso."
							group by lvl1,lvl2,lvl3,lvl4
						) c on(a.lvl1=c.lvl1 and a.lvl2=c.lvl2 and a.lvl3=c.lvl3 and a.lvl4=c.lvl4)
						left outer join(
							select	distinct
									id_kso
							from d_kso_user
							where id_user=".session('id_user')."
						) d on(a.id_kso=d.id_kso)
						left outer join t_tagihan e on(a.tagihan=e.kdtagihan)
						where a.id_kso=".$id_kso." and a.lvl=4 and d.id_kso is not null
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
					$sWhere=" where lower(lvl4) like lower('".$sSearch."%') or lower(lvl4) like lower('%".$sSearch."%') or
									lower(uraian) like lower('".$sSearch."%') or lower(uraian) like lower('%".$sSearch."%') ";
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
					$row->nmtagihan,
					$row->lvl4,
					$row->uraian,
					$row->satuan,
					'<div style="text-align:right;">'.number_format($row->harga).'</div>',
					'<div style="text-align:right;">'.number_format($row->volume).'</div>',
					'<div style="text-align:right;">'.number_format($row->bobot).'</div>',
					'<div style="text-align:right;">'.number_format($row->total).'</div>',
					$aksi
				);
			}
			
			return response()->json($output);
			
		}
	}
	
	public function lvl5(Request $request, $id_kso)
	{
		if($id_kso!=='x'){
			
			$aColumns = array('id','nmtagihan','lvl5','uraian','satuan','harga','volume','bobot','total');
			/* Indexed column (used for fast and accurate table cardinality) */
			$sIndexColumn = "id";
			/* DB table to use */
			$sTable = "select  a.id,
								e.nmtagihan,
								a.lvl1||'.'||a.lvl2||'.'||a.lvl3||'.'||a.lvl4||'.'||a.lvl5 as lvl5,
								a.uraian,
								b.satuan,
								a.harga,
								a.volume,
								a.bobot,
								nvl(c.total,0) as total
						from d_kso_teknis a
						left outer join t_satuan b on(a.id_satuan=b.id)
						left outer join(
							select  lvl1,
									lvl2,
									lvl3,
									lvl4,
									lvl5,
									sum(harga*volume) as total
							from d_kso_teknis
							where id_kso=".$id_kso."
							group by lvl1,lvl2,lvl3,lvl4,lvl5
						) c on(a.lvl1=c.lvl1 and a.lvl2=c.lvl2 and a.lvl3=c.lvl3 and a.lvl4=c.lvl4 and a.lvl5=c.lvl5)
						left outer join(
							select	distinct
									id_kso
							from d_kso_user
							where id_user=".session('id_user')."
						) d on(a.id_kso=d.id_kso)
						left outer join t_tagihan e on(a.tagihan=e.kdtagihan)
						where a.id_kso=".$id_kso." and a.lvl=5 and d.id_kso is not null
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
					$sWhere=" where lower(lvl5) like lower('".$sSearch."%') or lower(lvl5) like lower('%".$sSearch."%') or
									lower(uraian) like lower('".$sSearch."%') or lower(uraian) like lower('%".$sSearch."%') ";
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
					$row->nmtagihan,
					$row->lvl5,
					$row->uraian,
					$row->satuan,
					'<div style="text-align:right;">'.number_format($row->harga).'</div>',
					'<div style="text-align:right;">'.number_format($row->volume).'</div>',
					'<div style="text-align:right;">'.number_format($row->bobot).'</div>',
					'<div style="text-align:right;">'.number_format($row->total).'</div>',
					$aksi
				);
			}
			
			return response()->json($output);
			
		}
	}
	
	public function lvl6(Request $request, $id_kso)
	{
		if($id_kso!=='x'){
			
			$aColumns = array('id','nmtagihan','lvl6','uraian','satuan','harga','volume','bobot','total');
			/* Indexed column (used for fast and accurate table cardinality) */
			$sIndexColumn = "id";
			/* DB table to use */
			$sTable = "select  a.id,
								e.nmtagihan,
								a.lvl1||'.'||a.lvl2||'.'||a.lvl3||'.'||a.lvl4||'.'||a.lvl5||'.'||a.lvl6 as lvl6,
								a.uraian,
								b.satuan,
								a.harga,
								a.volume,
								a.bobot,
								nvl(c.total,0) as total
						from d_kso_teknis a
						left outer join t_satuan b on(a.id_satuan=b.id)
						left outer join(
							select  lvl1,
									lvl2,
									lvl3,
									lvl4,
									lvl5,
									lvl6,
									sum(harga*volume) as total
							from d_kso_teknis
							where id_kso=".$id_kso."
							group by lvl1,lvl2,lvl3,lvl4,lvl5,lvl6
						) c on(a.lvl1=c.lvl1 and a.lvl2=c.lvl2 and a.lvl3=c.lvl3 and a.lvl4=c.lvl4 and a.lvl5=c.lvl5 and a.lvl6=c.lvl6)
						left outer join(
							select	distinct
									id_kso
							from d_kso_user
							where id_user=".session('id_user')."
						) d on(a.id_kso=d.id_kso)
						left outer join t_tagihan e on(a.tagihan=e.kdtagihan)
						where a.id_kso=".$id_kso." and a.lvl=6 and d.id_kso is not null
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
					$sWhere=" where lower(lvl6) like lower('".$sSearch."%') or lower(lvl6) like lower('%".$sSearch."%') or
									lower(uraian) like lower('".$sSearch."%') or lower(uraian) like lower('%".$sSearch."%') ";
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
					$row->nmtagihan,
					$row->lvl6,
					$row->uraian,
					$row->satuan,
					'<div style="text-align:right;">'.number_format($row->harga).'</div>',
					'<div style="text-align:right;">'.number_format($row->volume).'</div>',
					'<div style="text-align:right;">'.number_format($row->bobot).'</div>',
					'<div style="text-align:right;">'.number_format($row->total).'</div>',
					$aksi
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
						a.lvl1,
						a.lvl2,
						a.lvl3,
						a.lvl4,
						a.lvl5,
						a.lvl6,
						a.uraian,
						a.id_satuan,
						nvl(a.harga,0) as harga,
						nvl(a.volume,0) as volume,
						nvl(a.bobot,0) as bobot,
						(nvl(a.harga,0)*nvl(a.volume,0)) as total,
						a.lvl||'-'||decode(a.lvl,b.lvl,'1','0') as lvl
				from d_kso_teknis a
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
	
	public function nilai(Request $request, $id)
	{
		try{
			$rows = DB::select("
				select  nvl(a.nilai,0) as nilai_kso,
						nvl(b.nilai,0) as nilai_teknis
				from(
					select  sum(nilai_uang+nilai_aset) as nilai
					from d_kso_pks
					where id_kso=?
				) a,
				(
					select  sum(harga*volume) as nilai
					from d_kso_teknis
					where id_kso=? and is_nilai=1
				) b
			",[
				$id,
				$id
			]);
			
			if(count($rows)>0){
				
				return '<div class="col-xs-12 col-sm-6">
							<div class="alert alert-success alert-dismissible">PKS : Rp. '.number_format($rows[0]->nilai_kso).',-</div>
						</div>
						<div class="col-xs-12 col-sm-6">
							<div class="alert alert-danger alert-dismissible">Dok.Teknis : Rp. '.number_format($rows[0]->nilai_teknis).',-</div>
						</div>
						<br>';
				
			}
			
		}
		catch(\Exception $e){
			return $e;
		}
	}
	
	private function getLvl1($id_kso)
	{
		$rows = DB::select("
			select  a.lvl1 as kode,
					a.uraian,
					b.satuan,
					a.harga,
					a.volume,
					a.bobot,
					nvl(c.total,0) as total
			from d_kso_teknis a
			left outer join t_satuan b on(a.id_satuan=b.id)
			left outer join(
				select  lvl1,
						sum(harga*volume) as total
				from d_kso_teknis
				where id_kso=".$id_kso."
				group by lvl1
			) c on(a.lvl1=c.lvl1)
			left outer join(
				select	distinct
						id_kso
				from d_kso_user
				where id_user=".session('id_user')."
			) d on(a.id_kso=d.id_kso)
			where a.id_kso=".$id_kso." and a.lvl=1 and d.id_kso is not null
			order by a.lvl1 asc
		");
		
		return $rows;
	}
	
	private function getLvl2($id_kso, $kode)
	{
		$rows = DB::select("
			select  a.lvl2 as kode,
					a.uraian,
					b.satuan,
					a.harga,
					a.volume,
					a.bobot,
					nvl(c.total,0) as total
			from d_kso_teknis a
			left outer join t_satuan b on(a.id_satuan=b.id)
			left outer join(
				select  lvl2,
						sum(harga*volume) as total
				from d_kso_teknis
				where id_kso=".$id_kso." and lvl1='".$kode."'
				group by lvl2
			) c on(a.lvl2=c.lvl2)
			left outer join(
				select	distinct
						id_kso
				from d_kso_user
				where id_user=".session('id_user')."
			) d on(a.id_kso=d.id_kso)
			where a.id_kso=".$id_kso." and a.lvl=2 and d.id_kso is not null and a.lvl1='".$kode."'
			order by a.lvl2 asc
		");
		
		return $rows;
	}
	
	private function getLvl3($id_kso, $kode)
	{
		$rows = DB::select("
			select  a.lvl3 as kode,
					a.uraian,
					b.satuan,
					a.harga,
					a.volume,
					a.bobot,
					nvl(c.total,0) as total
			from d_kso_teknis a
			left outer join t_satuan b on(a.id_satuan=b.id)
			left outer join(
				select  lvl3,
						sum(harga*volume) as total
				from d_kso_teknis
				where id_kso=".$id_kso." and lvl2='".$kode."'
				group by lvl3
			) c on(a.lvl3=c.lvl3)
			left outer join(
				select	distinct
						id_kso
				from d_kso_user
				where id_user=".session('id_user')."
			) d on(a.id_kso=d.id_kso)
			where a.id_kso=".$id_kso." and a.lvl=3 and d.id_kso is not null and a.lvl2='".$kode."'
			order by a.lvl3 asc
		");
		
		return $rows;
	}
	
	private function getLvl4($id_kso, $kode)
	{
		$rows = DB::select("
			select  a.lvl4 as kode,
					a.uraian,
					b.satuan,
					a.harga,
					a.volume,
					a.bobot,
					nvl(c.total,0) as total
			from d_kso_teknis a
			left outer join t_satuan b on(a.id_satuan=b.id)
			left outer join(
				select  lvl4,
						sum(harga*volume) as total
				from d_kso_teknis
				where id_kso=".$id_kso." and lvl3='".$kode."'
				group by lvl4
			) c on(a.lvl4=c.lvl4)
			left outer join(
				select	distinct
						id_kso
				from d_kso_user
				where id_user=".session('id_user')."
			) d on(a.id_kso=d.id_kso)
			where a.id_kso=".$id_kso." and a.lvl=4 and d.id_kso is not null and a.lvl3='".$kode."'
			order by a.lvl4 asc
		");
		
		return $rows;
	}
	
	private function getLvl5($id_kso, $kode)
	{
		$rows = DB::select("
			select  a.lvl5 as kode,
					a.uraian,
					b.satuan,
					a.harga,
					a.volume,
					a.bobot,
					nvl(c.total,0) as total
			from d_kso_teknis a
			left outer join t_satuan b on(a.id_satuan=b.id)
			left outer join(
				select  lvl5,
						sum(harga*volume) as total
				from d_kso_teknis
				where id_kso=".$id_kso." and lvl4='".$kode."'
				group by lvl5
			) c on(a.lvl5=c.lvl5)
			left outer join(
				select	distinct
						id_kso
				from d_kso_user
				where id_user=".session('id_user')."
			) d on(a.id_kso=d.id_kso)
			where a.id_kso=".$id_kso." and a.lvl=5 and d.id_kso is not null and a.lvl4='".$kode."'
			order by a.lvl5 asc
		");
		
		return $rows;
	}
	
	private function getLvl6($id_kso, $kode)
	{
		$rows = DB::select("
			select  a.lvl6 as kode,
					a.uraian,
					b.satuan,
					a.harga,
					a.volume,
					a.bobot,
					nvl(c.total,0) as total
			from d_kso_teknis a
			left outer join t_satuan b on(a.id_satuan=b.id)
			left outer join(
				select  lvl6,
						sum(harga*volume) as total
				from d_kso_teknis
				where id_kso=".$id_kso." and lvl5='".$kode."'
				group by lvl6
			) c on(a.lvl6=c.lvl6)
			left outer join(
				select	distinct
						id_kso
				from d_kso_user
				where id_user=".session('id_user')."
			) d on(a.id_kso=d.id_kso)
			where a.id_kso=".$id_kso." and a.lvl=6 and d.id_kso is not null and a.lvl5='".$kode."'
			order by a.lvl6 asc
		");
		
		return $rows;
	}
	
	public function tayang(Request $request, $id)
	{
		try{
			$rows = DB::select("
				select	b.id,
						b.lvl,
						b.nama,
						sum(a.nilai_uang+a.nilai_aset) as total
				from d_kso_pks a
				left outer join d_kso b on(a.id_kso=b.id)
				left outer join(
					select  distinct id_kso
					from d_kso_user
					where id_user=?
				) c on(a.id_kso=c.id_kso)
				where a.id_kso=? and c.id_kso is not null
				group by b.id,b.lvl,b.nama
			",[
				session('id_user'),
				$id
			]);
			
			if(count($rows)>0){
				
				$lvl = $rows[0]->lvl;
				
				$data = '<table class="table">
							<thead>
								<tr>
									<th>Kode</th>
									<th>Uraian</th>
									<th>Bobot</th>
									<th>Satuan</th>
									<th>Harga</th>
									<th>Volume</th>
									<th>Total</th>
								</tr>
							</thead>
							<tbody>
								<tr style="font-weight: bold;">
									<td colspan="6">Proyek '.$rows[0]->nama.'</td>
									<td style="text-align:right;">'.number_format($rows[0]->total).'</td>
								</tr>';
				
				if($lvl>=1){
					
					$rows1 = $this->getLvl1($id);
				
					if(count($rows1)>0){
					
						foreach($rows1 as $row1){
							
							$data .= '<tr style="font-weight: bold;">
										<td>'.$row1->kode.'</td>
										<td>'.$row1->uraian.'</td>
										<td style="text-align:right;">'.$row1->bobot.'</td>
										<td>'.$row1->satuan.'</td>
										<td style="text-align:right;">'.number_format($row1->harga).'</td>
										<td style="text-align:right;">'.number_format($row1->volume).'</td>
										<td style="text-align:right;">'.number_format($row1->total).'</td>
									  </tr>';
							
							if($lvl>=2){
					
								$rows2 = $this->getLvl2($id, $row1->kode);
							
								if(count($rows2)>0){
									
									foreach($rows2 as $row2){
										
										$data .= '<tr style="font-weight: bold;">
													<td style="padding-left:15px;">'.$row2->kode.'</td>
													<td style="padding-left:15px;">'.$row2->uraian.'</td>
													<td style="text-align:right;">'.$row2->bobot.'</td>
													<td>'.$row2->satuan.'</td>
													<td style="text-align:right;">'.number_format($row2->harga).'</td>
													<td style="text-align:right;">'.number_format($row2->volume).'</td>
													<td style="text-align:right;">'.number_format($row2->total).'</td>
												  </tr>';
												  
										if($lvl>=3){
											
											$rows3 = $this->getLvl3($id, $row2->kode);
											
											if(count($rows3)>0){
												
												foreach($rows3 as $row3){
										
													$data .= '<tr style="font-weight: bold;">
																<td style="padding-left:30px;">'.$row3->kode.'</td>
																<td style="padding-left:30px;">'.$row3->uraian.'</td>
																<td style="text-align:right;">'.$row3->bobot.'</td>
																<td>'.$row3->satuan.'</td>
																<td style="text-align:right;">'.number_format($row3->harga).'</td>
																<td style="text-align:right;">'.number_format($row3->volume).'</td>
																<td style="text-align:right;">'.number_format($row3->total).'</td>
															  </tr>';
															  
													if($lvl>=4){
											
														$rows4 = $this->getLvl4($id, $row3->kode);
														
														if(count($rows4)>0){
															
															foreach($rows4 as $row4){
																
																$data .= '<tr style="font-weight: bold;">
																			<td style="padding-left:45px;">'.$row4->kode.'</td>
																			<td style="padding-left:45px;">'.$row4->uraian.'</td>
																			<td style="text-align:right;">'.$row4->bobot.'</td>
																			<td>'.$row4->satuan.'</td>
																			<td style="text-align:right;">'.number_format($row4->harga).'</td>
																			<td style="text-align:right;">'.number_format($row4->volume).'</td>
																			<td style="text-align:right;">'.number_format($row4->total).'</td>
																		  </tr>';
																		  
																if($lvl>=5){
											
																	$rows5 = $this->getLvl5($id, $row4->kode);
																	
																	if(count($rows5)>0){
																		
																		foreach($rows5 as $row5){
																			
																			$data .= '<tr style="font-weight: bold;">
																						<td style="padding-left:60px;">'.$row5->kode.'</td>
																						<td style="padding-left:60px;">'.$row5->uraian.'</td>
																						<td style="text-align:right;">'.$row5->bobot.'</td>
																						<td>'.$row5->satuan.'</td>
																						<td style="text-align:right;">'.number_format($row5->harga).'</td>
																						<td style="text-align:right;">'.number_format($row5->volume).'</td>
																						<td style="text-align:right;">'.number_format($row5->total).'</td>
																					  </tr>';
																					  
																			if($lvl>=6){
											
																				$rows6 = $this->getLvl6($id, $row5->kode);
																				
																				if(count($rows6)>0){
																					
																					foreach($rows6 as $row6){
																						
																						$data .= '<tr style="font-weight: bold;">
																									<td style="padding-left:75px;">'.$row6->kode.'</td>
																									<td style="padding-left:75px;">'.$row6->uraian.'</td>
																									<td style="text-align:right;">'.$row6->bobot.'</td>
																									<td>'.$row6->satuan.'</td>
																									<td style="text-align:right;">'.number_format($row6->harga).'</td>
																									<td style="text-align:right;">'.number_format($row6->volume).'</td>
																									<td style="text-align:right;">'.number_format($row6->total).'</td>
																								  </tr>';
																								  
																					}
																					
																				}
																				
																			}
																					  
																		}
																	
																	}
																	
																}
																
															}
															
														}
														
													}
													
												}
												
											}
											
										}
										
									}
									
								}
								
							}
							
						}
						
					}
					
				}
				
				$data .= '</tbody></table>';
				
				return $data;
				
			}
			else{
				return 'Data tidak ditemukan!';
			}
			
		}
		catch(\Exception $e){
			return $e;
		}
	}
	
	public function cetak(Request $request, $id)
	{
		try{
			$rows = DB::select("
				select	a.*
				from d_kso_teknis a
				left outer join d_kso b on(a.id_kso=b.id)
				left outer join t_satuan c on(a.id_satuan=c.id)
				where a.id_kso=?
				order by a.nourut asc
			",[
				$id
			]);
			
			if(count($rows)>0){
				
				var_dump($rows);
				
			}
			else{
				return 'Data tidak ditemukan!';
			}
			
		}
		catch(\Exception $e){
			return $e;
		}
	}
	
	public function simpan(Request $request)
	{
		try{
			$arr_lvl = explode("-", $request->input('lvl'));
			$lvl = $arr_lvl[0];
			$is_nilai = $arr_lvl[1];
			$lanjut = true;
			
			for($i=1;$i<=$lvl;$i++){
				
				if($request->input('lvl'.$lvl)==null || $request->input('lvl'.$lvl)==''){
					$lanjut = false;
				}
				
			}
			
			if($is_nilai=='1'){
				
				if($request->input('id_satuan')==null || $request->input('id_satuan')=='' ||
				   $request->input('harga')==null || $request->input('harga')=='' ||
				   $request->input('volume')==null || $request->input('volume')=='' ||
				   $request->input('bobot')==null || $request->input('bobot')==''
				){
					$lanjut = false;
				}
				
			}
			
			if($lanjut){
				
				if($request->input('inp-rekambaru')=='1'){
				
					$rows = DB::select("
						SELECT	count(*) AS jml
						from d_kso_teknis
						where id_kso=? and lvl1=? and lvl2=? and lvl3=? and lvl4=? and lvl5=? and lvl6=?
					",[
						$request->input('id_kso'),
						$request->input('lvl1'),
						$request->input('lvl2'),
						$request->input('lvl3'),
						$request->input('lvl4'),
						$request->input('lvl5'),
						$request->input('lvl6')
					]);
					
					if($rows[0]->jml==0){
						
						$insert = DB::insert("
							INSERT INTO d_kso_teknis(
								id_kso,
								tagihan,
								lvl,
								lvl1,
								lvl2,
								lvl3,
								lvl4,
								lvl5,
								lvl6,
								uraian,
								id_satuan,
								harga,
								volume,
								bobot,
								id_user
							)
							VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)
						",[
							$request->input('id_kso'),
							$request->input('tagihan'),
							$lvl,
							$request->input('lvl1'),
							$request->input('lvl2'),
							$request->input('lvl3'),
							$request->input('lvl4'),
							$request->input('lvl5'),
							$request->input('lvl6'),
							$request->input('uraian'),
							$request->input('id_satuan'),
							str_replace(",", "", $request->input('harga')),
							str_replace(",", "", $request->input('volume')),
							str_replace(",", "", $request->input('bobot')),
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
						return 'Kode ini sudah ada!';
					}
					
				}
				else{
					
					$update = DB::update("
						update d_kso_teknis
						set tagihan=?,
							lvl=?,
							lvl1=?,
							lvl2=?,
							lvl3=?,
							lvl4=?,
							lvl5=?,
							lvl6=?,
							uraian=?,
							id_satuan=?,
							harga=?,
							volume=?,
							bobot=?,
							id_user=?,
							updated_at=sysdate
						where id=?
					",[
						$request->input('tagihan'),
						$lvl,
						$request->input('lvl1'),
						$request->input('lvl2'),
						$request->input('lvl3'),
						$request->input('lvl4'),
						$request->input('lvl5'),
						$request->input('lvl6'),
						$request->input('uraian'),
						$request->input('id_satuan'),
						str_replace(",", "", $request->input('harga')),
						str_replace(",", "", $request->input('volume')),
						str_replace(",", "", $request->input('bobot')),
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
			else{
				return 'Kolom tidak dapat dikosongkan!';
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
				delete from d_kso_teknis
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