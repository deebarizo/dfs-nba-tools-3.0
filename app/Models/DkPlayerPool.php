<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DkPlayerPool extends Model {

    public function dk_players() {

    	return $this->hasMany(DkPlayer::class);
    }
}