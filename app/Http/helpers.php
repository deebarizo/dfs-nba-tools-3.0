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
SET ACTIVE TAB
****************************************************************************************/

function setActive($path, $active = 'active') {

	return Request::is($path) ? $active : '';
}


/****************************************************************************************
FORMAT NUMBER
****************************************************************************************/

function numFormat($number, $decimalPlaces = 2) {
	
	$number = number_format(round($number, $decimalPlaces), $decimalPlaces);

	return $number;
}


/****************************************************************************************
GET DATES
****************************************************************************************/

function getYesterdayDate() {

	date_default_timezone_set('America/Chicago'); 

	return date("Y-m-d", strtotime("yesterday"));
}

function getTodayDate() {

	date_default_timezone_set('America/Chicago'); 

	return date("Y-m-d");
}


/****************************************************************************************
GET GAME DATA
****************************************************************************************/

function getGameMetadata($gameLines, $team) {

	$gameMetadata = [];

	foreach ($gameLines as $gameLine) {

		if ($gameLine->team->dk_name === $team) {

			$gameMetadata['team_pm_name'] = $gameLine->team->pm_name;

			$gameMetadata['team_score'] = $gameLine->pts;

			$gameMetadata['vegas_team_score'] = $gameLine->vegas_pts;
		}
		
		if ($gameLine->team->dk_name !== $team) {

			if ($gameLine->location === 'home') {

				$gameMetadata['opp_team'] = '@'.$gameLine->team->dk_name;

				$gameMetadata['home_pm_team'] = $gameLine->team->pm_name;
			
			} else {

				$gameMetadata['opp_team'] = $gameLine->team->dk_name;		

				$gameMetadata['away_pm_team'] = $gameLine->team->pm_name;
			}

			$gameMetadata['opp_team_score'] = $gameLine->pts;

			$gameMetadata['opp_vegas_team_score'] = $gameLine->vegas_pts;
		}
	}

	if (isset($gameMetadata['home_pm_team'])) {

		$gameMetadata['away_pm_team'] = $gameMetadata['team_pm_name'];
	
	} else {

		$gameMetadata['home_pm_team'] = $gameMetadata['team_pm_name'];
	}

	$score = $gameMetadata['team_score'].'-'.$gameMetadata['opp_team_score'];

    if ($gameMetadata['team_score'] > $gameMetadata['opp_team_score']) {

        $gameMetadata['html_score'] = '<span style="color: green">W</span> '.$score;
    }

    if ($gameMetadata['team_score'] < $gameMetadata['opp_team_score']) {
        
        $gameMetadata['html_score'] = '<span style="color: red">L</span> '.$score;
    }

    $diff = abs($gameMetadata['vegas_team_score'] - $gameMetadata['opp_vegas_team_score']);

    if ($gameMetadata['vegas_team_score'] > $gameMetadata['opp_vegas_team_score']) {
        
        $gameMetadata['line'] = '-'.$diff;
    }    

    if ($gameMetadata['vegas_team_score'] < $gameMetadata['opp_vegas_team_score']) {
        
        $gameMetadata['line'] = '+'.$diff;
    }       

    if ($gameMetadata['vegas_team_score'] == $gameMetadata['opp_vegas_team_score']) {
        
        $gameMetadata['line'] = 'PK';
    }

	return $gameMetadata;
}