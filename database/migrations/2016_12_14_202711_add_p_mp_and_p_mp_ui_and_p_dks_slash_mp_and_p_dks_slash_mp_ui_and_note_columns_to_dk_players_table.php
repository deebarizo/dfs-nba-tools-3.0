<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPMpAndPMpUiAndPDksSlashMpAndPDksSlashMpUiAndNoteColumnsToDkPlayersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('dk_players', function ($table) {
            
            $table->decimal('p_mp', 4, 2)->nullable()->after('ownership_percentage');
            $table->string('p_mp_ui')->nullable()->after('p_mp'); // 'ui' stands for 'update instructions'
            $table->decimal('p_dks_slash_mp', 4, 2)->nullable()->after('p_mp_ui');
            $table->string('p_dks_slash_mp_ui')->nullable()->after('p_dks_slash_mp');
            
            $table->string('note')->nullable()->after('p_dk_pts');
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

            $table->dropColumn('p_mp');
            $table->dropColumn('p_mp_ui');
            $table->dropColumn('p_dks_slash_mp');
            $table->dropColumn('p_dks_slash_mp_ui');
            
            $table->dropColumn('note');
        });
    }
}
