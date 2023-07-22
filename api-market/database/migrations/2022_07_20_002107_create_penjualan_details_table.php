<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePenjualanDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('penjualan_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('penjualan_id')->references('id')->on('penjualans')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('barang_id')->references('id')->on('barangs')->cascadeOnDelete()->cascadeOnUpdate();
            $table->double('harga')->default(0);
            $table->double('qty')->default(0);
            $table->string('satuan')->nullable();
            $table->double('diskon')->default(0);
            $table->double('ppn')->default(0);
            $table->string('catatan')->nullable();
            $table->double('satuan_isi')->default(1);
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
        Schema::dropIfExists('penjualan_details');
    }
}
