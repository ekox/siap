<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class KSOProyekController extends Controller {

	public function index(Request $request)
	{
		$where = "";
		if(session('kdlevel')!=='00'){
			$where = "where b.id_kso is not null";
		}
		
		$aColumns = array('id','nama','npwp','nopks','tgpks','tanggal','uraian','nilai','nmfile_pks');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  	a.id,
							a.nama,
							a.npwp,
							a.nopks,
							to_char(a.tgpks,'dd-mm-yyyy') as tgpks,
							to_char(a.tgawal,'dd-mm-yyyy')||' s.d. '||to_char(a.tgakhir,'dd-mm-yyyy') as tanggal,
							a.uraian,
							nvl(c.nilai,0) as nilai,
							a.nmfile_pks
					from d_kso a
					left outer join(
						select distinct id_kso
						from d_kso_user
						where id_user=".session('id_user')."
					) b on(a.id=b.id_kso)
					left outer join(
						select	id_kso,
								sum(nilai_uang+nilai_aset) as nilai
						from d_kso_pks
						group by id_kso
					) c on(a.id=c.id_kso)
					".$where."
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
				$row->nopks,
				$row->tgpks,
				$row->tanggal,
				$row->uraian,
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
				select  a.id,
						a.nama,
						a.npwp,
						a.nopks,
						to_char(a.tgpks,'yyyy-mm-dd') as tgpks,
						to_char(a.tgawal,'yyyy-mm-dd') as tgawal,
						to_char(a.tgakhir,'yyyy-mm-dd') as tgakhir,
						a.uraian,
						a.lt,
						a.lb,
						a.alamat,
						a.telp,
						a.email,
						a.website,
						a.nmfile_pks,
						a.lvl,
						a.aktif
				from d_kso a
				where a.id=?
			",[
				$id
			]);
			
			if(count($rows)>0){
				
				session(['upload_pks'=>null]);
				if($rows[0]->nmfile_pks!==null && $rows[0]->nmfile_pks!==''){
					session(['upload_pks'=>$rows[0]->nmfile_pks]);
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
            $destinationPath = 'data/kso/pks/';

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
				$fileTypes = ['pdf','PDF','jpg','JPG','png','PNG'];
				
				// cek type file	
				if (in_array($fileParts['extension'],$fileTypes)) {
					//cek ukuran file
					if($fileSize>0){
						
						$newFileName = md5(time()).'.'.$fileParts['extension'];
						
						$localUpload = move_uploaded_file($tempFile, $destinationPath.$newFileName);
						if($localUpload){
							session(['upload_pks'=>$newFileName]);
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
			if(session('upload_pks')!==null && session('upload_pks')!==''){
				$nmfile = session('upload_pks');
			}
			
			if($request->input('inp-rekambaru')=='1'){
				
				$rows = DB::select("
					SELECT	count(*) AS jml
					from d_kso
					where npwp=? or nopks=?
				",[
					$request->input('npwp'),
					$request->input('nopks'),
				]);
				
				if($rows[0]->jml==0){
					
					$insert = DB::insert("
						INSERT INTO d_kso(
							nama,npwp,nopks,tgpks,tgawal,tgakhir,uraian,lt,lb,alamat,telp,email,website,aktif,nmfile_pks,id_user,created_at,updated_at,lvl
						)
						VALUES (?,?,?,to_date(?,'yyyy-mm-dd'),to_date(?,'yyyy-mm-dd'),to_date(?,'yyyy-mm-dd'),?,?,?,?,?,?,?,?,?,?,sysdate,sysdate,?)
					",[
						$request->input('nama'),
						$request->input('npwp'),
						$request->input('nopks'),
						$request->input('tgpks'),
						$request->input('tgawal'),
						$request->input('tgakhir'),
						$request->input('uraian'),
						$request->input('lt'),
						$request->input('lb'),
						$request->input('alamat'),
						$request->input('telp'),
						$request->input('email'),
						$request->input('website'),
						$request->input('aktif'),
						session('upload_pks'),
						session('id_user'),
						$request->input('lvl')
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
					update d_kso
					set nama=?,
						nopks=?,
						tgpks=to_date(?,'yyyy-mm-dd'),
						tgawal=to_date(?,'yyyy-mm-dd'),
						tgakhir=to_date(?,'yyyy-mm-dd'),
						uraian=?,
						lt=?,
						lb=?,
						alamat=?,
						telp=?,
						email=?,
						website=?,
						aktif=?,
						nmfile_pks=?,
						id_user=?,
						updated_at=sysdate,
						lvl=?
					where id=?
				",[
					$request->input('nama'),
					$request->input('nopks'),
					$request->input('tgpks'),
					$request->input('tgawal'),
					$request->input('tgakhir'),
					$request->input('uraian'),
					$request->input('lt'),
					$request->input('lb'),
					$request->input('alamat'),
					$request->input('telp'),
					$request->input('email'),
					$request->input('website'),
					$request->input('aktif'),
					session('upload_pks'),
					session('id_user'),
					$request->input('lvl'),
					$request->input('inp-id')
				]);
				
				if($update){
					session(['upload_pks'=>null]);
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
				delete from d_kso
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