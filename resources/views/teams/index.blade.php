@extends('master')

@section('content')
	<div class="row">
		<div class="col-lg-12">
			<h2>{{ $h2Tag }}</h2>

			<hr>

			<table class="table table-striped table-bordered table-hover table-condensed" style="width: 40%">
				<thead>
					<tr>
						<th>Team</th>
						<th>Active</th>
						<th>RT</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($teams as $team)
						<tr>
							<td><a href="/teams/{{ $team->id }}" target="_blank">{{ $team->dk_name }}</a></td>
							<td><?php echo ($team->active ? $team->active :  ''); ?></td>
							<td><a href="http://www.rotoworld.com/teams/clubhouse/nba/{{ $team->rt_name }}" target="_blank">RT</a></td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
@stop