<?php namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Input;

use App\UseCases\GameScraper;
use App\UseCases\BoxScoreLineScraper;

use App\Models\Game;
use App\Models\GameLine;
use App\Models\BoxScoreLine;

use DB;

class CrudController extends Controller {

	public function games(Request $request) {

		$date = $request->input('date');

		$games = Game::where('date', $date)->get();

		foreach ($games as $game) {
			
			BoxScoreLine::where('game_id', $game->id)->delete();

			GameLine::where('game_id', $game->id)->delete();

			$game->delete();
		}

		$message = 'Success!';

        return redirect()->route('admin.crud.games')->with('message', $message);       	
	}

}