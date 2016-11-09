<?php namespace App\Http\Controllers;

ini_set('max_execution_time', 10800); // 10800 seconds = 3 hours

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Input;

use App\UseCases\FileUploader;
use App\UseCases\PlayerPoolParser;

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

}