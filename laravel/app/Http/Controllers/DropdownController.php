<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Dropdown;

class DropdownController extends Controller {

	public static $html_out = '<option value="" style="display:none;">Pilih Data</option>';
	
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
	
	public function dok($id_trans)
	{
		$rows = DB::select("
			select  b.*
			from t_alur_dok a
			left outer join t_dok b on(a.id_dok=b.id)
			where a.id_alur=(
				select	id_alur
				from d_trans
				where id=?
			)
			order by b.id asc
		",[
			$id_trans
		]);
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'">'.$row->uraian.'</option>';
		}
		
		return $data;
		
	}
	
	public function dokTransaksi($id_dok)
	{
		$rows = DB::select("
			select  b.id,
					b.uraian,
					b.ukuran,
					b.tipe
			from t_dok b
			where b.id=?
			order by b.id asc
		",[
			$id_dok
		]);
		
		$data = '';
		foreach($rows as $row){
			$data .= '<div class="form-group row">
						<label class="col-md-2 label-control" for="uraian">'.$row->uraian.' ('.$row->ukuran.'MB|'.$row->tipe.')</label>
						<div class="col-md-9">
							<span class="btn btn-primary fileinput-button">
								<i class="fa fa-upload"></i>
								<span>Browse File</span>
								<input id="fileupload'.$row->id.'" type="file" name="file">
							</span>
							<!-- The global progress bar -->
							<div id="files'.$row->id.'" class="files"></div>
							<div id="progress'.$row->id.'" class="progress">
								<div class="progress-bar progress-bar-danger"></div>
							</div>
						</div>
					</div>';
					
			$data .= "
					<script>
						jQuery('#fileupload".$row->id."').click(function(){
							jQuery('#progress".$row->id." .progress-bar').css('width', 0);
							jQuery('#progress".$row->id." .progress-bar').html('');
							jQuery('#nmfile".$row->id."').html('');
						});
						
						jQuery.get('token', function(result){
							
							//upload adk
							jQuery('#fileupload".$row->id."').fileupload({
								url:'penerimaan/rekam/upload/".$row->id."',
								dataType: 'json',
								formData:{
									_token: result
								},
								done: function (e, data) {
									jQuery('#nmfile".$row->id."').html(data.files[0].name);
									alertify.log('Data berhasil diupload!');
								},
								error: function(error) {
									alertify.log(error.responseText);
								},
								progressall: function (e, data) {
									var progress = parseInt(data.loaded / data.total * 100, 10);
									jQuery('#progress".$row->id." .progress-bar').css('width',progress + '%');
								}
							}).prop('disabled', !$.support.fileInput)
							  .parent().addClass($.support.fileInput ? undefined : 'disabled');
							
						});
					</script>";
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
	
	public function tagihan()
	{
		$rows = DB::select("
			select  a.id,
					a.nopks,
					to_char(a.tgpks,'dd-mm-yyyy') as tgpks,
					a.nilai
			from d_tagih a
			left outer join d_terima b on(a.id=b.id_tagih)
			where b.id_tagih is null
			order by a.id asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'">PKS : '.$row->nopks.', '.$row->tgpks.', Nilai Rp. '.number_format($row->nilai).',-</option>';
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
	
	public function transaksiByParam($param)
	{
		$rows = DB::select("
			select  *
			from t_trans
			where id_alur=?
			order by id asc
		",[
			$param
		]);
		
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
	
	public function alurTagihan()
	{
		$rows = DB::select("
			select	*
			from t_alur
			where menu=1
			order by id asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'"> '.$row->nmalur.'</option>';
		}
		
		return $data;
		
	}
	
	public function alurPenerimaan()
	{
		$rows = DB::select("
			select	*
			from t_alur
			where menu=2
			order by id asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'"> '.$row->nmalur.'</option>';
		}
		
		return $data;
		
	}
	
	public function alurUmk()
	{
		$rows = DB::select("
			select	*
			from t_alur
			where menu=3
			order by id asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'"> '.$row->nmalur.'</option>';
		}
		
		return $data;
		
	}
	
	public function alurPengeluaran()
	{
		$rows = DB::select("
			select	*
			from t_alur
			where menu=4
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
	
	public function pelanggan()
	{
		$rows = DB::select("
			select	*
			from t_pelanggan
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
			where lvl=6 ".$and."
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
	
	public function akun_belanja()
	{
		$rows = DB::select("
			select	*
			from t_akun
			where substr(kdakun,1,1)='5' and lvl=6
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
	
	public function akun_pajak_json()
	{
		$rows = DB::select("
			select  a.kdakun,
					b.nmakun,
					a.nilai,
					a.kddk
			from t_akun_pajak a
			left join t_akun b on(a.kdakun=b.kdakun)
			order by a.nourut
		");
		
		return response()->json($rows);
		
	}
	
	public function akun_debet($id_trans)
	{
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
			$id_trans
		]);
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->kdakun.'"> '.$row->nmakun.'</option>';
		}
		
		return $data;
		
	}
	
	public function akun_kredit($id_trans)
	{
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
			$id_trans
		]);
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->kdakun.'"> '.$row->nmakun.'</option>';
		}
		
		return $data;
		
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

	/**
	 * description 
	 */
	public function tahun()
	{
		$rows = Dropdown::getTahun();
		
		$html_out = self::$html_out;
		foreach($rows as $row) {
			$html_out.= '<option value="'.$row->tahun.'">'.$row->tahun.'</option>';
		}
		return $html_out;
	}
	
	public function proyek()
	{
		$rows = DB::select("
			select	*
			from t_proyek
			order by nmproyek asc
		");
		
		$data = '<option value="" style="display:none;">Pilih Data</option>';
		foreach($rows as $row){
			$data .= '<option value="'.$row->id.'-'.$row->id_penerima.'"> '.$row->nmproyek.' : Rp.'.number_format($row->nilai).',-</option>';
		}
		
		return $data;
		
	}
	
	public function nourut($param)
	{
		$rows = DB::select("
			select  nvl(lpad(max(nvl(a.nourut,0))+1,5,'0'),'00001') as nourut
			from d_trans a
			left join t_alur b on(a.id_alur=b.id)
			where a.thang=? and b.menu=?
		",[
			session('tahun'),
			$param
		]);
		
		if(count($rows)>0){
			return $rows[0]->nourut;
		}
		else{
			return '00001';
		}
		
	}
}
