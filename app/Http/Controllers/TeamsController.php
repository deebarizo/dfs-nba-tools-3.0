<?php namespace App\Http\Controllers;

use App\Models\DkPlayerPool;
use App\Models\DkPlayer;
use App\Models\Player;
use App\Models\Team;

use App\Models\Game;
use App\Models\GameLine;
use App\Models\BoxScoreLine;

use DB;

use Goutte\Client;
use vendor\symfony\DomCrawler\Crawler;

class TeamsController extends Controller {

	public function index() {

		$teams = Team::all();

		$h2Tag = 'Teams';
		$titleTag = $h2Tag.' | ';
	    
		return view('teams/index', compact('titleTag', 'h2Tag', 'teams'));
	}

	public function show($id) {

		$team = Team::where('id', $id)->first();

		$h2Tag = 'Teams - '.$team->dk_name;
		$titleTag = $h2Tag.' | ';

		$dates = Game::join('game_lines', function($join) {

							$join->on('game_lines.game_id', '=', 'games.id');
						})
						->take(7)
						->orderBy('date', 'desc')
						->where('team_id', $id)
						->where('date', '>', '2016-09-30')
						->pluck('date')
						->toArray();

		$dates = array_reverse($dates);

		$players = Game::join('box_score_lines', function($join) {

								$join->on('box_score_lines.game_id', '=', 'games.id');
							})
							->join('players', function($join) {

								$join->on('players.id', '=', 'box_score_lines.player_id');
							})
							->orderBy('date', 'desc')
							->orderBy('box_score_lines.id', 'asc')
							->where('box_score_lines.team_id', $id)
							->where('date', '>=', $dates[0])
							->pluck('players.br_name')
							->toArray();

		$players = array_values(array_unique($players));

		$series = []; // for series property in line chart

		foreach ($players as $player) {

			$series[] = [

				'name' => $player,
				'data' => [] // minutes
			];
		}

		# ddAll($series);

		$eGames = Game::select('games.id', 'games.date')
						->join('game_lines', function($join) {

							$join->on('game_lines.game_id', '=', 'games.id');
						})
						->take(7)
						->orderBy('date', 'asc')
						->where('team_id', $id)
						->where('date', '>=', $dates[0])
						->get()
						->toArray();

		$games = [];

		foreach ($eGames as $game) {
			
			$games[$game['date']] = $game;
		}

		# ddAll($games);

		foreach ($games as $key => &$game) {
			
			$boxScoreLines = BoxScoreLine::select('players.br_name', 'mp')
											->join('players', function($join) {

												$join->on('players.id', '=', 'box_score_lines.player_id');
											})
											->where('game_id', $game['id'])
											->where('box_score_lines.team_id', $id)
											->get()
											->toArray();

			# ddAll($boxScoreLines);

			$baseUrl = url('/');

			foreach ($series as &$player) {

				$playerFound = false;

				foreach ($boxScoreLines as $index => $boxScoreLine) {

					if ($boxScoreLine['br_name'] === $player['name']) {

						if ($index > 4) { // bench player

							$player['data'][] = floatval($boxScoreLine['mp']);
						}

						if ($index <= 4) { // bench player

							$player['data'][] = [

								'y' => floatval($boxScoreLine['mp']),
								'marker' => [

									'symbol' => 'url('.$baseUrl.'/files/images/basketball-icon.png)'
								]
							];
						}

						$playerFound = true;

						break;	
					}		
				}

				if (!$playerFound) {

					$player['data'][] = null;
				}
			}

			unset($player);

			$game['game_lines'] = GameLine::join('games', function($join) {

												$join->on('games.id', '=', 'game_lines.game_id');
											})
											->join('teams', function($join) {

												$join->on('teams.id', '=', 'game_lines.team_id');
											})
											->where('game_id', $game['id'])
											->get()
											->toArray();
		}

		unset($game);

		foreach ($games as $date => &$game) {

			$year = date('Y', strtotime($date));

			$monthNumber = date('m', strtotime($date));

			$dayNumber = date('d', strtotime($date));
			
			$game['pm_link'] = 'http://popcornmachine.net/gf?date='.$year.''.$monthNumber.''.$dayNumber.'&game='.$game['game_lines'][0]['pm_name'].''.$game['game_lines'][1]['pm_name'];
		}

		unset($game);

		# ddAll($games);

		return view('teams/show', compact('titleTag', 'h2Tag', 'team', 'dates', 'series', 'games'));
	}

}