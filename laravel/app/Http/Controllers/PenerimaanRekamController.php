<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PenerimaanRekamController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','nmunit','nama','nmtrans','pks','tgjtempo','nilai','status','lampiran');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  	a.id,
							d.nmunit,
							e.nama,
							h.nmtrans,
							a.nodok||'<br>'||to_char(a.tgdok,'dd-mm-yyyy') as pks,
							to_char(a.tgdok1,'dd-mm-yyyy') as tgjtempo,
							a.uraian,
							nvl(f.nilai,0) as nilai,
							c.nmstatus as status,
							i.lampiran
					from d_trans a
					left outer join t_alur b on(a.id_alur=b.id)
					left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
					left outer join t_unit d on(a.kdunit=d.kdunit)
					left outer join t_penerima e on(a.id_penerima=e.id)
					left outer join t_level g on(c.kdlevel=g.kdlevel)
					left outer join t_trans h on(a.kdtran=h.id)
					left outer join(
						select	id_trans,
								sum(nilai) as nilai
						from d_trans_akun
						where kddk='D'
						group by id_trans
					) f on(a.id=f.id_trans)
					left outer join(
						select  a.id_trans,
								rtrim(xmlagg(xmlelement(e, a.id||'|'||b.uraian, ',')).extract('//text()').getclobval(), ',') as lampiran
						from d_trans_dok a
						left outer join t_dok b on(a.id_dok=b.id)
						group by a.id_trans
					) i on(a.id=i.id_trans)
					where b.menu=2 and a.thang='".session('tahun')."' and a.kdunit='".session('kdunit')."'
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
				$sWhere=" where lower(nopks) like lower('".$sSearch."%') or lower(nopks) like lower('%".$sSearch."%') or
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
								<a id="'.$row->id.'" class="dropdown-item upload" href="javascript:;">Upload Lampiran</a>
							</div>
						</center>';
			}
			
			$lampiran = '<ul>';
			if($row->lampiran!==''){
				
				$arr_lampiran = explode(',', $row->lampiran);
				for($i=0;$i<count($arr_lampiran);$i++){
					
					$arr_dok = explode('|', $arr_lampiran[$i]);
					if(count($arr_dok)>1){
						
						$lampiran .= '<li>'.$arr_dok[1].'
										<a id="'.$arr_dok[0].'" href="javascript:;" class="hapus-dok" title="Hapus Lampiran"><i class="fa fa-times"></i></a>
										<a href="penerimaan/rekam/download/'.$arr_dok[0].'" target="_blank" title="Download Lampiran"><i class="fa fa-download"></i></a>
									 </li>';
						
					}
					
				}
				
			}
			$lampiran .= '</ul>';
			
			$output['aaData'][] = array(
				$row->no,
				$row->nmunit,
				$row->nama,
				$row->nmtrans,
				$row->pks,
				'<div style="text-align:right;">'.number_format($row->nilai).'</div>',
				$row->status,
				$lampiran,
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function pilih(Request $request, $id)
	{
		$rows = DB::select("
			select  a.id,
					a.kdunit,
					a.id_alur,
					a.kdtran,
					nvl(a.id_proyek,'') as id_proyek,
					a.id_penerima as id_pelanggan,
					a.nodok as nopks,
					to_char(a.tgdok,'yyyy-mm-dd') as tgpks,
					to_char(a.tgdok1,'yyyy-mm-dd') as tgjtempo,
					a.uraian,
					nvl(b.nilai,0) as nilai,
					nvl(d.nilai,0) as ppn,
					nvl(b.nilai,0)+nvl(d.nilai,0) as total,
					nvl(b.kdakun,'') as debet,
					nvl(c.kdakun,'') as kredit,
					nvl(a.parent_id,0) as parent_id
			from d_trans a
			left outer join(
				select	id_trans,
						kdakun,
						nilai
				from d_trans_akun
				where kddk='D' and grup is null
			) b on(a.id=b.id_trans)
			left outer join(
				select	id_trans,
						kdakun,
						nilai
				from d_trans_akun
				where kddk='K'
			) c on(a.id=c.id_trans)
			left outer join(
				select	id_trans,
						kdakun,
						nilai
				from d_trans_akun
				where kddk='D' and grup is not null
			) d on(a.id=d.id_trans)
			where a.id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$detil = $rows[0];
			
			$rows = DB::select("
				select  a.*
				from t_akun a,
				(
					select  kdakun,
							panjang
					from t_trans_akun
					where id_trans=? and kddk='D'
				) b
				where a.lvl=6 and substr(a.kdakun,1,b.panjang)=substr(b.kdakun,1,b.panjang)
				order by a.kdakun asc
			",[
				$detil->kdtran
			]);
			
			$dropdown = '<option value="" style="display:none;">Pilih Data</option>';
			foreach($rows as $row){
				$selected = '';
				if($row->kdakun==$detil->debet){
					$selected = 'selected';
				}
				$dropdown .= '<option value="'.$row->kdakun.'" '.$selected.'> '.$row->nmakun.'</option>';
			}
			
			$data['dropdown_d'] = $dropdown;
			
			$rows = DB::select("
				select  a.*
				from t_akun a,
				(
					select  kdakun,
							panjang
					from t_trans_akun
					where id_trans=? and kddk='K'
				) b
				where a.lvl=6 and substr(a.kdakun,1,b.panjang)=substr(b.kdakun,1,b.panjang)
				order by a.kdakun asc
			",[
				$detil->kdtran
			]);
			
			$dropdown = '<option value="" style="display:none;">Pilih Data</option>';
			foreach($rows as $row){
				$selected = '';
				if($row->kdakun==$detil->kredit){
					$selected = 'selected';
				}
				$dropdown .= '<option value="'.$row->kdakun.'" '.$selected.'> '.$row->nmakun.'</option>';
			}
			
			$data['dropdown_k'] = $dropdown;
			
			$rows = DB::select("
				select	*
				from t_proyek
				order by nmproyek asc
			");
			
			$dropdown = '<option value="" style="display:none;">Pilih Data</option>';
			foreach($rows as $row){
				$dropdown .= '<option value="'.$row->id.'-'.$row->id_penerima.'"> '.$row->nmproyek.' : Rp.'.number_format($row->nilai).',-</option>';
			}
			
			$data['dropdown_p'] = $dropdown;
			
			$dropdown = '';
			
			if($detil->parent_id!==''){
				$rows = DB::select("
					select	a.id,
							a.nodok as nopks,
							to_char(a.tgdok,'dd-mm-yyyy') as tgpks,
							a.id_penerima as id_pelanggan,
							a.uraian,
							nvl(c.nilai,0) as nilai,
							c.kdakun,
							a.id_proyek
					from d_trans a
					left outer join d_trans_akun c on(a.id=c.id_trans)
					where a.id=? and c.kddk='D'
				",[
					$detil->parent_id
				]);
				
				$dropdown = '<option value="" style="display:none;">Pilih Data</option>';
				foreach($rows as $row){
					$dropdown .= '<option value="'.$row->id.'|'.$row->id_pelanggan.'|'.$row->uraian.'|'.$row->nilai.'|'.$row->kdakun.'|'.$row->id_proyek.'-'.$row->id_pelanggan.'" selected> PKS : '.$row->nopks.', '.$row->tgpks.', Rp. '.number_format($row->nilai).',-</option>';
				}
			}
			
			$data['tagihan'] = $dropdown;
			
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
		
		$id_proyek = '';
		if($request->input('id_proyek')!==''){
			$arr_proyek = explode("-", $request->input('id_proyek'));
			$id_proyek = $arr_proyek[0];
		}
			
		if($request->input('inp-rekambaru')=='1'){
			
			$arr_parent = explode("|", $request->input('parent_id'));
			$parent_id = $arr_parent[0];
			
			$id_trans = DB::table('d_trans')->insertGetId([
				'kdunit' => session('kdunit'),
				'thang' => session('tahun'),
				'id_alur' => $request->input('id_alur'),
				'kdtran' => $request->input('kdtran'),
				'id_proyek' => $id_proyek,
				'id_penerima' => $request->input('id_pelanggan'),
				'nodok' => $request->input('nopks'),
				'tgdok' => $request->input('tgpks'),
				'tgdok1' => $request->input('tgjtempo'),
				'uraian' => $request->input('uraian'),
				'parent_id' => $parent_id,
				'status' => 1,
				'id_user' => session('id_user')
			]);
			
			if($id_trans){
				
				$insert = DB::insert("
					insert into d_trans_akun(id_trans,kdakun,kddk,nilai)
					select	?,
							?,
							'D',
							?
					from dual
					union all
					select	?,
							?,
							'K',
							?
					from dual
				",[
					$id_trans,
					$request->input('debet'),
					str_replace(',', '', $request->input('nilai')),
					$id_trans,
					$request->input('kredit'),
					str_replace(',', '', $request->input('nilai')),
				]);
				
				if($insert){
					
					$ppn = (int)str_replace(',', '', $request->input('ppn'));
					if($ppn>0){
						
						$now = new \DateTime();
						$grup = $now->format('YmdHis');
						
						$insert = DB::insert("
							insert into d_trans_akun(id_trans,kdakun,kddk,nilai,grup)
							select	?,
									?,
									'D',
									?,
									?
							from dual
							union all
							select	?,
									?,
									'K',
									?,
									?
							from dual
						",[
							$id_trans,
							'721000',
							$ppn,
							$grup,
							$id_trans,
							$request->input('kredit'),
							$ppn,
							$grup
						]);
						
					}
					
					if($insert){
						DB::commit();
						return 'success';
					}
					else{
						return 'Pajak gagal disimpan!';
					}
					
				}
				else{
					return 'Data detil akun gagal disimpan!';
				}
				
			}
			else{
				return 'Data gagal disimpan!';
			}
			
		}
		else{
			$update = DB::update("
				update d_trans
				set kdtran=?,
					id_proyek=?,
					id_penerima=?,
					nodok=?,
					tgdok=?,
					tgdok1=?,
					uraian=?,
					id_user=?,
					updated_at=sysdate
				where id=?
			",[
				$request->input('kdtran'),
				$id_proyek,
				$request->input('id_pelanggan'),
				$request->input('nopks'),
				$request->input('tgpks'),
				$request->input('tgjtempo'),
				$request->input('uraian'),
				session('id_user'),
				$request->input('inp-id')
			]);
			
			if($update){
				
				$delete = DB::delete("
					delete from d_trans_akun
					where id_trans=?
				",[
					$request->input('inp-id')
				]);
				
				if($delete){
					
					$insert = DB::insert("
						insert into d_trans_akun(id_trans,kdakun,kddk,nilai)
						select	?,
								?,
								'D',
								?
						from dual
						union all
						select	?,
								?,
								'K',
								?
						from dual
					",[
						$request->input('inp-id'),
						$request->input('debet'),
						str_replace(',', '', $request->input('nilai')),
						$request->input('inp-id'),
						$request->input('kredit'),
						str_replace(',', '', $request->input('nilai')),
					]);
					
					if($insert){
						
						$ppn = (int)str_replace(',', '', $request->input('ppn'));
						if($ppn>0){
							
							$now = new \DateTime();
							$grup = $now->format('YmdHis');
							
							$insert = DB::insert("
								insert into d_trans_akun(id_trans,kdakun,kddk,nilai,grup)
								select	?,
										?,
										'D',
										?,
										?
								from dual
								union all
								select	?,
										?,
										'K',
										?,
										?
								from dual
							",[
								$request->input('inp-id'),
								'721000',
								$ppn,
								$grup,
								$request->input('inp-id'),
								$request->input('kredit'),
								$ppn,
								$grup
							]);
							
						}
						
						if($insert){
							DB::commit();
							return 'success';
						}
						else{
							return 'Pajak gagal disimpan!';
						}
						
					}
					else{
						return 'Data detil akun gagal disimpan!';
					}
					
				}
				else{
					return 'Data akun yang lama gagal dihapus!';
				}
				
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
			from d_trans
			where id=? and status=1
		",[
			$request->input('id')
		]);
		
		if($rows[0]->jml==1){
			
			$delete = DB::delete("
				delete from d_trans_dok
				where id_trans=?
			",[
				$request->input('id')
			]);
			
			$delete = DB::delete("
				delete from d_trans_akun
				where id_trans=?
			",[
				$request->input('id')
			]);
			
			if($delete){
				
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
			else{
				return 'Data akun gagal dihapus!';
			}
			
		}
		else{
			return 'Data tidak dapat dihapus karena sudah diproses!';
		}	
	}
	
	public function hapusDok(Request $request)
	{
		DB::beginTransaction();
			
		$delete = DB::delete("
			delete from d_trans_dok
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
	
	public function tagihan($id)
	{
		$data['dropdown'] = '';
		$data['error'] = true;
		
		$rows = DB::select("
			select	*
			from t_trans
			where id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$is_parent = $rows[0]->is_parent;
			$parent_id = $rows[0]->parent_id;
			
			if($is_parent==1){
				
				$rows = DB::select("
					select	a.id,
							a.nodok as nopks,
							to_char(a.tgdok,'dd-mm-yyyy') as tgpks,
							a.id_penerima as id_pelanggan,
							a.uraian,
							nvl(c.nilai,0) as nilai,
							c.kdakun,
							a.id_proyek
					from d_trans a
					left outer join d_trans_akun c on(a.id=c.id_trans)
					where a.id_alur=? and c.kddk='D'
				",[
					$parent_id
				]);
				
				$dropdown = '<option value="" style="display:none;">Pilih Data</option>';
				foreach($rows as $row){
					$dropdown .= '<option value="'.$row->id.'|'.$row->id_pelanggan.'|'.$row->uraian.'|'.$row->nilai.'|'.$row->kdakun.'|'.$row->id_proyek.'-'.$row->id_pelanggan.'"> PKS : '.$row->nopks.', '.$row->tgpks.', Rp. '.number_format($row->nilai).',-</option>';
				}
				
				$data['dropdown'] = $dropdown;
				$data['error'] = false;
				
			}
			
		}
		
		return response()->json($data);
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
								
								session(array('upload_lampiran'=>$file_name_baru));
								return '1';
								
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
	
	public function uploadSimpan(Request $request)
	{
		DB::beginTransaction();
		
		if(session('upload_lampiran')!=='' && session('upload_lampiran')!==null){
			
			$delete = DB::delete("
				delete from d_trans_dok
				where id_trans=? and id_dok=?
			",[
				$request->input('id_trans'),
				$request->input('id_dok'),
			]);
			
			$insert = DB::insert("
				insert into d_trans_dok(id_trans,id_dok,nmfile)
				values(?,?,?)
			",[
				$request->input('id_trans'),
				$request->input('id_dok'),
				session('upload_lampiran')
			]);
			
			if($insert) {
				DB::commit();
				session(array('upload_lampiran'=>null));
				return 'success';
			}
			else {
				return 'Proses hapus gagal. Hubungi Administrator.';
			}
			
		}
		else{
			return 'Lampiran belum diupload!';
		}
	}
	
	public function download(Request $request, $id)
	{
		$rows = DB::select("
			select	*
			from d_trans_dok
			where id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$log = 'data/lampiran/'.$rows[0]->nmfile;
			
			header('Content-Description:Lampiran Transaksi');
			header('Content-Disposition:attachment;filename=' . basename($log));
			header('Content-Transfer-Encoding:binary');
			header('Expires:0');
			header('Cahce-Control:must-revalidate');
			header('Pragma:public');
			header('Content-Length:'.filesize($log));
			readfile($log);
			
		}
		else{
			return 'Dokumen tidak ditemukan!';
		}
	}
	
}