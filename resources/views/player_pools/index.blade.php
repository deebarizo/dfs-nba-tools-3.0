@extends('master')

@section('content')
	<div class="row">
		<div class="col-lg-12">
			<h2>{{ $h2Tag }}</h2>

			<hr>

			<table class="table table-striped table-bordered table-hover table-condensed" style="width: 66%">
				<thead>
					<tr>
						<th>Date</th>
						<th>Slate</th>
						<th>Link</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($playerPools as $playerPool)
						<tr>
							<td>{{ $playerPool->date }}</td>
							<td>{{ $playerPool->slate }}</td>
							<td><a href="/player_pools/{{ $playerPool->id }}">Player Pool</a></td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
@stop