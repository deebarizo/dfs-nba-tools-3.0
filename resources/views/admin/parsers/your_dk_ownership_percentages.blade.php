@extends('master')

@section('content')
	
	@include('_form_heading')

	<div class="row">

		{!! Form::open(array('url' => 'admin/parsers/your_dk_ownership_percentages')) !!}

			<div class="col-lg-2"> 
				<div class="form-group">
					{!! Form::label('date', 'Date:') !!}
					{!! Form::text('date', getYesterdayDate(), ['class' => 'form-control']) !!}
				</div>
			</div>

			<div class="col-lg-2"> 
				<div class="form-group">
					{!! Form::label('slate', 'Slate:') !!}
					{!! Form::text('slate', 'Main', ['class' => 'form-control']) !!}
				</div>
			</div>

			<div class="col-lg-6"> 
				<div class="form-group">
					{!! Form::label('your-dk-ownership-percentages', 'Your DK Ownership Percentages:') !!}
					{!! Form::textarea('your-dk-ownership-percentages', '', ['class' => 'form-control']) !!}
				</div>
			</div>

			<div class="col-lg-12"> 
				{!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
			</div>

		{!!	Form::close() !!}

	</div>
@stop