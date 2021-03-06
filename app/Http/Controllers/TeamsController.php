<?php namespace App\Http\Controllers;

use App\Models\DkPlayerPool;
use App\Models\DkPlayer;
use App\Models\Player;
use App\Models\Team;

use App\Models\Game;
use App\Models\GameLine;
use App\Models\BoxScoreLine;

use DB;

use Goutte\Client;
use vendor\symfony\DomCrawler\Crawler;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Input;

use Illuminate\Support\Facades\Cache;

use App\UseCases\SaoUpdater;
use App\UseCases\SaoScraper;
use App\UseCases\ActiveTeamsGetter;

class TeamsController extends Controller {

	public function index() {

		$teams = Team::all();

		$h2Tag = 'Teams';
		$titleTag = $h2Tag.' | ';

		$activeTeams = DkPlayer::select(DB::raw('dk_players.team_id as id, dk_players.game_time'))
									->join('dk_player_pools', function($join) {

										$join->on('dk_player_pools.id', '=', 'dk_players.dk_player_pool_id');
									})
									->where('date', '=', getTodayDate())
									->whereNotNull('p_dk_share')
									->groupBy('dk_players.team_id')
									->groupBy('dk_players.game_time')
									->get();

		foreach ($teams as $team) {

			$team->active = false;

			foreach ($activeTeams as $activeTeam) {

				if ($activeTeam->id === $team->id) {

					$team->active = '<img src="/files/images/basketball-icon.png"> '.$activeTeam->game_time;

					break;
				}
			}
		}

		return view('teams/index', compact('titleTag', 'h2Tag', 'teams'));
	}

