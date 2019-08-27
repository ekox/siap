<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PenerimaanRekamController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','nmunit','nama','nmtrans','bukti','tgsetor','uraian','nilai','status');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							d.nmunit,
							e.nama,
							h.nmtrans,
							a.nobukti||'<br>'||to_char(a.tgbukti,'dd-mm-yyyy') as bukti,
							to_char(a.tgsetor,'dd-mm-yyyy') as tgsetor,
							a.uraian,
							a.nilai,
							b.nmalur||'<br>'||g.nmlevel||'<br>'||c.nmstatus as status
					from d_terima a
					left outer join t_alur b on(a.id_alur=b.id)
					left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
					left outer join t_unit d on(a.kdunit=d.kdunit)
					left outer join t_pelanggan e on(a.id_pelanggan=e.id)
					left outer join t_level g on(c.kdlevel=g.kdlevel)
					left outer join t_trans h on(a.kdtran=h.id)
					where a.thang='".session('tahun')."' and a.kdunit='".session('kdunit')."'
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
				$sWhere=" where lower(bukti) like lower('".$sSearch."%') or lower(bukti) like lower('%".$sSearch."%') or
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
			if(session('kdlevel')=='11'){
				$aksi='<center>
							<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
							<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
								<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
								<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
							</div>
						</center>';
			}
			
			$output['aaData'][] = array(
				$row->no,
				$row->nmunit,
				$row->nama,
				$row->nmtrans,
				$row->bukti,
				$row->tgsetor,
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
			select  id,
					kdunit,
					id_alur,
					kdtran,
					id_pelanggan,
					nopks,
					to_char(tgpks,'yyyy-mm-dd') as tgpks,
					to_char(tgjtempo,'yyyy-mm-dd') as tgjtempo,
					uraian,
					nilai
			from d_tagih
			where id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$detil = $rows[0];
			$data['error'] = false;
			$data['message'] = $detil;
			
		}
		else{
			$data['error'] = true;
			$data['message'] = 'Data header tidak ditemukan!';
		}
		
		return response()->json($data);
	}
	
	public function simpan(Request $request)
	{
		DB::beginTransaction();
			
		if($request->input('inp-rekambaru')=='1'){
			
			$id_terima = DB::table('d_terima')->insertGetId([
				'kdunit' => session('kdunit'),
				'thang' => session('tahun'),
				'id_alur' => $request->input('id_alur'),
				'id_tagih' => $request->input('id_tagih'),
				'kdtran' => $request->input('kdtran'),
				'id_pelanggan' => $request->input('id_pelanggan'),
				'nobukti' => $request->input('nobukti'),
				'tgbukti' => $request->input('tgbukti'),
				'tgsetor' => $request->input('tgsetor'),
				'nilai' => str_replace(',', '', $request->input('nilai')),
				'uraian' => $request->input('uraian'),
				'status' => 1,
				'id_user' => session('id_user')
			]);
			
			if($id_terima){
				
				$insert = DB::insert("
					insert into d_terima_dok(id_terima,id_dok,nmfile)
					select	".$id_terima.",
							id_dok,
							nmfile
					from d_terima_dok_temp
					where id_user=?
				",[
					session('id_user')
				]);
				
				if($insert){
					
					$delete = DB::delete("
						delete from d_terima_dok_temp
						where id_user=?
					",[
						session('id_user')
					]);
					
					if($delete){
						DB::commit();
						return 'success';
					}
					else{
						return 'Data temporari gagal dihapus!';
					}
					
				}
				else{
					return 'Data dokumen gagal disimpan!';
				}
				
			}
			else{
				return 'Data gagal disimpan!';
			}
			
		}
		else{
			$update = DB::update("
				update d_terima
				set kdtran=?,
					id_tagih=?,
					id_pelanggan=?,
					nobukti=?,
					tgbukti=?,
					tgsetor=?,
					uraian=?,
					nilai=?,
					id_user=?,
					updated_at=sysdate
				where id=?
			",[
				$request->input('kdtran'),
				$request->input('id_tagih'),
				$request->input('id_pelanggan'),
				$request->input('nobukti'),
				$request->input('tgbukti'),
				$request->input('tgsetor'),
				$request->input('uraian'),
				str_replace(',', '', $request->input('nilai')),
				session('id_user'),
				$request->input('inp-id')
			]);
			
			if($update){
				DB::commit();
				return 'success';
			}
			else{
				return 'Data gagal diubah!';
			}
		}
	}
	
	public function hapus(Request $request)
	{
		DB::beginTransaction();
			
		$rows = DB::select("
			select	count(rowid) as jml
			from d_tagih
			where id=? and status=1
		",[
			$request->input('id')
		]);
		
		if($rows[0]->jml==1){
			
			$delete = DB::delete("
				delete from d_tagih
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
		else{
			return 'Data tidak dapat dihapus karena sudah diproses!';
		}	
	}
	
	public function dok(Request $request, $kdtran)
	{
		$rows = DB::select("
			select  b.id,
					b.uraian,
					b.ukuran,
					b.tipe
			from t_trans_dok a
			left outer join t_dok b on(a.id_dok=b.id)
			where a.id_trans=?
			order by a.id asc
		",[
			$kdtran
		]);
		
		$data = '';
		foreach($rows as $row){
			$data .= '<div class="form-group row">
						<label class="col-md-2 label-control" for="uraian">'.$row->uraian.' ('.$row->ukuran.'MB|'.$row->tipe.')</label>
						<div class="col-md-9">
							<span class="btn btn-primary fileinput-button">
								<i class="fa fa-upload"></i>
								<span>Browse File</span>
								<input id="fileupload'.$row->id.'" type="file" name="file">
							</span>
							<!-- The global progress bar -->
							<div id="files'.$row->id.'" class="files"></div>
							<div id="progress'.$row->id.'" class="progress">
								<div class="progress-bar progress-bar-danger"></div>
							</div>
						</div>
					</div>';
					
			$data .= "
					<script>
						jQuery('#fileupload".$row->id."').click(function(){
							jQuery('#progress".$row->id." .progress-bar').css('width', 0);
							jQuery('#progress".$row->id." .progress-bar').html('');
							jQuery('#nmfile".$row->id."').html('');
						});
						
						jQuery.get('token', function(result){
							
							//upload adk
							jQuery('#fileupload".$row->id."').fileupload({
								url:'penerimaan/rekam/upload/".$row->id."',
								dataType: 'json',
								formData:{
									_token: result
								},
								done: function (e, data) {
									jQuery('#nmfile".$row->id."').html(data.files[0].name);
									alertify.log('Data berhasil diupload!');
								},
								error: function(error) {
									alertify.log(error.responseText);
								},
								progressall: function (e, data) {
									var progress = parseInt(data.loaded / data.total * 100, 10);
									jQuery('#progress".$row->id." .progress-bar').css('width',progress + '%');
								}
							}).prop('disabled', !$.support.fileInput)
							  .parent().addClass($.support.fileInput ? undefined : 'disabled');
							
						});
					</script>";
		}
		
		return $data;
	}
	
	public function tagihan(Request $request, $id_tagih)
	{
		$rows = DB::select("
			select  *
			from d_tagih
			where id=?
		",[
			$id_tagih
		]);
		
		if(count($rows)>0){
			return response()->json($rows[0]);
		}
	}
	
	public function upload(Request $request, $id_dok)
	{
		$targetFolder = 'data/lampiran/'; // Relative to the root
		
		$rows = DB::select("
			select	*
			from t_dok
			where id=?
		",[
			$id_dok
		]);
		
		if(count($rows)>0){
			
			$ukuran = (int)$rows[0]->ukuran;
			$arr_tipe = explode(",", $rows[0]->tipe);
			
			if(!empty($_FILES)) {
				$file_name = $_FILES['file']['name'];
				$tempFile = $_FILES['file']['tmp_name'];
				$targetFile = $targetFolder.$file_name;
				$fileTypes = $arr_tipe; // File extensions
				$fileParts = pathinfo($_FILES['file']['name']);
				$fileSize = $_FILES['file']['size'];
				//type file sesuai..??	
				if(in_array($fileParts['extension'],$fileTypes)) {
					
					//isi kosong..??
					if($fileSize>0){
						
						if($fileSize<=$ukuran*1000000){
							
							$now = new \DateTime();
							$tglupload = $now->format('YmdHis');
							
							$file_name_baru = md5($tglupload).'.'.$fileParts['extension'];
							move_uploaded_file($tempFile,$targetFolder.$file_name_baru);
							
							if(file_exists($targetFolder.$file_name_baru)){
								
								$insert = DB::insert("
									insert into d_terima_dok_temp(id_dok,nmfile,id_user)
									values(?,?,?)
								",[
									$id_dok,
									$file_name_baru,
									session('id_user')
								]);
								
								if($insert){
									return '1';
								}
								else{
									return 'Dokumen gagal disimpan ke temporari!';
								}
								
							}
							else{
								return 'File gagal diupload!';
							}
							
						}
						else{
							return 'File melebihi batas ukuran maksimal!';
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
		else{
			return 'Setting dokumen tidak ditemukan!';
		}
	}
	
}