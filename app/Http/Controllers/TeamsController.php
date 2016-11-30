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
# ddAll($activeTeams);
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

		# ddAll($games);

		$dkPlayers = $this->getDkPlayers($id);

		# ddAll($dkPlayers);

		return view('teams/show', compact('titleTag', 'h2Tag', 'team', 'dates', 'series', 'games', 'dkPlayers', 'id'));
	}

	public function updateProjectedDkShare(Request $request) {

		$id = $request->input('id');

		$dkPlayers = $this->getDkPlayers($id);

		foreach ($dkPlayers as $dkPlayer) {

			$projectedDkShare = $request->input('dk_player_id_'.$dkPlayer->id);

			if ($projectedDkShare == '') {

				$projectedDkShare = null;
			}

			$dkPlayer->p_dk_share = $projectedDkShare;

			$dkPlayer->save();
		}

		return redirect()->route('teams.show', $id); 
	} 

	private function getDkPlayers($id) {

		$notNullLatestDkPlayers = DkPlayer::select('date')
											->join('dk_player_pools', function($join) {

												$join->on('dk_player_pools.id', '=', 'dk_players.dk_player_pool_id');
											})
											->where('team_id', $id)
											->whereNotNull('p_dk_share')
											->groupBy('date')
											->orderBy('date', 'desc')
											->take(1)
											->first();

		# ddAll($notNullLatestDkPlayers);

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
										'dk_players.p_dk_share',
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
								->get();

		if (!isset($notNullLatestDkPlayers['date'])) {

			return $dkPlayers;			
		}

		$notNullLatestDkPlayersDate = $notNullLatestDkPlayers['date'];

		$notNullDkPlayers = DkPlayer::select('dk_players.id', 
												'dk_players.p_dk_share',
												'players.dk_name',
												'dk_players.player_id')
										->join('players', function($join) {

											$join->on('players.id', '=', 'dk_players.player_id');
										})
										->join('dk_player_pools', function($join) {

											$join->on('dk_player_pools.id', '=', 'dk_players.dk_player_pool_id');
										})
										->where('date', $notNullLatestDkPlayersDate)
										->where('dk_players.team_id', $id)
										->get();

		foreach ($dkPlayers as $dkPlayer) {
			
			foreach ($notNullDkPlayers as $notNullDkPlayer) {
				
				if ($notNullDkPlayer->player_id === $dkPlayer->player_id) {

					$dkPlayer->p_dk_share = $notNullDkPlayer->p_dk_share;

					$dkPlayer->save();

					break;
				}
			}
		}

		return $dkPlayers;
	}

}