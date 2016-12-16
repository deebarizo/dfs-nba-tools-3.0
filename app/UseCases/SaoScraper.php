<?php namespace App\UseCases;

use Goutte\Client;
use vendor\symfony\DomCrawler\Crawler;

use Illuminate\Support\Facades\Cache;

class SaoScraper {

	public function scrapeSao($playerPoolDate, $activeTeams) {

		$client = new Client();

		$year = date('Y', strtotime($playerPoolDate));

		$monthNumber = date('m', strtotime($playerPoolDate));

		$dayNumber = date('d', strtotime($playerPoolDate));

		$crawler = $client->request('GET', 'http://www.scoresandodds.com/grid_'.$year.''.$monthNumber.''.$dayNumber.'.html');	

		$numTableRows = $crawler->filter('tr.team')->count();

		foreach ($activeTeams as &$activeTeam) {
			
			for ($i = 0; $i < $numTableRows; $i++) { 

				$tableRow = $crawler->filter('tr.team')->eq($i);

				$unformattedTeam = trim($tableRow->filter('td')->eq(0)->text());

				$saoName = preg_replace("/(\d+\s)(.+)/", "$2", $unformattedTeam);

				if ($saoName === $activeTeam['sao_name']) {

					$unformattedVegasScore = trim($tableRow->filter('td')->eq(3)->text());

					if ($unformattedVegasScore === '') {

						$activeTeam['total'] = null;
						$activeTeam['spread'] = null;

						continue;
					}

					if (strpos($unformattedVegasScore, '-') === false && $unformattedVegasScore !== 'PK') {

						$activeTeam['total'] = preg_replace('/(o|u)(.+)/', '', $unformattedVegasScore);

					} else if (strpos($unformattedVegasScore, '-') !== false) {

						$activeTeam['spread'] = abs(preg_replace("/(-\S+)(\s.+)/", "$1", $unformattedVegasScore));

					} else if ($unformattedVegasScore === 'PK') {
						
						$activeTeam['spread'] = 0;
					}
				}
			}				
		}

		unset($activeTeam);

		# ddAll($activeTeams);

		$mirrorActiveTeams = $activeTeams;

		foreach ($activeTeams as &$activeTeam) {
			
			foreach ($mirrorActiveTeams as $mirrorActiveTeam) {
				
				if ($activeTeam['opp_dk_name'] === $mirrorActiveTeam['dk_name']) {

					// http://php.net/manual/en/function.isset.php 
					// "isset() will return FALSE if testing a variable that has been set to NULL."

					if (isset($activeTeam['total'])) { 

						$activeTeam['vegas_pts'] = ($activeTeam['total'] - $mirrorActiveTeam['spread']) / 2;
						$activeTeam['real_total'] = $activeTeam['total'];
						$activeTeam['real_spread'] = $mirrorActiveTeam['spread'];
					}

					if (isset($activeTeam['spread'])) {

						$activeTeam['vegas_pts'] = ($mirrorActiveTeam['total'] + $activeTeam['spread']) / 2;
						$activeTeam['real_total'] = $mirrorActiveTeam['total'];
						$activeTeam['real_spread'] = $activeTeam['spread'] * -1;
					}

					if (!isset($activeTeam['vegas_pts'])) {

						$activeTeam['vegas_pts'] = 100;
						$activeTeam['real_total'] = null;
						$activeTeam['real_spread'] = null;						
					}

					$activeTeam['projected_dk_pts'] = $activeTeam['vegas_pts'] * 2.097070; // based on "SELECT sum(dk_pts) / sum(vegas_pts) FROM dfsninja.game_lines;"

					Cache::forever($activeTeam['dk_name'], $activeTeam['dk_name']);
					Cache::forever($activeTeam['dk_name'].'_total', $activeTeam['real_total']);
					Cache::forever($activeTeam['dk_name'].'_spread', $activeTeam['real_spread']);
					Cache::forever($activeTeam['dk_name'].'_projected_dk_pts', $activeTeam['projected_dk_pts']);

					break;
				}
			}
		}

		unset($activeTeam);

		# ddAll($activeTeams);

		return $activeTeams;
	}
}