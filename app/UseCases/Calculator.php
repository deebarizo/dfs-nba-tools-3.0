<?php namespace App\UseCases;

class Calculator {

	public function calculateMean($numbers) {

		$total = array_sum($numbers);

		return $total / count($numbers);
	}

	public function calculateSD($numbers, $mean) {

		$squaredDiffs = [];

		foreach ($numbers as $number) {
			
			$squaredDiffs[] = pow($number - $mean, 2);
		}

		$variance = array_sum($squaredDiffs) / count($numbers);

		return sqrt($variance);
	}

}