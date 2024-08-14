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
        Schema::create('pedidos', function (Blueprint $table) {
            $table->id();
            $table->dateTime("dataPedido");
            $table->string('status');
            $table->string('reference');
            $table->decimal('vlr_frete')->nullable();
            $table->unsignedBigInteger('empresa_id');
            $table->unsignedBigInteger('usuario_id');
            $table->decimal('vlr_total')->nullable();
            $table->string('formaPagamento');
            $table->unsignedBigInteger('endereco_id');
            $table->foreign('endereco_id')->references('id')->on('enderecos')->onDelete("cascade");
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete("cascade");
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pedidos');
    }
};
