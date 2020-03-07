<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ConvertController as KNV;
use Mpdf\Mpdf;
use App\Bukti;
use DB;

class BuktiTransaksiController extends TableController
{
    //
    public static $style = '<style>
		.ac {text-align:center;}
		.al {text-align:left;}
		.aj {text-align:justify;}
		.ar {text-align:right;}
		.ball {border:1px solid;}
		.blr {border-left:1px solid; border-right:1px solid;}
		.btb {border-top:1px solid; border-bottom:1px solid;}
		.bl {border-left:1px solid;}
		.bt {border-top:1px solid;}
		.br {border-right:1px solid;}
		.bb {border-bottom:1px solid;}
		.bo {font-weight:bold;}
		.pad2 {padding:2px 2px 2px 2px;}
	</style>';
	
    public static $perusahaan = 'PERUSAHAAN DAERAH PEMBANGUNAN<br>SARANA JAYA';

    public function __construct()
	{
		//
	}

	/**
	 * description 
	 */
	public function uangMasuk($id)
	{
		$rows = DB::select("
			select  a.id,
					lpad(a.nourut,5,'0') as nourut,
					a.thang,
					a.nodok,
					a.nobuku,
					to_char(a.tgdok,'dd-mm-yyyy') as tgdok,
					b.nama as nmpenerima,
					a.nilai,
					a.nilai_bersih,
					a.uraian,
					i.kdakun as debet,
					c.nmakun,
					nvl(a.nocek,'....................') as nocek,
					to_char(a.tgdok,'dd-mm-yyyy') as tgcek,
					a.id_alur,
					d.nmunit,
					e.nip as nip_ttd1,
					e.nama as nama_ttd1,
					f.nip as nip_ttd2,
					f.nama as nama_ttd2,
					g.nip as nip_ttd3,
					g.nama as nama_ttd3,
					h.nip as nip_ttd4,
					h.nama as nama_ttd4
			from d_trans a
			left join t_penerima b on(a.id_penerima=b.id)
			left join t_akun c on(a.debet=c.kdakun)
			left join t_unit d on(substr(a.kdunit,1,4)=d.kdunit)
			left join t_pejabat e on(a.ttd1=e.id)
			left join t_pejabat f on(a.ttd2=f.id)
			left join t_pejabat g on(a.ttd3=g.id)
			left join t_pejabat h on(a.ttd4=h.id)
			left join d_trans_akun i on(a.id=i.id_trans)
			where a.id=? and i.grup=1 and i.kddk='K'
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$rows_pajak = DB::select("
				select	a.kdakun,
						c.nmakun,
						b.kddk,
						a.nilai
				from d_trans_akun a
				left join t_akun_pajak b on(a.kdakun=b.kdakun)
				left join t_akun c on(b.kdakun=c.kdakun)
				where a.id_trans=? and a.grup=0
				order by b.nourut
			",[
				$id
			]);
			
			$kdakun_pajak1 = '....................';
			$kdakun_pajak2 = '....................';
			$kdakun_pajak3 = '....................';
			$kdakun_pajak4 = '....................';
			$nilai_pajak1 = '....................';
			$nilai_pajak2 = '....................';
			$nilai_pajak3 = '....................';
			$nilai_pajak4 = '....................';
			
			$total_pajak = 0;
			
			if(isset($rows_pajak[0]->kdakun)){
				$kdakun_pajak1 = $rows_pajak[0]->kdakun;
				if($rows_pajak[0]->kddk=='D'){
					$nilai_pajak1 = number_format($rows_pajak[0]->nilai);
					$total_pajak += $rows_pajak[0]->nilai;
				}
				else{
					$nilai_pajak1 = '('.number_format($rows_pajak[0]->nilai).')';
					$total_pajak -= $rows_pajak[0]->nilai;
				}
			}
			
			if(isset($rows_pajak[1]->kdakun)){
				$kdakun_pajak2 = $rows_pajak[1]->kdakun;
				if($rows_pajak[1]->kddk=='D'){
					$nilai_pajak2 = number_format($rows_pajak[1]->nilai);
					$total_pajak += $rows_pajak[1]->nilai;
				}
				else{
					$nilai_pajak2 = '('.number_format($rows_pajak[1]->nilai).')';
					$total_pajak -= $rows_pajak[1]->nilai;
				}
			}
			
			if(isset($rows_pajak[2]->kdakun)){
				$kdakun_pajak3 = $rows_pajak[2]->kdakun;
				if($rows_pajak[2]->kddk=='D'){
					$nilai_pajak3 = number_format($rows_pajak[2]->nilai);
					$total_pajak += $rows_pajak[2]->nilai;
				}
				else{
					$nilai_pajak3 = '('.number_format($rows_pajak[2]->nilai).')';
					$total_pajak -= $rows_pajak[2]->nilai;
				}
			}
			
			if(isset($rows_pajak[3]->kdakun)){
				$kdakun_pajak4 = $rows_pajak[3]->kdakun;
				if($rows_pajak[3]->kddk=='D'){
					$nilai_pajak4 = number_format($rows_pajak[3]->nilai);
					$total_pajak += $rows_pajak[3]->nilai;
				}
				else{
					$nilai_pajak4 = '('.number_format($rows_pajak[3]->nilai).')';
					$total_pajak -= $rows_pajak[3]->nilai;
				}
			}
			
			
			if($total_pajak<0){
				$total_pajak = '('.number_format($total_pajak).')';
			}
			else{
				$total_pajak = number_format($total_pajak);
			}
			
			$rows = (array)$rows[0];
			
			$nama_ttd1 = $rows['nama_ttd1'];
			$nama_ttd2 = $rows['nama_ttd2'];
			$jabatan = '';
			if($rows['nilai']<=20000000){
				$jabatan = '';
				$nama_ttd3 = '';
				$nip_ttd3 = '';
			}
			elseif($rows['nilai']>20000000 && $rows['nilai']<=100000000){
				$jabatan = 'DIREKTUR KEUANGAN';
				$nama_ttd3 = '('.$rows['nama_ttd3'].')';
				$nip_ttd3 = 'NIP/NRK '.$rows['nip_ttd3'];
			}
			elseif($rows['nilai']>100000000){
				$jabatan = 'DIREKTUR UTAMA';
				$nama_ttd3 = '('.$rows['nama_ttd4'].')';
				$nip_ttd3 = 'NIP/NRK '.$rows['nip_ttd4'];
			}
			
			$data = array(
				'nourut' => $rows['nourut'],
				'thang' => $rows['thang'],
				'nmunit' => $rows['nmunit'],
				'nobuku' => $rows['nobuku'],
				'nodok' => $rows['nodok'],
				'tgdok' => $rows['tgdok'],
				'nmpenerima' => $rows['nmpenerima'],
				'nilai' => number_format($rows['nilai']),
				'nilai_bersih' => number_format($rows['nilai_bersih']),
				'total_pajak' => $total_pajak,
				'sejumlah' => ucwords(KNV::terbilang($rows['nilai']).' rupiah'),
				'uraian' => $rows['uraian'],
				'kdakun' => $rows['debet'],
				'nmakun' => $rows['nmakun'],
				'nocek' => $rows['nocek'],
				'tgcek' => $rows['tgcek'],
				'kdakun_pajak1' => $kdakun_pajak1,
				'kdakun_pajak2' => $kdakun_pajak2,
				'kdakun_pajak3' => $kdakun_pajak3,
				'kdakun_pajak4' => $kdakun_pajak4,
				'nilai_pajak1' => $nilai_pajak1,
				'nilai_pajak2' => $nilai_pajak2,
				'nilai_pajak3' => $nilai_pajak3,
				'nilai_pajak4' => $nilai_pajak4,
				'nama_ttd1' => $nama_ttd1,
				'nama_ttd2' => $nama_ttd2,
				'nama_ttd3' => $nama_ttd3,
				'nip_ttd3' => $nip_ttd3,
				'jabatan' => $jabatan,
			);
		
			//~ return view('bukti.uang-keluar', $data);
			$html_out = view('bukti.uang-masuk', $data);

			$mpdf = new Mpdf([
				'mode' => 'utf-8',
				'format' => 'A4-P',
				'margin_left' => 8,
				'margin_right' => 8,
				'margin_top' => 18,
				'margin_bottom' => 18,
			]);

			//mode portrait or landscape
			$mpdf->AddPage('P');

			//write content to PDF
			$mpdf->writeHTML($html_out);
			$mpdf->Output('Bukti Uang Masuk.pdf', 'I');
			exit;
			
		}
		else{
			return 'Data tidak ditemukan!';
		}
	}

	/**
	 * description 
	 */
	public function uangKeluar($id)
	{
		$rows = DB::select("
			select  a.id,
					lpad(a.nourut,5,'0') as nourut,
					a.thang,
					a.nodok,
					a.nobuku,
					to_char(a.tgdok,'dd-mm-yyyy') as tgdok,
					b.nama as nmpenerima,
					a.nilai,
					a.nilai_bersih,
					a.uraian,
					i.kdakun as debet,
					c.nmakun,
					nvl(a.nocek,'....................') as nocek,
					to_char(a.tgdok,'dd-mm-yyyy') as tgcek,
					a.id_alur,
					d.nmunit,
					e.nip as nip_ttd1,
					e.nama as nama_ttd1,
					f.nip as nip_ttd2,
					f.nama as nama_ttd2,
					g.nip as nip_ttd3,
					g.nama as nama_ttd3,
					h.nip as nip_ttd4,
					h.nama as nama_ttd4
			from d_trans a
			left join t_penerima b on(a.id_penerima=b.id)
			left join t_akun c on(a.debet=c.kdakun)
			left join t_unit d on(substr(a.kdunit,1,4)=d.kdunit)
			left join t_pejabat e on(a.ttd1=e.id)
			left join t_pejabat f on(a.ttd2=f.id)
			left join t_pejabat g on(a.ttd3=g.id)
			left join t_pejabat h on(a.ttd4=h.id)
			left join d_trans_akun i on(a.id=i.id_trans)
			where a.id=? and i.grup=1 and i.kddk='D'
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$rows_pajak = DB::select("
				select	a.kdakun,
						c.nmakun,
						b.kddk,
						a.nilai
				from d_trans_akun a
				left join t_akun_pajak b on(a.kdakun=b.kdakun)
				left join t_akun c on(b.kdakun=c.kdakun)
				where a.id_trans=? and a.grup=0
				order by b.nourut
			",[
				$id
			]);
			
			$kdakun_pajak1 = '....................';
			$kdakun_pajak2 = '....................';
			$kdakun_pajak3 = '....................';
			$kdakun_pajak4 = '....................';
			$nilai_pajak1 = '....................';
			$nilai_pajak2 = '....................';
			$nilai_pajak3 = '....................';
			$nilai_pajak4 = '....................';
			
			$total_pajak = 0;
			
			if(isset($rows_pajak[0]->kdakun)){
				$kdakun_pajak1 = $rows_pajak[0]->kdakun;
				if($rows_pajak[0]->kddk=='D'){
					$nilai_pajak1 = number_format($rows_pajak[0]->nilai);
					$total_pajak += $rows_pajak[0]->nilai;
				}
				else{
					$nilai_pajak1 = '('.number_format($rows_pajak[0]->nilai).')';
					$total_pajak -= $rows_pajak[0]->nilai;
				}
			}
			
			if(isset($rows_pajak[1]->kdakun)){
				$kdakun_pajak2 = $rows_pajak[1]->kdakun;
				if($rows_pajak[1]->kddk=='D'){
					$nilai_pajak2 = number_format($rows_pajak[1]->nilai);
					$total_pajak += $rows_pajak[1]->nilai;
				}
				else{
					$nilai_pajak2 = '('.number_format($rows_pajak[1]->nilai).')';
					$total_pajak -= $rows_pajak[1]->nilai;
				}
			}
			
			if(isset($rows_pajak[2]->kdakun)){
				$kdakun_pajak3 = $rows_pajak[2]->kdakun;
				if($rows_pajak[2]->kddk=='D'){
					$nilai_pajak3 = number_format($rows_pajak[2]->nilai);
					$total_pajak += $rows_pajak[2]->nilai;
				}
				else{
					$nilai_pajak3 = '('.number_format($rows_pajak[2]->nilai).')';
					$total_pajak -= $rows_pajak[2]->nilai;
				}
			}
			
			if(isset($rows_pajak[3]->kdakun)){
				$kdakun_pajak4 = $rows_pajak[3]->kdakun;
				if($rows_pajak[3]->kddk=='D'){
					$nilai_pajak4 = number_format($rows_pajak[3]->nilai);
					$total_pajak += $rows_pajak[3]->nilai;
				}
				else{
					$nilai_pajak4 = '('.number_format($rows_pajak[3]->nilai).')';
					$total_pajak -= $rows_pajak[3]->nilai;
				}
			}
			
			
			if($total_pajak<0){
				$total_pajak = '('.number_format($total_pajak).')';
			}
			else{
				$total_pajak = number_format($total_pajak);
			}
			
			$rows = (array)$rows[0];
			
			$nama_ttd1 = $rows['nama_ttd1'];
			$nama_ttd2 = $rows['nama_ttd2'];
			$jabatan = '';
			if($rows['id_alur']==9){
				$jabatan = '';
				$nama_ttd3 = '';
				$nip_ttd3 = '';
			}
			elseif($rows['id_alur']==7){
				$jabatan = 'DIREKTUR KEUANGAN';
				$nama_ttd3 = '('.$rows['nama_ttd3'].')';
				$nip_ttd3 = 'NIP/NRK '.$rows['nip_ttd3'];
			}
			elseif($rows['id_alur']==8){
				$jabatan = 'DIREKTUR UTAMA';
				$nama_ttd3 = '('.$rows['nama_ttd4'].')';
				$nip_ttd3 = 'NIP/NRK '.$rows['nip_ttd4'];
			}
			
			$data = array(
				'nourut' => $rows['nourut'],
				'thang' => $rows['thang'],
				'nmunit' => $rows['nmunit'],
				'nobuku' => $rows['nobuku'],
				'nodok' => $rows['nodok'],
				'tgdok' => $rows['tgdok'],
				'nmpenerima' => $rows['nmpenerima'],
				'nilai' => number_format($rows['nilai']),
				'nilai_bersih' => number_format($rows['nilai_bersih']),
				'total_pajak' => $total_pajak,
				'sejumlah' => ucwords(KNV::terbilang($rows['nilai']).' rupiah'),
				'uraian' => $rows['uraian'],
				'kdakun' => $rows['debet'],
				'nmakun' => $rows['nmakun'],
				'nocek' => $rows['nocek'],
				'tgcek' => $rows['tgcek'],
				'kdakun_pajak1' => $kdakun_pajak1,
				'kdakun_pajak2' => $kdakun_pajak2,
				'kdakun_pajak3' => $kdakun_pajak3,
				'kdakun_pajak4' => $kdakun_pajak4,
				'nilai_pajak1' => $nilai_pajak1,
				'nilai_pajak2' => $nilai_pajak2,
				'nilai_pajak3' => $nilai_pajak3,
				'nilai_pajak4' => $nilai_pajak4,
				'nama_ttd1' => $nama_ttd1,
				'nama_ttd2' => $nama_ttd2,
				'nama_ttd3' => $nama_ttd3,
				'nip_ttd3' => $nip_ttd3,
				'jabatan' => $jabatan,
			);
		
			//~ return view('bukti.uang-keluar', $data);
			$html_out = view('bukti.uang-keluar', $data);

			$mpdf = new Mpdf([
				'mode' => 'utf-8',
				'format' => 'A4-P',
				'margin_left' => 8,
				'margin_right' => 8,
				'margin_top' => 18,
				'margin_bottom' => 18,
			]);

			//mode portrait or landscape
			$mpdf->AddPage('P');

			//write content to PDF
			$mpdf->writeHTML($html_out);
			$mpdf->Output('Bukti Uang Keluar.pdf', 'I');
			exit;
			
		}
		else{
			return 'Data tidak ditemukan!';
		}
	}

    /**
	 * description 
	 */
	public function uangMukaKerja($id)
    {
		$rows = DB::select("
			select  a.id,
					lpad(a.nourut,5,'0') as nourut,
					a.thang,
					a.nodok,
					a.nobuku,
					to_char(a.tgdok,'dd-mm-yyyy') as tgdok,
					to_char(a.tgdok1,'dd-mm-yyyy') as tgdok1,
					b.nama as nmpenerima,
					a.nilai,
					a.nilai_bersih,
					a.uraian,
					i.kdakun as debet,
					c.nmakun,
					nvl(a.nocek,'....................') as nocek,
					to_char(a.tgdok,'dd-mm-yyyy') as tgcek,
					a.id_alur,
					d.nmunit,
					e.nip as nip_ttd1,
					e.nama as nama_ttd1,
					f.nip as nip_ttd2,
					f.nama as nama_ttd2,
					g.nip as nip_ttd3,
					g.nama as nama_ttd3,
					h.nip as nip_ttd4,
					h.nama as nama_ttd4
			from d_trans a
			left join t_penerima b on(a.id_penerima=b.id)
			left join t_akun c on(a.debet=c.kdakun)
			left join t_unit d on(substr(a.kdunit,1,4)=d.kdunit)
			left join t_pejabat e on(a.ttd1=e.id)
			left join t_pejabat f on(a.ttd2=f.id)
			left join t_pejabat g on(a.ttd3=g.id)
			left join t_pejabat h on(a.ttd4=h.id)
			left join d_trans_akun i on(a.id=i.id_trans)
			where a.id=? and i.grup=1 and i.kddk='D'
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$rows = (array)$rows[0];
			
			$data = array(
				'nourut' => $rows['nourut'],
				'thang' => $rows['thang'],
				'nmunit' => $rows['nmunit'],
				'nobuku' => $rows['nobuku'],
				'nodok' => $rows['nodok'],
				'tgdok' => $rows['tgdok'],
				'tgdok1' => $rows['tgdok1'],
				'nmpenerima' => $rows['nmpenerima'],
				'nilai' => number_format($rows['nilai']),
				'nilai_bersih' => number_format($rows['nilai_bersih']),
				'sejumlah' => ucwords(KNV::terbilang($rows['nilai']).' rupiah'),
				'uraian' => $rows['uraian'],
				'kdakun' => $rows['debet'],
				'nmakun' => $rows['nmakun'],
				'nocek' => $rows['nocek'],
				'tgcek' => $rows['tgcek'],
				'nama_ttd1' => $rows['nama_ttd3'],
				'nama_ttd2' => $rows['nama_ttd4']
			);
		
			//~ return view('bukti.uang-keluar', $data);
			$html_out = view('bukti.uang-muka-baru', $data);

			$mpdf = new Mpdf([
				'mode' => 'utf-8',
				'format' => 'A4-P',
				'margin_left' => 8,
				'margin_right' => 8,
				'margin_top' => 18,
				'margin_bottom' => 18,
			]);

			//mode portrait or landscape
			$mpdf->AddPage('P');

			//write content to PDF
			$mpdf->writeHTML($html_out);
			$mpdf->Output('Bukti Uang Muka.pdf', 'I');
			exit;
			
		}
		else{
			return 'Data tidak ditemukan!';
		}
		
    }
	
	public function tandaTerima($id)
	{
		$rows = DB::select("
			select  a.id,
					a.thang,
					lpad(a.nourut,5,'0') as nourut,
					a.kdtran,
					a.kdtran_dtl,
					c.uraian as nmtrans,
					b.nmunit,
					d.nama as nmrekam
			from d_trans a
			left join t_unit b on(substr(a.kdunit,1,4)=b.kdunit)
			left join t_trans_dtl c on(a.kdtran_dtl=c.id)
			left join t_user d on(a.id_user=d.id)
			where a.id=?
		",[
			$id
		]);
		
		$data = (array)$rows[0];
		
		$rows_detil = DB::select("
			select  b.id,
					b.uraian,
					decode(d.nmfile,null,0,1) as cek
			from t_trans_dtl_dok a
			left join t_dok_dtl b on(a.id_dok_dtl=b.id)
			left join t_trans_dtl c on(a.id_trans_dtl=c.id)
			left join(
				select  id_dok_dtl,
						nmfile
				from d_trans_dok
				where id_trans=?
			) d on(a.id_dok_dtl=d.id_dok_dtl)
			where a.id_trans_dtl=?
			order by a.id
		",[
			$rows[0]->id,
			$rows[0]->kdtran_dtl
		]);
		
		$tabel = '';
		$i = 1;
		foreach($rows_detil as $row){
			
			$upload = 'Belum upload';
			if($row->cek==1){
				$upload = 'Sudah upload';
			}
			
			$tabel .= '<tr>
						<td>'.$i++.'</td>
						<td>'.$row->uraian.'</td>
						<td>'.$upload.'</td>
						<td style="text-align:center;"></td>
						<td style="text-align:center;"></td>
						<td></td>
					   </tr>';
		}
		
		$data['detil'] = $tabel;
		
		$rows = DB::select("
			select	*
			from t_pejabat
			where kdlevel='09'
		");
		
		$data['nmverifikasi'] = '';
		if(count($rows)>0){
			$data['nmverifikasi'] = $rows[0]->nama;
		}
		
		$html_out = view('bukti.tanda-terima', $data);
		
		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4-P',
			'margin_left' => 8,
			'margin_right' => 8,
			'margin_top' => 18,
			'margin_bottom' => 18,
		]);

		//mode portrait or landscape
		$mpdf->AddPage('P');

		//write content to PDF
		$mpdf->writeHTML($html_out);
		$mpdf->Output('Tanda Terima.pdf', 'I');
		exit;
		
	}

}
