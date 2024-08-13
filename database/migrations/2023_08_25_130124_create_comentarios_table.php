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
        Schema::create('comentarios', function (Blueprint $table) {
            $table->id();
            $table->text("descricao");
            $table->integer('estrela')->nullable();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedBigInteger('produto_id');
            $table->foreign('usuario_id')->references('id')->on('users')->onDelete("cascade");
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete("cascade");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comentarios');
    }
};
