@extends('master')

@section('content')
	<div class="row">
		<div class="col-lg-12">
			<h2>{{ $h2Tag }}</h2>

			<hr>

			<table id="player-pool" class="table table-striped table-bordered table-hover table-condensed">
				<thead>
					<tr>
						<th>Name</th>
						<th>Team</th>
						<th>Opp</th>
						<th>Pos</th>
						<th>Pos2</th>
						<th>Sal</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($dkPlayers as $dkPlayer)
					    <tr data-name="{{ $dkPlayer['name'] }}"
					    	data-team="{{ $dkPlayer['team'] }}"
					    	data-opp-team="{{ $dkPlayer['opp_team'] }}"
					    	data-first-position="{{ $dkPlayer['first_position'] }}"
					    	data-second-position="{{ $dkPlayer['second_position'] }}"
					    	data-salary="{{ $dkPlayer['salary'] }}"
					    	class="player-row">
						    	<td>{{ $dkPlayer['name'] }}</td>
						    	<td>{{ $dkPlayer['team'] }}</td>
						    	<td>{{ $dkPlayer['opp_team'] }}</td>
						    	<td>{{ $dkPlayer['first_position'] }}</td>
						    	<td>{{ $dkPlayer['second_position'] }}</td>
						    	<td>{{ $dkPlayer['salary'] }}</td>
					    </tr>
					@endforeach
				</tbody>
			</table>

		</div>
	</div>
@stop