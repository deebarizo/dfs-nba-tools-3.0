@extends('master')

@section('content')
	<div class="row">
		<div class="col-lg-12">
			<h2>Admin</h2>

			<hr>

			<h4>Scrapers</h4>

			<ul>
				<li><a href="/admin/scrapers/games">Games</a></li>
			</ul>

			<h4>Parsers</h4>

			<ul>
				<li><a href="/admin/parsers/dk_player_pool">DK Player Pool</a></li>
				<li><a href="/admin/parsers/dk_ownership_percentages">DK Ownership Percentages</a></li>
				<li><a href="/admin/parsers/your_dk_ownership_percentages">Your DK Ownership Percentages</a></li>
			</ul>

			<h4>CRUD</h4>

			<ul>
				<li><a href="/admin/crud/games">Games</a></li>
			</ul>			
		</div>
	</div>
@stop