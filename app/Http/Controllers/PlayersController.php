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

class PlayersController extends Controller {

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

		$currentProjectedDkShare = DkPlayer::select('p_dk_share')
													->join('dk_player_pools', function($join) {

														$join->on('dk_player_pools.id', '=', 'dk_players.dk_player_pool_id');
													})
													->where('dk_players.player_id', $id)
													->orderBy('dk_player_pools.date', 'desc')
													->take(1)
													->pluck('p_dk_share')[0];

		if ($currentProjectedDkShare === null) {

			$currentProjectedDkShare = 0;
		}

		$metadata['p_dk_share'] = $currentProjectedDkShare;

		$overviews = [];

		$years = [2015, 2016, 2017];

		$overviews['Both']['avg_dk_share'] = BoxScoreLine::join('games', function($join) {

																$join->on('games.id', '=', 'box_score_lines.game_id');
															})
															->where('date', '>', $years[0].'-09-01')
															->where('date', '<', $years[2].'-09-01')
															->where('player_id', $id)
															->avg('dk_share');

		$overviews['Both']['avg_mp'] = BoxScoreLine::join('games', function($join) {

															$join->on('games.id', '=', 'box_score_lines.game_id');
														})
														->where('date', '>', $years[0].'-09-01')
														->where('date', '<', $years[2].'-09-01')
														->where('player_id', $id)
														->avg('mp');

		

		if ($overviews['Both']['avg_mp'] === null) {

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

		for ($i = 0; $i < 2; $i++) { 
			
			$season = $years[$i].'-'.$years[$i+1];

			$overviews[$season]['avg_dk_share'] = BoxScoreLine::join('games', function($join) {

																	$join->on('games.id', '=', 'box_score_lines.game_id');
																})
																->where('date', '>', $years[$i].'-09-01')
																->where('date', '<', $years[$i+1].'-09-01')
																->where('player_id', $id)
																->avg('dk_share');

			$overviews[$season]['avg_mp'] = BoxScoreLine::join('games', function($join) {

																$join->on('games.id', '=', 'box_score_lines.game_id');
															})
															->where('date', '>', $years[$i].'-09-01')
															->where('date', '<', $years[$i+1].'-09-01')
															->where('player_id', $id)
															->avg('mp');

			if ($overviews[$season]['avg_mp'] === null) {

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
		}

		# ddAll($seasons);
		
		return view('players/show', compact('titleTag', 'h2Tag', 'player', 'metadata', 'overviews', 'seasons'));
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

}