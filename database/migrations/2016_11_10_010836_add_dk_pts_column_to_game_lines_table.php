<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDkPtsColumnToGameLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('game_lines', function ($table) {
            
            $table->decimal('dk_pts', 5, 2)->nullable()->after('vegas_pts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('game_lines', function ($table) {

            $table->dropColumn('dk_pts');
        });
    }
}
