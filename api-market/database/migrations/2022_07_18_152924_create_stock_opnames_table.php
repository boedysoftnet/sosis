<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockOpnamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_opnames', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('perusahaan_id')->references('id')->on('perusahaans')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('barang_id')->references('id')->on('barangs')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('satuan')->nullable();
            $table->double('stock_sebelumnya')->default(0);
            $table->double('qty')->default(0);
            $table->double('harga')->default(0);
            $table->string('alasan')->nullable();
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
        Schema::dropIfExists('stock_opnames');
    }
}
