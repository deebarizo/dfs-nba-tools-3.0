<?php namespace App\UseCases;

date_default_timezone_set('America/Chicago'); 

use DateTime;

use App\Models\DkPlayerPool;

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

		return true;

		if ($updatedAtDate === $playerPoolDate && ($timeDiffHour > 0 || $timeDiffMinute > 14)) { // update every 15 minutes

			return true;
		}

		return false;
	}

	public function getLastUpdate() {

		$updatedAtHour = Cache::get('updated_at_hour', 0);
		$updatedAtMinute = Cache::get('updated_at_minute', 0);
		$updatedAtDate = Cache::get('updated_at_date', '2016-11-21');

		$date = new DateTime();

		$date->setTime($updatedAtHour, $updatedAtMinute);
		$time = $date->format('g:i A');

		# ddAll($time);

		return $updatedAtDate.' ('.$time.')';
	}

	public function setNewUpdatedDateAndTime($currentHour, $currentMinute) {

		Cache::forever('updated_at_hour', $currentHour);
		Cache::forever('updated_at_minute', $currentMinute);

		$currentDateTime = new DateTime();
		$date = $currentDateTime->format('Y-m-d');

		Cache::forever('updated_at_date', $date);
	}

	public function getLatestDkPlayerPoolDate() {

		$date = DkPlayerPool::take(1)->orderBy('date', 'desc')->pluck('date')[0];

		return $date;
	}

}