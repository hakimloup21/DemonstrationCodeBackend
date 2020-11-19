<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBonSortieMagDetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bon_sortie_mag_dets', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('idbon')->unsigned(); //unsigned bessif
            $table->integer('numprod');
            $table->float('qteAchat');
            $table->float('prixAchat');
            $table->timestamps();

            $table->foreign('idbon')->references('id')->on('bon_sortie_mags')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bon_sortie_mag_dets');
    }
}
