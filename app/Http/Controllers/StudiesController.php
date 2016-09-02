<?php namespace App\Http\Controllers;

use App\Models\GameLine;

use App\UseCases\Calculator;

class StudiesController extends Controller {

	public function calculateCorrelationBetweenPtsAndVegasPts() {

		$titleTag = 'Correlation between PTS and Vegas PTS | ';
		$h2Tag = 'Correlation between PTS and Vegas PTS';

		$xNumbers = GameLine::orderBy('id', 'asc')->pluck('pts')->toArray();
		$yNumbers = GameLine::orderBy('id', 'asc')->pluck('vegas_pts')->toArray();

		$data = $this->calculateCorrelation($xNumbers, $yNumbers);

		$data['xTitle'] = 'PTS';
		$data['yTitle'] = 'Vegas PTS';

		for ($x=40; $x <= 150 ; $x++) { 
		
			$y = ($data['bOne'] * $x) + $data['bNaught'];
			$jsonLineOfBestFit[] = [$x, $y];
		}

		$data['jsonLineOfBestFit'] = $jsonLineOfBestFit;

		$jsonPerfectLine = [];

		for ($x=40; $x <= 150 ; $x++) { 
			
			$y = $x;
			$jsonPerfectLine[] = [$x, $y];
		}

		$data['jsonPerfectLine'] = $jsonPerfectLine;

		$data['equation'] = '('.$data['yTitle'].' - '.$data['bNaught'].') / '.$data['bOne']; 

		# ddAll($data);

		return view('studies.correlations.pts_and_vegas_pts', compact('titleTag', 'h2Tag', 'data'));
	}


	/****************************************************************************************
	HELPERS
	****************************************************************************************/

	// Needs two arrays of numbers
	// default arrays are from https://www.mathsisfun.com/data/correlation.html

	private function calculateCorrelation($xNumbers = [14.2, 16.4, 11.9, 15.2, 18.5, 22.1, 19.4, 25.1, 23.4, 18.1, 22.6, 17.2],
										 $yNumbers = [215, 325, 185, 332, 406, 522, 412, 614, 544, 421, 445, 408]) { 

		if (count($xNumbers) !== count($yNumbers)) {

			ddAll('The length of $xNumbers and $yNumbers are not equal.');
		}

		$numbers['x'] = $xNumbers;
		$numbers['y'] = $yNumbers;

		$calculator = new Calculator;

		return $calculator->calculateCorrelation($numbers);
	}

	// Needs array of numbers

	private function calculateCV($numbers = [600, 470, 170, 430, 300]) {

		$calculator = new Calculator;

		$this->mean = $calculator->calculateMean($numbers);

		$this->sd = $calculator->calculateSD($numbers, $this->mean);

		return numFormat($this->sd / $this->mean * 100, 2);
	}

}