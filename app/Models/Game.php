<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Game extends Model {

    public function game_lines() {

    	return $this->hasMany(GameLine::class);
    }

    public function box_score_lines() {

    	return $this->hasMany(BoxScoreLine::class);
    }
}