<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DkPlayer extends Model {

    public function dk_player_pool() {

    	return $this->belongsTo(DkPlayerPool::class);
    }

    public function player() {

    	return $this->belongsTo(Player::class);
    }
    
    public function team() {

    	return $this->belongsTo(Team::class);
    }

    public function opp_team() {

    	return $this->belongsTo(Team::class);
    }

    protected $fillable = ['p_mp', 'p_dk_share', 'p_dk_pts'];
}