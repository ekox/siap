<?php namespace App\Http\Middleware;

use Closure;
use DB;

class CheckToken {

	//token untuk aplikasi sas
	public function handle($request, Closure $next)
    {
        try{
		
			$JWT = new \App\Libraries\jwtphp\JWT;
			$key = 'cinta123!';
			$headers = apache_request_headers();
			
			$arr_url=$request->fullUrl();
			$arr_url=explode("/", $arr_url);
			$ip_server=$arr_url[2];
			
			if(isset($headers['Authorization'])){
				$token = str_replace(" ","", str_replace("Bearer ", "", $headers['Authorization']));
				
				if($JWT->decode($token, $key)){
					$json = $JWT->decode($token, $key);
					
					$data=json_decode($json,true);
					
					$current_time=time();
					$token_time=(int)$data['exp'];
					
					//cek apakah berasal dari server yang sama
					if($ip_server==$data['server']){
						
						//cek apakah lifetime token habis
						if($current_time<=$token_time){
							
							$request->merge(array(
								"kdsatker" => $data['kdsatker'],
								"tahun" => $data['tahun'],
								"user" => $data['user']
							));
							return $next($request, $json);
							
						}
						else{
							return response(json_encode(array('error' => true, 'code' => '99', 'message' => 'Token tidak valid!')), 401);
						}
						
					}
					else{
						return response(json_encode(array('error' => true, 'code' => '99', 'message' => 'Token tidak valid!')), 401);
					}
					
				}
				else{
					return response(json_encode(array('error' => true, 'code' => '99', 'message' => 'Token tidak valid!')), 401);
				}
				
			}
			else{
				return response(json_encode(array('error' => true, 'code' => '99', 'message' => 'Token tidak ada!')), 401);
			}
		
		}
		catch(TokenInvalidException $e){
			return response(json_encode(array('error' => true, 'code' => '99', 'message' => 'Token tidak valid!')), 401);
		}
    }
	
}