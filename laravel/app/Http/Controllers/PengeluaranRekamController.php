<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PengeluaranRekamController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','nourut','nmunit','nama','nmtrans','pks','nilai','status','lampiran');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  	a.id,
							lpad(a.nourut,5,'0') as nourut,
							d.nmunit,
							e.nama,
							h.nmtrans,
							a.nodok as pks,
							to_char(a.tgdok1,'dd-mm-yyyy') as tgjtempo,
							a.uraian,
							nvl(a.nilai,0) as nilai,
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
						select  a.id_trans,
								rtrim(xmlagg(xmlelement(e, a.id||'|'||b.uraian, ',')).extract('//text()').getclobval(), ',') as lampiran
						from d_trans_dok a
						left outer join t_dok b on(a.id_dok=b.id)
						group by a.id_trans
					) i on(a.id=i.id_trans)
					where b.menu=4 and a.thang='".session('tahun')."' and a.kdunit='".session('kdunit')."'
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
				$sWhere=" where lower(pks) like lower('".$sSearch."%') or lower(pks) like lower('%".$sSearch."%') or
								lower(nourut) like lower('".$sSearch."%') or lower(nourut) like lower('%".$sSearch."%') ";
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
			if(session('kdlevel')=='04' || session('kdlevel')=='11'){
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
				$row->nourut,
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
					lpad(a.nourut,5,'0') as nourut,
					a.kdunit,
					a.id_alur,
					a.id_output,
					a.kdtran,
					a.id_penerima as id_pelanggan,
					a.nodok as nopks,
					to_char(a.tgdok,'yyyy-mm-dd') as tgpks,
					to_char(a.tgdok1,'yyyy-mm-dd') as tgjtempo,
					a.uraian,
					nvl(a.nilai_bersih,0) as nilai,
					0 as total,
					nvl(a.debet,'') as debet,
					nvl(a.kredit,'') as kredit,
					a.parent_id
			from d_trans a
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
			
			$dropdown = '';
			
			if($detil->parent_id!==''){
				$rows = DB::select("
					select	a.id,
							a.nodok as nopks,
							to_char(a.tgdok,'dd-mm-yyyy') as tgpks,
							a.id_penerima as id_pelanggan,
							a.uraian,
							nvl(c.nilai,0) as nilai,
							c.kdakun
					from d_trans a
					left outer join d_trans_akun c on(a.id=c.id_trans)
					where a.id=? and c.kddk='D'
				",[
					$detil->parent_id
				]);
				
				$dropdown = '<option value="" style="display:none;">Pilih Data</option>';
				foreach($rows as $row){
					$dropdown .= '<option value="'.$row->id.'|'.$row->id_pelanggan.'|'.$row->uraian.'|'.$row->nilai.'|'.$row->kdakun.'" selected> PKS : '.$row->nopks.', '.$row->tgpks.', Rp. '.number_format($row->nilai).',-</option>';
				}
			}
			
			$data['tagihan'] = $dropdown;
			
			$data['akun'] = '';
			$data['x'] = 0;
			$rows = DB::select("
				select	a.kdakun,
						a.nilai,
						b.kddk,
						b.nilai as nilai1
				from d_trans_pajak a
				left join t_akun_pajak b on(a.kdakun=b.kdakun)
				where a.id_trans=?
			",[
				$id
			]);
			
			if(count($rows)>0){
				$data['akun'] = $rows;
				$data['x'] = count($rows);
			}
			
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
		$total = str_replace(',', '', $request->input('total'));
		
		if($total>0){
		
			DB::beginTransaction();
			
			$rows = DB::select("
				select	*
				from t_alur
				where id=?
			",[
				$request->input('id_alur')
			]);
			
			if(count($rows)>0){
				
				$nilai = (int)str_replace(',', '', $request->input('nilai'));
				
				if($nilai>=$rows[0]->batas1 && $nilai<=$rows[0]->batas2){
					
					if($request->input('inp-rekambaru')=='1'){
						
						$rows = DB::select("
							select	count(*) as jml
							from d_trans a
							left join t_alur b on(a.id_alur=b.id)
							where a.thang=? and b.menu=4 and a.nourut=?
						",[
							session('tahun'),
							str_replace('0', '', $request->input('nourut'))
						]);
						
						if($rows[0]->jml==0){
							
							$arr_parent = explode("|", $request->input('parent_id'));
							$parent_id = $arr_parent[0];
							
							$id_trans = DB::table('d_trans')->insertGetId([
								'kdunit' => session('kdunit'),
								'thang' => session('tahun'),
								'nourut' => str_replace('0', '', $request->input('nourut')),
								'id_output' => $request->input('id_output'),
								'id_alur' => $request->input('id_alur'),
								'kdtran' => $request->input('kdtran'),
								'id_penerima' => $request->input('id_pelanggan'),
								'nodok' => $request->input('nopks'),
								'tgdok' => $request->input('tgpks'),
								'tgdok1' => $request->input('tgjtempo'),
								'uraian' => $request->input('uraian'),
								'debet' => $request->input('debet'),
								'kredit' => $request->input('kredit'),
								'nilai' => str_replace(',', '', $request->input('total')),
								'nilai_bersih' => str_replace(',', '', $request->input('nilai')),
								'parent_id' => $parent_id,
								'status' => 1,
								'id_user' => session('id_user')
							]);
							
							if($id_trans){
								
								$lanjut = true;
								$arr_pajak = $request->input('rincian');
								if(count($arr_pajak)>0){
									
									$arr_keys = array_keys($arr_pajak);
									$arr_insert = array();
									
									for($i=0;$i<count($arr_keys);$i++){
										
										if($arr_pajak[$arr_keys[$i]]["'nilai'"]>0){
										
											$arr_akun = explode("|", $arr_pajak[$arr_keys[$i]]["'kdakun'"]);
											$kdakun = $arr_akun[0];
										
											$arr_insert[] = "select	".$id_trans." as id_trans,
																	'".$kdakun."' as kdakun,
																	".str_replace(',', '', $arr_pajak[$arr_keys[$i]]["'nilai'"])." as nilai
															 from dual";
															 
										}
										
									}
									
									if(count($arr_insert)>0){
											
										$delete = DB::delete("
											delete from d_trans_pajak
											where id_trans=?
										",[
											$id_trans
										]);
											
										$insert = DB::insert("
											insert into d_trans_pajak(id_trans,kdakun,nilai)
											".implode(" union all ", $arr_insert)."
										");
										
										if(!$insert){
											$lanjut = false;
										}
										
									}
									
								}
								
								if($lanjut){
									DB::commit();
									return 'success';
								}
								else{
									return 'Simpan pajak gagal!';
								}
								
							}
							else{
								return 'Data gagal disimpan!';
							}
							
						}
						else{
							return 'Duplikasi nomor transaksi!';
						}
						
					}
					else{
						$update = DB::update("
							update d_trans
							set id_output=?,
								kdtran=?,
								id_penerima=?,
								nodok=?,
								tgdok=?,
								tgdok1=?,
								uraian=?,
								debet=?,
								kredit=?,
								nilai=?,
								nilai_bersih=?,
								id_user=?,
								updated_at=sysdate
							where id=?
						",[
							$request->input('id_output'),
							$request->input('kdtran'),
							$request->input('id_pelanggan'),
							$request->input('nopks'),
							$request->input('tgpks'),
							$request->input('tgjtempo'),
							$request->input('uraian'),
							$request->input('debet'),
							$request->input('kredit'),
							str_replace(',', '', $request->input('total')),
							str_replace(',', '', $request->input('nilai')),
							session('id_user'),
							$request->input('inp-id')
						]);
						
						if($update){
							
							$lanjut = true;
							$arr_pajak = $request->input('rincian');
							$id_trans = $request->input('inp-id');
							if(count($arr_pajak)>0){
								
								$arr_keys = array_keys($arr_pajak);
								$arr_insert = array();
								
								for($i=0;$i<count($arr_keys);$i++){
									
									if($arr_pajak[$arr_keys[$i]]["'nilai'"]>0){
									
										$arr_akun = explode("|", $arr_pajak[$arr_keys[$i]]["'kdakun'"]);
										$kdakun = $arr_akun[0];
									
										$arr_insert[] = "select	".$id_trans." as id_trans,
																'".$kdakun."' as kdakun,
																".str_replace(',', '', $arr_pajak[$arr_keys[$i]]["'nilai'"])." as nilai
														 from dual";
														 
									}
									
								}
								
								if(count($arr_insert)>0){
										
									$delete = DB::delete("
										delete from d_trans_pajak
										where id_trans=?
									",[
										$id_trans
									]);
										
									$insert = DB::insert("
										insert into d_trans_pajak(id_trans,kdakun,nilai)
										".implode(" union all ", $arr_insert)."
									");
									
									if(!$insert){
										$lanjut = false;
									}
									
								}
								
							}
							
							if($lanjut){
								DB::commit();
								return 'success';
							}
							else{
								return 'Simpan pajak gagal!';
							}
							
						}
						else{
							return 'Data gagal diubah!';
						}
					}
					
				}
				else{
					return 'Nilai transaksi tidak valid!';
				}
				
			}
			else{
				return 'Kode proses tidak ditemukan!';
			}
			
		}
		else{
			return 'Hitung dulu total transaksi ini!';
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
				delete from d_trans_pajak
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
							c.kdakun
					from d_trans a
					left outer join d_trans_akun c on(a.id=c.id_trans)
					where a.id_alur=? and c.kddk='D'
				",[
					$parent_id
				]);
				
				$dropdown = '<option value="" style="display:none;">Pilih Data</option>';
				foreach($rows as $row){
					$dropdown .= '<option value="'.$row->id.'|'.$row->id_pelanggan.'|'.$row->uraian.'|'.$row->nilai.'|'.$row->kdakun.'"> PKS : '.$row->nopks.', '.$row->tgpks.', Rp. '.number_format($row->nilai).',-</option>';
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