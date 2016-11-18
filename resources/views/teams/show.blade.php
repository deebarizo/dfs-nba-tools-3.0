@extends('master')

@section('content')
	<div class="row">
		<div class="col-lg-12">
			<h2>{{ $h2Tag }}</h2>

			<hr>

			<div class="team-rotation-line-chart" style="height: 600px"></div>
		</div>

		{!! Form::open(array('url' => '/update_projected_dk_share' )) !!}

			<input name="id" type="hidden" value="{{ $id }}">

			<div class="col-lg-12">
				<h3>DK Players</h3>
			</div>

			<div class="col-lg-4">

				<table class="table table-striped table-bordered table-hover table-condensed">
					<thead>
						<tr>
							<th style="width: 70%">DK Player</th>
							<th>P DK Share</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($dkPlayers as $dkPlayer)
							<tr>
								<td><a href="/players/{{ $dkPlayer->player_id }}" target="_blank">{{ $dkPlayer->dk_name }}</a></td>
								<td><div class="form-group">{!! Form::text('dk_player_id_'.$dkPlayer->id, $dkPlayer->p_dk_share, ['class' => 'form-control']) !!}</div></td>
							</tr>
						@endforeach
					</tbody>
				</table>
			</div>

			<div class="col-lg-4"> 
				{!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
			</div>

		{!!	Form::close() !!}
		
	</div>

	<script>
		
		var team = <?php echo "'".$team->dk_name."'"; ?>;
		var dates = <?php echo json_encode($dates); ?>;
		var series = <?php echo json_encode($series); ?>;
		var games = <?php echo json_encode($games); ?>;

	</script>

	<script src="/js/teams/show.js"></script>
@stop