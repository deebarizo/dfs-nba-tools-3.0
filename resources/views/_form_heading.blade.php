<div class="row">
	<div class="col-lg-12">
		<h2>{{ $h2Tag }}</h2>

		<hr>

		@if (count($errors) > 0)
		    <div class="alert alert-danger fade in" role="alert">
				<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>
		    	
		    	<p>Please try again.</p>

		        <ul>
		            @foreach ($errors->all() as $error)
		                <li>{{ $error }}</li>
		            @endforeach
		        </ul>
		    </div>
		@endif

		@if (Session::has('message'))

			<?php 

				if (strpos(Session::get('message'), 'Success!') !== false) {

					$alertHtml = 'alert-info success-alert';

				} else {

					$alertHtml = 'alert-danger';
				}

			?>

			<div class="alert {{ $alertHtml }} fade in success-message" role="alert">
				<button type="button" class="close" data-dismiss="alert"><span aria-hidden="true">×</span><span class="sr-only">Close</span></button>

				{!! Session::get('message') !!}
		    </div>
		@endif
	</div>
</div>