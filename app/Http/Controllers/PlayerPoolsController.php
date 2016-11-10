<?php namespace App\Http\Controllers;

use App\Models\DkPlayerPool;
use App\Models\DkPlayer;
use App\Models\Player;
use App\Models\Team;

use DB;

class PlayerPoolsController extends Controller {

	public function index() {

		$playerPools = DkPlayerPool::take(10)->orderBy('date', 'desc')->get();

		$titleTag = 'Player Pools | ';
	    $h2Tag = 'Player Pools';	

		return view('player_pools/index', compact('titleTag', 'h2Tag', 'playerPools'));
	}

	public function show($id) {

		$playerPool = DkPlayerPool::where('id', $id)->first();

		$h2Tag = 'Player Pool - '.$playerPool->date.' - '.$playerPool->slate;
	    $titleTag = $h2Tag.' | ';	

		$dkPlayers = DkPlayer::select(DB::raw('players.dk_name as name,
												teams.dk_name as team,
												opp_team_id, 
												first_position,
												second_position,
												salary'))
								->join('dk_player_pools', function($join) {

									$join->on('dk_player_pools.id', '=', 'dk_players.dk_player_pool_id');
								})
								->join('players', function($join) {

									$join->on('players.id', '=', 'dk_players.player_id');
								})
								->join('teams', function($join) {

									$join->on('teams.id', '=', 'dk_players.team_id');
								})
								->where('dk_player_pools.id', $id)
								->get()
								->toArray();

		$teams = Team::all();

		$activeTeams = [];

		foreach ($dkPlayers as &$dkPlayer) {

			if ($dkPlayer['second_position'] !== null) {

				$dkPlayer['both_positions'] = $dkPlayer['first_position'].'/'.$dkPlayer['second_position'];
			
			} else {

				$dkPlayer['both_positions'] = $dkPlayer['first_position'];
			}
			
			foreach ($teams as $team) {
				
				if ($team->id === $dkPlayer['opp_team_id']) {

					$activeTeams[] = $dkPlayer['team'];

					$dkPlayer['opp_team'] = $team->dk_name;

					break;
				}
			}
		}

		unset($dkPlayer);

		$activeTeams = array_unique($activeTeams);
		sort($activeTeams);

		# ddAll($dkPlayers);

		return view('player_pools/show', compact('titleTag', 'h2Tag', 'activeTeams', 'dkPlayers'));
	}

}