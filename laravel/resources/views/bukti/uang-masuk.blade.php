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
			<td class="wd2 pl3 vt">:</td>
			<td class="pl3 vt">{{$nourut}}</td>
		</tr>
		<tr>
			<td class="pl3 vt">Tahun</td>
			<td class="pl3 vt">:</td>
			<td class="pl3 vt">{{$thang}}</td>
		</tr>
		<tr>
			<td class="pl3 vt">No. Bukti Masuk</td>
			<td class="pl3 vt">:</td>
			<td class="pl3 vt">{{$nodok}}</td>
		</tr>
	</tbody>
	<thead>
		<tr>
			<th colspan="3"><h3>BUKTI KAS MASUK</h3></th>
		</tr>
		<tr>
			<th colspan="3">&nbsp;</th>
		</tr>
	</thead>
</table>

<!--table content-->
<table width="100%" border="1" cellspacing="0" cellpadding="3" style="border: 1px solid #000;font-size:11px;">
	<tbody>
		<tr>
			<td class="wd15 pl3 vt">Tahun Anggaran</td>
			<td class="wd5 pl3 ar vt">:</td>
			<td class="wd15 pl3 vt">{{$thang}}</td>
			<td colspan="3" class="bl pl3">&nbsp;</td>
		</tr>
		<tr>
			<td class="pl3 vt">Otorisasi Penerimaan</td>
			<td class="pl3 ar vt">:</td>
			<td class="pl3 vt">...........</td>
			<td colspan="3" class="bl pl3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
			<td rowspan="2" class="bl pl3 vt">Dari</td>
			<td rowspan="2" class="pl3 ar vt">:</td>
			<td rowspan="2" class="pl3 vt">{{$nmpenerima}}</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3">Kode Mata Anggaran Penerimaan :</td>
			<td rowspan="3" class="bl wd15 pl3 vt">Uang Sejumlah</td>
			<td rowspan="3" class="wd2 pl3 ar vt">:</td>
			<td rowspan="3" class="pl3 vt">{{$sejumlah}}</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3">Anggaran Penerimaan</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
			<td colspan="3" class="bl">&nbsp;</td>
		</tr>
		<tr>
			<td class="pl3">{{$kredit}}</td>
			<td class="pl3 vt">Rp</td>
			<td class="pl3 ar">{{$nilai}}</td>
			<td rowspan="4" class="bl pl3 vt">Untuk</td>
			<td rowspan="4" class="wd2 pl3 ar vt">:</td>
			<td rowspan="4" class="pl3 vt">{{$uraian}}</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="pl3 vt">Rp</td>
			<td class="pl3 ar">....................</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="pl3 vt">Rp</td>
			<td class="pl3 ar">....................</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="pl3 vt">Rp</td>
			<td class="pl3 ar">....................</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="pl3 vt">Rp</td>
			<td class="pl3 ar vt">....................</td>
			<td rowspan="4" class="bl pl3 vt">Dasar Penerimaan</td>
			<td rowspan="4" class="wd2 pl3 ar vt">:</td>
			<td rowspan="4" class="pl3 vt">{{$nmakun}}</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar vt">....................</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar vt">....................</td>
		</tr>
		<tr>
			<td class="pl3">....................</td>
			<td class="pl3">Rp</td>
			<td class="pl3 ar vt">....................</td>
		</tr>
		<tr>
			<td colspan="3">&nbsp;</td>
			<td colspan="3" class="bl">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" rowspan="2" class="pl3 vt">Kode Mata Anggaran Pengembalian :</td>
			<td colspan="3" class="bl pl3 al">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;KASIR/TANDA TERIMA:</td>
		</tr>
		<tr>
			<td class="bl pl3 vt">Pada tanggal</td>
			<td class="pl3 ar vt">:</td>
			<td class="pl3 vt">{{$tgdok}}</td>
		</tr>
		<tr>
			<td class="pl3 vt">....................</td>
			<td class="pl3 ar vt">Rp</td>
			<td class="pl3 ar vt">....................</td>
			<td class="bl pl3 vt">Cash/Cek</td>
			<td class="pl3 ar vt">:</td>
			<td class="pl3 vt">{{$bayar}}</td>
		</tr>
		<tr>
			<td class="pl3 vt">....................</td>
			<td class="pl3 ar vt">Rp</td>
			<td class="pl3 ar vt">....................</td>
			<td class="bl pl3 vt">Tanda tangan</td>
			<td class="pl3 ar vt">:</td>
			<td class="pl3 vt">....................</td>
		</tr>
		<tr>
			<td class="pl3 vt">....................</td>
			<td class="pl3 ar vt">Rp</td>
			<td class="pl3 ar vt"><u>....................</u></td>
			<td colspan="3" class="bl pl3">&nbsp;</td>
		</tr>
		<tr>
			<td class="pl3 vt">Jumlah yang harus diterima</td>
			<td class="pl3 ar vt">Rp</td>
			<td class="pl3 ar vt">{{$nilai}}</td>
			<td colspan="3" class="bl pl3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3 bb">&nbsp;</td>
			<td colspan="3" class="bl bb pl3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3">&nbsp;</td>
			<td colspan="3" class="bl pl3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3">&nbsp;</td>
			<td colspan="1" class="bl pl3">&nbsp;</td>
			<td colspan="1" class="bl pl3">&nbsp;</td>
			<td class="pl3 vt">Jakarta</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3 ac">Senior Manager Keuangan dan Akuntansi</td>
			<td colspan="1" class="bl pl3">&nbsp;</td>
			<td colspan="1" class="bl pl3">&nbsp;</td>
			<td class="pl3">Junior Manajer Perbendaharaan & Perpajakan</td>
		</tr>
		<tr>
			<td colspan="3" style="padding:2em;">&nbsp;</td>
			<td colspan="1" class="bl">&nbsp;</td>
			<td colspan="1" class="bl">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3 ac">(........................................)</td>
			<td colspan="1" class="bl pl3">&nbsp;</td>
			<td colspan="1" class="bl pl3">&nbsp;</td>
			<td class="pl3">(........................................)</td>
		</tr>
		
		<tr>
			<td colspan="3" class="pl3 ac">&nbsp;</td>
			<td colspan="1" class="bl pl3">Telah diperiksa dan dibukukan dalam Buku Kas dengan nomor:<br>{{$nourut}}/{{$thang}}</td>
			<td colspan="1" class="bl pl3"></td>
			<td class="pl3 ac">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3 ac">&nbsp;</td>
			<td class="bl pl3 ac">&nbsp;</td>
			<td class="pl3 ac">&nbsp;</td>
			<td class="pl3 ac">&nbsp;</td>
		</tr>
		<tr>
			<td colspan="3" class="pl3">&nbsp;</td>
			<td class="bl pl3">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
			<td class="pl3">&nbsp;</td>
		</tr>
	</tbody>
</table>
