<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDkShareColumnToBoxScoreLinesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('box_score_lines', function ($table) {
            
            $table->decimal('dk_share', 5, 2)->nullable()->after('dk_pts');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('box_score_lines', function ($table) {

            $table->dropColumn('dk_share');
        });
    }
}
