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

		$games = Game::with('box_score_lines')
						->join('game_lines', function($join) {

							$join->on('game_lines.game_id', '=', 'games.id');
						})
						->take(7)
						->orderBy('date', 'desc')
						->where('team_id', $id)
						->get()
						->toArray();

		foreach ($games as &$game) {
			
			$boxScoreLines = BoxScoreLine::select('dk_name', 'mp')
											->join('players', function($join) {

												$join->on('players.id', '=', 'box_score_lines.player_id');
											})
											->where('game_id', $game['game_id'])
											->where('box_score_lines.team_id', $id)
											->get()
											->toArray();

			$game['box_score_lines'] = $boxScoreLines;
		}

		unset($game);

		ddAll($games);
	}

}