<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddVegasPtsColumnToGameLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('game_lines', function ($table) {
            
            $table->decimal('vegas_pts', 5, 2)->nullable()->after('pts');
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

            $table->dropColumn('vegas_pts');
        });
    }
}
