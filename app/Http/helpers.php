<?php

/****************************************************************************************
PRINT VARIABLE
****************************************************************************************/

function ddAll($var) {

	echo '<pre>';
	print_r($var);
	echo '</pre>';

	exit();
}

function prf($var) {

    echo '<pre>';
    print_r($var);
    echo '</pre>';
}


/****************************************************************************************
NUMBER FORMAT
****************************************************************************************/

function numFormat($number, $decimalPlaces = 2) {
	
	$number = number_format(round($number, $decimalPlaces), $decimalPlaces);

	return $number;
}