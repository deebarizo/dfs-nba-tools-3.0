@extends('master')

@section('content')
	<div class="row">
		<div class="col-lg-12">
			<h2>{{ $h2Tag }}</h2>

			<hr>

			<table class="table table-striped table-bordered table-hover table-condensed" style="width: 100%">
				<thead>
					<tr>
						<th>Team</th>
						<th>Active</th>
						<th>RT</th>
						<th>BR</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($teams as $team)
						<tr>
							<td><a href="/teams/{{ $team->id }}" target="_blank">{{ $team->dk_name }}</a></td>
							<td><?php echo ($team->active ? $team->active :  ''); ?></td>
							<td><a href="http://www.rotoworld.com/teams/clubhouse/nba/{{ $team->rt_name }}" target="_blank">News</a></td>
							<td><a href="http://www.basketball-reference.com/teams/{{ $team->br_name }}/2017_games.html" target="_blank">Schedule</a> | <a href="http://www.basketball-reference.com/teams/{{ $team->br_name }}/2017.html#all_advanced" target="_blank">Adv Player Stats</a> | <a href="http://www.basketball-reference.com/teams/{{ $team->br_name }}/2017.html#all_team_misc" target="_blank">Team Stats</a></td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
@stop