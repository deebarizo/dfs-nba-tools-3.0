@extends('master')

@section('content')
	<div class="row">
		<div class="col-lg-12">
			<h2>{{ $h2Tag }}</h2>

			<hr>

			<table class="table table-striped table-bordered table-hover table-condensed" style="width: 20%">
				<thead>
					<tr>
						<th>Team</th>
					</tr>
				</thead>
				<tbody>
					@foreach ($teams as $team)
						<tr>
							<td><a href="/teams/{{ $team->id }}">{{ $team->dk_name }}</a></td>
						</tr>
					@endforeach
				</tbody>
			</table>
		</div>
	</div>
@stop