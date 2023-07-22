<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKasTransaksisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kas_transaksis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->references('id')->on('perusahaans')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('bank_id')->references('id')->on('banks')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('akun_perkiraan_id')->references('id')->on('akun_perkiraans')->cascadeOnDelete()->cascadeOnUpdate();
            $table->longText('catatan')->nullable();
            $table->double('nominal')->nullable();
            $table->softDeletes();
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
        Schema::dropIfExists('kas_transaksis');
    }
}
