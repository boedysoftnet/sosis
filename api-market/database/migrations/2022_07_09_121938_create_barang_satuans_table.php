<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBarangSatuansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('barang_satuans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perusahaan_id')->references('id')->on('perusahaans')->cascadeOnDelete();
            $table->foreignId('barang_id')->references('id')->on('barangs')->cascadeOnDelete();
            $table->string('satuan')->nullable();
            $table->double('isi')->default(1);
            $table->double('harga')->default(0);
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
        Schema::dropIfExists('barang_satuans');
    }
}
