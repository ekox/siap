<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TransaksiRekamController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','notrans','nmalur','nama','nobukti','tgbukti','uraian','nilai');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  	a.id as id_trans,
							a.id,
							a.notrans,
							a.thang,
							d.nmunit,
							b.nmalur,
							e.nama,
							a.nobukti,
							to_char(a.tgbukti,'dd-mm-yyyy') as tgbukti,
							a.uraian,
							nvl(f.nilai,0) as nilai
					from d_trans a
					left outer join t_alur b on(a.id_alur=b.id)
					left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
					left outer join t_unit d on(a.kdunit=d.kdunit)
					left outer join t_penerima e on(a.id_penerima=e.id)
					left outer join(
						select  id_trans,
								sum(nilai) as nilai
						from d_trans_akun
						group by id_trans
					) f on(a.id=f.id_trans)
					where a.kdunit='".session('kdunit')."' and a.thang='".session('tahun')."'
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
								<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
								<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
								<a class="dropdown-item" href="transaksi/rekam/download/'.$row->id.'" target="_blank">Lampiran</a>
							</div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->id,
				$row->notrans,
				$row->nmalur,
				$row->nama,
				$row->nobukti,
				$row->tgbukti,
				$row->uraian,
				'<div style="text-align:right;">'.number_format($row->nilai).'</div>',
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function nomor(Request $request)
	{
		$rows = DB::select("
			select  count(*) as jml
			from d_trans
			where thang=? and kdunit=?
		",[
			session('tahun'),
			session('kdunit')
		]);
		
		$nourut = $rows[0]->jml+1;
		
		$notrans = session('tahun').session('kdunit').str_pad($nourut, 5, '0', STR_PAD_LEFT);
		
		return $notrans;
	}
	
	public function pilih(Request $request, $id)
	{
		$rows = DB::select("
			select  id,
					notrans,
					id_alur,
					id_output,
					id_penerima,
					nobukti,
					to_char(tgbukti,'yyyy-mm-dd') as tgbukti,
					uraian
			from d_trans
			where id=?
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
			
			if(count($rows)>0){
				session(['upload_lampiran' => $rows[0]->nmfile]);
			}
			
			$rows = DB::select("
				select	kdakun,
						nilai
				from d_trans_akun
				where id_trans=?
			",[
				$id
			]);
			
			if(count($rows)>0){
				$data['error'] = false;
				$data['message'] = $detil;
				$data['akun'] = $rows;
				$data['x'] = count($rows);
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
	
	public function detil(Request $request, $id)
	{
		$rows = DB::select("
			select	a.kdakun,
					b.nmakun,
					a.nilai
			from d_trans_akun a
			left outer join t_akun b on(a.kdakun=b.kdakun)
			where a.id_trans=?
			order by a.kdakun asc
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$detil = '<table class="table table-bordered">
						<thead>
							<tr>
								<th>No</th>
								<th>Akun</th>
								<th>Uraian</th>
								<th>Nilai</th>
							</tr>
						</thead>
						<tbody>';
			$i = 1;
			foreach($rows as $row){
				$detil .= '<tr>
								<td>'.$i++.'</td>
								<td>'.$row->kdakun.'</td>
								<td>'.$row->nmakun.'</td>
								<td style="text-align:right;">'.number_format($row->nilai).'</td>
						   </tr>';
			}
			
			$detil .= '</tbody></table>';
			
			$data['error'] = false;
			$data['message'] = $detil;
			
		}
		else{
			$data['error'] = true;
			$data['message'] = 'Data akun tidak ditemukan!';
		}
		
		return response()->json($data);
	}
	
	public function simpan(Request $request)
	{
		if(count($request->input('rincian'))>0){
			
			$arr_rincian = $request->input('rincian');
			
			DB::beginTransaction();
			
			if($request->input('inp-rekambaru')=='1'){
			
				$rows = DB::select("
					SELECT	count(*) AS jml
					from d_trans
					where notrans=?
				",[
					$request->input('notrans'),
				]);
				
				if($rows[0]->jml==0){
					
					$id_trans = DB::table('d_trans')->insertGetId([
						'thang' => session('tahun'),
						'kdunit' => session('kdunit'),
						'notrans' => $request->input('notrans'),
						'id_alur' => $request->input('id_alur'),
						'id_output' => $request->input('id_output'),
						'id_penerima' => $request->input('id_penerima'),
						'nobukti' => $request->input('nobukti'),
						'tgbukti' => $request->input('tgbukti'),
						'uraian' => $request->input('uraian'),
						'status' => 1,
						'id_user' => session('id_user')
					]);
					
					if($id_trans){
						
						if(session('upload_lampiran')!=='' && session('upload_lampiran')!==null){
							
							$delete = DB::delete("
								delete from d_trans_dok
								where id_trans=?
							",[
								$id_trans
							]);
							
							$insert = DB::insert("
								insert into d_trans_dok(id_trans,id_dok,nmfile)
								values(?,?,?)
							",[
								$id_trans,
								1,
								session('upload_lampiran')
							]);
							
						}
						
						foreach($request->input('rincian') as $input){
							$arr_insert[] = "select ".$id_trans.",'".$input["'kdakun'"]."',".str_replace(",", "", $input["'nilai'"]).",".session('id_user')." from dual";
						}
						
						$delete = DB::delete("
							delete from d_trans_akun
							where id_trans=?
						",[
							$id_trans
						]);
						
						$insert = DB::insert("
							insert into d_trans_akun(id_trans,kdakun,nilai,id_user)
							".implode(" union all ", $arr_insert)."
						");
						
						if($insert){
							DB::commit();
							session(['upload_lampiran' => null]);
							return 'success';
						}
						else{
							return 'Data akun gagal disimpan!';
						}
						
					}
					else{
						return 'Data header gagal disimpan!';
					}
					
				}
				else{
					return 'Duplikasi data!';
				}
				
			}
			else{
				
				$update = DB::update("
					update d_trans
					set id_alur=?,
						id_output=?,
						id_penerima=?,
						nobukti=?,
						tgbukti=?,
						uraian=?,
						id_user=?,
						updated_at=sysdate
					where id=?
				",[
					$request->input('id_alur'),
					$request->input('id_output'),
					$request->input('id_penerima'),
					$request->input('nobukti'),
					$request->input('tgbukti'),
					$request->input('uraian'),
					session('id_user'),
					$request->input('inp-id')
				]);
				
				foreach($request->input('rincian') as $input){
					$arr_insert[] = "select ".$request->input('inp-id').",'".$input["'kdakun'"]."',".str_replace(",", "", $input["'nilai'"]).",".session('id_user')." from dual";
				}
				
				if(session('upload_lampiran')!=='' && session('upload_lampiran')!==null){
							
					$delete = DB::delete("
						delete from d_trans_dok
						where id_trans=?
					",[
						$request->input('inp-id')
					]);
					
					$insert = DB::insert("
						insert into d_trans_dok(id_trans,id_dok,nmfile)
						values(?,?,?)
					",[
						$request->input('inp-id'),
						1,
						session('upload_lampiran')
					]);
					
				}
				
				$delete = DB::delete("
					delete from d_trans_akun
					where id_trans=?
				",[
					$request->input('inp-id')
				]);
				
				$insert = DB::insert("
					insert into d_trans_akun(id_trans,kdakun,nilai,id_user)
					".implode(" union all ", $arr_insert)."
				");
				
				if($insert){
					session(['upload_lampiran' => null]);
					DB::commit();
					return 'success';
				}
				else{
					return 'Data gagal disimpan!';
				}
				
			}
			
		}
		else{
			return 'Anda belum memilih kode akun!';
		}		
	}
	
	public function hapus(Request $request)
	{
		try{
			DB::beginTransaction();
			
			$delete = DB::delete("
				delete from d_trans_akun
				where id_trans=?
			",[
				$request->input('id')
			]);
			
			$delete = DB::delete("
				delete from d_trans_dok
				where id_trans=?
			",[
				$request->input('id')
			]);
			
			$delete = DB::delete("
				delete from d_trans_histori
				where id_trans=?
			",[
				$request->input('id')
			]);
			
			$delete = DB::delete("
				delete from d_trans
				where id=?
			",[
				$request->input('id')
			]);
			
			if($delete==true) {
				DB::commit();
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
	
	public function upload(Request $request)
	{
		$targetFolder = 'data/lampiran/'; // Relative to the root
			
		if(!empty($_FILES)) {
			$file_name = $_FILES['file']['name'];
			$tempFile = $_FILES['file']['tmp_name'];
			$targetFile = $targetFolder.$file_name;
			$fileTypes = ['pdf', 'PDF', 'jpg', 'JPG', 'jpeg', 'JPEG', 'png', 'PNG', 'doc', 'DOC', 'docx', 'DOCX', 'xls', 'XLS', 'xlsx', 'XLSX']; // File extensions
			$fileParts = pathinfo($_FILES['file']['name']);
			$fileSize = $_FILES['file']['size'];
			//type file sesuai..??	
			if(in_array($fileParts['extension'],$fileTypes)) {
				
				//isi kosong..??
				if($fileSize>0){
					
					$now = new \DateTime();
					$tglupload = $now->format('YmdHis');
					
					$file_name_baru = md5($tglupload).'.'.$fileParts['extension'];
					move_uploaded_file($tempFile,$targetFolder.$file_name_baru);
					
					if(file_exists($targetFolder.$file_name_baru)){
						
						session(['upload_lampiran' => $file_name_baru]);
						return '1';
						
					}
					else{
						return 'File gagal diupload!';
					}
					
				}
				else{
					return 'Isi file kosong, periksa data anda.';
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
	
	public function download(Request $request, $id)
	{
		$rows = DB::select("
			select	*
			from d_trans_dok
			where id_trans=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$path='data/lampiran/';

			$log = $path.$rows[0]->nmfile;
								
			header('Content-Description:Berkas Lampiran');
			header('Content-Type:application/octet-stream');
			header('Content-Disposition:attachment;filename=' . basename($rows[0]->nmfile));
			header('Content-Transfer-Encoding:binary');
			header('Expires:0');
			header('Cahce-Control:must-revalidate');
			header('Pragma:public');
			header('Content-Length:'.filesize($log));
			readfile($log);
			
		}
		else{
			return 'Data tidak ditemukan!';
		}
	}
	
}