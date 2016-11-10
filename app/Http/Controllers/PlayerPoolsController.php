<?php namespace App\Http\Controllers;

use App\Models\DkPlayerPool;

class PlayerPoolsController extends Controller {

	public function index() {

		$playerPools = DkPlayerPool::take(10)->orderBy('date', 'desc')->get();

		$titleTag = 'Player Pools | ';
	    $h2Tag = 'Player Pools';	

		return view('player_pools/index', compact('titleTag', 'h2Tag', 'playerPools'));
	}

}