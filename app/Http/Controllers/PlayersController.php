<?php namespace App\Http\Controllers;

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



		$overviews['Both']['avg_dk_share_slash_avg_mp'] = $overviews['Both']['avg_dk_share'] / $overviews['Both']['avg_mp'];

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

				$overviews[$season]['avg_dk_share_slash_avg_mp'] = $overviews[$season]['avg_dk_share'] / $overviews[$season]['avg_mp'];
			}

			$seasons[$season] = BoxScoreLine::select('*')
													->join('games', function($join) {

														$join->on('games.id', '=', 'box_score_lines.game_id');
													})
													->join('teams', function($join) {

														$join->on('teams.id', '=', 'box_score_lines.team_id');
													})
													->with('game.game_lines.team')
													->where('date', '>', $years[$i].'-09-01')
													->where('date', '<', $years[$i+1].'-09-01')
													->where('player_id', $id)
													->orderBy('games.date', 'desc')
													->get();
		}

		$seasons = array_reverse($seasons);

		# ddAll($overviews);
		
		return view('players/show', compact('titleTag', 'h2Tag', 'player', 'metadata', 'overviews', 'seasons'));
	}

}