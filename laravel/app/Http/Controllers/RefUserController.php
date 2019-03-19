<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RefUserController extends Controller {

	public function index(Request $request)
	{
		try{
			$aColumns = array('id','username','nama','nik','nmlevel','nmperusahaan','aktif');
			/* Indexed column (used for fast and accurate table cardinality) */
			$sIndexColumn = "id";
			/* DB table to use */
			$sTable = "SELECT  	a.id,
								a.username,
								a.nama,
								a.nik,
								b.nmlevel,
								c.nmperusahaan,
								decode(a.aktif,'1','Aktif','Tidak Aktif') AS aktif
						FROM t_user a
						LEFT OUTER JOIN(
							select  a.id_user,
									a.kdlevel,
									b.nmlevel
							from t_user_level a
							left outer join t_level b on(a.kdlevel=b.kdlevel)
							where a.status='1'
						) b ON(a.id=b.id_user)
						LEFT OUTER JOIN(
							select  a.id_user,
									c.nama as nmperusahaan 
							from t_user_usaha a
							left outer join t_perusahaan c on(a.id_perusahaan=c.id)
							where a.status='1'
						) c ON(a.id=c.id_user)
						ORDER BY a.id DESC";
			
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
					$sWhere=" where lower(nama) like lower('".$sSearch."%') or lower(nama) like lower('%".$sSearch."%') or
									lower(nik) like lower('".$sSearch."%') or lower(nik) like lower('%".$sSearch."%') or
									lower(username) like lower('".$sSearch."%') or lower(username) like lower('%".$sSearch."%')";
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
					$row->username,
					$row->nama,
					$row->nik,
					$row->nmlevel,
					$row->nmperusahaan,
					$row->aktif,
					$aksi
				);
			}
			
			return response()->json($output);
		}
		catch(\Exception $e){
			return $e;
			//return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function pilih(Request $request, $id)
	{
		try{
			$rows = DB::select("
				select	id,
						nama,
						nik,
						username,
						email,
						aktif
				from t_user
				where id=?
			",[
				$id
			]);
			
			if(count($rows)>0){
				
				$data = (array)$rows[0];
				
				$rows = DB::select("
					select	*
					from t_user_level
					where id_user=?
				",[
					$id
				]);
				
				$arr_level = array();
				foreach($rows as $row){
					$arr_level[] = $row->kdlevel;
				}
				
				$data['kdlevel'] = implode(",", $arr_level);
				
				$rows = DB::select("
					select	*
					from t_user_usaha
					where id_user=?
				",[
					$id
				]);
				
				$arr_usaha = array();
				foreach($rows as $row){
					$arr_usaha[] = $row->id_perusahaan;
				}
				
				$data['id_perusahaan'] = implode(",", $arr_usaha);
				
				$data['error'] = false;
				$data['message'] = $rows[0];
				return response()->json($data);
				
			}
			else{
				$data['error'] = true;
				$data['message'] = 'Data tidak ditemukan!';
				return response()->json($data);
			}
			
		}
		catch(\Exception $e){
			return $e;
		}
	}
	
	public function simpan(Request $request)
	{
		try{
			DB::beginTransaction();
			
			if(count($request->input('kdlevel'))>0){
				
				if($request->input('inp-rekambaru')=='1'){
				
					$password = md5('p4ssw0rd!');
					
					$rows = DB::select("
						SELECT	count(*) AS jml
						from t_user
						where username=?
					",[
						$request->input('username')
					]);
					
					if($rows[0]->jml==0){
						
						$id_user = DB::table('t_user')->insertGetId(
							array(
								'username' => $request->input('username'),
								'pass' => $password,
								'nama' => $request->input('nama'),
								'nik' => $request->input('nik'),
								'email' => $request->input('email'),
								'aktif' => '1',
								'foto' => 'no-image.png',
							)
						);
						
						if($id_user) {
							
							$arr_level = $request->input('kdlevel');
							
							$arr_insert = array();
							for($i=0;$i<count($arr_level);$i++){
								$aktif_level = '0';
								if($i==0){
									$aktif_level = '1';
								}
								$arr_insert[] = "select ".$id_user.",'".$arr_level[$i]."','".$aktif_level."' from dual";
							}
							
							$insert = DB::insert("
								insert into t_user_level(id_user,kdlevel,status)
								".implode(" union all ", $arr_insert)."
							");
							
							if($insert){
								
								if(count($request->input('id_perusahaan'))>0){
									
									$arr_perusahaan = $request->input('id_perusahaan');
							
									$arr_insert1 = array();
									for($j=0;$j<count($arr_perusahaan);$j++){
										$aktif_usaha = '0';
										if($j==0){
											$aktif_usaha = '1';
										}
										$arr_insert1[] = "select ".$id_user.",'".$arr_perusahaan[$j]."','".$aktif_usaha."' from dual";
									}
									
									$insert1 = DB::insert("
										insert into t_user_usaha(id_user,id_perusahaan,status)
										".implode(" union all ", $arr_insert1)."
									");
									
									if($insert){
										DB::commit();
										return 'success';
									}
									else{
										return 'Perusahaan gagal disimpan!';
									}
									
								}
								else{
									DB::commit();
									return 'success';
								}
								
							}
							else{
								return 'Level gagal disimpan!';
							}
							
						}
						else{
							return 'Proses simpan gagal. Hubungi Administrator.';
						}
					
					}
					else{
						return 'Username ini sudah ada!';
					}
					
				}
				else{
					
					$update = DB::update("
						update t_user
						set nik=?,
							nama=?,
							email=?,
							aktif=?
						where id=?
					",[
						$request->input('nik'),
						$request->input('nama'),
						$request->input('email'),
						$request->input('aktif'),
						$request->input('inp-id')
					]);
					
					if($update){
						
						$id_user = $request->input('inp-id');
						$arr_level = $request->input('kdlevel');
							
						$arr_insert = array();
						for($i=0;$i<count($arr_level);$i++){
							$aktif_level = '0';
							if($i==0){
								$aktif_level = '1';
							}
							$arr_insert[] = "select ".$id_user.",'".$arr_level[$i]."','".$aktif_level."' from dual";
						}
						
						$delete = DB::delete("
							delete from t_user_level
							where id_user=?
						",[
							$id_user
						]);
						
						$insert = DB::insert("
							insert into t_user_level(id_user,kdlevel,status)
							".implode(" union all ", $arr_insert)."
						");
						
						if($insert){
							
							if(count($request->input('id_perusahaan'))>0){
								
								$arr_perusahaan = $request->input('id_perusahaan');
						
								$arr_insert1 = array();
								for($j=0;$j<count($arr_perusahaan);$j++){
									$aktif_usaha = '0';
									if($j==0){
										$aktif_usaha = '1';
									}
									$arr_insert1[] = "select ".$id_user.",'".$arr_perusahaan[$j]."','".$aktif_usaha."' from dual";
								}
								
								$delete1 = DB::delete("
									delete from t_user_usaha
									where id_user=?
								",[
									$id_user
								]);
								
								$insert1 = DB::insert("
									insert into t_user_usaha(id_user,id_perusahaan,status)
									".implode(" union all ", $arr_insert1)."
								");
								
								if($insert){
									DB::commit();
									return 'success';
								}
								else{
									return 'Perusahaan gagal disimpan!';
								}
								
							}
							else{
								DB::commit();
								return 'success';
							}
							
						}
						else{
							return 'Level gagal disimpan!';
						}
						
					}
					else{
						return 'Data gagal diubah!';
					}
					
				}
				
			}
			else{
				return 'Level belum dipilih';
			}
						
		}
		catch(\Exception $e){
			return $e;
			return 'Koneksi terputus!';
		}		
	}
	
	public function hapus(Request $request)
	{
		try{
			DB::beginTransaction();
			
			$rows = DB::select("
				select	count(*) as jml
				from d_kso_user
				where id_user=?
			",[
				$request->input('id')
			]);
			
			if($rows[0]->jml==0){
				
				$delete = DB::delete("
					delete from t_user_level
					where id_user=?
				",[
					$request->input('id')
				]);
				
				$delete = DB::delete("
					delete from t_user_usaha
					where id_user=?
				",[
					$request->input('id')
				]);
				
				$delete = DB::delete("
					delete from t_user
					where id=?
				",[
					$request->input('id')
				]);
				
				if($delete==true){
					DB::commit();
					return 'success';
				}
				else {
					return 'Proses hapus gagal. Hubungi Administrator.';
				}
				
			}
			else{
				return 'User sudah terdaftar di KSO, data tidak dapat dihapus!';
			}
			
		}
		catch(\Exception $e){
			return $e;
		}		
	}
	
}