<?php namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\GameLine;

use App\UseCases\Calculator;

class StudiesController extends Controller {

	public function calculateCorrelationBetweenSpreadsAndVegasSpreads() {

		$h2Tag = 'Correlation between Spreads and Vegas Spreads';
		$titleTag = $h2Tag.' | ';
		
		$games = Game::with('game_lines')
						->orderBy('id', 'asc')
						->get();

		foreach ($games as $game) {
			
			$xNumbers[] = $game->game_lines[0]->pts - $game->game_lines[1]->pts;
			$yNumbers[] = $game->game_lines[0]->vegas_pts - $game->game_lines[1]->vegas_pts;
		}

		$data = $this->calculateCorrelation($xNumbers, $yNumbers);

		$data = $this->addCorrelationChartData($data, 'Spreads', 'Vegas Spreads', -60, 60);

		# ddAll($data);

		return view('studies.correlations.index', compact('titleTag', 'h2Tag', 'data'));		
	}

	public function calculateCorrelationBetweenTotalsAndVegasTotals() {

		$h2Tag = 'Correlation between Totals and Vegas Totals';
		$titleTag = $h2Tag.' | ';
		
		$games = Game::with('game_lines')
						->orderBy('id', 'asc')
						->get();

		foreach ($games as $game) {
			
			$xNumbers[] = $game->game_lines[0]->pts + $game->game_lines[1]->pts;
			$yNumbers[] = $game->game_lines[0]->vegas_pts + $game->game_lines[1]->vegas_pts;
		}

		$data = $this->calculateCorrelation($xNumbers, $yNumbers);

		$data = $this->addCorrelationChartData($data, 'Totals', 'Vegas Totals', 140, 310);

		# ddAll($data);

		return view('studies.correlations.index', compact('titleTag', 'h2Tag', 'data'));
	}

	public function calculateCorrelationBetweenPtsAndVegasPts() {

		$titleTag = 'Correlation between PTS and Vegas PTS | ';
		$h2Tag = 'Correlation between PTS and Vegas PTS';

		$xNumbers = GameLine::orderBy('id', 'asc')->pluck('pts')->toArray();
		$yNumbers = GameLine::orderBy('id', 'asc')->pluck('vegas_pts')->toArray();

		$data = $this->calculateCorrelation($xNumbers, $yNumbers);

		$data = $this->addCorrelationChartData($data, 'PTS', 'Vegas PTS', 40, 160);

		# ddAll($data);

		return view('studies.correlations.index', compact('titleTag', 'h2Tag', 'data'));
	}


	/****************************************************************************************
	HELPERS
	****************************************************************************************/

	private function addCorrelationChartData($data, $xTitle, $yTitle, $lowestXValue, $highestXValue) {

		$data['xTitle'] = $xTitle;
		$data['yTitle'] = $yTitle;

		for ($x = $lowestXValue; $x <= $highestXValue; $x++) { 
		
			$y = ($data['bOne'] * $x) + $data['bNaught'];
			$jsonLineOfBestFit[] = [$x, $y];
		}

		$data['jsonLineOfBestFit'] = $jsonLineOfBestFit;

		$jsonPerfectLine = [];

		for ($x = $lowestXValue; $x <= $highestXValue; $x++) { 
			
			$y = $x;
			$jsonPerfectLine[] = [$x, $y];
		}

		$data['jsonPerfectLine'] = $jsonPerfectLine;

		$data['equation'] = '('.$data['yTitle'].' - '.$data['bNaught'].') / '.$data['bOne']; 

		return $data;
	}

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