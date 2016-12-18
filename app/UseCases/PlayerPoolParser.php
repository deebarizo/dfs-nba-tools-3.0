<?php namespace App\UseCases;

use App\Models\DkPlayerPool;
use App\Models\DkPlayer;
use App\Models\Player;
use App\Models\Team;

use App\UseCases\SaoScraper;
use App\UseCases\ActiveTeamsGetter;
use App\UseCases\PStatsGetter;

class PlayerPoolParser {

	public function parseDkOwnershipPercentages($csvFile, $date, $slate) {

		$alreadyParsed = DkPlayerPool::join('dk_players', function($join) {
		  
											$join->on('dk_players.dk_player_pool_id', '=', 'dk_player_pools.id');
										})
										->where('date', $date)
										->whereNotNull('ownership_percentage')
										->count();

		if ($alreadyParsed) {

            $this->message = 'The ownership percentages for this player pool, '.$date.' '.$slate.', has already been parsed.';

            return $this;		
		}

		$sum = 0;

		if (($handle = fopen($csvFile, 'r')) !== false) {
			
			$i = 0; // index

			$this->ownershipPercentages = [];

			while (($row = fgetcsv($handle, 5000, ',')) !== false) {
				
				if ($i > 0) { 

					$dkName = $row[7];
					$ownershipPercentage = floatval(preg_replace('/%/', '', $row[8]));

				    if ($ownershipPercentage == 0) {

				    	break;
				    }

				    $sum += $ownershipPercentage;

				    $player = Player::where('dk_name', $dkName)->first();

				    if (!$player) {

			            $this->message = 'The player with DK name, '.$dkName.', does not exist in the players table.';

			            return $this;				    	
				    }

				    $dkPlayer = DkPlayer::select('dk_players.id')
				    						->join('dk_player_pools', function($join) {
		  
												$join->on('dk_player_pools.id', '=', 'dk_players.dk_player_pool_id');
											})
				    						->where('date', $date)
				    						->where('player_id', $player->id)
				    						->first();

				    if (!$dkPlayer) {

			            $this->message = 'The player with DK name, '.$dkName.', does not exist in the dk_players table.';

			            return $this;				    	
				    }

				    # ddAll($dkPlayer->id);

				    $this->ownershipPercentages[$i] = array( 

				    	'dk_name' => $dkName,
				       	'ownership_percentage' => $ownershipPercentage,
				       	'dk_player_id' => $dkPlayer->id
				    );
				}	

				$i++;	
			}
		}

		if ($sum <= 797 || $sum >= 801) {

            $this->message = 'The total ownership percentages for this player pool is '.$sum.'. It should be closer to 800.'; // 800 because 8 positions X 100%.

            return $this;			
		}

		foreach ($this->ownershipPercentages as $ownershipPercentage) {
			
			$dkPlayer = DkPlayer::where('id', $ownershipPercentage['dk_player_id'])->first();

			$dkPlayer->ownership_percentage = $ownershipPercentage['ownership_percentage'];

			$dkPlayer->save();
		}

		$this->message = 'Success!';

		return $this;
	}

	public function parseDkPlayerPool($csvFile, $date, $slate) {

        $duplicatePlayerPoolExists = DkPlayerPool::where('date', $date)
                                            		->where('slate', $slate)
                                            		->count();

        if ($duplicatePlayerPoolExists) {

            $this->message = 'The player pool, '.$date.' '.$slate.', has already been parsed.';

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

			# ddAll($this->dkPlayers);
		} 

		$playerPool = $this->saveDkPlayers($date, $slate);

		$activeTeamsGetter = new ActiveTeamsGetter;

		$activeTeams = $activeTeamsGetter->getActiveTeams($playerPool->id);

		$saoScraper = new SaoScraper;

		$activeTeams = $saoScraper->scrapeSao($playerPool->date, $activeTeams);

		$pStatsGetter = new PStatsGetter;

		$years = $pStatsGetter->getYears();		

		$dkPlayers = DkPlayer::where('dk_player_pool_id', $playerPool->id)->orderBy('team_id', 'asc')->get();

		foreach ($dkPlayers as $dkPlayer) {
			
			$latestDkPlayer = DkPlayer::join('dk_player_pools', function($join) {

											$join->on('dk_player_pools.id', '=', 'dk_players.dk_player_pool_id');
										})
										->where('dk_players.player_id', $dkPlayer->player_id)
										->where('dk_players.dk_player_pool_id', '!=', $playerPool->id)
										->orderBy('dk_player_pools.date', 'desc')
										->first();

			if ($latestDkPlayer) {

				$dkPlayer->p_mp = $latestDkPlayer->p_mp;
				$dkPlayer->p_mp_ui = $latestDkPlayer->p_mp_ui;

				$dkPlayer->p_mp = $pStatsGetter->getPMp($dkPlayer->p_mp, $dkPlayer->p_mp_ui, $years, $dkPlayer->player_id);

				$dkPlayer->p_dks_slash_mp = $latestDkPlayer->p_dks_slash_mp;
				$dkPlayer->p_dks_slash_mp_ui = $latestDkPlayer->p_dks_slash_mp_ui;

				$dkPlayer->p_dks_slash_mp = $pStatsGetter->getPDksSlashMp($dkPlayer->p_dks_slash_mp, $dkPlayer->p_dks_slash_mp_ui, $years, $dkPlayer->player_id);

				$dkPlayer->p_dk_share = $dkPlayer->p_mp * $dkPlayer->p_dks_slash_mp;

				$dkPlayer->note = $latestDkPlayer->note;

			} else {

				$dkPlayer->p_mp = 0;
				$dkPlayer->p_mp_ui = 'm';

				$dkPlayer->p_dks_slash_mp = 0;
				$dkPlayer->p_dks_slash_mp_ui = 'm';

				$dkPlayer->p_dk_share = 0;
			}

			foreach ($activeTeams as $activeTeam) {
				
				if ($dkPlayer->team_id === $activeTeam['id']) {

					$dkPlayer->p_dk_pts = ($dkPlayer->p_dk_share / 100) * $activeTeam['projected_dk_pts'];

					break;
				}
			}	

			$dkPlayer->save();
		}

		list($currentHour, $currentMinute, $timeDiffHour, $timeDiffMinute, $updatedAtDate) = $saoUpdater->getUpdateVariables($playerPool->date);

		$saoUpdater->setNewUpdatedDateAndTime($currentHour, $currentMinute);

		return $this;	
	}

	private function saveDkPlayers($date, $slate) {

		$dkPlayerPool = new DkPlayerPool;

		$dkPlayerPool->date = $date;
		$dkPlayerPool->slate = $slate;
		
		$dkPlayerPool->save();

		foreach ($this->dkPlayers as $dkPlayer) {

			$eDkPlayer = new DkPlayer;

			$eDkPlayer->dk_player_pool_id = $dkPlayerPool->id;
			$eDkPlayer->player_id = $dkPlayer['player_id'];
			$eDkPlayer->team_id = $dkPlayer['team_id'];
			$eDkPlayer->opp_team_id = $dkPlayer['opp_team_id'];
			$eDkPlayer->first_position = $dkPlayer['first_position'];
			$eDkPlayer->second_position = $dkPlayer['second_position'];
			$eDkPlayer->salary = $dkPlayer['salary'];
			$eDkPlayer->location = $dkPlayer['location'];
			$eDkPlayer->game_time = $dkPlayer['game_time'];
			$eDkPlayer->stars = 0;

			$eDkPlayer->save();
		}

		$this->message = 'Success!';

		return $dkPlayerPool;
	}

}