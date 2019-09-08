<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PembukuanJurnalController extends Controller {

	public function index(Request $request)
	{
		$aColumns = array('tgtrans','jenis','id_trans','kdlap','kdakun','nmakun','kddk','nilai');
		/* Indexed column (used for fast and accurate table cardinality) */
		$sIndexColumn = "tgtrans";
		/* DB table to use */
		$sTable = "select  a.*
					from(
						select  to_char(a.tgsawal,'DD-MM-YYYY') as tgtrans,
								'Saldo Awal' as jenis,
								a.id as id_trans,
								a.kdlap,
								a.kdakun,
								b.nmakun,
								a.kddk,
								a.nilai
						from d_sawal a
						left outer join t_akun b on(a.kdakun=b.kdakun)
						where a.thang='".session('tahun')."'

						union all

						select  to_char(b.tgdok,'DD-MM-YYYY') as tgtrans,
								'Transaksi' as jenis,
								a.id_trans,
								c.kdlap,
								a.kdakun,
								c.nmakun,
								a.kddk,
								a.nilai
						from d_trans_akun a
						left outer join d_trans b on(a.id_trans=b.id)
						left outer join t_akun c on(a.kdakun=c.kdakun)
						where b.thang='".session('tahun')."'
					) a
					order by a.tgtrans desc, a.kddk,a.kdakun asc";
		
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
				$sWhere=" where lower(nmakun) like lower('".$sSearch."%') or lower(nmakun) like lower('%".$sSearch."%') or
								lower(kdakun) like lower('".$sSearch."%') or lower(kdakun) like lower('%".$sSearch."%') ";
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
			$debet = '';
			$kredit = '';
			
			if($row->kddk=='D'){
				$debet = number_format($row->nilai);
			}
			else{
				$kredit = number_format($row->nilai);
			}
			
			$output['aaData'][] = array(
				$row->tgtrans,
				$row->jenis,
				$row->id_trans,
				$row->kdlap,
				$row->kdakun,
				$row->nmakun,
				'<div style="text-align:right;">'.$debet.'</div>',
				'<div style="text-align:right;">'.$kredit.'</div>'
			);
		}
		
		return response()->json($output);
	}
	
}