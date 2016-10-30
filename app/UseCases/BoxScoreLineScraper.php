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

		$boxScoreLines;

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
					$boxScoreLine['3p'] = intval($playerRow->filter('td')->eq(4)->text());
					$boxScoreLine['3pa'] = $playerRow->filter('td')->eq(5)->text();
					$boxScoreLine['3p_percentage'] = $playerRow->filter('td')->eq(6)->text();
					$boxScoreLine['ft'] = $playerRow->filter('td')->eq(7)->text();
					$boxScoreLine['fta'] = $playerRow->filter('td')->eq(8)->text();
					$boxScoreLine['ft_percentage'] = $playerRow->filter('td')->eq(9)->text();
					$boxScoreLine['orb'] = $playerRow->filter('td')->eq(10)->text();
					$boxScoreLine['drb'] = $playerRow->filter('td')->eq(11)->text();
					$boxScoreLine['trb'] = intval($playerRow->filter('td')->eq(12)->text());
					$boxScoreLine['ast'] = intval($playerRow->filter('td')->eq(13)->text());
					$boxScoreLine['stl'] = intval($playerRow->filter('td')->eq(14)->text());
					$boxScoreLine['blk'] = intval($playerRow->filter('td')->eq(15)->text());
					$boxScoreLine['tov'] = intval($playerRow->filter('td')->eq(16)->text());
					$boxScoreLine['pf'] = $playerRow->filter('td')->eq(17)->text();
					$boxScoreLine['pts'] = intval($playerRow->filter('td')->eq(18)->text());

					$doubleDigitCount = 0;

					if ($boxScoreLine['pts'] >= 10) {

						$doubleDigitCount++;
					}

					if ($boxScoreLine['trb'] >= 10) {

						$doubleDigitCount++;
					}

					if ($boxScoreLine['ast'] >= 10) {

						$doubleDigitCount++;
					}

					if ($boxScoreLine['blk'] >= 10) {

						$doubleDigitCount++;
					}

					if ($boxScoreLine['stl'] >= 10) {

						$doubleDigitCount++;
					}

					$bonusPoints = 0;

					if ($doubleDigitCount >= 2) {

						$bonusPoints += 1.5; // double-double bonus
					}

					if ($doubleDigitCount >= 3) {

						$bonusPoints += 3; // triple-double bonus
					}

					$boxScoreLine['dk_pts'] = $boxScoreLine['pts'] + 
												($boxScoreLine['3p'] * 0.5) + 
												($boxScoreLine['trb'] * 1.25) + 
												($boxScoreLine['ast'] * 1.5) + 
												($boxScoreLine['stl'] * 2) + 
												($boxScoreLine['blk'] * 2) + 
												($boxScoreLine['tov'] * -0.5) + 
												$bonusPoints;

					$playerRow = $crawler->filter('div#all_box_'.$team['br_name_lowercase'].'_advanced')->filter('tbody')->filter('tr')->eq($i);

					$boxScoreLine['ts_percentage'] = $playerRow->filter('td')->eq(1)->text();
					$boxScoreLine['efg_percentage'] = $playerRow->filter('td')->eq(2)->text();
					$boxScoreLine['3pa_rate'] = $playerRow->filter('td')->eq(3)->text();
					$boxScoreLine['fta_rate'] = $playerRow->filter('td')->eq(4)->text();
					$boxScoreLine['orb_percentage'] = $playerRow->filter('td')->eq(5)->text();
					$boxScoreLine['drb_percentage'] = $playerRow->filter('td')->eq(6)->text();
					$boxScoreLine['trb_percentage'] = $playerRow->filter('td')->eq(7)->text();
					$boxScoreLine['ast_percentage'] = $playerRow->filter('td')->eq(8)->text();
					$boxScoreLine['stl_percentage'] = $playerRow->filter('td')->eq(9)->text();
					$boxScoreLine['blk_percentage'] = $playerRow->filter('td')->eq(10)->text();
					$boxScoreLine['tov_percentage'] = $playerRow->filter('td')->eq(11)->text();
					$boxScoreLine['usg_percentage'] = $playerRow->filter('td')->eq(12)->text();

					$boxScoreLines[] = $boxScoreLine;

					prf($boxScoreLine);
				} 
			}
		}
	}


}