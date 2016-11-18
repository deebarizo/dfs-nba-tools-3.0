@extends('master')

@section('content')
	<div class="row">

		<div class="col-lg-12">

			<h2>{{ $h2Tag }}</h2>

			<hr>	

			<h4>Overviews</h4>

			<p><strong>Links:</strong> <a target="_blank" href="http://www.google.com/search?q={{ $player->br_name }}+Rotoworld">RT</a> | <a target="_blank" href="{{ $player->br_link }}#all_advanced">BR</a> | <a target="_blank" href="http://www.google.com/search?q={{ $player->br_name }}+ESPN">ESPN</a>

			<table class="table table-striped table-bordered table-hover table-condensed" style="width: 66%">
				<thead>
					<tr>
						<th>Season</th>
						<th>Mp</th>
						<th>DK Share</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($overviews as $timePeriod => $overview)
						<tr>
							<td>{{ $timePeriod }}</td>
							<td>{{ numFormat($overview['avg_mp'], 2) }}</td>
							<td>{{ numFormat($overview['avg_dk_share'], 2) }}%</td>
						</tr>
					@endforeach
				</tbody>
			</table>

		</div>

	</div>

@stop