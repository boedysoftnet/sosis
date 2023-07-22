<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('perusahaan_id')->references('id')->on('perusahaans')->cascadeOnDelete()->cascadeOnUpdate();
            $table->foreignId('barang_id')->references('id')->on('barangs')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('kelompok')->nullable();
            $table->double('qty')->default(0);
            $table->text('catatan')->nullable();
            $table->morphs('stockhistoryable');
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
        Schema::dropIfExists('stock_histories');
    }
}
