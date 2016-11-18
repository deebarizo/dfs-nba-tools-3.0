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

		$overviews = [];

		$years = [2015, 2016, 2017];

		$overviews['Both']['avg_mp'] = BoxScoreLine::join('games', function($join) {

															$join->on('games.id', '=', 'box_score_lines.game_id');
														})
														->where('date', '>', $years[0].'-09-01')
														->where('date', '<', $years[2].'-09-01')
														->where('player_id', $id)
														->avg('mp');

		if ($overviews['Both']['avg_mp'] > 25) {

			$minutesFloor = 25;

			$overviews['Both']['avg_mp'] = BoxScoreLine::join('games', function($join) {

																$join->on('games.id', '=', 'box_score_lines.game_id');
															})
															->where('date', '>', $years[0].'-09-01')
															->where('date', '<', $years[2].'-09-01')
															->where('player_id', $id)
															->where('mp', '>', $minutesFloor)
															->avg('mp');
		
		} else {

			$minutesFloor = 15;
		}

		$overviews['Both']['avg_dk_share'] = BoxScoreLine::join('games', function($join) {

																	$join->on('games.id', '=', 'box_score_lines.game_id');
																})
																->where('date', '>', $years[0].'-09-01')
																->where('date', '<', $years[2].'-09-01')
																->where('mp', '>', $minutesFloor)
																->where('player_id', $id)
																->avg('dk_share');

		for ($i = 0; $i < 2; $i++) { 
			
			$season = $years[$i].'-'.$years[$i+1];

			$overviews[$season]['avg_mp'] = BoxScoreLine::join('games', function($join) {

																$join->on('games.id', '=', 'box_score_lines.game_id');
															})
															->where('date', '>', $years[$i].'-09-01')
															->where('date', '<', $years[$i+1].'-09-01')
															->where('player_id', $id)
															->avg('mp');

			if ($overviews[$season]['avg_mp'] > 25) {

				$minutesFloor = 25;

				$overviews[$season]['avg_mp'] = BoxScoreLine::join('games', function($join) {

																	$join->on('games.id', '=', 'box_score_lines.game_id');
																})
																->where('date', '>', $years[$i].'-09-01')
																->where('date', '<', $years[$i+1].'-09-01')
																->where('player_id', $id)
																->where('mp', '>', $minutesFloor)
																->avg('mp');

			} else {

				$minutesFloor = 15;
			}

			$overviews[$season]['avg_dk_share'] = BoxScoreLine::join('games', function($join) {

																		$join->on('games.id', '=', 'box_score_lines.game_id');
																	})
																	->where('date', '>', $years[$i].'-09-01')
																	->where('date', '<', $years[$i+1].'-09-01')
																	->where('mp', '>', $minutesFloor)
																	->where('player_id', $id)
																	->avg('dk_share');

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

		# ddAll($seasons);
		
		return view('players/show', compact('titleTag', 'h2Tag', 'player', 'overviews', 'seasons'));
	}

}