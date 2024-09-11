<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('envio_pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('codigo_rastreio')->unique();
            $table->integer('agencia')->nullable();
            $table->integer('servico')->nullable();
            $table->string('prazo')->nullable();
            $table->decimal('valor', 10)->nullable();
            $table->unsignedBigInteger('pedido_id');
            $table->foreign('pedido_id')->references('id')->on('pedidos');
            $table->timestamps();
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
