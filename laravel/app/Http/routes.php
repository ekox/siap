<?php

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
	
	//Beranda
	Route::group(['prefix' => 'home'], function(){
		
		Route::get('total', 'HomeController@total');
		Route::get('data', 'HomeController@data');
		
	});
	
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
	
	//tagihan
	Route::group(['prefix' => 'tagihan'], function () {
		
		Route::group(['prefix' => 'monitoring'], function () {
			
			Route::get('', 'TagihanProsesController@monitoring');
			
		});
		
		Route::group(['prefix' => 'proses'], function () {
			
			Route::get('', 'TagihanProsesController@index');
			Route::get('/pilih/{param}', 'TagihanProsesController@pilih');
			Route::post('', 'TagihanProsesController@simpan');
			
		});
		
		Route::group(['prefix' => 'rekam'], function () {
			
			Route::get('', 'TagihanRekamController@index');
			Route::get('/pilih/{param}', 'TagihanRekamController@pilih')->middleware('role:12');
			Route::get('/nomor', 'TagihanRekamController@nomor')->middleware('role:12');
			Route::get('/detil/{param}', 'TagihanRekamController@detil');
			Route::get('/download/{param}', 'TagihanRekamController@download')->middleware('role:12');
			Route::post('', 'TagihanRekamController@simpan')->middleware('role:12');
			Route::post('/hapus', 'TagihanRekamController@hapus')->middleware('role:12');
			Route::post('/upload', 'TagihanRekamController@upload')->middleware('role:12');
			
		});
		
	});
	
	//penerimaan
	Route::group(['prefix' => 'penerimaan'], function () {
		
		Route::group(['prefix' => 'monitoring'], function () {
			
			Route::get('', 'PenerimaanProsesController@monitoring');
			
		});
		
		Route::group(['prefix' => 'proses'], function () {
			
			Route::get('', 'PenerimaanProsesController@index');
			Route::get('/pilih/{param}', 'PenerimaanProsesController@pilih');
			Route::post('', 'PenerimaanProsesController@simpan');
			
		});
		
		Route::group(['prefix' => 'pajak'], function () {
			
			Route::get('', 'PenerimaanPajakController@index');
			Route::get('/pilih/{param}', 'PenerimaanPajakController@pilih');
			Route::post('', 'PenerimaanPajakController@simpan');
			Route::post('/hapus', 'PenerimaanPajakController@hapus');
			
		});
		
		Route::group(['prefix' => 'rekam'], function () {
			
			Route::get('', 'PenerimaanRekamController@index');
			Route::get('/pilih/{param}', 'PenerimaanRekamController@pilih')->middleware('role:11');
			Route::get('/nomor', 'PenerimaanRekamController@nomor')->middleware('role:11');
			Route::get('/tagihan/{param}', 'PenerimaanRekamController@tagihan');
			Route::get('/detil/{param}', 'PenerimaanRekamController@detil');
			Route::get('/download/{param}', 'PenerimaanRekamController@download')->middleware('role:11');
			Route::get('/upload/{param}', 'PenerimaanRekamController@dok')->middleware('role:11');
			Route::post('', 'PenerimaanRekamController@simpan')->middleware('role:11');
			Route::post('/hapus', 'PenerimaanRekamController@hapus')->middleware('role:11');
			Route::post('/upload/{param}', 'PenerimaanRekamController@upload')->middleware('role:11');
			Route::post('/upload-simpan', 'PenerimaanRekamController@uploadSimpan')->middleware('role:11');
			Route::post('/hapus-dok', 'PenerimaanRekamController@hapusDok')->middleware('role:11');
			
		});
		
	});
	
	//umk
	Route::group(['prefix' => 'umk'], function () {
		
		Route::group(['prefix' => 'monitoring'], function () {
			
			Route::get('', 'UMKProsesController@monitoring');
			
		});
		
		Route::group(['prefix' => 'proses'], function () {
			
			Route::get('', 'UMKProsesController@index');
			Route::get('/pilih/{param}', 'UMKProsesController@pilih');
			Route::post('', 'UMKProsesController@simpan');
			
		});
		
		Route::group(['prefix' => 'rekam'], function () {
			
			Route::get('', 'UMKRekamController@index');
			Route::get('/pilih/{param}', 'UMKRekamController@pilih')->middleware('role:11');
			Route::get('/nomor', 'UMKRekamController@nomor')->middleware('role:11');
			Route::get('/detil/{param}', 'UMKRekamController@detil');
			Route::get('/download/{param}', 'UMKRekamController@download')->middleware('role:11');
			Route::post('', 'UMKRekamController@simpan')->middleware('role:11');
			Route::post('/hapus', 'UMKRekamController@hapus')->middleware('role:11');
			Route::post('/upload', 'UMKRekamController@upload')->middleware('role:11');
			
		});
		
	});
	
	//pengeluaran
	Route::group(['prefix' => 'pengeluaran'], function () {
		
		Route::group(['prefix' => 'monitoring'], function () {
			
			Route::get('', 'PengeluaranProsesController@monitoring');
			
		});
		
		Route::group(['prefix' => 'proses'], function () {
			
			Route::get('', 'PengeluaranProsesController@index');
			Route::get('/pilih/{param}', 'PengeluaranProsesController@pilih');
			Route::post('', 'PengeluaranProsesController@simpan');
			
		});
		
		Route::group(['prefix' => 'rekam'], function () {
			
			Route::get('', 'PengeluaranRekamController@index');
			Route::get('/pilih/{param}', 'PengeluaranRekamController@pilih')->middleware('role:11');
			Route::get('/nomor', 'PengeluaranRekamController@nomor')->middleware('role:11');
			Route::get('/tagihan/{param}', 'PengeluaranRekamController@tagihan');
			Route::get('/detil/{param}', 'PengeluaranRekamController@detil');
			Route::get('/download/{param}', 'PengeluaranRekamController@download')->middleware('role:11');
			Route::get('/upload/{param}', 'PengeluaranRekamController@dok')->middleware('role:11');
			Route::post('', 'PengeluaranRekamController@simpan')->middleware('role:11');
			Route::post('/hapus', 'PengeluaranRekamController@hapus')->middleware('role:11');
			Route::post('/upload/{param}', 'PengeluaranRekamController@upload')->middleware('role:11');
			Route::post('/upload-simpan', 'PengeluaranRekamController@uploadSimpan')->middleware('role:11');
			Route::post('/hapus-dok', 'PengeluaranRekamController@hapusDok')->middleware('role:11');
			
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
	
	//pembukuan
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
		
		Route::group(['prefix' => 'posting'], function () {
		
			Route::get('', 'PembukuanPostingController@index');
			Route::get('/buku-besar', 'PembukuanPostingController@buku_besar');
			Route::post('', 'PembukuanPostingController@simpan')->middleware('role:00');
			
		});
		
	});

	//route for Reporting
	Route::group(['prefix' => 'laporan'], function() {

		Route::get('/laba-rugi', 'LaporanKeuanganController@incomeStatement');
		Route::get('/prb-ekuitas', 'LaporanKeuanganController@changeOnEquity');
		Route::get('/neraca', 'LaporanKeuanganController@balanceSheet');
		Route::get('/arus-kas', 'LaporanKeuanganController@cashFlow');
		
	});
	
	//laporan
	Route::group(['prefix' => 'laporan'], function () {
		
		Route::get('/keuangan/{param}', 'LaporanController@keuangan');
		
	});
	
	//route for Dropdown
	Route::group(['prefix' => 'dropdown'], function(){
		
		Route::get('/output', 'DropdownController@output');
		Route::get('/kegiatan', 'DropdownController@kegiatan');
		Route::get('/dok/{param}', 'DropdownController@dok');
		Route::get('/dokDetil/{param}', 'DropdownController@dokTransaksi');
		Route::get('/tagihan', 'DropdownController@tagihan');
		Route::get('/transaksi', 'DropdownController@transaksi');
		Route::get('/transaksi/{param}', 'DropdownController@transaksiByParam');
		Route::get('/unit', 'DropdownController@unit_all');
		Route::get('/unit/{param}', 'DropdownController@unit');
		Route::get('/level', 'DropdownController@level');
		Route::get('/jenis-pagu', 'DropdownController@jenis_pagu');
		Route::get('/alur', 'DropdownController@alur');
		Route::get('/alur-tagihan', 'DropdownController@alurTagihan');
		Route::get('/alur-penerimaan', 'DropdownController@alurPenerimaan');
		Route::get('/alur-umk', 'DropdownController@alurUmk');
		Route::get('/alur-pengeluaran', 'DropdownController@alurPengeluaran');
		Route::get('/penerima', 'DropdownController@penerima');
		Route::get('/bank', 'DropdownController@bank');
		Route::get('/lap', 'DropdownController@lap');
		Route::get('/akun/html/level1', 'DropdownController@akun_html_level1');
		Route::get('/akun/json', 'DropdownController@akun_json');
		Route::get('/akun/html/all', 'DropdownController@akun_html_all');
		Route::get('/akun/debet/{param}', 'DropdownController@akun_debet');
		Route::get('/akun/kredit/{param}', 'DropdownController@akun_kredit');
		Route::get('/periode', 'DropdownController@periode');
		Route::get('/tahun', 'DropdownController@tahun');
		
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
		
		Route::group(['prefix' => 'alur'], function(){
			
			Route::get('', 'RefAlurController@index');
			Route::get('/pilih/{param}', 'RefAlurController@pilih')->middleware('role:00');
			Route::post('', 'RefAlurController@simpan')->middleware('role:00');
			Route::post('/hapus', 'RefAlurController@hapus')->middleware('role:00');
			
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
