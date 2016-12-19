<?php namespace App\UseCases;

use App\Models\DkPlayerPool;
use App\Models\DkPlayer;
use App\Models\Player;
use App\Models\Team;

use App\Models\Game;
use App\Models\GameLine;
use App\Models\BoxScoreLine;

use DB;

use Illuminate\Support\Facades\Cache;

class PStatsGetter {

	public function getYears() {

		return [2015, 2016, 2017];
	}

	public function getPMp($pMp, $pMpUi, $years, $playerId) {

		switch ($pMpUi) {

			case 'm': // manual
				
				return $pMp;
			
			case 'ts': // this season
				
				return BoxScoreLine::join('games', function($join) {

											$join->on('games.id', '=', 'box_score_lines.game_id');
										})
										->where('date', '>', $years[1].'-09-01')
										->where('date', '<', $years[2].'-09-01')
										->where('player_id', $playerId)
										->avg('mp');

			case 'ls': // this season
				
				return BoxScoreLine::join('games', function($join) {

											$join->on('games.id', '=', 'box_score_lines.game_id');
										})
										->where('date', '>', $years[0].'-09-01')
										->where('date', '<', $years[1].'-09-01')
										->where('player_id', $playerId)
										->avg('mp');

			case 'both': // this season
				
				return BoxScoreLine::join('games', function($join) {

											$join->on('games.id', '=', 'box_score_lines.game_id');
										})
										->where('date', '>', $years[0].'-09-01')
										->where('date', '<', $years[2].'-09-01')
										->where('player_id', $playerId)
										->avg('mp');
		}
	}

	public function getPDksSlashMp($pDksSlashMp, $pDksSlashMpUi, $years, $playerId) {

		switch ($pDksSlashMpUi) {

			case 'm': // manual
				
				return $pDksSlashMp;
			
			case 'ts': // this season

				$totalDkShare = BoxScoreLine::join('games', function($join) {

													$join->on('games.id', '=', 'box_score_lines.game_id');
												})
												->where('date', '>', $years[1].'-09-01')
												->where('date', '<', $years[2].'-09-01')
												->where('player_id', $playerId)
												->sum('dk_share');

				$totalMp = BoxScoreLine::join('games', function($join) {

												$join->on('games.id', '=', 'box_score_lines.game_id');
											})
											->where('date', '>', $years[1].'-09-01')
											->where('date', '<', $years[2].'-09-01')
											->where('player_id', $playerId)
											->sum('mp');

				return $totalDkShare / $totalMp;

			case 'both': // both seasons
				
				$totalDkShare = BoxScoreLine::join('games', function($join) {

													$join->on('games.id', '=', 'box_score_lines.game_id');
												})
												->where('date', '>', $years[0].'-09-01')
												->where('date', '<', $years[2].'-09-01')
												->where('player_id', $playerId)
												->sum('dk_share');

				$totalMp = BoxScoreLine::join('games', function($join) {

												$join->on('games.id', '=', 'box_score_lines.game_id');
											})
											->where('date', '>', $years[0].'-09-01')
											->where('date', '<', $years[2].'-09-01')
											->where('player_id', $playerId)
											->sum('mp');

				return $totalDkShare / $totalMp;
		}
	}

}