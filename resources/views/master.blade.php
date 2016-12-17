<!doctype html>

<html lang="en">

	<head>
		<meta charset="UTF-8">

		<link rel="stylesheet" href="/css/bootstrap.min.css">
		<link rel="stylesheet" href="/css/bootstrap-theme.min.css">
		<link rel="stylesheet" href="/css/jquery.dataTables.min.css">
		<link rel="stylesheet" href="/css/jquery.qtip.min.css">
		<link rel="stylesheet" href="/css/style.css">

		<script src="/js/jquery-1.11.3.min.js"></script>
		<script src="/js/jquery-migrate-1.2.1.min.js"></script>
		<script src="/js/bootstrap.min.js"></script>
		<script src="/js/jquery.dataTables.min.js"></script>
		<script src="/js/jquery.qtip.min.js"></script>
		<script src="/js/highcharts.js"></script>

		<?php $siteName = 'DFS Ninja'; ?>

		<title>{{ $titleTag }}{{ $siteName }}</title>
	</head>

	<body>
		{{ csrf_field() }}
	
		<div class="navbar navbar-inverse" role="navigation">
			<div class="container">
				<div class="navbar-header">
					<a class="navbar-brand" href="/">{{ $siteName }}</a>
				</div>
				<div class="collapse navbar-collapse">
					<ul class="nav navbar-nav">
						<li class="{!! setActive('player_pools*') !!}"><a href="/player_pools">Player Pools</a></li>
						<li class="{!! setActive('teams*') !!}"><a href="/teams">Teams</a></li>
						<li class="{!! setActive('players*') !!}"><a href="/players">Players</a></li>
						<li class="{!! setActive('admin*') !!}"><a href="/admin">Admin</a></li>
						<li class="{!! setActive('studies*') !!}"><a href="/studies">Studies</a></li>
					</ul>
				</div>
			</div>
	    </div>

		<div class="container">
			@yield('content')
		</div>
	</body>

</html>