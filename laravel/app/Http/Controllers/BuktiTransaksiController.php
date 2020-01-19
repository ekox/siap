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
					to_char(a.tgdok,'dd-mm-yyyy') as tgdok,
					b.nama as nmpenerima,
					a.nilai,
					a.uraian,
					a.kredit,
					c.nmakun,
					decode(a.id_alur,2,'Cash',3,'Cek','N/A') as bayar
			from d_trans a
			left join t_penerima b on(a.id_penerima=b.id)
			left join t_akun c on(a.kredit=c.kdakun)
			where a.id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$rows = (array)$rows[0];
			
			$rows_ttd = DB::select("
				select  '1' as kode,
						nvl(b.nama,'N/A') as nmttd1,
						nvl(c.nama,'N/A') as nmttd2
				from dual a,
				(
					select  nama
					from t_pejabat
					where kdlevel='04'
				) b,
				(
					select  nama
					from t_pejabat
					where kdlevel='06'
				) c
			");
			
			$nmttd1 = 'N/A';
			$nmttd2 = 'N/A';
			if(count($rows_ttd)>0){
				$nmttd1 = $rows_ttd[0]->nmttd1;
				$nmttd2 = $rows_ttd[0]->nmttd2;
			}
			
			$data = array(
				'nourut' => $rows['nourut'],
				'thang' => $rows['thang'],
				'nodok' => $rows['nodok'],
				'tgdok' => $rows['tgdok'],
				'nmpenerima' => $rows['nmpenerima'],
				'nilai' => number_format($rows['nilai']),
				'sejumlah' => ucwords(KNV::terbilang($rows['nilai']).' rupiah'),
				'uraian' => $rows['uraian'],
				'kredit' => $rows['kredit'],
				'nmakun' => $rows['nmakun'],
				'bayar' => $rows['bayar'],
				'ttd1' => $nmttd1,
				'ttd2' => $nmttd2
			);

			//~ return view('bukti.uang-masuk', $data);
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
					to_char(a.tgdok,'dd-mm-yyyy') as tgdok,
					b.nama as nmpenerima,
					a.nilai,
					a.nilai_bersih,
					a.uraian,
					a.debet,
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
			where a.id=?
		",[
			$id
		]);
		
		if(count($rows)>0){
			
			$rows_pajak = DB::select("
				select	a.kdakun,
						c.nmakun,
						b.kddk,
						a.nilai
				from d_trans_pajak a
				left join t_akun_pajak b on(a.kdakun=b.kdakun)
				left join t_akun c on(b.kdakun=c.kdakun)
				where a.id_trans=?
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
		$tahun = session('tahun');
		
		$namaBerkas = 'Pengeluaran Kas/Bank untuk Uang Muka Kerja';
		$noDokumen = 'No.:    PSJ/FM/DKA/MRI/01';
		$tglBerlaku = '';

		$data = Bukti::queryUangMukaKerja($tahun, $id);

		$html_out = self::$style;
		$html_out.= '<table border="0" cellspacing="0" cellpadding="1" width="100%" style="font-size:10px;">';
		$html_out.= self::$tbody_open;

		$html_out.= '<tr>
			<td colspan="7" class="">Sudah diterima dari P.D. Pembangunan Sarana Jaya :</td>
		</tr>';

		$html_out.= '<tr>
			<td colspan="7">&nbsp;</td>
		</tr>';

		$html_out.= '<tr>
			<td width="20%" class="">Sebesar</td>
			<td width="2%" class="">:</td>
			<td colspan="5" class="">'.self::cFmt($data->nilai).'</td>
		</tr>';

		$html_out.= '<tr>
			<td>Terbilang</td>
			<td>:</td>
			<td colspan="5">'.ucwords(KNV::terbilang($data->nilai).' rupiah').'</td>
		</tr>';

		$html_out.= '<tr>
			<td>Untuk Keperluan</td>
			<td>:</td>
			<td colspan="5">'.$data->uraian.'</td>
		</tr>';

		$html_out.= '<tr>
			<td>Nomor Mata Anggaran</td>
			<td>:</td>
			<td colspan="5">'.$data->kdakun.' ('.$data->nmakun.')</td>
		</tr>';

		$html_out.= '<tr>
			<td>&nbsp;</td>
			<td></td>
			<td width="10%">RKAP</td>
			<td width="2%">:</td>
			<td colspan="3"></td>
		</tr>';

		$html_out.= '<tr>
			<td>&nbsp;</td>
			<td></td>
			<td width="10%">Realisasi</td>
			<td width="2%">:</td>
			<td colspan="3"></td>
		</tr>';

		$html_out.= '<tr>
			<td>&nbsp;</td>
			<td></td>
			<td width="10%">Sisa</td>
			<td width="2%">:</td>
			<td colspan="3"></td>
		</tr>';

		$html_out.= '<tr>
			<td colspan="7">&nbsp;</td>
		</tr>';

		$html_out.= '<tr>
			<td colspan="5">Uang tersebut kami pertanggungjawabkan pada tanggal :</td>
			<td colspan="2"></td>
		</tr>';

		$html_out.= self::$tbody_close;	
		$html_out.= '</table>';

		$html_out.= '<br><br>';
		
		$html_out.= '<table border="0" cellspacing="0" cellpadding="3" width="100%" style="font-size:10px;">';
		$html_out.= self::$tbody_open;
		$html_out.= '<tr>
			<td colspan="2" width="70%">&nbsp;</td>
			<td width="30%" style="padding-right:1em;" class="ac">Jakarta, ..................... '.$data->thang.'</td>
		</tr>';

		$html_out.= '<tr>
			<td colspan="3">&nbsp;</td>
		</tr>';

		$html_out.= '<tr>
			<td class="ac">Menyetujui,</td>
			<td class="ac">Pemohon</td>
			<td class="ac">Penerima,</td>
		</tr>';
		
		$html_out.= '<tr>
			<td class="ac">SM. Divisi Keuangan & Akt.</td>
			<td class="ac">SM. Divisi Umum & SDM</td>
			<td class="ac">&nbsp;</td>
		</tr>';
		
		$html_out.= '<tr>
			<td colspan="3">&nbsp;</td>
		</tr>';

		$html_out.= '<tr>
			<td colspan="3">&nbsp;</td>
		</tr>';

		$html_out.= '<tr>
			<td colspan="3">&nbsp;</td>
		</tr>';
		
		$seniorMgrKeu = Bukti::getSeniorManagerKeuangan()->nama;
		$seniorDivUmum = Bukti::getDivisiUmumSDM()->nama;
		$penerimaUMK = Bukti::getPenerimaUangMuka($id)->nama;
		
		$html_out.= '<tr>
			<td class="ac">'.$seniorMgrKeu.'</td>
			<td class="ac">'.$seniorDivUmum.'</td>
			<td class="ac">'.$penerimaUMK.'</td>
		</tr>';
		
		$html_out.= self::$tbody_close;
		$html_out.= '</table>';

		//~ return $html_out;
		//~ require_once 'laravel/vendor/autoload.php';
		$mpdf = new Mpdf([
			'mode' => 'utf-8',
			'format' => 'A4-P',
			'margin_left' => 15,
			'margin_right' => 15,
			'margin_top' => 18,
			'margin_bottom' => 18,
		]);

		//mode portrait or landscape
		$mpdf->AddPage('P');

		//write content to PDF
		$mpdf->writeHTML($html_out);
		$mpdf->Output('Bukti Uang Muka Kerja.pdf', 'I');
		exit;
		
    }

}
