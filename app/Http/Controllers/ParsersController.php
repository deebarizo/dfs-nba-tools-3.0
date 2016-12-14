<?php namespace App\Http\Controllers;

ini_set('max_execution_time', 10800); // 10800 seconds = 3 hours

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Input;

use App\UseCases\FileUploader;
use App\UseCases\PlayerPoolParser;

use App\Models\DkPlayerPool;
use App\Models\Player;
use App\Models\Team;
use App\Models\DkPlayer;

class ParsersController extends Controller {

	public function parseDkPlayerPool(Request $request) {

		$date = $request->input('date');
		$slate = $request->input('slate');

		$fileUploader = new FileUploader;

		$csvFile = $fileUploader->uploadDkPlayerPool($request, $date, $slate);

		$playerPoolParser = new PlayerPoolParser;

        $results = $playerPoolParser->parseDkPlayerPool($csvFile, $date, $slate);

        $message = $results->message;

        return redirect()->route('admin.parsers.dk_player_pool')->with('message', $message);       	
	}

	public function parseDkOwnershipPercentages(Request $request) {

		$date = $request->input('date');
		$slate = $request->input('slate');

		$fileUploader = new FileUploader;

		$csvFile = $fileUploader->uploadDkOwnershipPercentages($request, $date, $slate);

		$playerPoolParser = new PlayerPoolParser;

        $results = $playerPoolParser->parseDkOwnershipPercentages($csvFile, $date, $slate);

        $message = $results->message;

        return redirect()->route('admin.parsers.dk_ownership_percentages')->with('message', $message);       	
	}

	public function parseYourDkOwnershipPercentages(Request $request) {

		$date = trim($request->input('date'));
		$slate = trim($request->input('slate'));

		$rawTextarea = trim($request->input('your-dk-ownership-percentages'));

		$dkPlayerPool = DkPlayerPool::where('date', $date)->where('slate', $slate)->first();

		$rawLines = preg_split("/\\r\\n|\\r|\\n/", $rawTextarea);

		foreach ($rawLines as $rawLine) {

			$rawPlayerName = preg_replace("/(.*)(\s\(.*)/", "$1", $rawLine);

			$dkPlayer = DkPlayer::join('players', function($join) {

										$join->on('players.id', '=', 'dk_players.player_id');
									})
									->where('dk_players.dk_player_pool_id', $dkPlayerPool->id)
									->where('players.dk_name', $raw)

			dd($dkPlayer);
		}

		ddAll($rawLines);

		
	}

}