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
			</ul>
		</div>
	</div>
@stop