@extends('master')

@section('content')
	<div class="row">
		<div class="col-lg-12">
			<h2>{{ $h2Tag }}</h2>

			<hr>

			<p><strong>Last Update: </strong>{{ $lastUpdate }}</p>

			<p><strong>Links: </strong><a href="http://www.rotoworld.com/teams/clubhouse/nba/{{ $team->rt_name }}" target="_blank">RT</a></p>

			<h3>DK Players</h3>

			<p><strong>Rotation Players: </strong>{{ $metadata['num_of_players_in_rotation'] }} | <strong>Total P MP: </strong>{{ $metadata['total_p_mp'] }} ({{ $metadata['total_p_mp_percentage'] }}%)</p>
		</div>

		<div class="col-lg-11">

			{!! Form::open(array('url' => '/teams/update_projected_stats' )) !!}

				<input name="id" type="hidden" value="{{ $id }}">

				<table class="table table-striped table-bordered table-condensed">
					<thead>
						<tr>
							<th>Name</th>
							<th style="width: 15%">P Mp</th>
							<th>P Mp Ui</th>
							<th>P DKS/Mp</th>
							<th>P DK Share</th>
							<th>P DK Pts</th>
							<th>Sal</th>
							<th>P Val</th>
							<th style="width: 20%">Note</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($dkPlayers as $dkPlayer)
							<tr>
								<td><a href="/players/{{ $dkPlayer->player_id }}" target="_blank">{{ $dkPlayer->dk_name }}</a></td>
								<td><div class="form-group">{!! Form::text('dk_player_id_'.$dkPlayer->id.'_p_mp', $dkPlayer->p_mp, ['class' => 'form-control']) !!}</div></td>
								<td>{{ $dkPlayer->p_mp_ui }}</td>
								<td>{{ $dkPlayer->p_dks_slash_mp }}</td>
								<td>{{ $dkPlayer->p_dk_share }}</td>
								<td>{{ $dkPlayer->p_dk_pts }}</td>
								<td>{{ $dkPlayer->salary }}</td>
								<td>{{ numFormat($dkPlayer->p_dk_pts / ($dkPlayer->salary / 1000), 2) }}</td>
								<td>{{ $dkPlayer->note }}</td>
							</tr>
						@endforeach
					</tbody>
				</table>
		</div>

		<div class="col-lg-1"> 
			{!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}

			{!!	Form::close() !!}
		</div>

		<div class="col-lg-12">

			<h3>Games</h3>

			<table class="table table-striped table-bordered table-hover table-condensed">
				<thead>
					<tr>
						<th>Date</th>
						<th>Links</th>
						<th>Location</th>
						<th>Opponent</th>
						<th>Tm</th>
						<th>Opp</th>
						<th>Ot</th>
					</tr>
				</thead>
					<tbody>
						@foreach ($games as $game)

							<?php 

								$year = date('Y', strtotime($game->date));

								$monthNumber = date('m', strtotime($game->date));

								$dayNumber = date('d', strtotime($game->date));

								$pmLink = 'http://popcornmachine.net/gf?date='.$year.''.$monthNumber.''.$dayNumber.'&game='.$game->away_pm_team.''.$game->home_pm_team;
							?>

							<tr>
						    	<td>{{ $game->date }}</td>
						    	<td><a target="_blank" href="{{ $game->br_link }}">BR</a> | <a target="_blank" href="{{ $pmLink }}">PM</a></td>
						    	<td><?php echo ($game->location === 'home' ? '' : '@'); ?></td>
						    	<td>{{ $game->opp_team_dk_name }}</td>
						    	<td>{{ $game->pts }}</td>
						    	<td>{{ $game->opp_team_pts }}</td>
						    	<td>{{ $game->ot_periods }}</td>
						    </tr>
						@endforeach
					</tbody>
			</table>
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
		var games = <?php echo json_encode($gamesForRotation); ?>;

	</script>

	<script src="/js/teams/show.js"></script>
@stop