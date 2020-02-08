<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Bukubesar;
use clsTinyButStrong;

class BukuBesarController extends Controller
{
    /**
	 * description 
	 */
	public function excel(Request $request)
	{
		if(isset($_GET['periode'])) {
			$periode = $_GET['periode'];
		} else {
			return '<script type="text/javascript">alert("Periode wajib diisi!");</script>';
		}
		
		if(isset($_GET['kdakun'])) {
			$kdakun = $_GET['kdakun'];
		} else {
			return '<script type="text/javascript">alert("Kode akun wajib diisi!");</script>';
		}
		
		$tahun = session('tahun');
		$periode = $_GET['periode'];
		$kdakun = $_GET['kdakun'];
		
		$rows = Bukubesar::getAllData($tahun, $periode, $kdakun);
		//$sum = Bukubesar::getSumData($tahun, $periode, $kdakun);

		if(count($rows) > 0) {		
		
			$tot_debet = 0;
			$tot_kredit = 0;
			$saldo = 0;
			foreach($rows as $row) {
				$val = (object) array(
					'tahun' => $tahun,
					'kdakun' => $row->kdakun,
					'nmakun' => $row->nmakun,
					'tanggal' => $row->tanggal,
					'no_voucher' => $row->no_voucher,
					'kd_pc' => $row->kd_pc,
					'remark' => $row->remark,
					'debet' => $row->debet,
					'kredit' => $row->kredit,
					'saldo' => $row->saldo,
				);

				$values[] = $val;

				$tot_debet += $row->debet;
				$tot_kredit += $row->kredit;
				$saldo = $row->saldo;
			}

			$param[] = array(
				'tahun' => $tahun,
				'kdakun' => $kdakun,
				'bulan'=> $rows[0]->bulan,
				'nmakun'=> $rows[0]->nmakun,
				'debet' => $tot_debet,
				'kredit' => $tot_kredit,
				'saldo' => $saldo
			);

			$TBS = new clsTinyButStrong();
			$TBS->Plugin(TBS_INSTALL, OPENTBS_PLUGIN);	
			
			//load template in folder /doc
			$TBS->LoadTemplate('tbs_template/'.'template_buku_besar.xlsx');
			
			$TBS->Plugin(OPENTBS_SELECT_SHEET,'Sheet1');
			$TBS->MergeBlock('p', $param);
			$TBS->MergeBlock('v', $values);
			
			//download file
			header('Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
			$TBS->Show(OPENTBS_DOWNLOAD,'Buku_besar_'.$kdakun.'.xlsx');
			
		} else {
		
			return '<script type="text/javascript">alert("Data tidak ditemukan!");</script>';
			
		}
	}
}
