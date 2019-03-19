<!-- admin -->
@if($level=='00')
	<center>
		<a href="javascript:;" class="btn btn-xs btn-warning ubah" title="Ubah Data?" id="{{ $id }}">
			<i class="material-icons">edit</i>
		</a>
	</center>
@else
	<center>-</center>
@endif