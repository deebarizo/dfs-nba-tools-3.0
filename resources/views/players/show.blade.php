@extends('master')

@section('content')
	<div class="row">

		<div class="col-lg-12">

			<h2>{{ $h2Tag }}</h2>

			<hr>	

			<h4>Overviews</h4>

			<p><strong>Links:</strong> <a target="_blank" href="http://www.google.com/search?q={{ $player->br_name }}+Rotoworld">RT</a> | <a target="_blank" href="{{ $player->br_link }}#all_advanced">BR</a> | <a target="_blank" href="http://www.google.com/search?q={{ $player->br_name }}+ESPN">ESPN</a></p>

			<p><strong>P DK Share: </strong>{{ $metadata['p_dk_share'] }}%</p>

			<table class="table table-striped table-bordered table-hover table-condensed" style="width: 66%">
				<thead>
					<tr>
						<th>Season</th>
						<th>Mp</th>
						<th>DK Share</th>
						<th>DKS/Mp</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($overviews as $timePeriod => $overview)
						<tr>
							<td>{{ $timePeriod }}</td>
							<td>{{ numFormat($overview['avg_dk_share'], 2) }}%</td>
							<td>{{ numFormat($overview['avg_mp'], 2) }}</td>
							<td>{{ numFormat($overview['avg_dk_share_slash_avg_mp'], 2) }}</td>
						</tr>
					@endforeach
				</tbody>
			</table>

			@foreach ($seasons as $years => $season) 

				<h4>Box Score Lines ({{ $years }})</h4>

				<table class="table table-striped table-bordered table-hover table-condensed">
					<thead>
						<tr>
							<th>Date</th>
							<th>Team</th>
							<th>Opp</th>
							<th>Score</th>
							<th>Line</th>
							<th>Links</th>
							<th>Mp</th>
							<th>Ot</th>
							<th>Fg</th>
							<th>3p</th>
							<th>Ft</th>
							<th>Or</th>
							<th>Dr</th>
							<th>Tr</th>
							<th>Ast</th>
							<th>Bl</th>
							<th>St</th>
							<th>Pf</th>
							<th>To</th>
							<th>Pt</th>
							<th>Usg</th>
							<th>DK</th>
							<th>DKS</th>
						</tr>
					</thead>
					<tbody>
						@foreach ($season as $boxScoreLine)

							<?php 

								$gameMetadata = getGameMetadata($boxScoreLine->game->game_lines, $boxScoreLine->dk_name);

								$year = date('Y', strtotime($boxScoreLine->date));

								$monthNumber = date('m', strtotime($boxScoreLine->date));

								$dayNumber = date('d', strtotime($boxScoreLine->date));

								$pmLink = 'http://popcornmachine.net/gf?date='.$year.''.$monthNumber.''.$dayNumber.'&game='.$gameMetadata['away_pm_team'].''.$gameMetadata['home_pm_team'];
							?>

							<tr>
						    	<td>{{ $boxScoreLine->date }}</td>
						    	<td>{{ $boxScoreLine->dk_name }}</td>
						    	<td>{{ $gameMetadata['opp_team'] }}</td>
						    	<td>{!! $gameMetadata['html_score'] !!}</td>
						    	<td>{{ $gameMetadata['line'] }}</td>
						    	<td><a target="_blank" href="{{ $boxScoreLine->br_link }}">BR</a> | <a target="_blank" href="{{ $pmLink }}">PM</a></td>
						    	<td>{{ $boxScoreLine->mp }}</td>
						    	<td>{{ $boxScoreLine->game->ot_periods }}</td>
						    	<td>{{ $boxScoreLine->fg }}-{{ $boxScoreLine->fga }}</td>
						    	<td>{{ $boxScoreLine->threep }}-{{ $boxScoreLine->threepa }}</td>
						    	<td>{{ $boxScoreLine->ft }}-{{ $boxScoreLine->fta }}</td>
						    	<td>{{ $boxScoreLine->orb }}</td>
						    	<td>{{ $boxScoreLine->drb }}</td>
						    	<td>{{ $boxScoreLine->trb }}</td>
						    	<td>{{ $boxScoreLine->ast }}</td>
						    	<td>{{ $boxScoreLine->blk }}</td>
						    	<td>{{ $boxScoreLine->stl }}</td>
						    	<td>{{ $boxScoreLine->pf }}</td>
						    	<td>{{ $boxScoreLine->tov }}</td>
						    	<td>{{ $boxScoreLine->pts }}</td>
						    	<td>{{ $boxScoreLine->usg_percentage }}</td>
						    	<td>{{ $boxScoreLine->dk_pts }}</td>
						    	<td>{{ $boxScoreLine->dk_share }}</td>
						    </tr>
						@endforeach
					</tbody>
				</table>

			@endforeach

		</div>

	</div>

@stop