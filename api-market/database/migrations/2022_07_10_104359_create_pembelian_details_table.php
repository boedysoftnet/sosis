<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembelianDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pembelian_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pembelian_id')->references('id')->on('pembelians')->cascadeOnDelete();
            $table->foreignId('barang_id')->references('id')->on('barangs')->cascadeOnDelete();
            $table->double('harga')->default(0);
            $table->string('satuan')->nullable();
            $table->double('ppn')->default(0);
            $table->double('diskon')->default(0);
            $table->string('catatan')->nullable();
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
        Schema::dropIfExists('pembelian_details');
    }
}
