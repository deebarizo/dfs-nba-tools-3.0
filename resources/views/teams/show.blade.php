@extends('master')

@section('content')
	<div class="row">
		<div class="col-lg-12">
			<h2>{{ $h2Tag }}</h2>

			<hr>

			<div class="team-rotation-line-chart" style="height: 600px"></div>
		</div>
	</div>

	<script>
		
		var team = '<?php echo $team->dk_name; ?>';
		var dates = <?php echo json_encode($dates); ?>;
		var series = <?php echo json_encode($series); ?>;

	</script>

	<script src="/js/teams/show.js"></script>
@stop