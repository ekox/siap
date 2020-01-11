@include('css')

<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:0px solid #000;">
	<thead>
		<tr>
			<th colspan="9" class="">
				RINCIAN REALISASI PENDAPATAN TAHUN {{$tahun}}
				<br>
				<span class="fs10">(Disajikan dalam jutaan Rupiah)</span>
			</th>
		</tr>
		<tr>
			<th colspan="9" class="">&nbsp;</th>
		</tr>
		<tr>
			<th class="bl bt plr3">Uraian</th>
			<th class="bl bt plr3 wd12">RKAP {{$tahun}}</th>
			<th class="bl bt plr3 wd12">Rencana TW.II {{$tahun}}</th>
			<th class="bl bt plr3 wd12">Realisasi TW.II {{$tahun}}</th>
			<th class="bl bt plr3 wd5">%</th>
			<th class="bl bt plr3 wd12">Rencana s.d TW.II {{$tahun}}</th>
			<th class="bl bt plr3 wd12">Realisasi s.d TW.II {{$tahun}}</th>
			<th class="bl bt plr3 wd5">%</th>
			<th class="bl bt br plr3 wd5">%</th>
		</tr>
		<tr>
			<td class="bl bt bb plr3 ac fs10">1</td>
			<td class="bl bt bb plr3 ac fs10">2</td>
			<td class="bl bt bb plr3 ac fs10">3</td>
			<td class="bl bt bb plr3 ac fs10">4</td>
			<td class="bl bt bb plr3 ac fs10">5 (4:3)</td>
			<td class="bl bt bb plr3 ac fs10">6</td>
			<td class="bl bt bb plr3 ac fs10">7</td>
			<td class="bl bt bb plr3 ac fs10">8 (7:6)</td>
			<td class="bl bt br bb plr3 ac fs10">9 (7:2)</td>
		</tr>
	</thead>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="0" style="border:0px solid #000;font-size:11px;">
	<tbody>		
		@foreach($rows as $row)
		<tr>
			<td class="bl al plr3">{{ $row['uraian'] }}</td>
			<td class="wd12 bl ar plr3">{{ $row['rkap'] }}</td>
			<td class="wd12 bl ar plr3">{{ $row['rctw'] }}</td>
			<td class="wd12 bl ar plr3">{{ $row['rltw'] }}</td>
			<td class="wd5 bl ar plr3">{{ $row['psn1'] }}</td>
			<td class="wd12 bl ar plr3">{{ $row['rcsdtw'] }}</td>
			<td class="wd12 bl ar plr3">{{ $row['rlsdtw'] }}</td>
			<td class="wd5 bl ar plr3">{{ $row['psn2'] }}</td>
			<td class="wd5 bl br ar plr3">{{ $row['psn3'] }}</td>
		</tr>
		@endforeach

		<tr>
			<td class="bl bt plr3 ac fb">Jumlah</td>
			<td class="bl bt  plr3 ar">0</td>
			<td class="bl bt plr3 ar">0</td>
			<td class="bl bt plr3 ar">0</td>
			<td class="bl bt plr3 ar">0</td>
			<td class="bl bt plr3 ar">0</td>
			<td class="bl bt plr3 ar">0</td>
			<td class="bl bt plr3 ar">0</td>
			<td class="bl bt br plr3 ar">0</td>
		</tr>

		<tr>
			<td class="bl bt plr3 al">Pendapatan Lain-lain</td>
			<td class="bl bt plr3 ar">0</td>
			<td class="bl bt plr3 ar">0</td>
			<td class="bl bt plr3 ar">0</td>
			<td class="bl bt plr3 ar">0</td>
			<td class="bl bt plr3 ar">0</td>
			<td class="bl bt plr3 ar">0</td>
			<td class="bl bt plr3 ar">0</td>
			<td class="bl bt br plr3 ar">0</td>
		</tr>

		<tr>
			<td class="bl bt bb plr3 ac fb">Total</td>
			<td class="bl bt bb plr3 ar">0</td>
			<td class="bl bt bb plr3 ar">0</td>
			<td class="bl bt bb plr3 ar">0</td>
			<td class="bl bt bb plr3 ar">0</td>
			<td class="bl bt bb plr3 ar">0</td>
			<td class="bl bt bb plr3 ar">0</td>
			<td class="bl bt bb plr3 ar">0</td>
			<td class="bl bt br bb plr3 ar">0</td>
		</tr>
		
	</tbody>
</table>
