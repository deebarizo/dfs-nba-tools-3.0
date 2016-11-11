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

		$games = Game::join('game_lines', function($join) {

							$join->on('game_lines.game_id', '=', 'games.id');
						})
						->take(7)
						->orderBy('date', 'asc')
						->where('team_id', $id)
						->where('date', '>=', $dates[0])
						->get()
						->toArray();

		# ddAll($games);

		foreach ($games as $gameIndex => &$game) {
			
			$boxScoreLines = BoxScoreLine::select('players.br_name', 'mp')
											->join('players', function($join) {

												$join->on('players.id', '=', 'box_score_lines.player_id');
											})
											->where('game_id', $game['game_id'])
											->where('box_score_lines.team_id', $id)
											->get()
											->toArray();

			$game['box_score_lines'] = $boxScoreLines;

			# ddAll($boxScoreLines);

			foreach ($series as &$player) {

				$playerFound = false;

				foreach ($boxScoreLines as $boxScoreLine) {

					if ($boxScoreLine['br_name'] === $player['name']) {

						$player['data'][] = floatval($boxScoreLine['mp']);

						$playerFound = true;

						break;
					}		
				}

				if (!$playerFound) {

					$player['data'][] = null;
				}
			}

			unset($player);
		}

		unset($game);

		# ddAll($series);

		return view('teams/show', compact('titleTag', 'h2Tag', 'team', 'dates', 'series'));
	}

}