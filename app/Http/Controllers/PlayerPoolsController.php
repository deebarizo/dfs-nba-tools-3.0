<?php namespace App\Http\Controllers;

use App\Models\DkPlayerPool;
use App\Models\DkPlayer;
use App\Models\Player;
use App\Models\Team;
use App\Models\BoxScoreLine;

use DB;

use Goutte\Client;
use vendor\symfony\DomCrawler\Crawler;

use Illuminate\Support\Facades\Cache;

use App\UseCases\SaoUpdater;
use App\UseCases\SaoScraper;

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

	    $dkPlayers = DkPlayer::select(DB::raw('dk_players.id as dk_player_id,
	    										players.dk_name as name,
												teams.dk_name as team,
												dk_players.team_id, 
												dk_players.opp_team_id, 
												first_position,
												second_position,
												salary,
												game_time,
												players.id as player_id,
												ownership_percentage,
												your_ownership_percentage, 
												p_dk_share'))
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

		$boxScoreLines = boxScoreLine::join('games', function($join) {

											$join->on('games.id', '=', 'box_score_lines.game_id');
										})
										->where('games.date', $playerPool->date)
										->get();

		if(count($boxScoreLines)) {

			$playerPoolIsActive = false; // this means the games have already been played

			foreach ($dkPlayers as &$dkPlayer) {

				$playerFound = false;
				
				foreach ($boxScoreLines as $boxScoreLine) {
					
					if ($dkPlayer['player_id'] === $boxScoreLine->player_id) {

						$dkPlayer['dk_pts'] = $boxScoreLine->dk_pts;

						$dkPlayer['value'] = $dkPlayer['dk_pts'] / ($dkPlayer['salary'] / 1000);

						$playerFound = true;

						break;
					}
				}

				if (!$playerFound) {

					$dkPlayer['dk_pts'] = 0;
					$dkPlayer['value'] = 0;
				}
			}

			unset($dkPlayer);
		
		} else {

			$playerPoolIsActive = true; 

			if ($playerPool->date !== getTodayDate()) {

				$currentDateTime = new DateTime();
				$currentHour = intval($currentDateTime->format('H'));

		   		if ($currentHour >= 9) {

			    	\Session::flash('message', 'Please scrape the finished games of this player pool first.');

			    	$activeTeams = [];
			    	$dkPlayers = [];

			    	$fontSize = '100%';

			    	return view('player_pools/show', compact('titleTag', 'h2Tag', 'activeTeams', 'dkPlayers', 'playerPoolIsActive', 'fontSize'));
			    } 
			}
		}

		# ddAll($dkPlayers);

		$teams = Team::all();

		$dkNameActiveTeams = [];

		foreach ($dkPlayers as &$dkPlayer) {

			if ($dkPlayer['second_position'] !== null) {

				$dkPlayer['both_positions'] = $dkPlayer['first_position'].'/'.$dkPlayer['second_position'];
			
			} else {

				$dkPlayer['both_positions'] = $dkPlayer['first_position'];
			}
			
			foreach ($teams as $team) {
				
				if ($team->id === $dkPlayer['opp_team_id']) {

					$dkNameActiveTeams[] = $dkPlayer['team'];

					$dkPlayer['opp_team'] = $team->dk_name;

					break;
				}
			}
		}

		unset($dkPlayer);

		$dkNameActiveTeams = array_unique($dkNameActiveTeams);
		sort($dkNameActiveTeams);

		$activeTeams = [];

		foreach ($dkNameActiveTeams as $dkNameActiveTeam) {

			foreach ($teams as $team) {

				if ($team->dk_name === $dkNameActiveTeam) {

					foreach ($dkPlayers as $dkPlayer) {
						
						if ($dkPlayer['team'] === $dkNameActiveTeam) {

							$dkNameOppTeam = $dkPlayer['opp_team'];

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

		# ddAll($activeTeams);


		/****************************************************************************************
		UPDATE PROJECTED DK SHARE
		****************************************************************************************/

		/*if ($updatedAtDate !== $playerPool->date) {

			foreach ($dkPlayers as &$dkPlayer) {

				if ($dkPlayer['p_dk_share'] === null) {

					$latestDkPlayer = DkPlayer::select('*')
													->join('dk_player_pools', function($join) {

														$join->on('dk_player_pools.id', '=', 'dk_players.dk_player_pool_id');
													})
													->where('dk_players.player_id', $dkPlayer['player_id'])
													->whereNotNull('p_dk_share')
													->orderBy('date', 'desc')
													->first();

					if ($latestDkPlayer !== null) {

						$dkPlayer['p_dk_share'] = $latestDkPlayer->p_dk_share;

						DkPlayer::where('dk_players.id', $dkPlayer['dk_player_id'])
									->update(['p_dk_share' => $latestDkPlayer->p_dk_share]);
					}
				}
			}

			unset($dkPlayer);
		} */


		/****************************************************************************************
		SCRAPE SCORES AND ODDS
		****************************************************************************************/

		$saoUpdater = new SaoUpdater;

		list($currentHour, $currentMinute, $timeDiffHour, $timeDiffMinute, $updatedAtDate) = $saoUpdater->getUpdateVariables($playerPool->date);

		if ($saoUpdater->needsToBeUpdated($timeDiffHour, $timeDiffMinute, $updatedAtDate, $playerPool->date)) { // update every 15 minutes

			$saoScraper = new SaoScraper;

			list($activeTeams, $dkPlayers) = $saoScraper->scrapeSao($playerPool->date, $activeTeams, $dkPlayers, $currentHour, $currentMinute);

			# dd($dkPlayers);

		} else {

			foreach ($dkPlayers as &$dkPlayer) {
				
				$dkPlayer['total'] = Cache::get($dkPlayer['team'].'_total');
				$dkPlayer['spread'] = Cache::get($dkPlayer['team'].'_spread');
				$dkPlayer['projected_team_dk_pts'] = Cache::get($dkPlayer['team'].'_projected_dk_pts');
			}

			unset($dkPlayer);
		}

		foreach ($dkPlayers as &$dkPlayer) {

			if ($dkPlayer['p_dk_share'] === null) {

				$dkPlayer['p_dk_share'] = 0;
			}
			
			$dkPlayer['p_dk_pts'] = $dkPlayer['p_dk_share'] / 100 * $dkPlayer['projected_team_dk_pts'];

			$dkPlayer['p_value'] = $dkPlayer['p_dk_pts'] / ($dkPlayer['salary'] / 1000);
		}

		unset($dkPlayer);

		# ddAll($dkPlayers);

		if ($playerPoolIsActive) {

			$fontSize = '100%';
		
		} else {

			$fontSize = '85%';
		}

		$numGames = count($activeTeams) / 2;

		$h2Tag .= ' ('.$numGames.' Games)';

		$lastUpdate = $saoUpdater->getLastUpdate();

		return view('player_pools/show', compact('titleTag', 'h2Tag', 'activeTeams', 'dkPlayers', 'playerPoolIsActive', 'fontSize', 'lastUpdate'));
	}

}