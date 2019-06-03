<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DropdownController extends Controller {

	public function index(Request $request)
	{
	}
	
	public function unit_all()
	{
		$rows = DB::select("
			select  kdunit,
					nmunit
			from t_unit
			order by to_number(kdunit) asc
		");
		
		$data = '<option value="">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->kdunit.'">'.$row->nmunit.'</option>';
		}
		
		return $data;
		
	}
	
	public function unit($param)
	{
		$rows = DB::select("
			select  kdunit,
					nmunit
			from t_unit
			where length(kdunit)=?
			order by to_number(kdunit) asc
		",[
			$param
		]);
		
		$data = '<option value="">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->kdunit.'">'.$row->nmunit.'</option>';
		}
		
		return $data;
		
	}
	
	public function output()
	{
		$rows = DB::select("
			select  *
			from t_output
			order by id asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'">'.$row->uraian.'</option>';
		}
		
		return $data;
		
	}
	
	public function kegiatan()
	{
		$rows = DB::select("
			select  *
			from t_kegiatan
			order by id asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'">'.$row->uraian.'</option>';
		}
		
		return $data;
		
	}
	
	public function transaksi()
	{
		$rows = DB::select("
			select  *
			from t_trans
			order by id asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'">'.$row->nmtrans.'</option>';
		}
		
		return $data;
		
	}
	
	public function level()
	{
		$rows = DB::select("
			select	*
			from t_level
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->kdlevel.'"> '.$row->nmlevel.'</option>';
		}
		
		return $data;
		
	}
	
	public function jenis_pagu()
	{
		$rows = DB::select("
			select	*
			from t_jnspagu
			order by id asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'"> '.$row->uraian.'</option>';
		}
		
		return $data;
		
	}
	
	public function alur()
	{
		$rows = DB::select("
			select	*
			from t_alur
			order by id asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'"> '.$row->nmalur.'</option>';
		}
		
		return $data;
		
	}
	
	public function penerima()
	{
		$rows = DB::select("
			select	*
			from t_penerima
			order by nama asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'"> '.$row->nama.'</option>';
		}
		
		return $data;
		
	}
	
	public function bank()
	{
		$rows = DB::select("
			select	*
			from t_bank
			order by kdbank asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->kdbank.'"> '.$row->nmbank.'</option>';
		}
		
		return $data;
		
	}
	
	public function lap()
	{
		$rows = DB::select("
			select	*
			from t_lap
			order by kdlap asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->kdlap.'"> '.$row->nmlap.'</option>';
		}
		
		return $data;
		
	}
	
	public function akun_html_level1()
	{
		$and = "";
		if(isset($_GET['kdlap'])){
			if($_GET['kdlap']!==''){
				$and = " and kdlap='".$_GET['kdlap']."' ";
			}
		}
		
		$rows = DB::select("
			select	*
			from t_akun
			where kddk is not null ".$and."
			order by kdakun asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->kdakun.'"> '.$row->nmakun.' | '.$row->kdakun.' | '.$row->kddk.'</option>';
		}
		
		return $data;
		
	}
	
	public function akun_html_all()
	{
		$rows = DB::select("
			select	*
			from t_akun
			order by kdakun asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->kdakun.'"> '.$row->nmakun.' | '.$row->kdakun.'</option>';
		}
		
		return $data;
		
	}
	
	public function akun_json()
	{
		$rows = DB::select("
			select	kdakun,
					nmakun
			from t_akun
			order by kdakun asc
		");
		
		return response()->json($rows);
		
	}
	
	public function periode()
	{
		$rows = DB::select("
			select	*
			from t_bulan
			order by bulan asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->bulan.'"> '.$row->nmbulan.'</option>';
		}
		
		return $data;
		
	}
	
}