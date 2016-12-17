@extends('master')

@section('content')
	<div class="row">

		<div class="col-lg-12">

			@include('_form_heading')

			<p><strong>Last Update: </strong>{{ $lastUpdate }}</p>

			<form class="form-inline" style="margin-bottom: 20px">

				<label>Teams</label>
				<select class="form-control team-filter" style="width: 10%; margin-right: 20px">
				  	<option value="All">All</option>
				  	@foreach ($activeTeams as $activeTeam)
					  	<option value="{{ $activeTeam['dk_name'] }}">{{ $activeTeam['dk_name'] }}</option>
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

			<table id="player-pool" style="font-size: {{ $fontSize }}" class="table table-striped table-bordered table-hover table-condensed">
				<thead>
					<tr>
						<th>Name</th>
						<th>TP</th>
						<th>Tim</th>
						<th>Tot</th>
						<th>Spr</th>
						<th>Tm</th>
						<th>Op</th>
						<th>P</th>
						<th>P2</th>
						<th>P Mp</th>
						<th>PDK</th>
						<th>Sal</th>
						<th>PV</th>
						<th>DK</th>
						<th>Sal</th>
						<th>V</th>
						<th>YO</th>
						<th>O</th>
						<th>Both Pos</th> <!-- hidden -->
					</tr>
				</thead>
				<tbody>
					@foreach ($dkPlayers as $dkPlayer)

						<?php 

							$gameTime = preg_replace('/\s/', '', $dkPlayer->game_time);
							$gameTime = preg_replace('/M/', '', $gameTime);
						?>

					    <tr data-name="{{ $dkPlayer->name }}"
					    	data-team="{{ $dkPlayer->team }}"
					    	data-opp-team="{{ $dkPlayer->opp_team }}"
					    	data-first-position="{{ $dkPlayer->first_position }}"
					    	data-second-position="{{ $dkPlayer->second_position }}"
					    	data-salary="{{ $dkPlayer->salary }}"
					    	data-dk-player-id="{{ $dkPlayer->dk_player_id }}"
					    	class="player-row">
						    	<td><a href="/players/{{ $dkPlayer->player_id }}" target="_blank">{{ $dkPlayer->name }}</a></td>
						    	<td class="stars">{!! $dkPlayer->stars_html !!}</td>
						    	<td>{{ $gameTime }}</td>
						    	<td>{{ $dkPlayer->total }}</td>
						    	<td>{{ $dkPlayer->spread }}</td>
						    	<td><a href="/teams/{{ $dkPlayer->team_id }}" target="_blank">{{ $dkPlayer->team }}</a></td>
						    	<td><a href="/teams/{{ $dkPlayer->opp_team_id }}" target="_blank">{{ $dkPlayer->opp_team }}</a></td>
						    	<td>{{ $dkPlayer->first_position }}</td>
						    	<td>{{ $dkPlayer->second_position }}</td>
						    	<td>{{ $dkPlayer->p_mp }}</td>
						    	<td>{{ $dkPlayer->p_dk_pts }}</td>
						    	<td>{{ $dkPlayer->salary }}</td>
						    	<td>{{ numFormat($dkPlayer->p_dk_pts / ($dkPlayer->salary / 1000), 2) }}</td>
						    	@if (isset($dkPlayer->dk_pts))
						    		<td>{{ numFormat($dkPlayer->dk_pts, 2) }}</td>
						    	@else
						    		<td>{{ numFormat(0, 2) }}</td>
						    	@endif
						    	<td>{{ $dkPlayer->salary }}</td>
						    	@if (isset($dkPlayer->value))
						    		<td>{{ numFormat($dkPlayer->value, 2) }}</td>
						    	@else
						    		<td>{{ numFormat(0, 2) }}</td>
						    	@endif
						    	@if (isset($dkPlayer->your_ownership_percentage))
						    		<td>{{ $dkPlayer->your_ownership_percentage }}</td>
						    	@else
						    		<td>{{ numFormat(0, 2) }}</td>
						    	@endif
						    	@if (isset($dkPlayer->ownership_percentage))
						    		<td>{{ $dkPlayer->ownership_percentage }}</td>
						    	@else
						    		<td>{{ numFormat(0, 2) }}</td>
						    	@endif
						    	<td>{{ $dkPlayer->both_positions }}</td>
					    </tr>
					@endforeach
				</tbody>
			</table>

		</div>
	</div>

	<script type="text/javascript">

		/****************************************************************************************
		GLOBAL VARIABLES
		****************************************************************************************/

		var baseUrl = '<?php echo url('/'); ?>';

		var columnIndexes = {

			team: 5,
			salary: 11,
			dk_pts: 13,
			salary2: 14,
			value: 15,
			your_ownership_percentage: 16, 
			ownership_percentage: 17,
			position: 18
		};

		var playerPoolTable = $('#player-pool').DataTable({
			
			"scrollY": "600px",
			"paging": false,
			"order": [[columnIndexes.salary, "desc"]],
	        "aoColumns": [
	            { "orderSequence": [ "desc", "asc" ] }, // 0
	            { "orderSequence": [ "desc", "asc" ] }, // 1
	            { "orderSequence": [ "desc", "asc" ] }, // 2
	            { "orderSequence": [ "desc", "asc" ] }, // 3
	            { "orderSequence": [ "desc", "asc" ] }, // 4
	            { "orderSequence": [ "desc", "asc" ] }, // 5
	            { "orderSequence": [ "desc", "asc" ] }, // 6
	            { "orderSequence": [ "desc", "asc" ] }, // 7
	            { "orderSequence": [ "desc", "asc" ] }, // 8
	            { "orderSequence": [ "desc", "asc" ] }, // 9
	            { "orderSequence": [ "desc", "asc" ] }, // 10
	            { "orderSequence": [ "desc", "asc" ] }, // 11
	            { "orderSequence": [ "desc", "asc" ] }, // 12
	            { "orderSequence": [ "desc", "asc" ] }, // 13
	            { "orderSequence": [ "desc", "asc" ] }, // 14
	            { "orderSequence": [ "desc", "asc" ] }, // 15
	            { "orderSequence": [ "desc", "asc" ] }, // 16
	            { "orderSequence": [ "desc", "asc" ] }, // 17
	            { "orderSequence": [ "desc", "asc" ] } // 18
	        ],
	        "columnDefs": [ 
	        	{
	            	"visible": false,
	            	"targets": columnIndexes.position
	        	}
	        	<?php if ($playerPoolIsActive) { ?>
	        		,{
		            	"visible": false,
		            	"targets": columnIndexes.dk_pts
	        		},
					{
		            	"visible": false,
		            	"targets": columnIndexes.salary2
	        		},
					{
		            	"visible": false,
		            	"targets": columnIndexes.value
	        		},
					{
		            	"visible": false,
		            	"targets": columnIndexes.ownership_percentage
	        		},
					{
		            	"visible": false,
		            	"targets": columnIndexes.your_ownership_percentage
	        		}	        		
	        	<?php } ?>
	        ]
		});

		$('#player-pool_filter').hide();


		/****************************************************************************************
		AJAX SETUP
		****************************************************************************************/

		$.ajaxSetup({ // http://stackoverflow.com/a/37663496/1946525
		    
		    headers: {
		        
		        'X-CSRF-Token': $('input[name="_token"]').val()
		    }
		});


		/****************************************************************************************
		STARS
		****************************************************************************************/

		var maxNumOfStars = 4;

		$('span.star').on('click', function(e) {

			e.preventDefault();

			var clickNumber = $(this).data('star');

			var stars = $(this).closest('td.stars');

			var dkPlayerId = stars.closest('tr').data('dk-player-id');

			var numOfActiveStarsOnClick = stars.find('span.star.glyphicon-star').length;

			if ($(this).hasClass('glyphicon-star')) {

				var userClickedActiveStar = true;

			} else if ($(this).hasClass('glyphicon-star-empty')) {

				var userClickedActiveStar = false;
			}

			if (userClickedActiveStar) {

				var numOfActiveStarsAfterClick = numOfActiveStarsOnClick - (numOfActiveStarsOnClick - clickNumber);
			}

			if (!userClickedActiveStar) {

				var numOfActiveStarsAfterClick = numOfActiveStarsOnClick + (clickNumber - numOfActiveStarsOnClick + 1);
			}

			$.ajax({

	            url: baseUrl+'/dk_players/update_stars',
	           	type: 'POST',
	           	data: { 
	           	
	           		numOfActiveStarsAfterClick: numOfActiveStarsAfterClick,
	           		dkPlayerId: dkPlayerId
	           	},
	            success: function() {
	            
					if (userClickedActiveStar) {

						for (var n = clickNumber; n < numOfActiveStarsOnClick; n++) {

							var star = stars.find('span.star').eq(n);

							star.removeClass('glyphicon-star').addClass('glyphicon-star-empty');
						}
					
					} else if (!userClickedActiveStar) {

						for (var n = 0; n < clickNumber + 1; n++) {

							var star = stars.find('span.star').eq(n);

							star.removeClass('glyphicon-star-empty').addClass('glyphicon-star');
						}
					}
	            }
	        });
		});



	</script>

	<script src="/js/player_pools/show.js"></script>
@stop