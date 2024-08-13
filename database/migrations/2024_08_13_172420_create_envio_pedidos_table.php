<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('envio_pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_rastreio')->unique();
            $table->string('status')->nullable();
            $table->string('agencia')->nullable();
            $table->string('servico')->nullable();
            $table->string('prazo')->nullable();
            $table->string('valor')->nullable();
            $table->unsignedBigInteger('pedido_id');
            $table->foreign('pedido_id')->references('id')->on('pedidos');
            $table->timestamps();
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->unsignedBigInteger('envio_id')->nullable();
            $table->foreign('envio_id')->references('id')->on('envio_pedidos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('envio_pedidos');
    }
};
