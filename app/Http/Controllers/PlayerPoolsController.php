<?php namespace App\Http\Controllers;

date_default_timezone_set('America/Chicago'); 

use App\Models\DkPlayerPool;
use App\Models\DkPlayer;
use App\Models\Player;
use App\Models\Team;
use App\Models\BoxScoreLine;

use DB;

use DateTime;

use Goutte\Client;
use vendor\symfony\DomCrawler\Crawler;

use Illuminate\Support\Facades\Cache;

class PlayerPoolsController extends Controller {

	public function index() {

		$playerPools = DkPlayerPool::take(10)->orderBy('date', 'desc')->get();

		$titleTag = 'Player Pools | ';
	    $h2Tag = 'Player Pools';	

		return view('player_pools/index', compact('titleTag', 'h2Tag', 'playerPools'));
	}

	public function show($id) {

		$currentDate = new DateTime();
		$currentHour = intval($currentDate->format('H'));
		$currentMinute = intval($currentDate->format('i'));

		$updatedAtHour = Cache::get('updated_at_hour', 0);
		$updatedAtMinute = Cache::get('updated_at_minute', 0);

		$timeDiffHour = $currentHour - $updatedAtHour;
		$timeDiffMinute = $currentMinute - $updatedAtMinute;

		$playerPool = DkPlayerPool::where('id', $id)->first();

		$h2Tag = 'Player Pool - '.$playerPool->date.' - '.$playerPool->slate;
	    $titleTag = $h2Tag.' | ';	

	    $date = $playerPool->date;

		$dkPlayers = DkPlayer::select(DB::raw('players.dk_name as name,
												teams.dk_name as team,
												dk_players.team_id, 
												dk_players.opp_team_id, 
												first_position,
												second_position,
												salary,
												game_time,
												players.id as player_id,
												ownership_percentage'))
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
										->where('games.date', $date)
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
		SCRAPE SCORES AND ODDS
		****************************************************************************************/

		if ($timeDiffHour > 0 || $timeDiffMinute > 14) { // update every 15 minutes

			$client = new Client();

			$year = date('Y', strtotime($date));

			$monthNumber = date('m', strtotime($date));

			$dayNumber = date('d', strtotime($date));

			$crawler = $client->request('GET', 'http://www.scoresandodds.com/grid_'.$year.''.$monthNumber.''.$dayNumber.'.html');	

			$numTableRows = $crawler->filter('tr.team')->count();

			foreach ($activeTeams as &$activeTeam) {
				
				for ($i = 0; $i < $numTableRows; $i++) { 

					$tableRow = $crawler->filter('tr.team')->eq($i);

					$unformattedTeam = trim($tableRow->filter('td')->eq(0)->text());

					$saoName = preg_replace("/(\d+\s)(.+)/", "$2", $unformattedTeam);

					if ($saoName === $activeTeam['sao_name']) {

						$unformattedVegasScore = trim($tableRow->filter('td')->eq(3)->text());

						if ($unformattedVegasScore === '') {

							$activeTeam['total'] = null;
							$activeTeam['spread'] = null;

							continue;
						}

						if (strpos($unformattedVegasScore, '-') === false && $unformattedVegasScore !== 'PK') {

							$activeTeam['total'] = $unformattedVegasScore;

						} else if (strpos($unformattedVegasScore, '-') !== false) {

							$activeTeam['spread'] = abs(preg_replace("/(-\S+)(\s.+)/", "$1", $unformattedVegasScore));

						} else if ($unformattedVegasScore === 'PK') {
							
							$activeTeam['spread'] = 0;
						}
					}
				}				
			}

			unset($activeTeam);

			$mirrorActiveTeams = $activeTeams;

			foreach ($activeTeams as &$activeTeam) {
				
				foreach ($mirrorActiveTeams as $mirrorActiveTeam) {
					
					if ($activeTeam['opp_dk_name'] === $mirrorActiveTeam['dk_name']) {

						// http://php.net/manual/en/function.isset.php 
						// "isset() will return FALSE if testing a variable that has been set to NULL."

						if (isset($activeTeam['total'])) { 

							$activeTeam['vegas_pts'] = ($activeTeam['total'] - $mirrorActiveTeam['spread']) / 2;
							$activeTeam['real_total'] = $activeTeam['total'];
							$activeTeam['real_spread'] = $mirrorActiveTeam['spread'];
						}

						if (isset($activeTeam['spread'])) {

							$activeTeam['vegas_pts'] = ($mirrorActiveTeam['total'] + $activeTeam['spread']) / 2;
							$activeTeam['real_total'] = $mirrorActiveTeam['total'];
							$activeTeam['real_spread'] = $activeTeam['spread'] * -1;
						}

						if (!isset($activeTeam['vegas_pts'])) {

							$activeTeam['vegas_pts'] = 100;
							$activeTeam['real_total'] = null;
							$activeTeam['real_spread'] = null;						
						}

						$activeTeam['projected_dk_pts'] = $activeTeam['vegas_pts'] * 2.097070; // based on "SELECT sum(dk_pts) / sum(vegas_pts) FROM dfsninja.game_lines;"

						Cache::forever($activeTeam['dk_name'], $activeTeam['dk_name']);
						Cache::forever($activeTeam['dk_name'].'_total', $activeTeam['real_total']);
						Cache::forever($activeTeam['dk_name'].'_spread', $activeTeam['real_spread']);
						Cache::forever($activeTeam['dk_name'].'_projected_dk_pts', $activeTeam['projected_dk_pts']);

						break;
					}
				}
			}

			unset($activeTeam);

			# ddAll($activeTeams);

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

			unset($dkPlayer);

			Cache::forever('updated_at_hour', $currentHour);
			Cache::forever('updated_at_minute', $currentMinute);

			# dd($dkPlayers);

		} else {

			foreach ($dkPlayers as &$dkPlayer) {
				
				$dkPlayer['total'] = Cache::get($dkPlayer['team'].'_total');
				$dkPlayer['spread'] = Cache::get($dkPlayer['team'].'_spread');
				$dkPlayer['projected_team_dk_pts'] = Cache::get($dkPlayer['team'].'_projected_dk_pts');
			}

			unset($dkPlayer);
		}

		ddAll($dkPlayers);

		return view('player_pools/show', compact('titleTag', 'h2Tag', 'activeTeams', 'dkPlayers', 'playerPoolIsActive'));
	}

}