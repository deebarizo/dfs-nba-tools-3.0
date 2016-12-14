@extends('master')

@section('content')
	
	@include('_form_heading')

	<div class="row">

		{!! Form::open(array('url' => 'players/'.$player->id.'/update' )) !!}

			<div class="col-lg-2"> 
				<div class="form-group">
					<label>Team</label>
					<select name="team" class="form-control">
					  	@foreach ($teams as $team)
					  		<?php $selected = ($player->team_id === $team->id ? 'selected' : ''); ?>

						  	<option value="{{ $team->dk_name }}" {{ $selected }}>{{ $team->dk_name }}</option>
					  	@endforeach
					</select>	
				</div>
			</div>

			<div class="col-lg-3"> 
				<div class="form-group">
					{!! Form::label('br-name', 'BR Name:') !!}
					{!! Form::text('br-name', $player->br_name, ['class' => 'form-control']) !!}
				</div>
			</div>

			<div class="col-lg-3"> 
				<div class="form-group">
					{!! Form::label('dk-name', 'DK Name:') !!}
					{!! Form::text('dk-name', $player->dk_name, ['class' => 'form-control']) !!}
				</div>
			</div>

			<div class="col-lg-3"> 
				<div class="form-group">
					{!! Form::label('dk-short-name', 'DK Short Name:') !!}
					{!! Form::text('dk-short-name', $player->dk_short_name, ['class' => 'form-control']) !!}
				</div>
			</div>

			<div class="col-lg-12"> 
				{!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
			</div>

		{!!	Form::close() !!}

	</div>
@stop