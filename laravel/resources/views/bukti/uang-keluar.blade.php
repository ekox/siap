@include('css')
<!--table header-->
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border: 1px solid #000;font-size:12px;">
	<thead>
		<tr>
			<th colspan="3">&nbsp;</th>
		</tr>
		<tr>
			<th colspan="3"><h3>PERUSAHAAN DAERAH PEMBANGUNAN SARANA JAYA<br/>PEMERINTAH KHUSUS DKI JAKARTA</h3></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td class="wd10 pl3 vt">Nomor</td>
			<td class="wd2 vt">:</td>
			<td class="pl3 vt">&nbsp;</td>
		</tr>
		<tr>
			<td class="pl3 vt">Tahun</td>
			<td class=" t">:</td>
			<td class="pl3 vt">{{$tahun}}</td>
		</tr>
		<tr>
			<td class="pl3 vt">No. Bukti Keluar</td>
			<td class="vt">:</td>
			<td class="pl3 vt">&nbsp;</td>
		</tr>
	</tbody>
	<thead>
		<tr>
			<th colspan="3"><h3>BUKTI UANG KELUAR</h3></th>
		</tr>
		<tr>
			<th colspan="3">&nbsp;</th>
		</tr>
	</thead>
</table>

<!--table content-->
<table width="100%" border="1" cellspacing="0" cellpadding="3" style="border:1px solid #000;font-size:11px;">
	<tbody>
		<tr>
			<td class="wd15 pl3 vt">Tahun Anggaran</td>
			<td class="wd5 ar vt">:</td>
			<td class="wd15 pl3 vt">{{$tahun}}</td>
			<td colspan="4" class="bl pl3">&nbsp;</td>
		</tr>
		<tr>
			<td class="pl3 vt">Otorisasi Pengeluaran</td>
			<td class="ar vt">:</td>
			<td class="pl3 vt">...........</td>
			<td colspan="4" class="bl pl3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
			<td rowspan="2" class="wd15 bl pl3 vt">Kepada</td>
			<td rowspan="2" class="ar vt">:</td>
			<td rowspan="2" colspan="2" class="pl3 vt">..............................................................................</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3">Kode Mata Anggaran Pengeluaran :</td>
			<td rowspan="3" class="bl pl3 vt">Uang Sejumlah</td>
			<td rowspan="3" class="wd2 ar vt">:</td>
			<td rowspan="3" colspan="2" class="pl3 vt">..............................................................................</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3">Beban Anggaran Belanja</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
			<td colspan="4" class="bl">&nbsp;</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar">....................</td>
			<td rowspan="4" class="bl pl3 vt">Untuk</td>
			<td rowspan="4" class="pl3 ar vt">:</td>
			<td rowspan="4" colspan="2" class="pl3 vt">........................................................................................</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar">....................</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar">....................</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar">....................</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar">....................</td>
			<td rowspan="4" class="bl pl3 vt">Dasar Pembayaran</td>
			<td rowspan="4" class="pl3 ar vt">:</td>
			<td rowspan="4" colspan="2" class="pl3 vt">........................................................................................</td>
		</tr>
		<tr>
			<td class="pl3 vt">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar">....................</td>
		</tr>
		<tr>
			<td class="pl3 vt">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar">....................</td>
		</tr>
		<tr>
			<td class="pl3 vt">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar">....................</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
			<td colspan="4" class="bl">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" rowspan="2" class="pl3 vt">Kode Mata Anggaran Pengembalian :</td>
			<td colspan="3" class="bl pl3 ac">KASIR/BENDAHARA:</td>
			<td colspan="1" class="pl3">&nbsp;</td>
		</tr>
		<tr>
			<td class="bl pl3">Pada tanggal</td>
			<td class="pl3 ar vt">:</td>
			<td colspan="2" class="pl3">....................</td>
		</tr>
		<tr>
			<td class="pl3 vt">....................</td>
			<td class="vt">Rp</td>
			<td class="pl3 ar vt">....................</td>
			<td class="bl pl3 vt">Cash/Cek</td>
			<td class="ar vt">:</td>
			<td colspan="2" class="pl3">....................</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar">....................</td>
			<td class="bl pl3">Tanda tangan</td>
			<td class="ar vt">:</td>
			<td class="vt">....................</td>
			<td class="pl3 al">&nbsp;</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar">....................</td>
			<td colspan="3" class="bl pl3">&nbsp;</td>
			<td class="pl3 al">Jakarta, ....................</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar"><u>....................</u></td>
			<td colspan="3" class="bl pl3 ac"><u>PENERIMA UANG</u></td>
			<td class="pl3 ac">PD PEMBANGUNAN SARANA JAYA</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3">&nbsp;</td>
			<td class="bl pl3 vt">Nama</td>
			<td class="ar vt">:</td>
			<td class="pl3 vt">....................</td>
			<td rowspan="4" class="pl3 ac vt">DIREKTUR UTAMA/ADMINISTRASI DAN KEUANGAN</td>
		</tr>
		<tr>
			<td rowspan="2" class="pl3 vt">Jumlah yang harus dibayar</td>
			<td rowspan="2" class="pl3 vt">Rp</td>
			<td rowspan="2" class="pl3 ar vt">....................</td>
			<td class="bl pl3 vt">KTP/SIM No.</td>
			<td class="ar vt">:</td>
			<td class="pl3 vt">....................</td>

		</tr>
		<tr>
			<td class="bl pl3 vt">Alamat</td>
			<td class="ar vt">:</td>
			<td class="pl3">....................</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3">&nbsp;</td>
			<td class="bl pl3">Tanda tangan</td>
			<td class="ar vt">:</td>
			<td class="pl3 vt">....................</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3">&nbsp;</td>
			<td colspan="2" class="bl">&nbsp;</td>
			<td class="">&nbsp;</td>
			<td class="vb ac">(...................................)</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3">&nbsp;</td>
			<td colspan="2" class="bl pl3">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
			<td class="pl3 ac vb">NIP/NRK .................... ....................</td>
		</tr>
		<tr>
			<td colspan="3" class="bb pl3">&nbsp;</td>
			<td colspan="2" class="bl bb pl3">&nbsp;</td>
			<td class="bb pl3">&nbsp;</td>
			<td class="bb pl3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3">&nbsp;</td>
			<td colspan="2" class="bl pl3">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3 ac">&nbsp;</td>
			<td colspan="2" class="bl pl3">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
			<td class="pl3 ac vt">Senior Manajer Divisi Keuangan & Akuntansi</td>
		</tr>
		<tr>
			<td colspan="3" style="padding:2em; bt">&nbsp;</td>
			<td rowspan="2" colspan="3" class="bl vm">Telah diperiksa dan dibukukan dalam Buku Kas/Bank dengan nomor: <br>...................................</td>
			<td class="pl3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3 ac">&nbsp;</td>
			<td class="pl3 ac vt">(...................................)</td>
		</tr>
		
		<tr>
			<td colspan="3" class="pl3 ac">&nbsp;</td>
			<td colspan="2" class="bl pl3">&nbsp;</td>
			<td class="pl3 ac">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
		</tr>
	</tbody>
</table>
