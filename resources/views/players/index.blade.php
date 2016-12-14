@extends('master')

@section('content')

	@include('_form_heading')

	<div class="row">
		<div class="col-lg-12">

			<table id="players" class="table table-striped table-bordered table-hover table-condensed">
				<thead>
					<tr>
						<th>Name (DK)</th>
						<th>Short Name (DK)</th>
						<th>Name (BR)</th>
						<th>BR Link</th>
						<th>Team</th>
						<th>Edit</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($players as $player)
						<tr>
							<td><a href="/players/{{ $player->id }}" target="_blank">{{ $player->dk_name }}</a></td>
							<td>{{ $player->dk_short_name }}</td>
							<td>{{ $player->br_name }}</td>
							<td><a href="{{ $player->br_link }}" target="_blank">BR</a></td>
							<td>{{ $player->team }}</td>
							<td><a href="/players/{{ $player->id }}/edit"><span class="glyphicon glyphicon-edit" aria-hidden="true"></span></a></td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>

	<script type="text/javascript">

		$('#players').dataTable({

			"paging": true,
			"order": [[2, "asc"]]
		});

	</script>
@stop