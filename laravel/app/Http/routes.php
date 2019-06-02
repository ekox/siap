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
		Route::post('/upload', 'ProfileController@upload');
		
	});
	
	//anggaran
	Route::group(['prefix' => 'anggaran'], function () {
		
		Route::group(['prefix' => 'pagu'], function () {
			
			Route::group(['prefix' => 'unit'], function () {
			
				Route::get('', 'AnggaranPaguUnitController@index');
				Route::get('/pilih/{param}', 'AnggaranPaguUnitController@pilih')->middleware('role:00');
				Route::post('', 'AnggaranPaguUnitController@simpan')->middleware('role:00');
				Route::post('/hapus', 'AnggaranPaguUnitController@hapus')->middleware('role:00');
				
			});
			
		});
		
	});
	
	//transaksi
	Route::group(['prefix' => 'transaksi'], function () {
		
		Route::group(['prefix' => 'monitoring'], function () {
			
			Route::get('', 'TransaksiProsesController@monitoring');
			
		});
		
		Route::group(['prefix' => 'proses'], function () {
			
			Route::get('', 'TransaksiProsesController@index');
			Route::get('/pilih/{param}', 'TransaksiProsesController@pilih');
			Route::post('', 'TransaksiProsesController@simpan');
			
		});
		
		Route::group(['prefix' => 'rekam'], function () {
			
			Route::get('', 'TransaksiRekamController@index');
			Route::get('/pilih/{param}', 'TransaksiRekamController@pilih')->middleware('role:01');
			Route::get('/nomor', 'TransaksiRekamController@nomor')->middleware('role:01');
			Route::get('/detil/{param}', 'TransaksiRekamController@detil');
			Route::get('/download/{param}', 'TransaksiRekamController@download')->middleware('role:01');
			Route::post('', 'TransaksiRekamController@simpan')->middleware('role:01');
			Route::post('/hapus', 'TransaksiRekamController@hapus')->middleware('role:01');
			Route::post('/upload', 'TransaksiRekamController@upload')->middleware('role:01');
			
		});
		
	});
		
	Route::group(['prefix' => 'pembukuan'], function () {
		
		Route::group(['prefix' => 'saldo-awal'], function () {
		
			Route::get('', 'PembukuanSaldoAwalController@index');
			Route::get('/pilih/{param}', 'PembukuanSaldoAwalController@pilih')->middleware('role:00.05');
			Route::post('', 'PembukuanSaldoAwalController@simpan')->middleware('role:00.05');
			Route::post('/hapus', 'PembukuanSaldoAwalController@hapus')->middleware('role:00.05');
			
		});
		
		Route::group(['prefix' => 'jurnal'], function () {
		
			Route::get('', 'PembukuanJurnalController@index');
			
		});
		
	});
	
	//route for Dropdown
	Route::group(['prefix' => 'dropdown'], function(){
		
		Route::get('/output', 'DropdownController@output');
		Route::get('/kegiatan', 'DropdownController@kegiatan');
		Route::get('/transaksi', 'DropdownController@transaksi');
		Route::get('/unit', 'DropdownController@unit_all');
		Route::get('/unit/{param}', 'DropdownController@unit');
		Route::get('/level', 'DropdownController@level');
		Route::get('/jenis-pagu', 'DropdownController@jenis_pagu');
		Route::get('/alur', 'DropdownController@alur');
		Route::get('/penerima', 'DropdownController@penerima');
		Route::get('/bank', 'DropdownController@bank');
		Route::get('/lap', 'DropdownController@lap');
		Route::get('/akun/html/level1', 'DropdownController@akun_html_level1');
		Route::get('/akun/json', 'DropdownController@akun_json');
		
	});
	
	//route for Referensi
	Route::group(['prefix' => 'ref'], function(){
		
		Route::group(['prefix' => 'user'], function(){
			
			Route::get('', 'RefUserController@index')->middleware('role:00');
			Route::get('/pilih/{param}', 'RefUserController@pilih')->middleware('role:00');
			Route::post('', 'RefUserController@simpan')->middleware('role:00');
			Route::post('/hapus', 'RefUserController@hapus')->middleware('role:00');
			Route::post('/reset', 'RefUserController@reset')->middleware('role:00');
			
		});
		
		Route::group(['prefix' => 'unit'], function(){
			
			Route::get('', 'RefUnitController@index');
			Route::get('/pilih/{param}', 'RefUnitController@pilih')->middleware('role:00');
			Route::post('', 'RefUnitController@simpan')->middleware('role:00');
			Route::post('/hapus', 'RefUnitController@hapus')->middleware('role:00');
			
		});
		
		Route::group(['prefix' => 'output'], function(){
			
			Route::get('', 'RefOutputController@index');
			Route::get('/pilih/{param}', 'RefOutputController@pilih')->middleware('role:00');
			Route::post('', 'RefOutputController@simpan')->middleware('role:00');
			Route::post('/hapus', 'RefOutputController@hapus')->middleware('role:00');
			
		});
		
		Route::group(['prefix' => 'kegiatan'], function(){
			
			Route::get('', 'RefKegiatanController@index');
			Route::get('/pilih/{param}', 'RefKegiatanController@pilih')->middleware('role:00');
			Route::post('', 'RefKegiatanController@simpan')->middleware('role:00');
			Route::post('/hapus', 'RefKegiatanController@hapus')->middleware('role:00');
			
		});
		
		Route::group(['prefix' => 'akun'], function(){
			
			Route::get('', 'RefAkunController@index');
			Route::get('/pilih/{param}', 'RefAkunController@pilih')->middleware('role:00');
			Route::post('', 'RefAkunController@simpan')->middleware('role:00');
			Route::post('/hapus', 'RefAkunController@hapus')->middleware('role:00');
			
		});
		
		Route::group(['prefix' => 'transaksi'], function(){
			
			Route::get('', 'RefTransaksiController@index');
			Route::get('/pilih/{param}', 'RefTransaksiController@pilih')->middleware('role:00');
			Route::get('/detil/{param}', 'RefTransaksiController@detil');
			Route::get('/detil1/{param}', 'RefTransaksiController@detil1')->middleware('role:00.01');
			Route::post('', 'RefTransaksiController@simpan')->middleware('role:00');
			Route::post('/hapus', 'RefTransaksiController@hapus')->middleware('role:00');
			
		});
		
		Route::group(['prefix' => 'penerima'], function(){
			
			Route::get('', 'RefPenerimaController@index');
			Route::get('/pilih/{param}', 'RefPenerimaController@pilih')->middleware('role:00.01');
			Route::post('', 'RefPenerimaController@simpan')->middleware('role:00.01');
			Route::post('/hapus', 'RefPenerimaController@hapus')->middleware('role:00.01');
			
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
