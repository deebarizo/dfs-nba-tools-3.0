<?php

/****************************************************************************************
HOME
****************************************************************************************/

Route::get('/', function() {

	$titleTag = '';
	
	return View::make('master', compact('titleTag'));
});


/****************************************************************************************
STUDIES
****************************************************************************************/

Route::get('/studies', function() {

	$titleTag = 'Studies | ';
	
	return View::make('studies/index', compact('titleTag'));
});

Route::get('/studies/correlations/pts_and_vegas_pts', 'StudiesController@calculateCorrelationBetweenPtsAndVegasPts');


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
ONE TIME PROCESS
****************************************************************************************/

// use App\Models\Team;

Route::get('/one_time_process', function() {

	ddAll('Success!');
});