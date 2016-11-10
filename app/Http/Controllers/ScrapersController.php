<?php namespace App\Http\Controllers;

ini_set('max_execution_time', 10800); // 10800 seconds = 3 hours

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Input;

use App\UseCases\GameScraper;
use App\UseCases\BoxScoreLineScraper;

use App\Models\Game;
use App\Models\GameLine;
use App\Models\BoxScoreLine;

use DB;

class ScrapersController extends Controller {

	public function scrapeGames(Request $request) {

		$date = $request->input('date');

		$gameScraper = new GameScraper;

        $results = $gameScraper->scrapeGames($date);

        $message = $results->message;

        if ($message === 'Success!') {

	 		$boxScoreLineScraper = new BoxScoreLineScraper;

			$results = $boxScoreLineScraper->scrapeBoxScoreLines($date);

			$message = $results->message;			
        } 

        return redirect()->route('admin.scrapers.games')->with('message', $message);       	
	}

	public function oneTimeProcess() {



		ddAll('Success!');
	}

}