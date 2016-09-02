@extends('master')

@section('content')
	<div class="row">
		<div class="col-lg-12">
			<h2>{{ $h2Tag }}</h2>

			<hr>

			<p><strong>Correlation:</strong> {{ $data['correlation'] }}</p>
			<p><strong>Calculate Predicted Score:</strong> {{ $data['equation'] }}</p>

			<div id="container" style="width:100%; height:800px;"></div>

		</div>
	</div>

	@include('studies.correlations._chart')
@stop