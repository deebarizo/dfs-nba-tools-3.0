<?php namespace App\Http\Controllers;

ini_set('max_execution_time', 10800); // 10800 seconds = 3 hours

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Input;

use Goutte\Client;
use vendor\symfony\DomCrawler\Crawler;

# use App\UseCases\GamesScraper;

class ScrapersController extends Controller {

	// Needs two arrays of numbers

	public function scrapeGames(Request $request) {

		ddAll($request->input('month'));
	}

}