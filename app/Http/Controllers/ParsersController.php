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

			$dkPlayer = DkPlayer::select('dk_players.id', 'players.dk_name', 'dk_players.your_ownership_percentage')
									->join('players', function($join) {

										$join->on('players.id', '=', 'dk_players.player_id');
									})
									->where('dk_players.dk_player_pool_id', $dkPlayerPool->id)
									->where(function($query) use($rawPlayerName) {

										return $query->where('dk_name', $rawPlayerName)
														->orWhere('dk_short_name', $rawPlayerName);
									})
									->first();

			if (!$dkPlayer) {

				$message = 'The player name, '.$rawPlayerName.', does not exist in the database.';

				return redirect()->route('admin.parsers.your_dk_ownership_percentages')->with('message', $message);      
			}

			$yourOwnershipPercentage = preg_replace("/(.*:\s)(.*)(%)/", "$2", $rawLine);

			$dkPlayer->your_ownership_percentage = $yourOwnershipPercentage;

			$dkPlayer->save();
		}

		$message = 'Success!';

		return redirect()->route('admin.parsers.your_dk_ownership_percentages')->with('message', $message);  
	}

}