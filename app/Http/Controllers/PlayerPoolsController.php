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
use App\UseCases\activeTeamsGetter;

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
												p_mp,
												p_dks_slash_mp,
												p_dk_share,
												p_dk_pts'))
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
								->get();

		# ddAll($dkPlayers);

		$boxScoreLines = boxScoreLine::join('games', function($join) {

											$join->on('games.id', '=', 'box_score_lines.game_id');
										})
										->where('games.date', $playerPool->date)
										->get();

		if(count($boxScoreLines)) {

			$playerPoolIsActive = false; // this means the games have already been played

			foreach ($dkPlayers as $dkPlayer) {

				$playerFound = false;
				
				foreach ($boxScoreLines as $boxScoreLine) {
					
					if ($dkPlayer->player_id === $boxScoreLine->player_id) {

						$dkPlayer->dk_pts = $boxScoreLine->dk_pts;

						$dkPlayer->value = $dkPlayer->dk_pts / ($dkPlayer->salary / 1000);

						$playerFound = true;

						break;
					}
				}

				if (!$playerFound) {

					$dkPlayer->dk_pts = 0;
					$dkPlayer->value = 0;
				}
			}
		
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

		foreach ($dkPlayers as $dkPlayer) {

			if ($dkPlayer->second_position !== null) {

				$dkPlayer->both_positions = $dkPlayer->first_position.'/'.$dkPlayer->second_position;
			
			} else {

				$dkPlayer->both_positions = $dkPlayer->first_position;
			}
		}

		$activeTeamsGetter = new ActiveTeamsGetter;

		$activeTeams = $activeTeamsGetter->getActiveTeams($dkPlayers);

		ddAll($activeTeams);
		

		/****************************************************************************************
		SCRAPE SCORES AND ODDS
		****************************************************************************************/

		$saoUpdater = new SaoUpdater;

		list($currentHour, $currentMinute, $timeDiffHour, $timeDiffMinute, $updatedAtDate) = $saoUpdater->getUpdateVariables($playerPool->date);

		if ($saoUpdater->needsToBeUpdated($timeDiffHour, $timeDiffMinute, $updatedAtDate, $playerPool->date)) { // update every 15 minutes

			$saoScraper = new SaoScraper;

			list($activeTeams, $dkPlayers) = $saoScraper->scrapeSao($playerPool->date, $activeTeams, $dkPlayers, $currentHour, $currentMinute);

			foreach ($dkPlayers as &$dkPlayer) {
				
				foreach ($activeTeams as $activeTeam) {
					
					if ($dkPlayer['team'] === $activeTeam['dk_name']) {

						$dkPlayer['total'] = $activeTeam['real_total'];
						$dkPlayer['spread'] = $activeTeam['real_spread'];
						$dkPlayer['projected_team_dk_pts'] = $activeTeam['projected_dk_pts'];

						break;
					}
				}
			}

			Cache::forever('updated_at_hour', $currentHour);
			Cache::forever('updated_at_minute', $currentMinute);
			Cache::forever('updated_at_date', $playerPoolDate);

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