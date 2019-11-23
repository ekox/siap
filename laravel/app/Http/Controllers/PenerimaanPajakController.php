<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PenerimaanPajakController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('id','nmunit','nama','nmtrans','uraian','nilai','pajak','total');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "id";
		/* DB table to use */
		$sTable = "select  a.id,
							d.nmunit,
							e.nama,
							h.nmtrans,
							a.nodok||'<br>'||to_char(a.tgdok,'dd-mm-yyyy') as pks,
							to_char(a.tgdok1,'dd-mm-yyyy') as tgjtempo,
							a.uraian,
							nvl(f.nilai,0) as nilai,
							b.nmalur||'<br>'||g.nmlevel||'<br>'||c.nmstatus as status,
							i.pajak,
							nvl(f.nilai,0)-nvl(j.pajak1,0) as total
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
						where kddk='D' and grup is null
						group by id_trans
					) f on(a.id=f.id_trans)
					left outer join(
						select  a.id_trans,
								rtrim(xmlagg(xmlelement(e, a.id||'|'||b.nmakun||'|'||a.nilai||'|'||a.grup, ',')).extract('//text()').getclobval(), ',') as pajak
						from d_trans_akun a
						left outer join t_akun b on(a.kdakun=b.kdakun)
						where substr(a.kdakun,1,1)='7'
						group by a.id_trans
					) i on(a.id=i.id_trans)
					left outer join(
						select  a.id_trans,
								sum(a.nilai) as pajak1
						from d_trans_akun a
						where substr(a.kdakun,1,1)='7'
						group by a.id_trans
					) j on(a.id=j.id_trans)
					where b.menu=2 and a.thang='".session('tahun')."' and c.is_pajak1='1'
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
			$pajak = '<ul>';
			if($row->pajak!==''){
				
				$arr_pajak = explode(',', $row->pajak);
				for($i=0;$i<count($arr_pajak);$i++){
					
					$arr_pajak1 = explode('|', $arr_pajak[$i]);
					if(count($arr_pajak1)>1){
						
						$pajak .= '<li>'.$arr_pajak1[1].' '.number_format($arr_pajak1[2]).'
										<a id="'.$row->id.'-'.$arr_pajak1[3].'" href="javascript:;" class="hapus-pajak" title="Hapus Pajak"><i class="fa fa-times"></i></a>
									 </li>';
						
					}
					
				}
				
			}
			$pajak .= '</ul>';
			
			$aksi='<center>
						<button type="button" class="btn btn-raised btn-sm btn-icon btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><i class="fa fa-check"></i></button>
						<div class="dropdown-menu" x-placement="bottom-start" style="position: absolute; transform: translate3d(0px, 38px, 0px); top: 0px; left: 0px; will-change: transform;">
							<a id="'.$row->id.'" class="dropdown-item proses" href="javascript:;">Hitung Pajak</a>
						</div>
					</center>';
			
			$output['aaData'][] = array(
				$row->no,
				$row->nmunit,
				$row->nama,
				$row->nmtrans,
				$row->uraian,
				'<div style="text-align:right;">'.number_format($row->nilai).'</div>',
				$pajak,
				'<div style="text-align:right;">'.number_format($row->total).'</div>',
				$aksi
			);
		}
		
		return response()->json($output);
	}
	
	public function pilih(Request $request, $id)
	{
		$rows = DB::select("
			select  a.id,
					b.nmalur,
					c.nmunit,
					d.nama as nmpelanggan,
					f.nmtrans,
					a.nodok as nopks,
					to_char(a.tgdok,'yyyy-mm-dd') as tgpks,
					to_char(a.tgdok1,'yyyy-mm-dd') as tgjtempo,
					a.uraian,
					nvl(g.nilai,0) as nilai,
					nvl(f.nmakun,0) as debet,
					nvl(i.nmakun,0) as kredit,
					a.id_alur,
					a.status
			from d_trans a
			left outer join t_alur b on(a.id_alur=b.id)
			left outer join t_unit c on(a.kdunit=c.kdunit)
			left outer join t_penerima d on(a.id_penerima=d.id)
			left outer join t_trans f on(a.kdtran=f.id)
			left outer join(
				select	id_trans,
						kdakun,
						nilai
				from d_trans_akun
				where kddk='D'
			) g on(a.id=g.id_trans)
			left outer join(
				select	id_trans,
						kdakun,
						nilai
				from d_trans_akun
				where kddk='K'
			) h on(a.id=h.id_trans)
			left outer join t_akun f on(g.kdakun=f.kdakun)
			left outer join t_akun i on(h.kdakun=i.kdakun)
			where a.id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$id_alur = $rows[0]->id_alur;
			$detil = $rows[0];
			$data['error'] = false;
			$data['message'] = $detil;
			
			$rows = DB::select("
				select	a.id,
						b.uraian,
						a.nmfile
				from d_trans_dok a
				left outer join t_dok b on(a.id_dok=b.id)
				where a.id_trans=?
			",[
				$id
			]);
			
			$lampiran = '<ul>';
			foreach($rows as $row){
				$lampiran .= '<li><a href="penerimaan/rekam/download/'.$row->id.'" target="_blank" title="Download Lampiran">'.$row->uraian.'</li>';
			}
			$lampiran .= '</ul>';
			
			$data['lampiran'] = $lampiran;
			
			$rows = DB::select("
				select  *
				from t_akun
				where substr(kdakun,1,2)='72' and lvl=6
			");
			
			$pajak = '<option value="">Pilih Data</option>';
			foreach($rows as $row){
				$pajak .= '<option value="'.$row->kdakun.'">'.$row->nmakun.'</option>';
			}
			
			$data['pajak'] = $pajak;
			
		}
		else{
			$data['error'] = true;
			$data['message'] = 'Data header tidak ditemukan!';
		}
		
		return response()->json($data);
	}
	
	public function simpan(Request $request)
	{
		$rows = DB::select("
			select    a.kdakun
			from d_trans_akun a
			left outer join d_trans b on(a.id_trans=b.id)
			left outer join t_alur_status c on(b.id_alur=c.id_alur and b.status=c.status)
			where a.id_trans=? and a.kddk='D' and c.is_pajak1='1' and a.grup is null
		",[
			$request->input('inp-id')
		]);
		
		if(count($rows)>0){
			
			$kredit = $rows[0]->kdakun;
			
			DB::beginTransaction();
			
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
				$request->input('pajak'),
				str_replace(',', '', $request->input('nilai1')),
				$grup,
				$request->input('inp-id'),
				$kredit,
				str_replace(',', '', $request->input('nilai1')),
				$grup
			]);
			
			if($insert){
				DB::commit();
				return 'success';
			}
			else{
				return 'Data gagal disimpan!';
			}
			
		}
		else{
			return 'Data tidak ditemukan!';
		}
		
	}
	
	public function hapus(Request $request)
	{
		DB::beginTransaction();
		
		$arr_id = explode("-", $request->input('id'));
		
		$delete = DB::delete("
			delete from d_trans_akun
			where id_trans=? and grup=?
		",[
			$arr_id[0],
			$arr_id[1]
		]);
		
		if($delete==true) {
			DB::commit();
			return 'success';
		}
		else {
			return 'Proses hapus gagal. Hubungi Administrator.';
		}
	}
}