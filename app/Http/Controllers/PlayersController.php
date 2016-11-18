<?php namespace App\Http\Controllers;

use App\Models\DkPlayerPool;
use App\Models\DkPlayer;
use App\Models\Player;
use App\Models\Team;

use App\Models\Game;
use App\Models\GameLine;
use App\Models\BoxScoreLine;

use DB;

class PlayersController extends Controller {

	public function index() {

		$h2Tag = 'Players';
		$titleTag = $h2Tag.' | ';

		$players = Player::select(DB::raw('players.id,
											players.br_name,
											players.dk_name,
											players.br_link,
											teams.dk_name as team'))
							->join('teams', function($join) {

								$join->on('teams.id', '=', 'players.team_id');
							})
							->orderBy('players.br_name')
							->get();

		# ddAll($players);

		return view('players/index', compact('titleTag', 'h2Tag', 'players'));
	}

	public function show($id) {

		
	}

}