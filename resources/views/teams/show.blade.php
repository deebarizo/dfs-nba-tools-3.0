@extends('master')

@section('content')
	<div class="row">
		<div class="col-lg-12">
			<h2>{{ $h2Tag }}</h2>

			<hr>

			<p><strong>Links: </strong><a href="http://www.rotoworld.com/teams/clubhouse/nba/{{ $team->rt_name }}" target="_blank">RT</a></p>

			<h3>DK Players</h3>
		</div>

		<div class="col-lg-6">

			{!! Form::open(array('url' => '/update_projected_dk_share' )) !!}

				<input name="id" type="hidden" value="{{ $id }}">

				<table class="table table-striped table-bordered table-hover table-condensed">
					<thead>
						<tr>
							<th style="width: 50%">DK Player</th>
							<th style="width: 20%">Sal</th>
							<th>P DK Share</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($dkPlayers as $dkPlayer)
							<tr>
								<td><a href="/players/{{ $dkPlayer->player_id }}" target="_blank">{{ $dkPlayer->dk_name }}</a></td>
								<td>{{ $dkPlayer->salary }}</td>
								<td><div class="form-group">{!! Form::text('dk_player_id_'.$dkPlayer->id, $dkPlayer->p_dk_share, ['class' => 'form-control']) !!}</div></td>
							</tr>
						@endforeach
					</tbody>
				</table>
		</div>

		<div class="col-lg-4"> 
			{!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}

			{!!	Form::close() !!}
		</div>

		<div class="col-lg-12">

			<h3>Rotation</h3>

			<div class="team-rotation-line-chart" style="height: 800px"></div>
		</div>
	</div>

	<script>
		
		var team = <?php echo "'".$team->dk_name."'"; ?>;
		var dates = <?php echo json_encode($dates); ?>;
		var series = <?php echo json_encode($series); ?>;
		var games = <?php echo json_encode($games); ?>;

	</script>

	<script src="/js/teams/show.js"></script>
@stop