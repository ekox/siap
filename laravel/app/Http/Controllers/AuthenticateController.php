<?php

namespace App\Http\Controllers;

use DB;
use Session;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthenticateController extends Controller {

	public function index()
	{
		try{
			$rows = DB::select("
				select	*
				from t_app_version
				where status='1'
			");
			
			return view('login',
				array(
					'app_versi'=>$rows[0]->versi,
					'app_nama'=>$rows[0]->nama,
					'app_ket'=>$rows[0]->ket
				)
			);
		}
		catch(\Exception $e){
			return $e;
			//return 'Terdapat kesalahan lainnya!';
		}
	}
	
	public function login(Request $request)
	{
		try{
			$username = $request->input('username');
			$password = $request->input('password');
			
			if($username!='' && $password!=''){
				
				$rows = DB::select("
					select  a.id,
							a.pass,
							a.nama,
							a.nik,
							a.aktif,
							a.foto,
							b.kdlevel,
							c.nmlevel,
							d.versi as app_versi,
							d.nama as app_nama,
							d.ket as app_ket
					from t_user a
					left outer join t_user_level b on(a.id=b.id_user)
					left outer join t_level c on(b.kdlevel=c.kdlevel),
					(
						select    *
						from t_app_version
						where status='1'
					) d
					where a.username=? and b.status='1'
				",[
					$username
				]);
				
				if(isset($rows[0]) && $rows[0]->pass){
				
					if($rows[0]->pass==md5($password)){
					
						if($rows[0]->aktif=='1'){
						
							session([
								'authenticated' => true,
								'id_user' => $rows[0]->id,
								'username' => $username,
								'nama' => $rows[0]->nama,
								'nik' => $rows[0]->nik,
								'foto' => $rows[0]->foto,
								'kdlevel' => $rows[0]->kdlevel,
								'nmlevel' => $rows[0]->nmlevel,
								'app_versi' => $rows[0]->app_versi,
								'app_nama' => $rows[0]->app_nama,
								'app_ket' => $rows[0]->app_ket
							]);

							return response()->json(['error' => false,'message' => 'Login berhasil!</br>Selamat Datang']);
							
						}
						else{
							return response()->json(['error' => true,'message' => 'User tidak aktif!']);
						}
						
					}
					else{
						return response()->json(['error' => true,'message' => 'Password salah!']);
					}
				
				}
				else{
					return response()->json(['error' => true,'message' => 'Username tidak terdaftar!']);
				}
				
			}
			else{
				return response()->json(['error' => true,'message' => 'Parameter tidak valid!']);
			}

		}
		catch(\Exception $e){
			return $e;
			return response()->json(['error' => true,'message' => 'Terdapat kesalahan lainnya!'], 503);
		}
	}
	
	public function create_token(Request $request)
	{
		try {
			$data=(array)json_decode($request->getContent());
		
			//cek apakah http post body kosong
			if(count($data)>0){
				
				$username= $data['username'];
				$password = $data['password'];
				$tahun = $data['tahun'];
				
				$rows = DB::select("
					select *
					from t_api_key
					where username=?
				",[$username]);
				
				if(isset($rows[0]) && $rows[0]->password){
				
					if($rows[0]->password==md5($password)){
					
						if($rows[0]->aktif=='1'){
							
							$id = $rows[0]->id;
							
							$lifetime=60*60*24; //60 detik * 60 menit * 24 jam = 1 hari
							$issued=time();
							$exp=time()+$lifetime;
							
							$header = '{
										"typ":"JWT",
										"alg":"HS256"
									   }';
							
							$arr_url=$request->fullUrl();
							$arr_url=explode("/", $arr_url);
							$ip_server=$arr_url[2];
							
							$payload = '{
										 "iss":"'.$id.'",
										 "exp":'.$exp.',
										 "issued":'.$issued.',
										 "user":"'.$rows[0]->username.'",
										 "server":"'.$ip_server.'",
										 "kdsatker":"'.$rows[0]->kdsatker.'",
										 "tahun":"'.$tahun.'"
										}';

							$key = 'cinta123!';

							$JWT = new \App\Libraries\jwtphp\JWT;

							$token = $JWT->encode($header, $payload, $key);
							return response()->json(['error' => false, 'message' => $token], 200);
							
						}
						else{
							return response()->json(['error' => true, 'message' => 'User tidak aktif!'], 401);
						}
						
					}
					else{
						return response()->json(['error' => true, 'message' => 'Password salah!'], 401);
					}
				
				}
				else{
					return response()->json(['error' => true, 'message' => 'Username tidak terdaftar!'], 401);
				}
				
			}
			else{
				return response()->json(['error' => true, 'message' => 'Body content kosong, silahkah lihat dokumentasi API!'], 400);
			}
		}
		catch(\Exception $e) {
			return response(json_encode(array('error' => true, 'message' => 'Kesalahan lainnya!')), 500);
		}
		
	}
	
	public function cek_level()
	{
		return session('kdlevel');
	}
	
	public function logout()
	{
		Session::flush();
		return redirect()->guest('/auth');
	}

	public function hapus_sesi_upload(Request $request)
	{
		session(['sesi_upload' => null]);

		return 'Sesi upload berhasil dihapus!';
	}
}