<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPDkShareAndPDkPtsColumnsToDkPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dk_players', function ($table) {
            
            $table->decimal('p_dk_share', 5, 2)->nullable()->after('ownership_percentage');
            $table->decimal('p_dk_pts', 5, 2)->nullable()->after('p_dk_share');
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
            
            $table->dropColumn('p_dk_share');
            $table->dropColumn('p_dk_pts');
        });
    }
}
