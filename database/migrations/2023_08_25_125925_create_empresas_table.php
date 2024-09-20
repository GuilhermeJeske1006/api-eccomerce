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
        Schema::create('empresas', function (Blueprint $table) {
            $table->id();
            $table->string('nome', 100);
            $table->string('logo')->nullable();
            $table->string('email');
            $table->string('whatsapp')->nullable();
            $table->string('instagram')->nullable();
            $table->string('facebook')->nullable();
            $table->string('telefone')->nullable();
            $table->string('cor');
            $table->string('email_melhor_envio')->nullable();
            $table->longText('token_melhor_envio')->nullable();
            $table->mediumText('token_pagseguro')->nullable();
            $table->string('cnpj')->unique();
            $table->text('descricao')->nullable();
            $table->string('palavras_chaves')->nullable();
            $table->string('titulo')->nullable();
            $table->unsignedBigInteger('endereco_id')->nullable();
            $table->foreign('endereco_id')->references('id')->on('enderecos')->onDelete("cascade");
            $table->timestamps();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('empresa_id')->nullable();
            $table->foreign('empresa_id')->references('id')->on('empresas')->onDelete("cascade");
            ;
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['empresa_id']);
            $table->dropColumn('empresa_id');
        });

        Schema::dropIfExists('empresas');
    }
};
