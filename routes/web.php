<?php

/****************************************************************************************
HOME
****************************************************************************************/

Route::get('/', 'PlayerPoolsController@index');


/****************************************************************************************
PLAYER POOLS
****************************************************************************************/

Route::get('/player_pools', 'PlayerPoolsController@index');
Route::get('/player_pools/{id}', 'PlayerPoolsController@show');


/****************************************************************************************
TEAMS
****************************************************************************************/

Route::get('/teams', 'TeamsController@index');
Route::get('/teams/{id}', 'TeamsController@show');


/****************************************************************************************
PLAYERS
****************************************************************************************/

Route::get('/players', 'PlayersController@index');
Route::get('/players/{id}', 'PlayersController@show');


/****************************************************************************************
STUDIES
****************************************************************************************/

Route::get('/studies', function() {

	$titleTag = 'Studies | ';
	
	return View::make('studies/index', compact('titleTag'));
});

Route::get('/studies/correlations/dk_pts_and_vegas_pts', 'StudiesController@calculateCorrelationBetweenDkPtsAndVegasPts');
Route::get('/studies/correlations/pts_and_vegas_pts', 'StudiesController@calculateCorrelationBetweenPtsAndVegasPts');
Route::get('/studies/correlations/totals_and_vegas_totals', 'StudiesController@calculateCorrelationBetweenTotalsAndVegasTotals');
Route::get('/studies/correlations/spreads_and_vegas_spreads', 'StudiesController@calculateCorrelationBetweenSpreadsAndVegasSpreads');


/****************************************************************************************
ADMIN
****************************************************************************************/

Route::get('/admin', function() {

	$titleTag = 'Admin | ';
	
	return View::make('admin/index', compact('titleTag'));
});


/****************************************************************************************
SCRAPERS
****************************************************************************************/

Route::get('/admin/scrapers/games', ['as' => 'admin.scrapers.games', function() {

	$titleTag = 'Scrape Games | ';
    $h2Tag = 'Scrape Games';	

	return View::make('admin/scrapers/games', compact('titleTag', 'h2Tag'));
}]);

Route::post('/admin/scrapers/games', 'ScrapersController@scrapeGames');


/****************************************************************************************
PARSERS
****************************************************************************************/

Route::get('/admin/parsers/dk_player_pool', ['as' => 'admin.parsers.dk_player_pool', function() {

	$titleTag = 'Parse DK Player Pool | ';
    $h2Tag = 'Parse DK Player Pool';	

	return View::make('admin/parsers/dk_player_pool', compact('titleTag', 'h2Tag'));
}]);

Route::post('/admin/parsers/dk_player_pool', 'ParsersController@parseDkPlayerPool');


Route::get('/admin/parsers/dk_ownership_percentages', ['as' => 'admin.parsers.dk_ownership_percentages', function() {

	$titleTag = 'Parse DK Ownership Percentages | ';
    $h2Tag = 'Parse DK Ownership Percentages';	

	return View::make('admin/parsers/dk_ownership_percentages', compact('titleTag', 'h2Tag'));
}]);

Route::post('/admin/parsers/dk_ownership_percentages', 'ParsersController@parseDkOwnershipPercentages');


/****************************************************************************************
CRUD
****************************************************************************************/

Route::get('/admin/crud/games', ['as' => 'admin.crud.games', function() {

	$titleTag = 'CRUD - Games | ';
    $h2Tag = 'CRUD - Games';	

	return View::make('admin/crud/games', compact('titleTag', 'h2Tag'));
}]);

Route::post('/admin/crud/games', 'CrudController@games');


/****************************************************************************************
ONE TIME PROCESS
****************************************************************************************/

Route::get('/one_time_process', 'ScrapersController@oneTimeProcess');