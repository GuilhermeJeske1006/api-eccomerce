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
        Schema::create('produtos', function (Blueprint $table) {
            $table->id();
            $table->string("nome", 100);
            $table->decimal("valor", 10, 2);
            $table->string("foto")->nullable();
            $table->string("descricao", 255)->nullable();
            $table->longText("descricao_longa")->nullable();
            $table->string("peso")->nullable();
            $table->string('largura')->nullable();
            $table->string('altura')->nullable();
            $table->string('comprimento')->nullable();
            $table->string("material", 50)->nullable();
            $table->string("cupom", 50)->nullable();
            $table->timestamps();

            $table->unsignedBigInteger('empresa_id');
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete("cascade");

            $table->unsignedBigInteger('categoria_id');
            $table->foreign('categoria_id')->references('id')->on('categorias')->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('produtos');
    }
};
