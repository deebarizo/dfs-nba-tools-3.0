@extends('master')

@section('content')
	
	@include('_form_heading')

	<div class="row">

		{!! Form::open(array('url' => '/admin/scrapers/games' )) !!}

			<div class="col-lg-3"> 
				<div class="form-group">
					{!! Form::label('date', 'Date:') !!}
					{!! Form::text('date', '2016-10-25', ['class' => 'form-control']) !!}
				</div>
			</div>

			<div class="col-lg-12"> 
				{!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
			</div>

		{!!	Form::close() !!}

	</div>
@stop