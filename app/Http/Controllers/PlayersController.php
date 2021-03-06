<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Input;

use App\Models\DkPlayerPool;
use App\Models\DkPlayer;
use App\Models\Player;
use App\Models\Team;

use App\Models\Game;
use App\Models\GameLine;
use App\Models\BoxScoreLine;

use DB;

use Illuminate\Support\Facades\Cache;

use App\UseCases\SaoUpdater;
use App\UseCases\SaoScraper;
use App\UseCases\ActiveTeamsGetter;
use App\UseCases\PStatsGetter;

class PlayersController extends Controller {

	public $years; 

	public function index() {

		$h2Tag = 'Players';
		$titleTag = $h2Tag.' | ';

		$players = Player::select(DB::raw('players.id,
											players.br_name,
											players.dk_name,
											players.dk_short_name,
											players.br_link,
											teams.dk_name as team'))
							->join('teams', function($join) {

								$join->on('teams.id', '=', 'players.team_id');
							})
							->orderBy('players.br_name')
							->get();

		# ddAll($players);

		return view('players/index', compact('titleTag', 'h2Tag', 'players'));
	}

	public function show($id) {

		$player = Player::where('id', $id)->first();

		$h2Tag = $player->br_name;
		$titleTag = $h2Tag.' | ';

		$pStatsGetter = new PStatsGetter;

		$this->years = $pStatsGetter->getYears();

		$years = $this->years;

		$overviews = [];

		$overviews['Both']['avg_mp'] = BoxScoreLine::join('games', function($join) {

															$join->on('games.id', '=', 'box_score_lines.game_id');
														})
														->where('date', '>', $years[0].'-09-01')
														->where('date', '<', $years[2].'-09-01')
														->where('player_id', $id)
														->avg('mp');

		if ($overviews['Both']['avg_mp'] === null) {

			$overviews['Both']['avg_mp'] = 0;
			$overviews['Both']['avg_dk_share_slash_avg_mp'] = 0;

		} else {

			$overviews['Both']['total_dk_share'] = BoxScoreLine::join('games', function($join) {

																	$join->on('games.id', '=', 'box_score_lines.game_id');
																})
																->where('date', '>', $years[0].'-09-01')
																->where('date', '<', $years[2].'-09-01')
																->where('player_id', $id)
																->sum('dk_share');

			$overviews['Both']['total_mp'] = BoxScoreLine::join('games', function($join) {

																	$join->on('games.id', '=', 'box_score_lines.game_id');
																})
																->where('date', '>', $years[0].'-09-01')
																->where('date', '<', $years[2].'-09-01')
																->where('player_id', $id)
																->sum('mp');

			$overviews['Both']['avg_dk_share_slash_avg_mp'] = $overviews['Both']['total_dk_share'] / $overviews['Both']['total_mp'];
		}

		$overviews['Both']['avg_dk_share'] = $overviews['Both']['avg_mp'] * $overviews['Both']['avg_dk_share_slash_avg_mp'];

		for ($i = 0; $i < 2; $i++) { 
			
			$season = $years[$i].'-'.$years[$i+1];

			$overviews[$season]['avg_mp'] = BoxScoreLine::join('games', function($join) {

																$join->on('games.id', '=', 'box_score_lines.game_id');
															})
															->where('date', '>', $years[$i].'-09-01')
															->where('date', '<', $years[$i+1].'-09-01')
															->where('player_id', $id)
															->avg('mp');

			if ($overviews[$season]['avg_mp'] === null) {

				$overviews[$season]['avg_mp'] = 0;
				$overviews[$season]['avg_dk_share_slash_avg_mp'] = 0;

			} else {

				$overviews[$season]['total_dk_share'] = BoxScoreLine::join('games', function($join) {

																		$join->on('games.id', '=', 'box_score_lines.game_id');
																	})
																	->where('date', '>', $years[$i].'-09-01')
																	->where('date', '<', $years[$i+1].'-09-01')
																	->where('player_id', $id)
																	->sum('dk_share');

				$overviews[$season]['total_mp'] = BoxScoreLine::join('games', function($join) {

																	$join->on('games.id', '=', 'box_score_lines.game_id');
																})
																->where('date', '>', $years[$i].'-09-01')
																->where('date', '<', $years[$i+1].'-09-01')
																->where('player_id', $id)
																->sum('mp');

				$overviews[$season]['avg_dk_share_slash_avg_mp'] = $overviews[$season]['total_dk_share'] / $overviews[$season]['total_mp'];
			}

			$overviews[$season]['avg_dk_share'] = $overviews[$season]['avg_mp'] * $overviews[$season]['avg_dk_share_slash_avg_mp'];

			$seasons[$season] = BoxScoreLine::select('*')
													->join('games', function($join) {

														$join->on('games.id', '=', 'box_score_lines.game_id');
													})
													->join('teams', function($join) {

														$join->on('teams.id', '=', 'box_score_lines.team_id');
													})
													->with('game.game_lines.team')
													->where('games.date', '>', $years[$i].'-09-01')
													->where('games.date', '<', $years[$i+1].'-09-01')
													->where('box_score_lines.player_id', $id)
													->orderBy('games.date', 'desc')
													->get();
		}

		$seasons = array_reverse($seasons);

		$dkPlayerPools = DkPlayerPool::select(DB::raw('dk_player_pool_id as id'))
										->join('dk_players', function($join) {

											$join->on('dk_player_pools.id', '=', 'dk_players.dk_player_pool_id');
										})
										->where('dk_players.player_id', $id)
										->get();

		# ddAll($dkPlayerPools);

		foreach ($dkPlayerPools as $dkPlayerPool) {

			$teamIds = DkPlayer::select('team_id')
						->where('dk_player_pool_id', $dkPlayerPool->id)
						->groupBy('team_id')
						->get();

			$dkPlayerPool->num_of_games = count($teamIds) / 2;
		}

		$dkPlayers = DkPlayer::select('*')
								->join('dk_player_pools', function($join) {

									$join->on('dk_player_pools.id', '=', 'dk_players.dk_player_pool_id');
								})
								->where('dk_players.player_id', $id)
								->get();

		foreach ($seasons as $season) {
			
			foreach ($season as $boxScoreLine) {

				$dkPlayerExists = false;
				
				foreach ($dkPlayers as $dkPlayer) {
					
					if ($dkPlayer->date === $boxScoreLine->date) {

						$boxScoreLine->salary = $dkPlayer->salary;
						$boxScoreLine->ownership_percentage = $dkPlayer->ownership_percentage;

						$boxScoreLine->value = $boxScoreLine->dk_pts / ($boxScoreLine->salary / 1000);

						foreach ($dkPlayerPools as $dkPlayerPool) {
							
							if ($dkPlayerPool->id === $dkPlayer->dk_player_pool_id) {

								$boxScoreLine->num_of_games = $dkPlayerPool->num_of_games;

								break;
							}
						}

						$dkPlayerExists = true;

						break;
					}
				}

				if (!$dkPlayerExists) {

					$placeholder = null;

					$boxScoreLine->salary = $placeholder;
					$boxScoreLine->ownership_percentage = $placeholder;

					$boxScoreLine->value = $placeholder;
				}
			}

			unset($dkPlayer);
		}

		$dkPlayer = DkPlayer::select('dk_players.id',
										'salary',
										'p_mp',
										'p_mp_ui',
										'p_dks_slash_mp',
										'p_dks_slash_mp_ui',
										'p_dk_share',
										'p_dk_pts',
										'note',
										'team_id')
										->join('dk_player_pools', function($join) {

											$join->on('dk_player_pools.id', '=', 'dk_players.dk_player_pool_id');
										})
										->join('teams', function($join) {

											$join->on('teams.id', '=', 'dk_players.team_id');
										})
										->where('dk_players.player_id', $id)
										->orderBy('dk_player_pools.date', 'desc')
										->first();

		# ddAll($dkPlayer);

		$saoUpdater = new SaoUpdater;

		$playerPool = $saoUpdater->getLatestDkPlayerPool();

		list($currentHour, $currentMinute, $timeDiffHour, $timeDiffMinute, $updatedAtDate) = $saoUpdater->getUpdateVariables($playerPool->date);

		if ($saoUpdater->needsToBeUpdated($timeDiffHour, $timeDiffMinute, $updatedAtDate, $playerPool->date)) { // update every 15 minutes

			$activeTeamsGetter = new ActiveTeamsGetter;

			$activeTeams = $activeTeamsGetter->getActiveTeams($playerPool->id);

			$saoScraper = new SaoScraper;

			$activeTeams = $saoScraper->scrapeSao($playerPool->date, $activeTeams);

			foreach ($activeTeams as $activeTeam) {
				
				if ($activeTeam['id'] === $dkPlayer->team_id) {

					$dkPlayer->p_dk_share = ($dkPlayer->p_dk_share === null ? 0 : $dkPlayer->p_dk_share);

					$dkPlayer->p_dk_pts = numFormat(($dkPlayer->p_dk_share / 100) * $activeTeam['projected_dk_pts'], 2);

					$dkPlayer->save();

					break;
				}
			}

			$saoUpdater->setNewUpdatedDateAndTime($currentHour, $currentMinute);
		}

		$lastUpdate = $saoUpdater->getLastUpdate();

		# ddAll($player);
		
		return view('players/show', compact('titleTag', 'h2Tag', 'player', 'dkPlayer', 'overviews', 'seasons', 'lastUpdate'));
	}

	public function edit($id) {

		$player = Player::with('team')->where('id', $id)->first();

		$h2Tag = $player->br_name;
		$titleTag = $h2Tag.' | ';

		$teams = Team::all();

		# 	ddAll($player);

		return view('players/edit', compact('titleTag', 'h2Tag', 'player', 'teams'));
	}

	public function update($id, Request $request) {

		$player = Player::find($id);

		$teamDkName = $request->input('team');
		$team = Team::where('dk_name', $teamDkName)->first();

		$player->team_id = $team->id;
		$player->br_name = ($request->input('br-name') ? $request->input('br-name') : null);
		$player->dk_name = ($request->input('dk-name') ? $request->input('dk-name') : null);
		$player->dk_short_name = ($request->input('dk-short-name') ? $request->input('dk-short-name') : null);

		$player->save();

        $message = 'Success!';

        return redirect('/players/'.$id.'/edit')->with('message', $message);		
	}

	public function updateProjectedStats(Request $request) {

		$pStatsGetter = new PStatsGetter;

		$this->years = $pStatsGetter->getYears();

		$dkPlayer = DkPlayer::with('team')
								->where('dk_players.id', $request->input('dk-player-id'))
								->first();

		$playerId = $request->input('player-id');

		$pMp = (trim($request->input('p-mp')) == '' ? null : trim($request->input('p-mp')));
		$pMpUi = trim($request->input('p-mp-ui'));

		if ($pMp !== $dkPlayer->p_mp) {

			$pMpUi = 'm';
		}

		$pMp = $pStatsGetter->getPMp($pMp, $pMpUi, $this->years, $playerId);

		# ddAll($pMp);

		$dkPlayer->p_mp = $pMp;
		$dkPlayer->p_mp_ui = $pMpUi;

		/////////////////////////////////////////////////////////////////////////////////////////////

		$pDksSlashMp = (trim($request->input('p-dks-slash-mp')) == '' ? null : trim($request->input('p-dks-slash-mp')));
		$pDksSlashMpUi = trim($request->input('p-dks-slash-mp-ui'));

		if ($pDksSlashMp !== $dkPlayer->p_dks_slash_mp) {

			$pDksSlashMpUi = 'm';
		}

		$pDksSlashMp = $pStatsGetter->getPDksSlashMp($pDksSlashMp, $pDksSlashMpUi, $this->years, $playerId);

		$dkPlayer->p_dks_slash_mp = $pDksSlashMp;
		$dkPlayer->p_dks_slash_mp_ui = $pDksSlashMpUi;	

		$dkPlayer->p_dk_share = $dkPlayer->p_mp * $dkPlayer->p_dks_slash_mp;

		$teamProjectedDkPts = Cache::get($dkPlayer->team->dk_name.'_projected_dk_pts');

		$dkPlayer->p_dk_pts = $teamProjectedDkPts * ($dkPlayer->p_dk_share / 100);

		$dkPlayer->note = (trim($request->input('note')) ? trim($request->input('note')) : null);

		$dkPlayer->save();

		return redirect()->route('players.show', $playerId); 
	} 

}