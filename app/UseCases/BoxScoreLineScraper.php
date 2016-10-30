<?php namespace App\UseCases;

ini_set('max_execution_time', 10800); // 10800 seconds = 3 hours

use App\Models\Game;

use App\Models\Team;

use App\Models\Player;

use App\Models\BoxScoreLine;

use DB;

use Goutte\Client;
use vendor\symfony\DomCrawler\Crawler;

class BoxScoreLineScraper {

	public function scrapeBoxScoreLines() {

		$games = Game::take(5)->orderBy('id', 'asc')->get();

		foreach ($games as $game) {

			$client = new Client();

			$crawler = $client->request('GET', $game->br_link);

			$teams = [

				'away' => null,
				'home' => null
			];

			for ($i = 0; $i < 2; $i++) { 

				$teamLink = trim($crawler->filter('div.scorebox')->filter('strong')->eq($i)->filter('a')->link()->getUri());
				$teamLink = preg_replace('/http:\\/\\/www.basketball-reference.com\\/teams\\//', '', $teamLink);
				$team = preg_replace('/\\/2016.html/', '', $teamLink);

				if ($i === 0) {

					$teams['away'] = $team;
				}

				if ($i === 1) {

					$teams['home'] = $team;
				}
			}

			ddAll($teams);
		}

		
	}


}