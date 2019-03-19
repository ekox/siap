<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KSOPKSController extends Controller {

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
			$arr_where[] = " d.id_kso is not null ";
		}
		
		if(count($arr_where)>0){
			$where = "where ".implode(" and ", $arr_where);
		}
		
		$aColumns = array('id','id_kso_pks','nama_kso','nama_owner','nmdirut','porsi','nilai_uang','nilai_aset','jml1','jml2');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  	a.id,
							a.id as id_kso_pks,
							b.nama as nama_kso,
							c.nama as nama_owner,
							c.nmdirut,
							round((a.nilai_uang+a.nilai_aset)/nvl(g.total,1)*100) as porsi,
							a.nilai_uang,
							a.nilai_aset,
							nvl(e.jml,0) as jml1,
							nvl(f.jml,0) as jml2
					from d_kso_pks a
					left outer join d_kso b on(a.id_kso=b.id)
					left outer join t_perusahaan c on(a.id_owner=c.id)
					left outer join(
						select distinct id_kso
						from d_kso_user
						where id_user=".session('id_user')."
					) d on(a.id_kso=d.id_kso)
					left outer join(
						select	a.id_kso_pks,
								count(*) as jml
						from d_kso_pks_org a
						group by a.id_kso_pks
					) e on(a.id=e.id_kso_pks)
					left outer join(
						select	a.id_kso_pks,
								count(*) as jml
						from d_kso_pks_dtl a
						group by a.id_kso_pks
					) f on(a.id=f.id_kso_pks)
					left outer join(
						select	id_kso,
								sum(nilai_uang+nilai_aset) as total
						from d_kso_pks
						group by id_kso
					) g on(a.id_kso=g.id_kso)
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
								lower(nama_owner) like lower('".$sSearch."%') or lower(nama_owner) like lower('%".$sSearch."%') ";
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
				$aksi='<center style="width:100px;">
							<div class="dropdown pull-right">
								<button type="button" class="btn btn-danger btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
	                                <i class="material-icons">add</i>
	                                <span class="caret"></span>
	                            </button>
	                            <ul class="dropdown-menu">
	                                <li><a id="'.$row->id.'" href="javascript:void(0);" class="tambah1">Tambah Struktur</a></li>
	                                <li><a id="'.$row->id.'" href="javascript:void(0);" class="tambah2">Tambah Jadwal</a></li>
	                            </ul>
	                        </div>
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
				$row->id,
				$row->no,
				$row->nama_kso,
				$row->nama_owner,
				$row->nmdirut,
				'<div style="text-align:right;">'.number_format($row->porsi).'%</div>',
				'<div style="text-align:right;">'.number_format($row->nilai_uang).'</div>',
				'<div style="text-align:right;">'.number_format($row->nilai_aset).'</div>',
				'<div style="text-align:right;">'.number_format($row->jml1).'</div>',
				'<div style="text-align:right;">'.number_format($row->jml2).'</div>',
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
						a.id_owner,
						a.porsi,
						a.nilai_uang,
						a.nilai_aset
				from d_kso_pks a
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
					from d_kso_pks
					where id_kso=? and id_owner=?
				",[
					$request->input('id_kso'),
					$request->input('id_owner'),
				]);
				
				if($rows[0]->jml==0){
					
					$insert = DB::insert("
						INSERT INTO d_kso_pks(
							id_kso,id_owner,porsi,nilai_uang,nilai_aset,id_user,created_at,updated_at
						)
						VALUES (?,?,?,?,?,?,sysdate,sysdate)
					",[
						$request->input('id_kso'),
						$request->input('id_owner'),
						str_replace(",", "", $request->input('porsi')),
						str_replace(",", "", $request->input('nilai_uang')),
						str_replace(",", "", $request->input('nilai_aset')),
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
					return 'NIK ini sudah ada!';
				}
				
			}
			else{
				
				$update = DB::update("
					update d_kso_pks
					set porsi=?,
						nilai_uang=?,
						nilai_aset=?,
						id_user=?,
						updated_at=sysdate
					where id=?
				",[
					str_replace(",", "", $request->input('porsi')),
					str_replace(",", "", $request->input('nilai_uang')),
					str_replace(",", "", $request->input('nilai_aset')),
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
				delete from d_kso_pks
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
	
	public function detil1(Request $request, $param)
	{
		try{
			$rows = DB::select("
				select  a.id,
						b.nmjab,
						a.nama,
						a.nik
				from d_kso_pks_org a
				left outer join t_jabatan b on(a.kdjab=b.kdjab)
				where a.id_kso_pks=?
				order by a.kdjab asc
			",[
				$param
			]);
			
			$data = '<legend>Struktur</legend>
					 <table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>No</th>
								<th>Jabatan</th>
								<th>Nama</th>
								<th>NIK</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>';
			
			$aksi = '';
			if(session('kdlevel')=='00' || session('kdlevel')=='01'){
				$aksi = '<a href="javascript:;" id="'.$row->id.'" class="btn btn-xs btn-danger hapus1"><i class="material-icons">delete_forever</i></a>';
			}
			
			$i = 1;
			foreach($rows as $row){
				
				$data .= '<tr>
							<td>'.$i++.'</td>
							<td>'.$row->nmjab.'</td>
							<td>'.$row->nama.'</td>
							<td>'.$row->nik.'</td>
							<td>
								<center>
									<!--<a href="javascript:;" id="'.$row->id.'" class="btn btn-xs btn-success ubah-detil1"><i class="material-icons">mode_edit</i></a>-->
									'.$aksi.'
								</center>
							</td>
						 </tr>';
				
			}
			
			$rows = DB::select("
				select  a.id,
						a.tahap,
						a.tahun,
						b.nmbulan,
						a.porsi
				from d_kso_pks_dtl a
				left outer join t_bulan b on(a.bulan=b.bulan)
				where a.id_kso_pks=?
				order by a.tahap asc
			",[
				$param
			]);
			
			$data .= '	</tbody>
					 </table>
					 <br>
					 <legend>Jadwal Setoran</legend>
					 <table class="table table-bordered table-striped">
						<thead>
							<tr>
								<th>No</th>
								<th>Tahap</th>
								<th>Tahun</th>
								<th>Bulan</th>
								<th>Porsi</th>
								<th>Aksi</th>
							</tr>
						</thead>
						<tbody>';
						
			$aksi = '';
			if(session('kdlevel')=='00' || session('kdlevel')=='01'){
				$aksi = '<a href="javascript:;" id="'.$row->id.'" class="btn btn-xs btn-danger hapus1"><i class="material-icons">delete_forever</i></a>';
			}
						
			$i = 1;
			foreach($rows as $row){
				
				$data .= '<tr>
							<td>'.$i++.'</td>
							<td>'.$row->tahap.'</td>
							<td>'.$row->tahun.'</td>
							<td>'.$row->nmbulan.'</td>
							<td>'.$row->porsi.'</td>
							<td>
								<center>
									<!--<a href="javascript:;" id="'.$row->id.'" class="btn btn-xs btn-success ubah-detil2"><i class="material-icons">mode_edit</i></a>-->
									'.$aksi.'
								</center>
							</td>
						 </tr>';
				
			}
			
			$data .= '	</tbody>
					 </table>';
			
			return $data;
						
		}
		catch(\Exception $e){
			return $e;
			return 'Terdapat kesalahan lainnya, hubungi Administrator!';
		}		
	}
	
	public function simpan1(Request $request)
	{
		try{
			if($request->input('inp-rekambaru')=='1'){
				
				$rows = DB::select("
					SELECT	count(*) AS jml
					from d_kso_pks_org
					where id_kso_pks=? and nik=?
				",[
					$request->input('id_kso_pks'),
					$request->input('nik'),
				]);
				
				if($rows[0]->jml==0){
					
					$insert = DB::insert("
						INSERT INTO d_kso_pks_org(
							id_kso_pks,kdjab,nama,nik
						)
						VALUES (?,?,?,?)
					",[
						$request->input('id_kso_pks'),
						$request->input('kdjab'),
						$request->input('nama'),
						$request->input('nik')
					]);
					
					if($insert){
						return 'success';
					}
					else{
						return 'Data gagal disimpan!';
					}
					
				}
				else{
					return 'Owner ini sudah ada!';
				}
				
			}
			else{
				
				$update = DB::update("
					update d_kso_pks_org
					set kdjab=?,
						nama=?
					where id=?
				",[
					$request->input('kdjab'),
					$request->input('nama'),
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
	
	public function hapus1(Request $request)
	{
		try{
			$delete = DB::delete("
				delete from d_kso_pks_org
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
	
	public function simpan2(Request $request)
	{
		try{
			if($request->input('inp-rekambaru')=='1'){
				
				$rows = DB::select("
					SELECT	count(*) AS jml
					from d_kso_pks_dtl
					where id_kso_pks=? and tahap=?
				",[
					$request->input('id_kso_pks'),
					$request->input('tahap'),
				]);
				
				if($rows[0]->jml==0){
					
					$insert = DB::insert("
						INSERT INTO d_kso_pks_dtl(
							id_kso_pks,tahap,tahun,bulan,porsi
						)
						VALUES (?,?,?,?,?)
					",[
						$request->input('id_kso_pks'),
						$request->input('tahap'),
						$request->input('tahun'),
						$request->input('bulan'),
						$request->input('porsi'),
					]);
					
					if($insert){
						return 'success';
					}
					else{
						return 'Data gagal disimpan!';
					}
					
				}
				else{
					return 'Tahapan ini sudah ada!';
				}
				
			}
			else{
				
				$update = DB::update("
					update d_kso_pks_dtl
					set tahun=?,
						bulan=?,
						porsi=?
					where id=?
				",[
					$request->input('tahun'),
					$request->input('bulan'),
					$request->input('porsi'),
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
	
	public function hapus2(Request $request)
	{
		try{
			$delete = DB::delete("
				delete from d_kso_pks_dtl
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