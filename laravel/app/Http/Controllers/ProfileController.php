<?php namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ProfileController extends Controller {

	public function index()
	{
		$rows = DB::select("
			select  username,
					nama,
					nip,
					telp,
					email,
					alamat,
					foto
			from t_user
			where id=?
		",
			[session('id_user')]
		);
		return response()->json($rows[0]);
	}
	
	public function ubah(Request $request)
	{
		try {
			if($request->input('p_password_baru')=='' || $request->input('p_password_baru')==false){
			
				if(session('upload_foto_user')=='' || session('upload_foto_user')==false){
					$update = DB::update("
						UPDATE t_user
						SET nama=?, nip=?, telp=?, email=?, alamat=?
						WHERE id=?
					", [
						$request->input('p_nama'),
						$request->input('p_nip'),
						$request->input('p_telp'),
						$request->input('p_email'),
						$request->input('p_alamat'),
						session('id_user')
					]);
				}
				else{
					$update = DB::update("
						UPDATE t_user
						SET nama=?, nip=?, telp=?, email=?, alamat=?, foto=?
						WHERE id=?
					", [
						$request->input('p_nama'),
						$request->input('p_nip'),
						$request->input('p_telp'),
						$request->input('p_email'),
						$request->input('p_alamat'),
						session('upload_foto_user'),
						session('id_user')
					]);
				}
			}
			else{
			
				$select = DB::select("SELECT password FROM t_user WHERE id=?", [session('id_user')]);
				$password_lama=$select[0]->password;
				
				if(md5($request->input('p_password_lama'))==$password_lama){
					
					$password_baru = md5($request->input('p_password_baru'));
					if(session('upload_foto_user')=='' || session('upload_foto_user')==false){
						$update = DB::update("
							UPDATE t_user
							SET nama=?, nip=?, alamat=?, telp=?, email=?, password=?
							WHERE id=?
						", [
							$request->input('p_nama'),
							$request->input('p_nip'),
							$request->input('p_alamat'),
							$request->input('p_telp'),
							$request->input('p_email'),
							$password_baru,
							session('id_user')
						]);
					}
					else{
						$update = DB::update("
							UPDATE t_user
							SET nama=?, nip=?, alamat=?, telp=?, email=?, foto=?, password=?
							WHERE id=?
						", [
							$request->input('p_nama'),
							$request->input('p_nip'),
							$request->input('p_alamat'),
							$request->input('p_telp'),
							$request->input('p_email'),
							session('upload_foto_user'),
							$password_baru,
							session('id_user')
						]);
					}
				}
				else{
					return 'Password lama tidak sesuai!';
				}
			}
			
			session(['upload_foto_user'=>null]);
			
			if($update==true) {
				
				
				
				return 'success';
			} else {
				return 'Proses ubah gagal. Hubungi Admin!';
			}
		} catch (\Exception $e) {
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
