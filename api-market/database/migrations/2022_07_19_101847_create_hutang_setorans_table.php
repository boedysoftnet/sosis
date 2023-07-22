<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateHutangSetoransTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('hutang_setorans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('pembelian_id')->references('id')->on('pembelians')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('bank_id')->references('id')->on('banks')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('perusahaan_id')->references('id')->on('perusahaans')->cascadeOnDelete()->cascadeOnUpdate();
            $table->double('jumlah')->default(0);
            $table->text('penerima')->nullable();
            $table->text('catatan')->nullable();
            $table->timestamp('tgl_setoran')->nullable();
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
        Schema::dropIfExists('hutang_setorans');
    }
}
