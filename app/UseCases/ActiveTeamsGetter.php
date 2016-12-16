<?php namespace App\UseCases;

date_default_timezone_set('America/Chicago'); 

use App\Models\DkPlayerPool;
use App\Models\DkPlayer;
use App\Models\Team;

use DB;

class ActiveTeamsGetter {

	public function getActiveTeams($playerPoolId) {

		$activeTeams = DkPlayer::select(DB::raw('teams.id, teams.dk_name, teams.sao_name, dk_players.opp_team_id'))
										->join('teams', function($join) {

											$join->on('teams.id', '=', 'dk_players.team_id');
										})
										->where('dk_players.dk_player_pool_id', $playerPoolId)
										->groupBy('dk_players.team_id')
										->groupBy('dk_players.opp_team_id')
										->orderBy('dk_players.team_id')
										->get()
										->toArray();

		# ddAll($activeTeams);

		$teams = Team::all();

		foreach ($activeTeams as &$activeTeam) {
			
			foreach ($teams as $team) {
				
				if ($team->id === $activeTeam['opp_team_id']) {

					$activeTeam['opp_dk_name'] = $team->dk_name;

					break;
				}
			}
		}

		unset($activeTeam);

		# ddAll($activeTeams);

		return $activeTeams;
	}

}