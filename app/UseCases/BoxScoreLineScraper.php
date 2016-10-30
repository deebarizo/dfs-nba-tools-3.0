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

		$game = Game::take(1)->orderBy('id', 'asc')->get()[0];

		$client = new Client();

		$crawler = $client->request('GET', $game->br_link);

		$teams = [];

		for ($i = 0; $i < 2; $i++) { 

			$teamLink = trim($crawler->filter('div.scorebox')->filter('strong')->eq($i)->filter('a')->link()->getUri());
			$teamLink = preg_replace('/http:\\/\\/www.basketball-reference.com\\/teams\\//', '', $teamLink);
			$team = preg_replace('/\\/2016.html/', '', $teamLink);

			$teams[$i]['id'] = Team::where('br_name', $team)->pluck('id')[0];
			$teams[$i]['br_name'] = $team;
			$teams[$i]['br_name_lowercase'] = strtolower($team);
		}

		foreach ($teams as $team) {
			
			$numOfTableRows = $crawler->filter('div#all_box_'.$team['br_name_lowercase'].'_basic')->filter('tbody')->filter('tr')->count();

			for ($i = 0; $i < $numOfTableRows; $i++) {

				if ($i != 5) {

					$playerRow = $crawler->filter('div#all_box_'.$team['br_name_lowercase'].'_basic')->filter('tbody')->filter('tr')->eq($i);

					$boxScoreLine = [];

					$boxScoreLine['game_id'] = $game->id;
					$boxScoreLine['team_id'] = $team['id'];
					$boxScoreLine['player_name'] = $playerRow->filter('th')->eq(0)->text();
					$boxScoreLine['player_id'] = Player::where('br_name', $boxScoreLine['player_name'])->pluck('id')[0];

					$boxScoreLine['raw_mp'] = $playerRow->filter('td')->eq(0)->text();
					$minutes = intval(preg_replace("/(\d+)(:\d+)/", "$1", $boxScoreLine['raw_mp']));
					$rawSeconds = preg_replace("/(\d+:)(\d+)/", "$2", $boxScoreLine['raw_mp']);
					$seconds = intval(preg_replace("/(0)(\d)/", "$2", $rawSeconds));
					$boxScoreLine['mp'] = numFormat($minutes + ($seconds / 60), 2);
					$boxScoreLine['fg'] = $playerRow->filter('td')->eq(1)->text();
					$boxScoreLine['fga'] = $playerRow->filter('td')->eq(2)->text();
					$boxScoreLine['fg_percentage'] = $playerRow->filter('td')->eq(3)->text();

					ddAll($boxScoreLine);
				} 
			}
		}
	}


}