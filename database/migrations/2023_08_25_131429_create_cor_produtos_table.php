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
        Schema::create('cor_produtos', function (Blueprint $table) {
            $table->id();
            $table->string('cor');
            $table->unsignedBigInteger('produto_id');
            $table->foreign('produto_id')->references('id')->on('produtos')->onDelete("cascade");
            $table->timestamps();
        });

        Schema::table('tamanho_produtos', function (Blueprint $table) {
            $table->unsignedBigInteger('cor_id');
            $table->foreign('cor_id')->references('id')->on('cor_produtos')->onDelete("cascade");
        });

        Schema::table('item_pedidos', function (Blueprint $table) {
            $table->unsignedBigInteger('cor_id');
            $table->foreign('cor_id')->references('id')->on('cor_produtos')->onDelete("cascade");
            $table->unsignedBigInteger('tamanho_id');
            $table->foreign('tamanho_id')->references('id')->on('tamanho_produtos')->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tamanho_produtos', function (Blueprint $table) {
            $table->dropForeign(['cor_id']);
            $table->dropColumn('cor_id');
        });

        Schema::table('item_pedidos', function (Blueprint $table) {
            $table->dropForeign(['cor_id']);
            $table->dropColumn('cor_id');
            $table->dropForeign(['tamanho_id']);
            $table->dropColumn('tamanho_id');
        });

        Schema::dropIfExists('cor_produtos');
    }
};
