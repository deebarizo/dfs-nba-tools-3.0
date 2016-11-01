<?php namespace App\UseCases;

use App\Models\DkPlayerPool;
use App\Models\Player;
use App\Models\Team;

class PlayerPoolParser {

	public function parseDkPlayerPool($csvFile, $date, $slate) {

        $duplicatePlayerPoolExists = DkPlayerPool::where('date', $date)
                                            		->where('slate', $slate)
                                            		->count();

        if ($duplicatePlayerPoolExists) {

            $this->message = 'This player pool has already been parsed.';

            return $this;
        } 

		if (($handle = fopen($csvFile, 'r')) !== false) {
			
			$i = 0; // index

			$this->dkPlayers = [];

			while (($row = fgetcsv($handle, 5000, ',')) !== false) {
				
				if ($i > 0) { 
				
				    $this->dkPlayers[$i] = array( 

				    	'positions' => $row[0],
				       	'dk_name' => $row[1],
				       	'salary' => $row[2],
				       	'game_info' => $row[3],
				       	'dk_team' => $row[5]
				    );

				    $dkNameMatchesBrName = Player::where('br_name', $this->dkPlayers[$i]['dk_name'])->first();
				    $dkNameMatchesDkName = Player::where('dk_name', $this->dkPlayers[$i]['dk_name'])->first();

					if (!$dkNameMatchesBrName && !$dkNameMatchesDkName) {

						$this->message = 'The DK name, '.$this->dkPlayers[$i]['dk_name'].', does not match any BR name in the database.';

						return $this;
				    }

				    if (!$dkNameMatchesDkName) {

				    	$dkPlayer = $dkNameMatchesBrName;

				    	$dkPlayer->dk_name = $this->dkPlayers[$i]['dk_name'];

				    	$dkPlayer->save();
				    }

				    $this->dkPlayers[$i]['player_id'] = Player::where('dk_name', $this->dkPlayers[$i]['dk_name'])->pluck('id')[0];

				    $matchup = preg_replace("/(\w+@\w+)(\s)(.*)/", "$1", $this->dkPlayers[$i]['game_info']);
				    $matchupWithoutAtSymbol = preg_replace("/@/", "", $matchup);
				    $this->dkPlayers[$i]['opp_dk_team'] = preg_replace("/".$this->dkPlayers[$i]['dk_team']."/", "", $matchupWithoutAtSymbol);

				    preg_match("/@".$this->dkPlayers[$i]['dk_team']."/", $matchup, $playerIsHome); // home or away

				    if ($playerIsHome) {

				    	$this->dkPlayers[$i]['location'] = 'home';
				    
				    } else {

				    	$this->dkPlayers[$i]['location'] = 'away';
				    }

				    $teams = [

				    	[	
				    		'key' => 'dk_team',
				    		'phrase' => ' '
			    		],

			    		[
			    			'key' => 'opp_dk_team',
			    			'phrase' => ' opposing '
			    		]
			    	];

				    foreach ($teams as $team) {

					    $teamExists = Team::where('dk_name', $this->dkPlayers[$i][$team['key']])->first();

					    if (!$teamExists) {

							$this->message = 'The DraftKings'.$team['phrase'].'team name, <strong>'.$this->dkPlayers[$i][$team['key']].'</strong>, does not exist in the database.'; 

							return $this;
					    }	

					    $eTeam = $teamExists;

					    if ($team['key'] === 'dk_team') {

					    	$this->dkPlayers[$i]['team_id'] = $eTeam->id;
					    }

					    if ($team['key'] === 'opp_dk_team') {

					    	$this->dkPlayers[$i]['opp_team_id'] = $eTeam->id;
					    }					    
				    } 

				    if (strpos($this->dkPlayers[$i]['positions'], '/') !== false) {

				    	$this->dkPlayers[$i]['first_position'] = preg_replace("/(\D+)(\/\D+)/", "$1", $this->dkPlayers[$i]['positions']);
				    	$this->dkPlayers[$i]['second_position'] = preg_replace("/(\D+\/)(\D+)/", "$2", $this->dkPlayers[$i]['positions']);

				    } else {

				    	$this->dkPlayers[$i]['first_position'] = $this->dkPlayers[$i]['positions'];
				    	$this->dkPlayers[$i]['second_position'] = null;
				    }

				    $this->dkPlayers[$i]['game_time_eastern'] = preg_replace("/(\S+@\S+\s+)(.+)(\sET)/", "$2", $this->dkPlayers[$i]['game_info']);

				    $this->dkPlayers[$i]['game_time'] = date('g:i A', strtotime($this->dkPlayers[$i]['game_time_eastern']) - 3600);
				}

				$i++;
			}

			ddAll($this->dkPlayers);
		} 

		$this->saveDkPlayers($date, $slate);

		return $this;	
	}

	private function saveDkPlayers($date, $site, $timePeriod) {

		$playerPool = new PlayerPool;

		$playerPool->date = $date;
		$playerPool->time_period = $timePeriod;
		$playerPool->site = $site;
		$playerPool->buy_in = 0;

		$playerPool->save();

		foreach ($this->dkPlayers as $dkPlayer) {

			$teamId = Team::where('name_dk', $dkPlayer['teamNameDk'])->pluck('id')[0];

			$playerExists = Player::where('name_dk', $dkPlayer['nameDk'])->where('team_id', $teamId)->count();

			if (!$playerExists) {

				$player = new Player;

				$player->team_id = $teamId;
				$player->name_dk = $dkPlayer['nameDk'];

				$player->save();
			}

			$eDkPlayer = new DkPlayer;

			$eDkPlayer->player_pool_id = $playerPool->id;
			$eDkPlayer->player_id = Player::where('name_dk', $dkPlayer['nameDk'])->pluck('id')[0];
			$eDkPlayer->dk_id = $dkPlayer['dkId'];
			$eDkPlayer->team_id = $teamId;
			$eDkPlayer->opp_team_id = Team::where('name_dk', $dkPlayer['oppTeamNameDk'])->pluck('id')[0];
			$eDkPlayer->position = $dkPlayer['position'];
			$eDkPlayer->salary = $dkPlayer['salary'];

			$eDkPlayer->save();
		}

		$this->message = 'Success!';
	}

}