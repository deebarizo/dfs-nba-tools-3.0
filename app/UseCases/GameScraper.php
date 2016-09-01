<?php namespace App\UseCases;

ini_set('max_execution_time', 10800); // 10800 seconds = 3 hours

use App\Models\Game;
use App\Models\GameLine;

use App\Models\Team;

use DB;

use Goutte\Client;
use vendor\symfony\DomCrawler\Crawler;

class GameScraper {

	public function scrapeGames($month, $year) {

		$client = new Client();

		$crawler = $client->request('GET', 'http://www.basketball-reference.com/leagues/NBA_'.$year.'_games-'.strtolower($month).'.html');

		$numGames = $crawler->filter('table#schedule > tbody > tr')->count();

		for ($i = 0; $i < 38; $i++) { 
			
			$unformattedDate = $crawler->filter('table#schedule > tbody > tr')->eq($i)->filter('th')->text();
			$games[$i]['date'] = date('Y-m-d', strtotime($unformattedDate));

			$unformattedTime = $crawler->filter('table#schedule > tbody > tr')->eq($i)->filter('td')->eq(0)->text();
			$unformattedTime2 = \DateTime::createFromFormat('H:i A', $unformattedTime);
			$games[$i]['eastern_time'] = $unformattedTime2->format('H:i:s');

			$games[$i]['br_link'] = $crawler->filter('table#schedule > tbody > tr')->eq($i)->filter('td')->eq(5)->filter('a')->link()->getUri();

			$unformattedOvertimePeriods = $crawler->filter('table#schedule > tbody > tr')->eq($i)->filter('td')->eq(6)->text();
			if ($unformattedOvertimePeriods == '') { 
				$games[$i]['ot_periods'] = 0;
			} else if ($unformattedOvertimePeriods === 'OT') { 
				$games[$i]['ot_periods'] = 1;
			} else {
				$games[$i]['ot_periods'] = preg_replace("/(\d)(OT)/", "$1", $unformattedOvertimePeriods);
			}

			$teamBrName = $crawler->filter('table#schedule > tbody > tr')->eq($i)->filter('td')->eq(1)->filter('a')->link()->getUri();
			$games[$i]['line'][0]['br_name'] = preg_replace("/(http:\/\/www.basketball-reference.com\/teams\/)(\D+)(\/\d+.html)/", "$2", $teamBrName);
			$games[$i]['line'][0]['pts'] = $crawler->filter('table#schedule > tbody > tr')->eq($i)->filter('td')->eq(2)->text();
			$games[$i]['line'][0]['location'] = 'away';
			$teamBrName = $crawler->filter('table#schedule > tbody > tr')->eq($i)->filter('td')->eq(3)->filter('a')->link()->getUri();
			$games[$i]['line'][1]['br_name'] = preg_replace("/(http:\/\/www.basketball-reference.com\/teams\/)(\D+)(\/\d+.html)/", "$2", $teamBrName);
			$games[$i]['line'][1]['pts'] = $crawler->filter('table#schedule > tbody > tr')->eq($i)->filter('td')->eq(4)->text();
			$games[$i]['line'][1]['location'] = 'home';
		}

		foreach ($games as $game) {

			$eGame = new Game;

			$eGame->date = $game['date'];
			$eGame->eastern_time = $game['eastern_time'];
			$eGame->br_link = $game['br_link'];
			$eGame->ot_periods = $game['ot_periods'];

			$eGame->save();

			foreach ($game as $gameLine) {

				$eGameLine = new GameLine;

				$eGameLine->game_id = $eGame->id;
				$eGameLine->team_id = Team::where('br_name', $gameLine['br_name'])->pluck('id')[0];
				$eGameLine->pts = $gameLine['pts'];
				$eGameLine->location = $gameLine['location'];

				$eGameLine->save();
			} 
		} 

		ddAll($games);
	}

}