<?php namespace App\UseCases;

date_default_timezone_set('America/Chicago'); 

use App\Models\Team;

class ActiveTeamsGetter {

	public function getActiveTeams($dkPlayers) {

		$teams = Team::all();

		$dkNameActiveTeams = [];

		foreach ($dkPlayers as $dkPlayer) {

			foreach ($teams as $team) {
				
				if ($team->id === $dkPlayer->opp_team_id) {

					$dkNameActiveTeams[] = $dkPlayer->team;

					$dkPlayer->opp_team = $team->dk_name;

					break;
				}
			}
		}

		$dkNameActiveTeams = array_unique($dkNameActiveTeams);
		sort($dkNameActiveTeams);

		$activeTeams = [];

		foreach ($dkNameActiveTeams as $dkNameActiveTeam) {

			foreach ($teams as $team) {

				if ($team->dk_name === $dkNameActiveTeam) {

					foreach ($dkPlayers as $dkPlayer) {
						
						if ($dkPlayer->team === $dkNameActiveTeam) {

							$dkNameOppTeam = $dkPlayer->opp_team;

							break;
						}
					}

					$activeTeams[] = [

						'id' => $team->id,
						'dk_name' => $team->dk_name,
						'sao_name' => $team->sao_name,
						'opp_dk_name' => $dkNameOppTeam
					];

					break;
				}
			}
		}

		return $activeTeams;
	}


}