	public function show($id) {

		$team = Team::where('id', $id)->first();

		$h2Tag = 'Teams - '.$team->dk_name;
		$titleTag = $h2Tag.' | ';


		/****************************************************************************************
		ROTATION
		****************************************************************************************/

		$dates = Game::join('game_lines', function($join) {

							$join->on('game_lines.game_id', '=', 'games.id');
						})
						->take(7)
						->orderBy('date', 'desc')
						->where('team_id', $id)
						->where('date', '>', '2016-09-30')
						->pluck('date')
						->toArray();

		$dates = array_reverse($dates);

		$players = Game::join('box_score_lines', function($join) {

								$join->on('box_score_lines.game_id', '=', 'games.id');
							})
							->join('players', function($join) {

								$join->on('players.id', '=', 'box_score_lines.player_id');
							})
							->orderBy('date', 'desc')
							->orderBy('box_score_lines.id', 'asc')
							->where('box_score_lines.team_id', $id)
							->where('date', '>=', $dates[0])
							->pluck('players.br_name')
							->toArray();

		$players = array_values(array_unique($players));

		$series = []; // for series property in line chart

		foreach ($players as $player) {

			$series[] = [

				'name' => $player,
				'data' => [] // minutes
			];
		}

		# ddAll($series);

		$eGames = Game::select('games.id', 'games.date')
						->join('game_lines', function($join) {

							$join->on('game_lines.game_id', '=', 'games.id');
						})
						->take(7)
						->orderBy('date', 'asc')
						->where('team_id', $id)
						->where('date', '>=', $dates[0])
						->get()
						->toArray();

		$games = [];

		foreach ($eGames as $game) {
			
			$games[$game['date']] = $game;
		}

		# ddAll($games);

		foreach ($games as $key => &$game) {
			
			$boxScoreLines = BoxScoreLine::select('players.br_name', 'mp')
											->join('players', function($join) {

												$join->on('players.id', '=', 'box_score_lines.player_id');
											})
											->where('game_id', $game['id'])
											->where('box_score_lines.team_id', $id)
											->get()
											->toArray();

			# ddAll($boxScoreLines);

			$baseUrl = url('/');

			foreach ($series as &$player) {

				$playerFound = false;

				foreach ($boxScoreLines as $index => $boxScoreLine) {

					if ($boxScoreLine['br_name'] === $player['name']) {

						if ($index > 4) { // bench player

							$player['data'][] = floatval($boxScoreLine['mp']);
						}

						if ($index <= 4) { // bench player

							$player['data'][] = [

								'y' => floatval($boxScoreLine['mp']),
								'marker' => [

									'symbol' => 'url('.$baseUrl.'/files/images/basketball-icon.png)'
								]
							];
						}

						$playerFound = true;

						break;	
					}		
				}

				if (!$playerFound) {

					$player['data'][] = null;
				}
			}

			unset($player);

			$game['game_lines'] = GameLine::join('games', function($join) {

												$join->on('games.id', '=', 'game_lines.game_id');
											})
											->join('teams', function($join) {

												$join->on('teams.id', '=', 'game_lines.team_id');
											})
											->where('game_id', $game['id'])
											->get()
											->toArray();
		}

		unset($game);

		foreach ($games as $date => &$game) {

			$year = date('Y', strtotime($date));

			$monthNumber = date('m', strtotime($date));

			$dayNumber = date('d', strtotime($date));
			
			$game['pm_link'] = 'http://popcornmachine.net/gf?date='.$year.''.$monthNumber.''.$dayNumber.'&game='.$game['game_lines'][0]['pm_name'].''.$game['game_lines'][1]['pm_name'];
		}

		unset($game);

		$gamesForRotation = $games;


		/****************************************************************************************
		DK PLAYERS
		****************************************************************************************/

		$games = Game::select(DB::raw('games.id,
										teams.dk_name as team_dk_name,
										teams.pm_name as team_pm_name, 
										games.date,
										games.br_link,
										game_lines.pts,
										game_lines.location,
										games.ot_periods'))
						->with('game_lines')
						->join('game_lines', function($join) {

							$join->on('game_lines.game_id', '=', 'games.id');
						})
						->join('teams', function($join) {

							$join->on('teams.id', '=', 'game_lines.team_id');
						})
						->where('game_lines.team_id', $id)
						->where('games.date', '>=', '2016-09-01')
						->orderBy('games.date', 'desc')
						->get();

		# ddAll($games);

		foreach ($games as $game) {
			
			$oppTeamGameLine = GameLine::select(DB::raw('teams.dk_name as team_dk_name,
															teams.pm_name as team_pm_name, 
															game_lines.pts'))
										->join('teams', function($join) {

											$join->on('teams.id', '=', 'game_lines.team_id');
										})
										->where('game_id', $game->id)
										->where('team_id', '!=', $id)
										->first();

			$game->opp_team_dk_name = $oppTeamGameLine->team_dk_name;
			$game->opp_team_pts = $oppTeamGameLine->pts;

			if ($game->location === 'home') {

				$game->home_pm_team = $game->team_pm_name;
				$game->away_pm_team = $oppTeamGameLine->team_pm_name;
			}

			if ($game->location === 'away') {

				$game->home_pm_team = $oppTeamGameLine->team_pm_name;
				$game->away_pm_team = $game->team_pm_name;
			}
		}

		# ddAll($games);


		/****************************************************************************************
		DK PLAYERS
		****************************************************************************************/

		$dkPlayers = $this->getDkPlayers($id);

		# ddAll($dkPlayers);

		$saoUpdater = new SaoUpdater;

		$playerPool = $saoUpdater->getLatestDkPlayerPool();

		list($currentHour, $currentMinute, $timeDiffHour, $timeDiffMinute, $updatedAtDate) = $saoUpdater->getUpdateVariables($playerPool->date);

		if ($saoUpdater->needsToBeUpdated($timeDiffHour, $timeDiffMinute, $updatedAtDate, $playerPool->date)) { // update every 15 minutes

			$activeTeamsGetter = new ActiveTeamsGetter;

			$activeTeams = $activeTeamsGetter->getActiveTeams($playerPool->id);

			$saoScraper = new SaoScraper;

			$activeTeams = $saoScraper->scrapeSao($playerPool->date, $activeTeams);

			foreach ($dkPlayers as $dkPlayer) {
				
				foreach ($activeTeams as $activeTeam) {
					
					if ($activeTeam['id'] === $dkPlayer->team_id) {

						$dkPlayer->p_dk_share = ($dkPlayer->p_dk_share === null ? 0 : $dkPlayer->p_dk_share);

						$dkPlayer->p_dk_pts = numFormat(($dkPlayer->p_dk_share / 100) * $activeTeam['projected_dk_pts'], 2);

						$dkPlayer->save();

						break;
					}
				} 				
			}

			$saoUpdater->setNewUpdatedDateAndTime($currentHour, $currentMinute);
		}

		$metadata = [

			'num_of_players_in_rotation' => 0,
			'total_p_mp' => 0
		];

		foreach ($dkPlayers as $dkPlayer) {

			$metadata['total_p_mp'] += $dkPlayer->p_mp;
			
			if ($dkPlayer->p_mp > 0) {

				$metadata['num_of_players_in_rotation']++;
			}
		}

		$metadata['total_p_mp_percentage'] = numFormat($metadata['total_p_mp'] / 240 * 100, 2);
		$metadata['total_p_mp_left'] = numFormat(240 - $metadata['total_p_mp'], 2);

		$lastUpdate = $saoUpdater->getLastUpdate();

		return view('teams/show', compact('titleTag', 'h2Tag', 'team', 'dates', 'series', 'gamesForRotation', 'games', 'dkPlayers', 'id', 'metadata', 'lastUpdate'));
	}

	public function updateProjectedStats(Request $request) {

		$id = $request->input('id');

		$teamDkName = Team::where('id', $id)->pluck('dk_name')[0];

		$dkPlayers = $this->getDkPlayers($id);

		$numOfPlayersInRotation = 0;

		foreach ($dkPlayers as $dkPlayer) {

			$pMp = (trim($request->input('dk_player_id_'.$dkPlayer->id.'_p_mp')) == '' ? null : trim($request->input('dk_player_id_'.$dkPlayer->id.'_p_mp')));

			if ($pMp == '') {

				$pMp = null;
			}

			if ($pMp !== $dkPlayer->p_mp) {

				$pMpUi = 'm';

			} else {

				continue;
			}

			$dkPlayer->p_mp = $pMp;
			$dkPlayer->p_mp_ui = 'm';

			$dkPlayer->p_dk_share = $dkPlayer->p_mp * $dkPlayer->p_dks_slash_mp;

			$teamProjectedDkPts = Cache::get($teamDkName.'_projected_dk_pts');

			$dkPlayer->p_dk_pts = $teamProjectedDkPts * ($dkPlayer->p_dk_share / 100);

			$dkPlayer->save();
		}

		return redirect()->route('teams.show', $id); 
	} 

	private function getDkPlayers($id) {

		$latestDkPlayersDate = DkPlayer::select('date')
											->join('dk_player_pools', function($join) {

												$join->on('dk_player_pools.id', '=', 'dk_players.dk_player_pool_id');
											})
											->where('team_id', $id)
											->groupBy('date')
											->orderBy('date', 'desc')
											->take(1)
											->pluck('date')[0];

		$dkPlayers = DkPlayer::select('dk_players.id', 
										'dk_players.p_mp',
										'dk_players.p_mp_ui',
										'dk_players.p_dks_slash_mp',
										'dk_players.p_dk_share',
										'dk_players.p_dk_pts',
										'dk_players.salary',
										'dk_players.note',
										'players.dk_name',
										'dk_players.player_id')
								->join('players', function($join) {

									$join->on('players.id', '=', 'dk_players.player_id');
								})
								->join('dk_player_pools', function($join) {

									$join->on('dk_player_pools.id', '=', 'dk_players.dk_player_pool_id');
								})
								->where('date', $latestDkPlayersDate)
								->where('dk_players.team_id', $id)
								->orderBy('dk_players.p_mp', 'desc')
								->orderBy('dk_players.salary', 'desc')
								->get();

		return $dkPlayers;
	}

}