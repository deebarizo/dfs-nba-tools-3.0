<?php namespace App\Http\Controllers;

use App\UseCases\Calculator;

class ResearchController extends Controller {

	// Needs two arrays of numbers

	public function calculateCorrelation($xNumbers = [14.2, 16.4, 11.9, 15.2, 18.5, 22.1, 19.4, 25.1, 23.4, 18.1, 22.6, 17.2],
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

	public function calculateCV($numbers = [600, 470, 170, 430, 300]) {

		$calculator = new Calculator;

		$this->mean = $calculator->calculateMean($numbers);

		$this->sd = $calculator->calculateSD($numbers, $this->mean);

		return numFormat($this->sd / $this->mean * 100, 2);
	}

}