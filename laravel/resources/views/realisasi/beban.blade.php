@include('css')

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:1px solid #000;">
	<thead>
		<tr>
			<th colspan="9" class="">
				<h3>RINCIAN REALISASI BEBAN TAHUN {{$tahun}}</h3>
				<br>
				(Disajikan dalam Jutaan Rupiah)
			</th>
		</tr>
		<tr>
			<th colspan="9" class="">&nbsp;</th>
		</tr>
		<tr>
			<th class="ball plr3">Uraian</th>
			<th class="ball plr3 wd12">RKAP 20XX</th>
			<th class="ball plr3 wd12">Rencana TW.II 20XX</th>
			<th class="ball plr3 wd12">Realisasi TW.II 20XX</th>
			<th class="ball plr3 wd5">%</th>
			<th class="ball plr3 wd12">Rencana s.d TW.II 20XX</th>
			<th class="ball plr3 wd12">Realisasi s.d TW.II 20XX</th>
			<th class="ball plr3 wd5">%</th>
			<th class="ball plr3 wd5">%</th>
		</tr>
		<tr>
			<td class="ball plr3 ac fs10">1</td>
			<td class="ball plr3 ac fs10">2</td>
			<td class="ball plr3 ac fs10">3</td>
			<td class="ball plr3 ac fs10">4</td>
			<td class="ball plr3 ac fs10">5 (4:3)</td>
			<td class="ball plr3 ac fs10">6</td>
			<td class="ball plr3 ac fs10">7</td>
			<td class="ball plr3 ac fs10">8 (7:6)</td>
			<td class="ball plr3 ac fs10">9 (7:2)</td>
		</tr>
	</thead>
	<tbody>
		
		@foreach($rows as $row)
		<tr>
			<td class="ball plr3"><?php echo $row['uraian'];?></td>
			<td class="ball plr3 ar"><?php echo $row['rkap'];?></td>
			<td class="ball plr3 ar"><?php echo $row['rc'];?></td>
			<td class="ball plr3 ar"><?php echo $row['rl'];?></td>
			<td class="ball plr3 ar"><?php echo round(($row['rl']/$row['rc']),2);?></td>
			<td class="ball plr3 ar"><?php echo $row['rcsd'];?></td>
			<td class="ball plr3 ar"><?php echo $row['rlsd'];?></td>
			<td class="ball plr3 ar"><?php echo round(($row['rlsd']/$row['rcsd']),2);?></td>
			<td class="ball plr3 ar"><?php echo round(($row['rlsd']/$row['rkap']),2);?></td>
		</tr>
		@endforeach

		<tr>
			<td class="ball plr3 ar">Jumlah</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
		</tr>

		<tr>
			<td class="ball plr3 al">Beban Lain-lain</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
		</tr>

		<tr>
			<td class="ball plr3 ar">Total</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
			<td class="ball plr3 ar">0</td>
		</tr>
		
	</tbody>
</table>
