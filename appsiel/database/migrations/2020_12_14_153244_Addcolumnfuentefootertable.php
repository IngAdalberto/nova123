<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Addcolumnfuentefootertable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pw_footer', function (Blueprint $table) {
            $table->unsignedInteger('configuracionfuente_id')->after('color')->nullable();
            $table->foreign('configuracionfuente_id')->references('id')->on('pw_configuracionfuentes')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pw_footer', function (Blueprint $table) {
            $table->dropColumn('configuracionfuente_id');
        });
    }
}
