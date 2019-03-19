<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RefPerusahaanController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','nama','npwp','siup','nmjenis','owner','mk','qs','kontraktor');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							a.nama,
							a.npwp,
							a.siup,
							b.nmjenis,
							decode(a.owner,'1','ya','tidak') as owner,
							decode(a.mk,'1','ya','tidak') as mk,
							decode(a.qs,'1','ya','tidak') as qs,
							decode(a.kontraktor,'1','ya','tidak') as kontraktor
					from t_perusahaan a
					left outer join t_jenis_usaha b on(a.kdjenis=b.kdjenis)
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
				$sWhere=" where lower(nama) like lower('".$sSearch."%') or lower(nama) like lower('%".$sSearch."%') or
								lower(npwp) like lower('".$sSearch."%') or lower(npwp) like lower('%".$sSearch."%') ";
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
				$row->nama,
				$row->npwp,
				$row->siup,
				$row->nmjenis,
				$row->owner,
				$row->mk,
				$row->qs,
				$row->kontraktor,
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
						a.nama,
						a.npwp,
						a.nmdirut,
						a.kdjenis,
						a.siup,
						a.alamat,
						a.telp,
						a.email,
						a.website,
						a.owner,
						a.mk,
						a.qs,
						a.kontraktor,
						a.logo
				from t_perusahaan a
				where a.id=?
			",[
				$id
			]);
			
			if(count($rows)>0){
				
				session(['upload_logo'=>null]);
				if($rows[0]->logo!==null && $rows[0]->logo!==''){
					session(['upload_logo'=>$rows[0]->logo]);
				}
				
				return response()->json($rows[0]);
				
			}
			
		}
		catch(\Exception $e){
			return $e;
		}
	}
	
	public function upload(Request $request)
	{
        try {
            $destinationPath = 'data/logo/';

            //cek folder
            $listing = file_exists($destinationPath);
			if(!$listing){
				mkdir($destinationPath,0777,true);
			}
			
			//cek file
			if (!empty($_FILES)) {
				
				$fileName = $_FILES['file']['name'];
				$tempFile = $_FILES['file']['tmp_name'];
				$fileParts = pathinfo($fileName);
				$targetFile = $destinationPath.$fileName;
				$fileSize = $_FILES['file']['size'];
				$fileTypes = ['jpg','JPG','png','PNG'];
				
				// cek type file	
				if (in_array($fileParts['extension'],$fileTypes)) {
					//cek ukuran file
					if($fileSize>0){
						
						$newFileName = md5(time()).'.'.$fileParts['extension'];
						
						$localUpload = move_uploaded_file($tempFile, $destinationPath.$newFileName);
						if($localUpload){
							session(['upload_logo'=>$newFileName]);
							return '1';
						}
						else{
							return 'File gagal diupload ke local storage.';
						}
					}
					else{
						return 'Isi file kosong, periksa data Anda.';
					}
				}
				else{
					return 'Tipe file tidak sesuai.';
				}
			}
			else{
				return 'Tidak ada file yang diupload.';
			}
        }
		catch(\Exception $e) {
			return 'Terdapat kesalahan lainnya, hubungi Administrator!';
		}
	}
	
	public function simpan(Request $request)
	{
		try{
			$nmfile = '';
			if(session('upload_logo')!==null && session('upload_logo')!==''){
				$nmfile = session('upload_logo');
			}
			
			if($request->input('inp-rekambaru')=='1'){
				
				$rows = DB::select("
					SELECT	count(*) AS jml
					from t_perusahaan
					where npwp=?
				",[
					$request->input('npwp')
				]);
				
				if($rows[0]->jml==0){
					
					$insert = DB::insert("
						INSERT INTO t_perusahaan(
							nama,npwp,kdjenis,siup,nmdirut,alamat,telp,email,website,owner,mk,qs,kontraktor,logo
						)
						VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)
					",[
						$request->input('nama'),
						$request->input('npwp'),
						$request->input('kdjenis'),
						$request->input('siup'),
						$request->input('nmdirut'),
						$request->input('alamat'),
						$request->input('telp'),
						$request->input('email'),
						$request->input('website'),
						$request->input('owner'),
						$request->input('mk'),
						$request->input('qs'),
						$request->input('kontraktor'),
						$nmfile
					]);
					
					if($insert){
						session(['upload_pks'=>null]);
						return 'success';
					}
					else{
						return 'Data gagal disimpan!';
					}
					
				}
				else{
					return 'Nomor PKS atau NPWP ini sudah ada!';
				}
				
			}
			else{
				
				$update = DB::update("
					update t_perusahaan
					set nama=?,
						kdjenis=?,
						siup=?,
						nmdirut=?,
						alamat=?,
						telp=?,
						email=?,
						website=?,
						owner=?,
						mk=?,
						qs=?,
						kontraktor=?,
						logo=?
					where id=?
				",[
					$request->input('nama'),
					$request->input('kdjenis'),
					$request->input('siup'),
					$request->input('nmdirut'),
					$request->input('alamat'),
					$request->input('telp'),
					$request->input('email'),
					$request->input('website'),
					$request->input('owner'),
					$request->input('mk'),
					$request->input('qs'),
					$request->input('kontraktor'),
					$nmfile,
					$request->input('inp-id')
				]);
				
				if($update){
					session(['upload_logo'=>null]);
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
				delete from t_perusahaan
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