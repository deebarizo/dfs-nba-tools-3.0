<?php namespace App\UseCases;

date_default_timezone_set('America/Chicago'); 

use DateTime;

use Illuminate\Support\Facades\Cache;

class SaoUpdater {

	public function getUpdateVariables($playerPoolDate) {

		$currentDateTime = new DateTime();
		$currentHour = intval($currentDateTime->format('H'));
		$currentMinute = intval($currentDateTime->format('i'));

		$updatedAtHour = Cache::get('updated_at_hour', 0);
		$updatedAtMinute = Cache::get('updated_at_minute', 0);
		$updatedAtDate = Cache::get('updated_at_date', '2016-11-21');

		# ddAll($updatedAtHour.' '.$updatedAtMinute.' '.$updatedAtDate);

		$timeDiffHour = $currentHour - $updatedAtHour;
		$timeDiffMinute = $currentMinute - $updatedAtMinute;

		$dateDiff = date_diff(new DateTime($playerPoolDate), $currentDateTime);

		return array($currentHour, $currentMinute, $timeDiffHour, $timeDiffMinute, $updatedAtDate);
	}

	public function needsToBeUpdated($timeDiffHour, $timeDiffMinute, $updatedAtDate, $playerPoolDate) {

		if ($updatedAtDate !== $playerPoolDate || $timeDiffHour > 0 || $timeDiffMinute > 14) { // update every 15 minutes

			return true;
		}

		return false;
	}

	public function getLastUpdate() {

		$updatedAtHour = Cache::get('updated_at_hour', 0);
		$updatedAtMinute = Cache::get('updated_at_minute', 0);
		$updatedAtDate = Cache::get('updated_at_date', '2016-11-21');

		return $updatedAtDate.' '.$updatedAtHour.':'.$updatedAtMinute;
	}

	public function setNewUpdatedDateAndTime($currentHour, $currentMinute, $playerPoolDate) {

		Cache::forever('updated_at_hour', $currentHour);
		Cache::forever('updated_at_minute', $currentMinute);
		Cache::forever('updated_at_date', $playerPoolDate);
	}

}