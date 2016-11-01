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

					    $teamExists = Team::where('dk_name', $this->dkPlayers[$i][$team['key']])->count();

					    if (!$teamExists) {

							$this->message = 'The DraftKings'.$team['phrase'].'team name, <strong>'.$this->dkPlayers[$i][$team['key']].'</strong>, does not exist in the database.'; 

							return $this;
					    }	
				    } 
				}

				$i++;
			}

			ddAll($this->dkPlayers);
		} 

		$this->saveDkPlayers($date, $slate);

		return $this;	
	}

}