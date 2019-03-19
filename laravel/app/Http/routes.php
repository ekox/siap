<?php

Route::get('/test', function(){
	
	return view('app');
	
});

//otentikasi/login
Route::group(['prefix' => 'auth'], function () {

	Route::get('', 'AuthenticateController@index'); //get login html
	Route::get('logout', 'AuthenticateController@logout');
	Route::post('', 'AuthenticateController@login'); //post login

});

//Authenticate only
Route::group(['middleware' => 'auth'], function(){
	
	//root for template
	Route::get('/', 'AppController@index');
	
	//route for CSRF Token
	Route::get('/token', 'AppController@token');
	Route::get('cek/level', 'AuthenticateController@cek_level');
	Route::get('hapus/sesi/upload', 'AuthenticateController@hapus_sesi_upload');
	
	//Profile
	Route::group(['prefix' => 'profile'], function(){
		
		Route::get('', 'ProfileController@index');
		Route::post('', 'ProfileController@ubah');
		Route::post('/foto', 'ProfileController@upload');
		
	});
	
	//route KSO
	Route::group(['prefix' => 'kso'], function () {
		
		Route::group(['prefix' => 'proyek'], function () {
			
			Route::get('', 'KSOProyekController@index');
			Route::get('/pilih/{param}', 'KSOProyekController@pilih');
			Route::post('', 'KSOProyekController@simpan');
			Route::post('/hapus', 'KSOProyekController@hapus');
			Route::post('/upload', 'KSOProyekController@upload');
			
		});
		
		Route::group(['prefix' => 'pks'], function () {
			
			Route::get('', 'KSOPKSController@index');
			Route::get('/pilih/{param}', 'KSOPKSController@pilih');
			Route::get('/detil1/{param}', 'KSOPKSController@detil1');
			Route::post('', 'KSOPKSController@simpan');
			Route::post('/hapus', 'KSOPKSController@hapus');
			Route::post('/detil1', 'KSOPKSController@simpan1');
			Route::post('/hapus1', 'KSOPKSController@hapus1');
			Route::post('/detil2', 'KSOPKSController@simpan2');
			Route::post('/hapus2', 'KSOPKSController@hapus2');
			
		});
		
		Route::group(['prefix' => 'pihak3'], function () {
			
			Route::get('', 'KSOPihak3Controller@index');
			Route::get('/pilih/{param}', 'KSOPihak3Controller@pilih');
			Route::post('', 'KSOPihak3Controller@simpan');
			Route::post('/hapus', 'KSOPihak3Controller@hapus');
			
		});
		
		Route::group(['prefix' => 'user'], function () {
			
			Route::get('', 'KSOUserController@index');
			Route::get('/pilih/{param}', 'KSOUserController@pilih');
			Route::post('', 'KSOUserController@simpan');
			Route::post('/hapus', 'KSOUserController@hapus');
			
		});
		
		Route::group(['prefix' => 'teknis'], function () {
			
			Route::get('', 'KSOTeknisController@index');
			Route::get('/pilih/{param}', 'KSOTeknisController@pilih');
			Route::get('/nilai/{param}', 'KSOTeknisController@nilai');
			Route::get('/tayang/{param}', 'KSOTeknisController@tayang');
			Route::post('', 'KSOTeknisController@simpan');
			Route::post('/hapus', 'KSOTeknisController@hapus');
			
		});
		
		Route::group(['prefix' => 'teknis-baru'], function () {
			
			Route::get('', 'KSOTeknisBaruController@index');
			Route::get('/lvl1/{param}', 'KSOTeknisBaruController@lvl1');
			Route::get('/lvl2/{param}', 'KSOTeknisBaruController@lvl2');
			Route::get('/lvl3/{param}', 'KSOTeknisBaruController@lvl3');
			Route::get('/lvl4/{param}', 'KSOTeknisBaruController@lvl4');
			Route::get('/lvl5/{param}', 'KSOTeknisBaruController@lvl5');
			Route::get('/lvl6/{param}', 'KSOTeknisBaruController@lvl6');
			Route::get('/detil/{param}', 'KSOTeknisBaruController@detil');
			Route::get('/pilih/{param}', 'KSOTeknisBaruController@pilih');
			Route::get('/nilai/{param}', 'KSOTeknisBaruController@nilai');
			Route::get('/tayang/{param}', 'KSOTeknisBaruController@tayang');
			Route::post('', 'KSOTeknisBaruController@simpan');
			Route::post('/hapus', 'KSOTeknisBaruController@hapus');
			
		});
		
		Route::group(['prefix' => 'progres'], function () {
			
			Route::group(['prefix' => 'rekam'], function () {
			
				Route::get('', 'KSOProgresController@index');
				Route::get('/pilih/{param}', 'KSOProgresController@pilih');
				Route::get('/detil/{param}', 'KSOProgresController@detil');
				Route::post('', 'KSOProgresController@simpan');
				Route::post('/detil', 'KSOProgresController@simpan_detil');
				Route::post('/hapus', 'KSOProgresController@hapus');
				
			});
			
			Route::group(['prefix' => 'proses'], function () {
			
				Route::get('', 'KSOProgresController@proses');
				Route::get('/pilih/{param}', 'KSOProgresController@proses_pilih');
				Route::get('/detil/{param}', 'KSOProgresController@proses_detil');
				Route::post('', 'KSOProgresController@proses_simpan');
				
			});
			
			Route::group(['prefix' => 'monitoring'], function () {
			
				Route::get('', 'KSOProgresController@monitoring');
				Route::get('/pilih/{param}', 'KSOProgresController@monitoring_pilih');
				Route::get('/detil/{param}', 'KSOProgresController@monitoring_detil');
				
			});
			
		});
		
		Route::group(['prefix' => 'tagihan'], function () {
			
			Route::group(['prefix' => 'rekam'], function () {
			
				Route::get('', 'KSOTagihanController@index');
				Route::get('/pilih/{param}', 'KSOTagihanController@pilih');
				Route::get('/detil/{param}', 'KSOTagihanController@detil');
				Route::post('', 'KSOTagihanController@simpan');
				Route::post('/detil', 'KSOTagihanController@simpan_detil');
				Route::post('/hapus', 'KSOTagihanController@hapus');
				
			});
			
			Route::group(['prefix' => 'proses'], function () {
			
				Route::get('', 'KSOTagihanController@proses');
				Route::get('/pilih/{param}', 'KSOTagihanController@proses_pilih');
				Route::get('/detil/{param}', 'KSOTagihanController@proses_detil');
				Route::post('', 'KSOTagihanController@proses_simpan');
				
			});
			
			Route::group(['prefix' => 'monitoring'], function () {
			
				Route::get('', 'KSOTagihanController@monitoring');
				Route::get('/pilih/{param}', 'KSOTagihanController@monitoring_pilih');
				Route::get('/detil/{param}', 'KSOTagihanController@monitoring_detil');
				
			});
			
		});

	});
	
	//route for Dropdown
	Route::group(['prefix' => 'dropdown'], function(){
		
		Route::get('/kso', 'DropdownController@kso');
		Route::get('/owner', 'DropdownController@owner');
		Route::get('/mk', 'DropdownController@mk');
		Route::get('/qs', 'DropdownController@qs');
		Route::get('/kon', 'DropdownController@kon');
		Route::get('/jabatan', 'DropdownController@jabatan');
		Route::get('/tahun', 'DropdownController@tahun');
		Route::get('/bulan', 'DropdownController@bulan');
		Route::get('/user', 'DropdownController@user');
		Route::get('/satuan', 'DropdownController@satuan');
		Route::get('/lvl-teknis', 'DropdownController@lvl_teknis');
		Route::get('/lvl-teknis/{param}', 'DropdownController@lvl_teknis_by_id');
		Route::get('/dok-teknis/{param}', 'DropdownController@dok_teknis');
		Route::get('/tagihan', 'DropdownController@tagihan');
		Route::get('/perusahaan', 'DropdownController@perusahaan');
		Route::get('/level', 'DropdownController@level');
		Route::get('/jenis-usaha', 'DropdownController@jenis_usaha');
		
	});
	
	//route for Referensi
	Route::group(['prefix' => 'ref'], function(){
		
		Route::group(['prefix' => 'notifikasi'], function(){
			
			Route::get('', 'RefNotifController@index');
			Route::get('/pilih/{param}', 'RefNotifController@pilih');
			Route::post('', 'RefNotifController@simpan');
			Route::post('/hapus', 'RefNotifController@hapus');
			
		});
		
		Route::group(['prefix' => 'user'], function(){
			
			Route::get('', 'RefUserController@index');
			Route::get('/pilih/{param}', 'RefUserController@pilih');
			Route::post('', 'RefUserController@simpan');
			Route::post('/hapus', 'RefUserController@hapus');
			
		});
		
		Route::group(['prefix' => 'perusahaan'], function(){
			
			Route::get('', 'RefPerusahaanController@index');
			Route::get('/pilih/{param}', 'RefPerusahaanController@pilih');
			Route::post('', 'RefPerusahaanController@simpan');
			Route::post('/upload', 'RefPerusahaanController@upload');
			Route::post('/hapus', 'RefPerusahaanController@hapus');
			
		});
		
	});

});

//API Route
Route::group(['prefix' => 'api'], function () {

	//Versi 1
	Route::group(['prefix' => 'v1', 'middleware' => 'cors'], function () {
		
		//Group auth
		Route::group(['prefix' => 'auth'], function () {
			
			Route::post('', 'AuthenticateController@create_token');
			
		});
	
	});
	
});
