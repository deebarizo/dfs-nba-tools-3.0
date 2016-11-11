<?php namespace App\Http\Controllers;

use App\Models\DkPlayerPool;
use App\Models\DkPlayer;
use App\Models\Player;
use App\Models\Team;

use DB;

use Goutte\Client;
use vendor\symfony\DomCrawler\Crawler;

class TeamsController extends Controller {

	public function index() {

		$teams = Team::all();

		$h2Tag = 'Teams';
		$titleTag = $h2Tag.' | ';
	    
		return view('teams/index', compact('titleTag', 'h2Tag', 'teams'));
	}

}