<?php namespace App\Http\Controllers;

use App\Models\DkPlayerPool;
use App\Models\DkPlayer;
use App\Models\Player;
use App\Models\Team;

use DB;

use Goutte\Client;
use vendor\symfony\DomCrawler\Crawler;

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

	    $date = $playerPool->date;

		$dkPlayers = DkPlayer::select(DB::raw('players.dk_name as name,
												teams.dk_name as team,
												opp_team_id, 
												first_position,
												second_position,
												salary,
												game_time'))
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

					$activeTeam['projected_dk_pts'] = $activeTeam['vegas_pts'] * 2.097070; // based on "SELECT sum(dk_pts) / sum(vegas_pts) FROM dfsninja.game_lines;"

					break;
				}
			}
		}

		unset($activeTeam);

		# ddAll($activeTeams);

		foreach ($dkPlayers as &$dkPlayer) {
			
			
		}

		unset($dkPlayer)

		return view('player_pools/show', compact('titleTag', 'h2Tag', 'activeTeams', 'dkPlayers'));
	}

}