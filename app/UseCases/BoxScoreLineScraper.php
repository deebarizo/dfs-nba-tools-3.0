<?php namespace App\UseCases;

ini_set('max_execution_time', 10800); // 10800 seconds = 3 hours

use App\Models\Game;
use App\Models\GameLine;

use App\Models\Team;

use App\Models\Player;

use App\Models\BoxScoreLine;

use DB;

use Goutte\Client;
use vendor\symfony\DomCrawler\Crawler;

class BoxScoreLineScraper {

	public function scrapeBoxScoreLines($date) {

		$year = intval(date('Y', strtotime($date)));

		$monthNumber = intval(date('m', strtotime($date)));

		if ($monthNumber >= 9) {

			$year += 1;
		}

		$games = Game::where('date', $date)->get();

		$numScrapedGames = 0;

		foreach ($games as $game) {

			$boxScoreLinesExist = BoxScoreLine::where('game_id', $game->id)->count();

			if ($boxScoreLinesExist) {

				continue;
			}

			$client = new Client();

			$crawler = $client->request('GET', $game->br_link);
			
			$teams = [];

			for ($i = 0; $i < 2; $i++) { 

				$teamLink = trim($crawler->filter('div.scorebox')->filter('strong')->eq($i)->filter('a')->link()->getUri());
				$teamLink = preg_replace('/http:\\/\\/www.basketball-reference.com\\/teams\\//', '', $teamLink);
				$team = preg_replace('/\\/'.$year.'.html/', '', $teamLink);

				# dd($team);

				$teams[$i]['id'] = Team::where('br_name', $team)->pluck('id')[0];
				$teams[$i]['br_name'] = $team;
				$teams[$i]['br_name_lowercase'] = strtolower($team);
			}

			$boxScoreLines = [];

			foreach ($teams as $team) {
				
				$numOfTableRows = $crawler->filter('div#all_box_'.$team['br_name_lowercase'].'_basic')->filter('tbody')->filter('tr')->count();

				# dd($numOfTableRows);

				for ($i = 0; $i < $numOfTableRows; $i++) {

					if ($i != 5) {

						$playerRow = $crawler->filter('div#all_box_'.$team['br_name_lowercase'].'_basic')->filter('tbody')->filter('tr')->eq($i);

						$boxScoreLine = [];

						$boxScoreLine['br_link'] = $game->br_link;

						$boxScoreLine['game_id'] = $game->id;
						$boxScoreLine['team_id'] = $team['id'];
						$boxScoreLine['player_name'] = $playerRow->filter('th')->eq(0)->text();

						$playerExists = Player::where('br_name', $boxScoreLine['player_name'])->first();

						if ($playerExists) {

							$boxScoreLine['player_id'] = Player::where('br_name', $boxScoreLine['player_name'])->pluck('id')[0];
						
						} else {

							$player = new Player;

							$player->team_id = $boxScoreLine['team_id'];
							$player->br_name = $boxScoreLine['player_name'];
							$player->br_link = $playerRow->filter('th')->eq(0)->filter('a')->link()->getUri();

							$player->save();

							$boxScoreLine['player_id'] = $player->id;
						}

						$boxScoreLine['raw_mp'] = $playerRow->filter('td')->eq(0)->text();

						preg_match("/\d+:\d+/", $boxScoreLine['raw_mp'], $thisPlayerPlayed);

						if (!$thisPlayerPlayed || $boxScoreLine['raw_mp'] == '0:00') {

							continue;
						} 

						$minutes = intval(preg_replace("/(\d+)(:\d+)/", "$1", $boxScoreLine['raw_mp']));
						$rawSeconds = preg_replace("/(\d+:)(\d+)/", "$2", $boxScoreLine['raw_mp']);
						$seconds = intval(preg_replace("/(0)(\d)/", "$2", $rawSeconds));
						
						$boxScoreLine['mp'] = numFormat($minutes + ($seconds / 60), 2);

						$boxScoreLine['fg'] = $playerRow->filter('td')->eq(1)->text();
						$boxScoreLine['fga'] = $playerRow->filter('td')->eq(2)->text();
						$boxScoreLine['fg_percentage'] = $playerRow->filter('td')->eq(3)->text();
						if ($boxScoreLine['fg_percentage'] == '') {

							$boxScoreLine['fg_percentage'] = null;
						}

						$boxScoreLine['threep'] = intval($playerRow->filter('td')->eq(4)->text());
						$boxScoreLine['threepa'] = $playerRow->filter('td')->eq(5)->text();
						$boxScoreLine['threep_percentage'] = $playerRow->filter('td')->eq(6)->text();
						if ($boxScoreLine['threep_percentage'] == '') {

							$boxScoreLine['threep_percentage'] = null;
						}

						$boxScoreLine['ft'] = $playerRow->filter('td')->eq(7)->text();
						$boxScoreLine['fta'] = $playerRow->filter('td')->eq(8)->text();
						$boxScoreLine['ft_percentage'] = $playerRow->filter('td')->eq(9)->text();
						if ($boxScoreLine['ft_percentage'] == '') {

							$boxScoreLine['ft_percentage'] = null;
						}

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
													($boxScoreLine['threep'] * 0.5) + 
													($boxScoreLine['trb'] * 1.25) + 
													($boxScoreLine['ast'] * 1.5) + 
													($boxScoreLine['stl'] * 2) + 
													($boxScoreLine['blk'] * 2) + 
													($boxScoreLine['tov'] * -0.5) + 
													$bonusPoints;

						$playerRow = $crawler->filter('div#all_box_'.$team['br_name_lowercase'].'_advanced')->filter('tbody')->filter('tr')->eq($i);

						$boxScoreLine['ts_percentage'] = $playerRow->filter('td')->eq(1)->text();
						if ($boxScoreLine['ts_percentage'] == '') {

							$boxScoreLine['ts_percentage'] = null;
						}

						$boxScoreLine['efg_percentage'] = $playerRow->filter('td')->eq(2)->text();
						if ($boxScoreLine['efg_percentage'] == '') {

							$boxScoreLine['efg_percentage'] = null;
						}

						$boxScoreLine['threepa_rate'] = $playerRow->filter('td')->eq(3)->text();
						if ($boxScoreLine['threepa_rate'] == '') {

							$boxScoreLine['threepa_rate'] = null;
						}

						$boxScoreLine['fta_rate'] = $playerRow->filter('td')->eq(4)->text();
						if ($boxScoreLine['fta_rate'] == '') {

							$boxScoreLine['fta_rate'] = null;
						}

						$boxScoreLine['orb_percentage'] = $playerRow->filter('td')->eq(5)->text();
						$boxScoreLine['drb_percentage'] = $playerRow->filter('td')->eq(6)->text();
						$boxScoreLine['trb_percentage'] = $playerRow->filter('td')->eq(7)->text();
						$boxScoreLine['ast_percentage'] = $playerRow->filter('td')->eq(8)->text();
						if ($boxScoreLine['ast_percentage'] == '-1000.0' || $boxScoreLine['ast_percentage'] == '') {
							$boxScoreLine['ast_percentage'] = 0;
						}
						$boxScoreLine['stl_percentage'] = $playerRow->filter('td')->eq(9)->text();
						$boxScoreLine['blk_percentage'] = $playerRow->filter('td')->eq(10)->text();
						$boxScoreLine['tov_percentage'] = $playerRow->filter('td')->eq(11)->text();
						if ($boxScoreLine['tov_percentage'] == '') {

							$boxScoreLine['tov_percentage'] = null;
						}
						$boxScoreLine['usg_percentage'] = $playerRow->filter('td')->eq(12)->text();

						$boxScoreLines[] = $boxScoreLine;

						# prf($boxScoreLine);
					} 
				}
			}

			foreach ($boxScoreLines as $boxScoreLine) {
				
				$eBoxScoreLine = new BoxScoreLine;

				$eBoxScoreLine->game_id = $boxScoreLine['game_id'];
				$eBoxScoreLine->team_id = $boxScoreLine['team_id'];
				$eBoxScoreLine->player_id = $boxScoreLine['player_id'];
				$eBoxScoreLine->mp = $boxScoreLine['mp'];
				$eBoxScoreLine->fg = $boxScoreLine['fg'];
				$eBoxScoreLine->fga = $boxScoreLine['fga'];
				$eBoxScoreLine->fg_percentage = $boxScoreLine['fg_percentage'];
				$eBoxScoreLine->threep = $boxScoreLine['threep'];
				$eBoxScoreLine->threepa = $boxScoreLine['threepa'];
				$eBoxScoreLine->threep_percentage = $boxScoreLine['threep_percentage'];
				$eBoxScoreLine->ft = $boxScoreLine['ft'];
				$eBoxScoreLine->fta = $boxScoreLine['fta'];
				$eBoxScoreLine->ft_percentage = $boxScoreLine['ft_percentage'];
				$eBoxScoreLine->orb = $boxScoreLine['orb'];
				$eBoxScoreLine->drb = $boxScoreLine['drb'];
				$eBoxScoreLine->trb = $boxScoreLine['trb'];
				$eBoxScoreLine->ast = $boxScoreLine['ast'];
				$eBoxScoreLine->stl = $boxScoreLine['stl'];
				$eBoxScoreLine->blk = $boxScoreLine['blk'];
				$eBoxScoreLine->tov = $boxScoreLine['tov'];
				$eBoxScoreLine->pf = $boxScoreLine['pf'];
				$eBoxScoreLine->pts = $boxScoreLine['pts'];
				$eBoxScoreLine->dk_pts = $boxScoreLine['dk_pts'];
				$eBoxScoreLine->ts_percentage = $boxScoreLine['ts_percentage'];
				$eBoxScoreLine->efg_percentage = $boxScoreLine['efg_percentage'];
				$eBoxScoreLine->threepa_rate = $boxScoreLine['threepa_rate'];
				$eBoxScoreLine->fta_rate = $boxScoreLine['fta_rate'];
				$eBoxScoreLine->orb_percentage = $boxScoreLine['orb_percentage'];
				$eBoxScoreLine->drb_percentage = $boxScoreLine['drb_percentage'];
				$eBoxScoreLine->trb_percentage = $boxScoreLine['trb_percentage'];
				$eBoxScoreLine->ast_percentage = $boxScoreLine['ast_percentage'];
				$eBoxScoreLine->stl_percentage = $boxScoreLine['stl_percentage'];
				$eBoxScoreLine->blk_percentage = $boxScoreLine['blk_percentage'];
				$eBoxScoreLine->tov_percentage = $boxScoreLine['tov_percentage'];
				$eBoxScoreLine->usg_percentage = $boxScoreLine['usg_percentage'];
				
				$eBoxScoreLine->save();
			}

			$numScrapedGames++;
		}

		$gameLines = GameLine::select(DB::raw('game_lines.id,
										game_lines.game_id,
										game_lines.team_id,
										game_lines.pts,
										game_lines.vegas_pts,
										game_lines.dk_pts'))
						->join('games', function($join) {
		  
							$join->on('game_lines.game_id', '=', 'games.id');
						})
						->where('games.date', $date)
						->get();

		foreach ($gameLines as $gameLine) {
			
			$gameLineDkPts = BoxScoreLine::where('game_id', $gameLine->game_id)
									->where('team_id', $gameLine->team_id)
									->sum('dk_pts');

			$gameLine->dk_pts = $gameLineDkPts;

			$gameLine->save();

			$boxScoreLines = BoxScoreLine::where('game_id', $gameLine->game_id)
									->where('team_id', $gameLine->team_id)
									->get();

			foreach ($boxScoreLines as $boxScoreLine) {
				
				$dkShare = $boxScoreLine->dk_pts / $gameLine->dk_pts * 100;

				$boxScoreLine->dk_share = $dkShare;

				$boxScoreLine->save();
			}
		}

		$this->message = 'Success! <strong>'.$numScrapedGames.'</strong> games were scraped.';		

		return $this;
	}

}