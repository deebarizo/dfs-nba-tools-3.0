<?php namespace App\Http\Controllers;

use App\UseCases\Calculator;

class ResearchController extends Controller {

	// Needs array of numbers

	public function calculateCV($numbers = [600, 470, 170, 430, 300]) {

		$calculator = new Calculator;

		$this->mean = $calculator->calculateMean($numbers);

		$this->sd = $calculator->calculateSD($numbers, $this->mean);

		return numFormat($this->sd / $this->mean * 100, 2);
	}

}