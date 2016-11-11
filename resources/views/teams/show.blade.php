@extends('master')

@section('content')
	<div class="row">
		<div class="col-lg-12">
			<h2>{{ $h2Tag }}</h2>

			<hr>

			<div class="team-rotation-line-chart"></div>
		</div>
	</div>

	<script>
		
		var games = <?php echo json_encode($games); ?>

	</script>

	<script src="/js/teams/show.js"></script>
@stop