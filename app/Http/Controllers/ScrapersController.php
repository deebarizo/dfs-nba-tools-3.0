<?php namespace App\Http\Controllers;

ini_set('max_execution_time', 10800); // 10800 seconds = 3 hours

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Input;

use App\UseCases\GameScraper;
use App\UseCases\BoxScoreLineScraper;

class ScrapersController extends Controller {

	// Needs two arrays of numbers

	public function scrapeGames(Request $request) {

		$gameScraper = new GameScraper;

        $results = $gameScraper->scrapeGames($request->input('month'), $request->input('year'));

        $message = $results->message;

		return redirect()->route('admin.scrapers.games')->with('message', $message);
	}

	public function scrapeBoxScoreLines() {

		$boxScoreLineScraper = new BoxScoreLineScraper;

		$boxScoreLineScraper->scrapeBoxScoreLines();

		ddAll('Success');
	}

}