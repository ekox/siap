<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KSOTeknisController extends Controller {

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
			$arr_where[] = " c.id_kso is not null ";
		}
		
		if(count($arr_where)>0){
			$where = "where ".implode(" and ", $arr_where);
		}
		
		$aColumns = array('id','nama_kso','lvl','kode','uraian','nourut','satuan','harga','volume','bobot');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  	a.id,
							b.nama as nama_kso,
							a.lvl,
							a.lvl1||'.'||a.lvl2||'.'||a.lvl3||'.'||a.lvl4||'.'||a.lvl5||'.'||a.lvl6 as kode,
							a.uraian,
							a.nourut,
							d.satuan,
							a.harga,
							a.volume,
							a.bobot
					from d_kso_teknis a
					left outer join d_kso b on(a.id_kso=b.id)
					left outer join(
						select distinct id_kso
						from d_kso_user
						where id_user=".session('id_user')."
					) c on(a.id_kso=c.id_kso)
					left outer join t_satuan d on(a.id_satuan=d.id)
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
				$row->lvl,
				$row->kode,
				$row->uraian,
				$row->nourut,
				$row->satuan,
				'<div style="text-align:right;">'.number_format($row->harga).'</div>',
				'<div style="text-align:right;">'.number_format($row->volume).'</div>',
				'<div style="text-align:right;">'.number_format($row->bobot).'</div>',
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
						a.id_user,
						a.kode,
						a.uraian,
						a.nourut,
						a.is_nilai,
						a.id_satuan,
						nvl(a.harga,0) as harga,
						nvl(a.volume,0) as volume,
						nvl(a.bobot,0) as bobot,
						(nvl(a.harga,0)*nvl(a.volume,0)) as total
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
	
	private function getDokTeknis($id_kso, $parent_id = 0)
	{
		$rows = DB::select("
			select  a.id,
					a.kode,
					a.uraian,
					c.satuan,
					a.bobot,
					a.volume,
					a.harga,
					(a.volume*a.harga) as total
			from d_kso_teknis a
			left outer join d_kso b on(a.id_kso=b.id)
			left outer join t_satuan c on(a.id_satuan=c.id)
			left outer join(
				select  distinct id_kso
				from d_kso_user
				where id_user=?
			) d on(a.id_kso=d.id_kso)
			where a.id_kso=? and d.id_kso is not null and nvl(a.parent_id,0)=?
			order by a.nourut asc
		",[
			session('id_user'),
			$id_kso,
			$parent_id
		]);
		
		$data = '';
		
		foreach($rows as $row){
			$data .= '<tr style="font-weight: bold;">
						<td>'.$row->kode.'</td>
						<td>'.$row->uraian.'</td>
						<td style="text-align:right;">'.$row->bobot.'</td>
						<td>'.$row->satuan.'</td>
						<td style="text-align:right;">'.number_format($row->harga).'</td>
						<td style="text-align:right;">'.number_format($row->volume).'</td>
						<td style="text-align:right;">'.number_format($row->total).'</td>
					  </tr>';
		}
		
		
		return $rows;
	}
	
	public function tayang(Request $request, $id)
	{
		try{
			$rows = DB::select("
				select	b.id,
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
				group by b.id,b.nama
			",[
				session('id_user'),
				$id
			]);
			
			if(count($rows)>0){
				
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
				
				$rows = $this->getDokTeknis($id, 0);
				
				if(count($rows)>0){
									
					foreach($rows as $row){
						
						$data .= '<tr style="font-weight: bold;">
									<td>'.$row->kode.'</td>
									<td>'.$row->uraian.'</td>
									<td style="text-align:right;">'.$row->bobot.'</td>
									<td>'.$row->satuan.'</td>
									<td style="text-align:right;">'.number_format($row->harga).'</td>
									<td style="text-align:right;">'.number_format($row->volume).'</td>
									<td style="text-align:right;">'.number_format($row->total).'</td>
								  </tr>';
						
						$rows1 = $this->getDokTeknis($id, $row->id);
						
						if(count($rows1)>0){
							
							foreach($rows1 as $row1){
								
								$data .= '<tr>
											<td style="padding-left:15px;">'.$row1->kode.'</td>
											<td style="padding-left:15px;">'.$row1->uraian.'</td>
											<td style="text-align:right;">'.$row1->bobot.'</td>
											<td>'.$row1->satuan.'</td>
											<td style="text-align:right;">'.number_format($row1->harga).'</td>
											<td style="text-align:right;">'.number_format($row1->volume).'</td>
											<td style="text-align:right;">'.number_format($row1->total).'</td>
										  </tr>';
										  
								$rows2 = $this->getDokTeknis($id, $row1->id);
								
								if(count($rows2)>0){
									
									foreach($rows2 as $row2){
										
										$data .= '<tr>
													<td style="padding-left:30px">'.$row2->kode.'</td>
													<td style="padding-left:30px">'.$row2->uraian.'</td>
													<td style="text-align:right;">'.$row2->bobot.'</td>
													<td>'.$row2->satuan.'</td>
													<td style="text-align:right;">'.number_format($row2->harga).'</td>
													<td style="text-align:right;">'.number_format($row2->volume).'</td>
													<td style="text-align:right;">'.number_format($row2->total).'</td>
												  </tr>';
										
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
			if($request->input('inp-rekambaru')=='1'){
				
				$rows = DB::select("
					SELECT	count(*) AS jml
					from d_kso_teknis
					where id_kso=? and kode=?
				",[
					$request->input('id_kso'),
					$request->input('id_user')
				]);
				
				if($rows[0]->jml==0){
					
					$insert = DB::insert("
						INSERT INTO d_kso_teknis(
							id_kso,
							parent_id,
							kode,
							uraian,
							nourut,
							is_nilai,
							id_satuan,
							harga,
							volume,
							bobot,
							id_user,
							created_at,
							updated_at
						)
						VALUES (?,?,?,?,?,?,?,?,?,?,?,sysdate,sysdate)
					",[
						$request->input('id_kso'),
						$request->input('parent_id'),
						$request->input('kode'),
						$request->input('uraian'),
						str_replace(",", "", $request->input('nourut')),
						$request->input('is_nilai'),
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
					return 'User ini sudah ada!';
				}
				
			}
			else{
				
				$update = DB::update("
					update d_kso_teknis
					set kode=?,
						parent_id=?,
						uraian=?,
						nourut=?,
						is_nilai=?,
						id_satuan=?,
						harga=?,
						volume=?,
						bobot=?,
						id_user=?,
						updated_at=sysdate
					where id=?
				",[
					$request->input('kode'),
					$request->input('parent_id'),
					$request->input('uraian'),
					str_replace(",", "", $request->input('nourut')),
					$request->input('is_nilai'),
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