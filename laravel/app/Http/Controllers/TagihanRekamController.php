<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TagihanRekamController extends Controller {

	public function index(Request $request)
	{
		$panjang = strlen(session('kdunit'));
		
		$arrLevel = ['03','05','08','11'];
		
		$and = "";
		if(in_array(session('kdlevel'), $arrLevel)){
			$and = " and substr(a.kdunit,1,".$panjang.")='".session('kdunit')."'";
		}
		
		$aColumns = array('id','nourut','unit','nama','nmtrans','pks','nilai','status','is_ubah','is_final');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  	a.id,
							lpad(a.nourut,5,'0') as nourut,
							d.nmunit as unit,
							e.nama,
							h.nmtrans,
							a.nodok as pks,
							to_char(a.tgdok1,'dd-mm-yyyy') as tgjtempo,
							a.uraian,
							nvl(a.nilai,0) as nilai,
							c.nmstatus as status,
							c.is_ubah,
							c.is_final
					from d_trans a
					left outer join t_alur b on(a.id_alur=b.id)
					left outer join t_alur_status c on(a.id_alur=c.id_alur and a.status=c.status)
					left outer join t_unit d on(a.kdunit=d.kdunit)
					left outer join t_penerima e on(a.id_penerima=e.id)
					left outer join t_trans h on(a.kdtran=h.id)
					where b.menu=1 and a.thang='".session('tahun')."' ".$and."
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
								lower(nourut) like lower('".$sSearch."%') or lower(nourut) like lower('%".$sSearch."%') or nilai=".$sSearch." ";
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
			$aksi = '';
			if(session('kdlevel')=='12'){
				
				if($row->is_ubah==1){
				
					$aksi = '<center>
								<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
								<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
									<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
									<a id="'.$row->id.'" class="dropdown-item hapus" href="javascript:;">Hapus Data</a>
								</div>
							</center>';
							
				}
				
			}
			elseif(session('kdlevel')=='07' || session('kdlevel')=='04'){
				
				if($row->is_final!=='1'){
					
					$aksi = '<center>
								<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
								<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
									<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
								</div>
							</center>';
					
				}
				
			}
			elseif(session('kdlevel')=='00'){
				
				$aksi = '<center>
							<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
							<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
								<a id="'.$row->id.'" class="dropdown-item ubah" href="javascript:;">Ubah Data</a>
							</div>
						</center>';
				
			}
			
			$output['aaData'][] = array(
				$row->no,
				$row->nourut,
				$row->unit,
				$row->nama,
				$row->nmtrans,
				$row->pks,
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
			select  a.id,
					lpad(a.nourut,5,'0') as nourut,
					a.kdunit,
					a.kdsdana,
					a.id_alur,
					a.kdtran,
					a.id_proyek,
					a.id_penerima as id_pelanggan,
					a.nodok as nopks,
					to_char(a.tgdok,'yyyy-mm-dd') as tgpks,
					to_char(a.tgdok1,'yyyy-mm-dd') as tgjtempo,
					to_char(a.tgrekam,'yyyy-mm-dd') as tgrekam,
					a.uraian,
					nvl(b.nilai,0) as nilai,
					a.nilai_bersih,
					nvl(c.nilai,0) as total,
					b.kdakun as debet,
					c.kdakun as kredit,
					a.ttd1,
					a.ttd2,
					a.ttd3,
					a.ttd4
			from d_trans a
			left outer join(
				select	id_trans,
						kdakun,
						nilai
				from d_trans_akun
				where kddk='D' and grup=1
			) b on(a.id=b.id_trans)
			left outer join(
				select	id_trans,
						kdakun,
						nilai
				from d_trans_akun
				where kddk='K' and grup=1
			) c on(a.id=c.id_trans)
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
			
			$data['akun'] = '';
			$data['x'] = 0;
			$rows = DB::select("
				select	a.kdakun,
						a.nilai,
						b.kddk,
						b.nilai as nilai1
				from d_trans_akun a
				left join t_akun_pajak b on(a.kdakun=b.kdakun)
				where a.id_trans=? and a.grup=0
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
		
		$nourut = (int)$request->input('nourut');
		
		if($total>0){
		
			DB::beginTransaction();
			
			$id_proyek = '';
			if($request->input('id_proyek')!==''){
				$arr_proyek = explode("-", $request->input('id_proyek'));
				$id_proyek = $arr_proyek[0];
			}
			
			if($request->input('inp-rekambaru')=='1'){
				
				$rows = DB::select("
					select	count(*) as jml
					from d_trans a
					left join t_alur b on(a.id_alur=b.id)
					where a.thang=? and b.menu=1 and a.nourut=?
				",[
					session('tahun'),
					$nourut
				]);
				
				if($rows[0]->jml==0){
					
					$id_trans = DB::table('d_trans')->insertGetId([
						'kdsdana' => $request->input('kdsdana'),
						'kdunit' => $request->input('kdunit'),
						'thang' => session('tahun'),
						'id_alur' => $request->input('id_alur'),
						'nourut' => $nourut,
						'kdtran' => $request->input('kdtran'),
						'id_proyek' => $id_proyek,
						'id_penerima' => $request->input('id_pelanggan'),
						'nodok' => $request->input('nopks'),
						'tgdok' => $request->input('tgpks'),
						'tgdok1' => $request->input('tgjtempo'),
						'tgrekam' => DB::raw("to_date('".$request->input('tgrekam')."','yyyy-mm-dd')"),
						'uraian' => $request->input('uraian'),
						'ttd1' => $request->input('ttd1'),
						'ttd2' => $request->input('ttd2'),
						'ttd3' => $request->input('ttd3'),
						'ttd4' => $request->input('ttd4'),
						'nilai_bersih' => str_replace(',', '', $request->input('nilai')),
						'nilai' => str_replace(',', '', $request->input('total')),
						'status' => 1,
						'id_user' => session('id_user')
					]);
					
					if($id_trans){
						
						$arr_insert[] = "select	".$id_trans." as id_trans,
												'".$request->input('debet')."' as kdakun,
												'D' as kddk,
												".str_replace(',', '', $request->input('nilai'))." as nilai,
												1 as grup
										 from dual";
										 
						$arr_insert[] = "select	".$id_trans." as id_trans,
												'".$request->input('kredit')."' as kdakun,
												'K' as kddk,
												".str_replace(',', '', $request->input('total'))." as nilai,
												1 as grup
										 from dual
										 ";
						
						$lanjut = true;
						$arr_pajak = $request->input('rincian');
						if(is_array($arr_pajak)){
							if(count($arr_pajak)>0){
								
								$arr_keys = array_keys($arr_pajak);
								
								for($i=0;$i<count($arr_keys);$i++){
									
									if($arr_pajak[$arr_keys[$i]]["'nilai'"]>0){
									
										$arr_akun = explode("|", $arr_pajak[$arr_keys[$i]]["'kdakun'"]);
										$kdakun = $arr_akun[0];
										$kddk = $arr_akun[1];
									
										$arr_insert[] = "select	".$id_trans." as id_trans,
																'".$kdakun."' as kdakun,
																'".$kddk."' as kddk,
																".str_replace(',', '', $arr_pajak[$arr_keys[$i]]["'nilai'"])." as nilai,
																0 as grup
														 from dual";
														 
									}
									
								}
								
							}
						}
							
						$insert = DB::insert("
							insert into d_trans_akun(id_trans,kdakun,kddk,nilai,grup)
							".implode(" union all ", $arr_insert)."
						");
						
						if($insert){
							DB::commit();
							return 'success';
						}
						else{
							return 'Simpan detil gagal!';
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
				
				$id_trans = $request->input('inp-id');
					
				$arr_insert[] = "select	".$id_trans." as id_trans,
										'".$request->input('debet')."' as kdakun,
										'D' as kddk,
										".str_replace(',', '', $request->input('nilai'))." as nilai,
										1 as grup
								 from dual";
								 
				$arr_insert[] = "select	".$id_trans." as id_trans,
										'".$request->input('kredit')."' as kdakun,
										'K' as kddk,
										".str_replace(',', '', $request->input('total'))." as nilai,
										1 as grup
								 from dual
								 ";
				
				$lanjut = true;
				$arr_pajak = $request->input('rincian');
				if(is_array($arr_pajak)){
					if(count($arr_pajak)>0){
						
						$arr_keys = array_keys($arr_pajak);
						
						for($i=0;$i<count($arr_keys);$i++){
							
							if($arr_pajak[$arr_keys[$i]]["'nilai'"]>0){
							
								$arr_akun = explode("|", $arr_pajak[$arr_keys[$i]]["'kdakun'"]);
								$kdakun = $arr_akun[0];
								$kddk = $arr_akun[1];
								
								$arr_insert[] = "select	".$id_trans." as id_trans,
														'".$kdakun."' as kdakun,
														'".$kddk."' as kddk,
														".str_replace(',', '', $arr_pajak[$arr_keys[$i]]["'nilai'"])." as nilai,
														0 as grup
												 from dual";
												 
							}
							
						}
						
					}
				}
				
				$delete = DB::delete("
					delete from d_trans_akun
					where id_trans=?
				",[
					$id_trans
				]);
					
				$insert = DB::insert("
					insert into d_trans_akun(id_trans,kdakun,kddk,nilai,grup)
					".implode(" union all ", $arr_insert)."
				");
				
				if($insert){
					DB::commit();
					return 'success';
				}
				else{
					return 'Simpan detil gagal!';
				}
				
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
				delete from d_trans_histori
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
	
}