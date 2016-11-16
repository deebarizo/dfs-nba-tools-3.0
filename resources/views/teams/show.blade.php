@extends('master')

@section('content')
	<div class="row">
		<div class="col-lg-12">
			<h2>{{ $h2Tag }}</h2>

			<hr>

			<div class="team-rotation-line-chart" style="height: 600px"></div>
		</div>

		{!! Form::open(array('url' => '/team/'.$id )) !!}

			<div class="col-lg-12">
				<h3>DK Players</h3>
			</div>

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

			<div class="col-lg-2">
				

				@foreach ($dkPlayers as $dkPlayer)

					<div class="form-group">
						{!! Form::label($dkPlayer->id, $dkPlayer->dk_name) !!}
						{!! Form::text($dkPlayer->id, '', ['class' => 'form-control', 'style' => 'width: 30%']) !!}
					</div>

				@endforeach
			</div>

			<div class="col-lg-4"> 
				{!! Form::submit('Submit', ['class' => 'btn btn-primary', 'style' => 'margin-top: 10px']) !!}
			</div>

		{!!	Form::close() !!}
		
	</div>

	<script>
		
		var team = '<?php echo $team->dk_name; ?>';
		var dates = <?php echo json_encode($dates); ?>;
		var series = <?php echo json_encode($series); ?>;
		var games = <?php echo json_encode($games); ?>;

	</script>

	<script src="/js/teams/show.js"></script>
@stop