<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePembeliansTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreignId('supplier_id')->references('id')->on('suppliers')->cascadeOnDelete();
            $table->foreignId('perusahaan_id')->references('id')->on('perusahaans')->cascadeOnDelete();
            $table->foreignId('bank_id')->references('id')->on('banks')->cascadeOnDelete();
            $table->string('no_faktur')->nullable();
            $table->double('hutang')->default(0);
            $table->timestamp('tgl_tempo')->nullable();
            $table->timestamp('valid')->nullable();
            $table->enum('status',['Preorder','Batal','Finish'])->default('Preorder');
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
        Schema::dropIfExists('pembelians');
    }
}
