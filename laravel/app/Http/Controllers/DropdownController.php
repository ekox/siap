<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class DropdownController extends Controller {

	public function index(Request $request)
	{
	}
	
	public function kso()
	{
		$rows = DB::select("
			select	a.id,
					a.nama,
					a.nopks
			from d_kso a
			left outer join(
				select	distinct id_kso
				from d_kso_user
				where id_user=".session('id_user')."
			) b on(a.id=b.id_kso)
			order by a.nama asc
		");
		
		$data = '<option value="">Pilih KSO</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'">'.$row->nama.', Nomor PKS : '.$row->nopks.'</option>';
		}
		
		return $data;
		
	}
	
	public function owner()
	{
		$rows = DB::select("
			select	a.id,
					a.nama,
					a.npwp
			from t_perusahaan a
			where a.owner='1'
			order by a.nama asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Owner</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'">'.$row->nama.', NPWP : '.$row->npwp.'</option>';
		}
		
		return $data;
		
	}
	
	public function mk()
	{
		$rows = DB::select("
			select	a.id,
					a.nama,
					a.npwp
			from t_perusahaan a
			where a.mk='1'
			order by a.nama asc
		");
		
		$data = '<option value="" style="display:none;">Pilih MK</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'">'.$row->nama.', NPWP : '.$row->npwp.'</option>';
		}
		
		return $data;
		
	}
	
	public function qs()
	{
		$rows = DB::select("
			select	a.id,
					a.nama,
					a.npwp
			from t_perusahaan a
			where a.qs='1'
			order by a.nama asc
		");
		
		$data = '<option value="" style="display:none;">Pilih QS</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'">'.$row->nama.', NPWP : '.$row->npwp.'</option>';
		}
		
		return $data;
		
	}
	
	public function kon()
	{
		$rows = DB::select("
			select	a.id,
					a.nama,
					a.npwp
			from t_perusahaan a
			where a.kontraktor='1'
			order by a.nama asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Kontraktor</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'">'.$row->nama.', NPWP : '.$row->npwp.'</option>';
		}
		
		return $data;
		
	}
	
	public function jabatan()
	{
		$rows = DB::select("
			select	*
			from t_jabatan
			order by kdjab asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Jabatan</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->kdjab.'">'.$row->nmjab.'</option>';
		}
		
		return $data;
		
	}
	
	public function tahun()
	{
		$rows = DB::select("
			select	*
			from t_tahun
			order by tahun asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Tahun</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->tahun.'">'.$row->tahun.'</option>';
		}
		
		return $data;
		
	}
	
	public function bulan()
	{
		$rows = DB::select("
			select	*
			from t_bulan
			order by bulan asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Bulan</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->bulan.'">'.$row->nmbulan.'</option>';
		}
		
		return $data;
		
	}
	
	public function user()
	{
		$rows = DB::select("
			select	id_user,
					username,
					nama,
					nik
			from t_user
			order by nama asc
		");
		
		$data = '<option value="" style="display:none;">Pilih User</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id_user.'">'.$row->username.' - '.$row->nik.' - '.$row->nama.'</option>';
		}
		
		return $data;
		
	}
	
	public function satuan()
	{
		$rows = DB::select("
			select	id,
					satuan
			from t_satuan
			order by id asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Satuan</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'">'.$row->satuan.'</option>';
		}
		
		return $data;
		
	}
	
	public function dok_teknis($id_kso)
	{
		$rows = DB::select("
			select	id,
					kode,
					uraian
			from d_kso_teknis
			where id_kso=? and is_nilai='0'
			order by id asc
		",[
			$id_kso
		]);
		
		$data = '<option value="">Pilih Dok. Teknis</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'">'.$row->kode.' - '.$row->uraian.'</option>';
		}
		
		return $data;
		
	}
	
	public function lvl_teknis()
	{
		$rows = DB::select("
			select	lvl
			from t_lvl_teknis
			order by lvl asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Level Dok</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->lvl.'">Level '.$row->lvl.'</option>';
		}
		
		return $data;
		
	}
	
	public function lvl_teknis_by_id($param)
	{
		$rows = DB::select("
			select	lvl
			from t_lvl_teknis
			where lvl<=(
				select	lvl
				from d_kso
				where id=?
			)
			order by lvl asc
		",[
			$param
		]);
		
		$data = '<option value="" style="display:none;">Pilih Level Dok</option>';
		foreach($rows as $row){
			if($row->lvl<count($rows)){
				$data .= '<option value="'.$row->lvl.'-0">Level '.$row->lvl.'</option>';
			}
			else{
				$data .= '<option value="'.$row->lvl.'-1">Level '.$row->lvl.'</option>';
			}
		}
		
		return $data;
		
	}
	
	public function tagihan()
	{
		$rows = DB::select("
			select	*
			from t_tagihan
		");
		
		$data = '<option value="" style="display:none;">Pilih Jenis Tagihan</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->kdtagihan.'"> '.$row->nmtagihan.'</option>';
		}
		
		return $data;
		
	}
	
	public function perusahaan()
	{
		$rows = DB::select("
			select	*
			from t_perusahaan
		");
		
		$data = '<option value="" style="display:none;">Pilih Perusahaan</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'"> '.$row->nama.'</option>';
		}
		
		return $data;
		
	}
	
	public function level()
	{
		$rows = DB::select("
			select	*
			from t_level
		");
		
		$data = '<option value="" style="display:none;">Pilih Level</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->kdlevel.'"> '.$row->nmlevel.'</option>';
		}
		
		return $data;
		
	}
	
	public function jenis_usaha()
	{
		$rows = DB::select("
			select	*
			from t_jenis_usaha
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->kdjenis.'"> '.$row->nmjenis.'</option>';
		}
		
		return $data;
		
	}
	
}