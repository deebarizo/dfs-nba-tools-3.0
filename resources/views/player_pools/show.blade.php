@extends('master')

@section('content')
	<div class="row">
		<div class="col-lg-12">
			<h2>{{ $h2Tag }}</h2>

			<hr>

			<form class="form-inline" style="margin-bottom: 20px">

				<label>Teams</label>
				<select class="form-control team-filter" style="width: 10%; margin-right: 20px">
				  	<option value="All">All</option>
				  	@foreach ($activeTeams as $activeTeam)
					  	<option value="{{ $activeTeam->dk_name }}">{{ $activeTeam->dk_name }}</option>
				  	@endforeach
				</select>	

				<label>Positions</label>
				<select class="form-control position-filter" style="width: 10%; margin-right: 20px">
				  	<option value="All">All</option>
				  	<option value="PG">PG</option>
				  	<option value="SG">SG</option>
				  	<option value="SF">SF</option>
				  	<option value="PF">PF</option>
				  	<option value="C">C</option>
				  	<option value="G">G</option>
				  	<option value="F">F</option>
				</select>

				<label>Salary</label>
				<input class="salary-input form-control" type="number" value="100000" style="width: 10%">
				<input class="form-control" type="radio" name="salary-toggle" id="greater-than" value="greater-than">>=
				<input class="form-control" type="radio" name="salary-toggle" id="less-than" value="less-than" checked="checked"><=				
				<input style="width: 10%; margin-right: 20px; outline: none; margin-left: 5px" class="salary-reset btn btn-default" name="salary-reset" value="Salary Reset">

			</form>

			<table id="player-pool" class="table table-striped table-bordered table-hover table-condensed">
				<thead>
					<tr>
						<th>Name</th>
						<th>Time</th>
						<th>Team</th>
						<th>Opp</th>
						<th>Pos</th>
						<th>Pos2</th>
						<th>Sal</th>
						<th>Both Pos</th> <!-- hidden -->
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
						    	<td>{{ $dkPlayer['game_time'] }}</td>
						    	<td>{{ $dkPlayer['team'] }}</td>
						    	<td>{{ $dkPlayer['opp_team'] }}</td>
						    	<td>{{ $dkPlayer['first_position'] }}</td>
						    	<td>{{ $dkPlayer['second_position'] }}</td>
						    	<td>{{ $dkPlayer['salary'] }}</td>
						    	<td>{{ $dkPlayer['both_positions'] }}</td>
					    </tr>
					@endforeach
				</tbody>
			</table>

		</div>
	</div>

	<script type="text/javascript">

		var playerPoolTable = $('#player-pool').DataTable({
			
			"scrollY": "600px",
			"paging": false,
			"order": [[6, "desc"]],
	        "columnDefs": [ 
	        	{
	            	"visible": false,
	            	"targets": 7
	        	}
	        ]
		});

		$('#player-pool_filter').hide();

	</script>

	<script src="/js/player_pools/index.js"></script>
@stop