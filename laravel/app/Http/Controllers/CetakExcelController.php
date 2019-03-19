<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use clsTinyButStrong;
use DateTime;

class CetakExcelController extends Controller {
	/*
	|--------------------------------------------------------------------------
	| Home Controller
	|--------------------------------------------------------------------------
	|
	| This controller renders your application's "dashboard" for users that
	| are authenticated. Of course, you are free to change or remove the
	| controller as you wish. It is just here to get your app started!
	|
	*/

	/**
	 * Create a new controller instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
	
	}

	/**
	 * Show the application dashboard to the user.
	 *
	 * @return Response
	 */
	public function downloadRapat($param)
	{
		$row = DB::select("
			select nosurat,DATE_FORMAT(tgsurat,'%d %M %Y')tgsurat,DATE_FORMAT(tgrapat,'%d %M %Y')tgrapat,jamrapat,jmlhari,jmljam,
			UPPER(ket) as ket,DATE_FORMAT(now(),'%d %M %Y') skrg,b.nama,b.nip
			from d_rapat a
			left outer join (select thang,kdsatker,nip,nama from t_pejabat where kdjabatan='1') b
			on a.kdsatker=b.kdsatker and a.thang=b.thang
			where id=?
		", [$param]);
		if ( empty($row)) return 'Data Tidak Ditemukan';
		
		$header[] = (object) array(
			'nosurat' => $row[0]->nosurat,
			'tgsurat' => $row[0]->tgsurat,
			'tgrapat' => $row[0]->tgrapat,
			'jamrapat' => $row[0]->jamrapat,
			'jmlhari' => $row[0]->jmlhari,
			'jmljam' => $row[0]->jmljam,
			'ket' => $row[0]->ket,
			'nmsatker' => Session::get('nmsatker'),
			'nmjubar' => Session::get('nama'),
			'nipjubar' => Session::get('nip'),
			'nmkpa' => $row[0]->nama,
			'nipkpa' => $row[0]->nip,
			'skrg' => $row[0]->skrg
		);
		$rows1 = DB::select("
			select 
			case when ISNULL(b.nip)then d.nip else b.nip end as nip,
			case when ISNULL(b.nip)then d.nama else c.nama end as nama,
			case when ISNULL(b.nip)then f.nmgol else e.nmgol end as nmgol,
			case when ISNULL(b.nip)then d.instansi else c.instansi end as instansi,
			b.nilai,b.pajak,b.nilai-b.pajak nilai_bersih,b.ket,g.nmjab
			 from
			(select id from d_rapat where id=?)a
			left join
			(select id_rapat,nip,id_peg_non,kdjab,instansi,nilai,pajak,ket from d_rapat_detil)b
			on a.id=b.id_rapat
			left join
			(select nip,nama,kdgol,instansi from t_pegawai)c
			on b.nip=c.nip
			left join
			(select id,nip,nama,kdgol,instansi from t_pegawai_non)d
			on b.id_peg_non=d.id
			left join t_gol e
			on c.kdgol=e.kdgol
			left join t_gol f
			on d.kdgol=f.kdgol
			left outer join t_jab_kegiatan g
			on b.kdjab=g.kdjab
			order by b.kdjab,ifnull(c.kdgol,d.kdgol) desc
		", [$param]);
		$i=1;
		foreach($rows1 as $value) {
				$_value = (object)array(
					'no'=>$i,
					'nama'=>$value->nama,
					'nmgol'=>$value->nmgol,
					'instansi'=>$value->instansi,
					'nilai'=>number_format($value->nilai, 0, ",", "."),
					'pajak'=>number_format($value->pajak, 0, ",", "."),
					'nilai_bersih'=>number_format($value->nilai_bersih, 0, ",", "."),
					'nmjab'=>$value->nmjab			
				);
				$i++;
				$values[]=$_value;
			}
		//instance TBS class
		$TBS = new clsTinyButStrong();
		$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
		
		//load template 
		$TBS->LoadTemplate('reports/'.'template_daftar_nominatif_rapat.xlsx');
		$TBS->Plugin(OPENTBS_SELECT_SHEET,'Sheet1');
		$TBS->MergeBlock('v', $values);
		$TBS->MergeBlock('p', $header);
		
		//download file
		$TBS->Show(OPENTBS_DOWNLOAD,'daftar nominatif rapat '.date("Y-m-d H:i:s").'.xlsx');	
		//~ return "surat-setoran-pajak";
	}
	
	public function downloadAbsen($param)
	{
		$row = DB::select("
			select DATE_FORMAT(tgrapat,'%Y-%m-%d')tgrapat,jamrapat,jmlhari,jmljam,
			UPPER(ket) as ket
			from d_rapat a
			where id=?
		", [$param]);
		if ( empty($row)) return 'Data Tidak Ditemukan';
		
		
		$rows1 = DB::select("
			select 
			case when ISNULL(b.nip)then d.nip else b.nip end as nip,
			case when ISNULL(b.nip)then d.nama else c.nama end as nama,
			case when ISNULL(b.nip)then f.nmgol else e.nmgol end as nmgol,
			case when ISNULL(b.nip)then d.instansi else c.instansi end as instansi,
			b.nilai,b.pajak,b.nilai-b.pajak nilai_bersih,b.ket,g.nmjab
			 from
			(select id from d_rapat where id=?)a
			left join
			(select id_rapat,nip,id_peg_non,kdjab,instansi,nilai,pajak,ket from d_rapat_detil)b
			on a.id=b.id_rapat
			left join
			(select nip,nama,kdgol,instansi from t_pegawai)c
			on b.nip=c.nip
			left join
			(select id,nip,nama,kdgol,instansi from t_pegawai_non)d
			on b.id_peg_non=d.id
			left join t_gol e
			on c.kdgol=e.kdgol
			left join t_gol f
			on d.kdgol=f.kdgol
			left outer join t_jab_kegiatan g
			on b.kdjab=g.kdjab
			order by b.kdjab,ifnull(c.kdgol,d.kdgol) desc
		", [$param]);
		$i=1;
		foreach($rows1 as $value) {
				$_value = (object)array(
					'no'=>$i,
					'nama'=>$value->nama,
					'nip'=>$value->nip,
					'nmgol'=>$value->nmgol,
					'instansi'=>$value->instansi,
					'nilai'=>number_format($value->nilai, 0, ",", "."),
					'pajak'=>number_format($value->pajak, 0, ",", "."),
					'nilai_bersih'=>number_format($value->nilai_bersih, 0, ",", "."),
					'nmjab'=>$value->nmjab			
				);
				$i++;
				$values[]=$_value;
			}
		
		//instance TBS class
		$TBS = new clsTinyButStrong();
		$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
		$TBS->LoadTemplate('reports/'.'template_absen_rapat.xlsx');
		
		$tanggal = strtotime($row[0]->tgrapat);
		$jmlhari= (int) $row[0]->jmlhari;
		if($jmlhari>7)return "jumlah hari rapat terlalu banyak";
		for($i=1;$i<=7;$i++){
			if($i<=$jmlhari){
				$header = array(array(
					'hrrapat' => $this->hari(date('l',$tanggal)),
					'tgrapat' => date('d F Y',$tanggal),
					'jamrapat' => $row[0]->jamrapat,
					'jmlhari' => $row[0]->jmlhari,
					'jmljam' => $row[0]->jmljam,
					'ket' => $row[0]->ket
				));
				$tanggal = strtotime(date('Y-m-d',$tanggal). ' + 1 days');
				$TBS->Plugin(OPENTBS_SELECT_SHEET,'Sheet'.$i);
				$TBS->MergeBlock('v', $values);
				$TBS->MergeBlock('p'.$i, $header);				
			}
			else{
				$TBS->Plugin(OPENTBS_DELETE_SHEETS,'Sheet'.$i);
			}
		}

		//download file
		$TBS->Show(OPENTBS_DOWNLOAD,'daftar absen rapat '.date("Y-m-d H:i:s").'.xlsx');	
		/* $TBS->PlugIn(OPENTBS_DEBUG_XML_SHOW); */
	
	}
	
	private function hari($param){
		if($param=="Sunday")return "Minggu";
		elseif($param=="Monday")return "Senin";
		elseif($param=="Tuesday")return "Selasa";
		elseif($param=="Wednesday")return "Rabu";
		elseif($param=="Thursday")return "Kamis";
		elseif($param=="Friday")return "Jumat";
		elseif($param=="Saturday")return "Sabtu";
		else return $param;
	}
	
	public function downloadPerjadin($param)
	{
		$row = DB::select("
			select CONCAT_WS('.',kddept,kdunit,kdprogram,kdgiat,kdoutput,kdsoutput,kdkmpnen,kdakun) as mak,UPPER(ket)ket,DATE_FORMAT(now(),'%d %M %Y') skrg,
			b.nama,b.nip,uang_muka,u_tiket,u_hotel,u_harian,u_representatif,u_taxi,nilai
			 from d_perjadin a
			left outer join (select thang,kdsatker,nip,nama from t_pejabat where kdjabatan='1') b
			on a.kdsatker=b.kdsatker and a.thang=b.thang
			left join (
			select x.id_perjadin,sum(uang_muka)uang_muka,sum(u_tiket)u_tiket,sum(u_hotel)u_hotel,
			sum(u_harian)u_harian,sum(u_representatif)u_representatif,sum(u_taxi)u_taxi,
			sum(nilai)nilai
			from d_perjadin_detil x
			where x.id_perjadin=?
			group by x.id_perjadin
			)c
			on a.id=c.id_perjadin
			where id=?
		", [$param,$param]);
		if ( empty($row)) return 'Data Tidak Ditemukan';
		$header[] = (object) array(
			'mak' => $row[0]->mak,
			'ket' => $row[0]->ket,
			'nmsatker' => Session::get('nmsatker'),
			'nmjubar' => Session::get('nama'),
			'nipjubar' => Session::get('nip'),
			'nmkpa' => $row[0]->nama,
			'nipkpa' => $row[0]->nip,
			'skrg' => $row[0]->skrg,
			'uang_muka'=>number_format($row[0]->uang_muka, 0, ",", "."),
			'u_tiket'=>number_format($row[0]->u_tiket, 0, ",", "."),
			'u_hotel'=>number_format($row[0]->u_hotel, 0, ",", "."),
			'u_harian'=>number_format($row[0]->u_harian, 0, ",", "."),
			'u_representatif'=>number_format($row[0]->u_representatif, 0, ",", "."),
			'u_taxi'=>number_format($row[0]->u_taxi, 0, ",", "."),
			'nilai'=>number_format($row[0]->nilai, 0, ",", ".")
		);
		$rows1 = DB::select("
			select IFNULL(b.nama,c.nama)as nama,
			IFNULL(b.nip,'-')as nip,
			IFNULL(d.nmgol,'-')as nmgol,
			DATE_FORMAT(tanggal, '%d-%m-%Y') as tgl,
			jmlhari,
			e.nmkabkota kabkota_dr,
			f.nmkabkota kabkota_tj,
			uang_muka,
			u_tiket,
			u_hotel,
			u_harian,
			u_representatif,
			u_taxi,
			nilai
			from d_perjadin_detil a
			LEFT OUTER JOIN t_pegawai b
			on a.nip=b.nip
			LEFT OUTER JOIN t_pegawai_non c
			on a.id_peg_non=c.id
			LEFT OUTER JOIN t_gol d
			on b.kdgol=d.kdgol
			LEFT OUTER JOIN t_kabkota e
			on a.kdkabkota_dari=CONCAT(e.kdlokasi,e.kdkabkota)
			LEFT OUTER JOIN t_kabkota f
			on a.kdkabkota_tujuan=CONCAT(f.kdlokasi,f.kdkabkota)
			where a.id_perjadin=?
		", [$param]);
		$i=1;
		foreach($rows1 as $value) {
				$_value = (object)array(
					'no'=>$i,
					'nama'=>$value->nama,
					'nip'=>$value->nip,
					'nmgol'=>$value->nmgol,
					'tgl'=>$value->tgl,
					'jmlhari'=>$value->jmlhari,
					'kabkota_dr'=>$value->kabkota_dr,
					'kabkota_tj'=>$value->kabkota_tj,
					'uang_muka'=>number_format($value->uang_muka, 0, ",", "."),
					'u_tiket'=>number_format($value->u_tiket, 0, ",", "."),
					'u_hotel'=>number_format($value->u_hotel, 0, ",", "."),
					'u_harian'=>number_format($value->u_harian, 0, ",", "."),
					'u_representatif'=>number_format($value->u_representatif, 0, ",", "."),
					'u_taxi'=>number_format($value->u_taxi, 0, ",", "."),
					'nilai'=>number_format($value->nilai, 0, ",", ".")
				);
				$i++;
				$values[]=$_value;
			}
		//instance TBS class
		$TBS = new clsTinyButStrong();
		$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);
		
		//load template 
		$TBS->LoadTemplate('reports/'.'template_daftar_nominatif_perjadin.xlsx');
		$TBS->Plugin(OPENTBS_SELECT_SHEET,'Sheet1');
		$TBS->MergeBlock('v', $values);
		$TBS->MergeBlock('p', $header);
		
		//download file
		$TBS->Show(OPENTBS_DOWNLOAD,'daftar nominatif perjadin '.date("Y-m-d H:i:s").'.xlsx');	
		//~ return "surat-setoran-pajak";
	}
}