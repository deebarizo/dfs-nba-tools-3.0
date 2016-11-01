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

		dd('stop');

		$playerPoolParser = new PlayerPoolParser;

        $results = $playerPoolParser->parseDkPlayerPool($date, $slate);

        $message = $results->message;

        return redirect()->route('admin.parsers.dk_player_pool')->with('message', $message);       	
	}

}