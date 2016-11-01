<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDkPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('dk_players', function (Blueprint $table) {
            $table->increments('id');

            $table->integer('dk_player_pool_id')->unsigned();
            $table->foreign('dk_player_pool_id')->references('id')->on('dk_player_pools');
            $table->integer('player_id')->unsigned();
            $table->foreign('player_id')->references('id')->on('players');
            $table->integer('team_id')->unsigned();
            $table->foreign('team_id')->references('id')->on('teams');
            $table->integer('opp_team_id')->unsigned();
            $table->foreign('opp_team_id')->references('id')->on('teams');
            $table->string('first_position');
            $table->string('second_position')->nullable();
            $table->integer('salary');
            $table->string('location');
            $table->time('game_time');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('dk_players');
    }
}
