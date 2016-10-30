<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBoxScoreLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('box_score_lines', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('game_id')->unsigned();
            $table->foreign('game_id')->references('id')->on('games');
            $table->integer('team_id')->unsigned();
            $table->foreign('team_id')->references('id')->on('teams');
            $table->integer('player_id')->unsigned();
            $table->foreign('player_id')->references('id')->on('players');
            $table->decimal('mp', 5, 2);
            $table->integer('fg');
            $table->integer('fga');
            $table->decimal('fg_percentage', 4, 3)->nullable(); // if they take no shots
            $table->integer('threep');
            $table->integer('threepa');
            $table->decimal('threep_percentage', 4, 3)->nullable();
            $table->integer('ft');
            $table->integer('fta');
            $table->decimal('ft_percentage', 4, 3)->nullable();
            $table->integer('orb');
            $table->integer('drb');
            $table->integer('trb');
            $table->integer('ast');
            $table->integer('stl');
            $table->integer('blk');
            $table->integer('tov');
            $table->integer('pf');
            $table->integer('pts');
            $table->integer('dk_pts');
            $table->decimal('ts_percentage', 4, 3)->nullable();
            $table->decimal('efg_percentage', 4, 3)->nullable();
            $table->decimal('threepa_rate', 4, 3)->nullable();
            $table->decimal('fta_rate', 4, 3)->nullable();
            $table->decimal('orb_percentage', 4, 1);
            $table->decimal('drb_percentage', 4, 1);
            $table->decimal('trb_percentage', 4, 1);
            $table->decimal('ast_percentage', 4, 1);
            $table->decimal('stl_percentage', 4, 1);
            $table->decimal('blk_percentage', 4, 1);
            $table->decimal('tov_percentage', 4, 1)->nullable();
            $table->decimal('usg_percentage', 4, 1);
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
        Schema::drop('box_score_lines');
    }
}
