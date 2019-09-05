<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProfileController extends Controller {

	public function index()
	{
		$data['error'] = true;
		$data['message'] = '';
		
		$rows = DB::select("
			select  username,
					nama,
					nik,
					email,
					foto
			from t_user
			where id=?
		",
			[session('id_user')]
		);
		
		if(count($rows)>0){
			
			$data['error'] = false;
			$data['message'] = $rows[0];
			session(['upload_foto_user' => $rows[0]->foto]);
			
			$rows = DB::select("
				select  a.kdlevel,
						b.nmlevel,
						a.status
				from t_user_level a
				left outer join t_level b on(a.kdlevel=b.kdlevel)
				where a.id_user=?
				order by a.kdlevel asc
			",[
				session('id_user')
			]);
			
			if(count($rows)>0){
				$kdlevel = '';
				foreach($rows as $row){
					$checked = '';
					if($row->status=='1'){
						$checked = 'selected';
					}
					$kdlevel .= '<option value="'.$row->kdlevel.'" '.$checked.'>'.$row->nmlevel.'</option>';
				}
				
				$data['kdlevel'] = $kdlevel;
			}
			else{
				$data['kdlevel'] = '';
			}
			
			$rows = DB::select("
				select  a.kdunit,
                        b.nmunit,
                        a.status
                from t_user_unit a
                left outer join t_unit b on(a.kdunit=b.kdunit)
                where a.id_user=?
			",[
				session('id_user')
			]);
			
			if(count($rows)>0){
				$kdunit = '';
				foreach($rows as $row){
					$checked = '';
					if($row->status=='1'){
						$checked = 'selected';
					}
					$kdunit .= '<option value="'.$row->kdunit.'" '.$checked.'>'.$row->nmunit.'</option>';
				}
				
				$data['kdunit'] = $kdunit;
			}
			else{
				$data['kdunit'] = '';
			}
			
		}
		
		return response()->json($data);
	}
	
	public function ubah(Request $request)
	{
		try {
			DB::beginTransaction();
			
			$rows = DB::select("
				select	*
				from t_user
				where id=?
			",[
				session('id_user')
			]);
			
			if(count($rows)>0){
				
				$password = $rows[0]->pass;
				$lanjut = true;
				$error = '';
				
				if($request->input('password1')!==''){
					
					if($request->input('password')!==''){
						
						if(md5($request->input('password'))==$password){
							
							$password = md5($request->input('password1'));
							
						}
						else{
							$lanjut = false;
							$error = 'Password lama tidak valid!';
						}
						
					}
					else{
						$lanjut = false;
						$error = 'Password lama harus diisi!';
					}
				
				}
				
				if($lanjut){
					
					//rekam header user
					$update1 = DB::update("
						update t_user
						set nama=?,
							nik=?,
							email=?,
							foto=?,
							pass=?,
							aktif='1'
						where id=?
					",[
						htmlspecialchars($request->input('nama')),
						htmlspecialchars($request->input('nik')),
						htmlspecialchars($request->input('email')),
						session('upload_foto_user'),
						$password,
						session('id_user')
					]);
					
					$update2 = DB::update("
						update t_user_level
						set status=0
						where id_user=?
					",[
						session('id_user')
					]);
					
					$update3 = DB::update("
						update t_user_level
						set status=1
						where id_user=? and kdlevel=?
					",[
						session('id_user'),
						htmlspecialchars($request->input('kdlevel')),
					]);
					
					$update4 = DB::update("
						update t_user_unit
						set status=0
						where id_user=?
					",[
						session('id_user')
					]);
					
					$update5 = DB::update("
						update t_user_unit
						set status=1
						where id_user=? and kdunit=?
					",[
						session('id_user'),
						htmlspecialchars($request->input('kdunit')),
					]);
					
					if($update1 || $update2 || $update3 || $update4 || $update5){
						
						DB::commit();
						
						$rows = DB::select("
							select  a.id,
									a.pass,
									a.nama,
									a.nik,
									a.aktif,
									a.foto,
									b.kdlevel,
									c.nmlevel,
									d.kdunit,
									e.nmunit
							from t_user a
							left outer join t_user_level b on(a.id=b.id_user)
							left outer join t_level c on(b.kdlevel=c.kdlevel)
							left outer join t_user_unit d on(a.id=d.id_user)
							left outer join t_unit e on(d.kdunit=e.kdunit)
							where a.id=? and b.status='1' and d.status='1'
						",[
							session('id_user'),
						]);
						
						session([
							'nama' => $rows[0]->nama,
							'nik' => $rows[0]->nik,
							'foto' => $rows[0]->foto,
							'kdlevel' => $rows[0]->kdlevel,
							'nmlevel' => $rows[0]->nmlevel,
							'kdunit' => $rows[0]->kdunit,
							'nmunit' => $rows[0]->nmunit
						]);
						
						return 'success';
					}
					else{
						return 'Data gagal disimpan!';
					}
					
				}
				else{
					return $error;
				}
				
			}
			else{
				return 'Data tidak ditemukan!';
			}
						
		}
		catch (\Exception $e) {
			return $e;
			return 'Terjadi kesalahan lain. Hubungi Admin!';
			//return $e;
		}
	}
	
	public function upload(Request $request)
	{		
		try {
			
			$targetFolder = 'data/user/'; // Relative to the root

			if (!empty($_FILES)) {
				$file_name = $_FILES['file']['name'];
				$tempFile = $_FILES['file']['tmp_name'];
				$fileParts = pathinfo($file_name);
				$targetFile = $targetFolder.$file_name;
				$fileTypes = array('PNG', 'png', 'JPG', 'jpg'); // File extensions
				$fileSize = $_FILES['file']['size'];
				
				//type file sesuai..??	
				if (in_array($fileParts['extension'],$fileTypes)) {
					
					//isi kosong..??
					if($fileSize>0 && $fileSize<=1000000){
					
						$newFileName = session('username').'_'.md5(time()).'.'.$fileParts['extension'];
						
						$localUpload = move_uploaded_file($tempFile,$targetFolder.$newFileName);
						
						if($localUpload){
							
							session(['upload_foto_user' => $newFileName]);
							
							return '1';
						}
						else{
							echo 'File gagal diupload.';
						}
					}
					else{
						echo 'Isi file kosong, periksa data anda.';
					}
				}
				else{
					echo 'Tipe file tidak sesuai.';
				}
			}
			else{
				echo 'Tidak ada file yang diupload.';
			}
		} catch (\Exception $e) {
			return 'Terdapat kesalahan lain. Hubungi Admin!';
			//return $e;
		}
	}

}
