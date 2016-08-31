<?php namespace App\UseCases;

class Calculator {

	public function calculateCorrelation($numbers) {

		$axises = ['x', 'y'];

		foreach ($axises as $axis) {
			
			$means[$axis] = $this->calculateMean($numbers[$axis]);
		}

		$secondaryNumbers['a'] = [];
		$secondaryNumbers['b'] = [];

		foreach ($axises as $axis) {
			
			foreach ($numbers[$axis] as $number) {
				
				switch ($axis) {
					
					case 'x':
						$secondaryAxis = 'a';
						break;
					
					case 'y':
						$secondaryAxis = 'b';
						break;
				}

				$secondaryNumbers[$secondaryAxis][] = $number - $means[$axis];
			}
		}

		unset($secondaryAxis);

		$finalNumbers['a_squared'] = [];
		$finalNumbers['b_squared'] = [];

		$secondaryAxises = ['a', 'b'];

		foreach ($secondaryAxises as $secondaryAxis) {
			
			foreach ($secondaryNumbers[$secondaryAxis] as $secondaryNumber) {
				
				$finalNumbers[$secondaryAxis.'_squared'][] = pow($secondaryNumber, 2);
			}
		}

		$finalNumbers['axb'] = [];

		for ($i = 0; $i < count($secondaryNumbers['a']); $i++) { 
			
			$finalNumbers['axb'][] = $secondaryNumbers['a'][$i] * $secondaryNumbers['b'][$i];
		}

		$finalSums['axb'] = array_sum($finalNumbers['axb']);

		foreach ($secondaryAxises as $secondaryAxis) {
			
			$finalSums[$secondaryAxis.'_squared'] = array_sum($finalNumbers[$secondaryAxis.'_squared']);	
		}

		return $finalSums['axb'] / sqrt($finalSums['a_squared'] * $finalSums['b_squared']);
	}

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