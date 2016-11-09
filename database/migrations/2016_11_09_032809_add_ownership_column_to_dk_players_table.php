<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOwnershipColumnToDkPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dk_players', function ($table) {
            
            $table->decimal('ownership_percentage', 5, 2)->nullable()->after('game_time');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('dk_players', function ($table) {

            $table->dropColumn('ownership_percentage');
        });
    }
}
