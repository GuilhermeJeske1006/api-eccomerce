<?php

use App\Models\StatusEnvio;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('status_envios', function (Blueprint $table) {
            $table->id();
            $table->string('nome_status', 50);
            $table->timestamps();
        });

        Schema::table('envio_pedidos', function (Blueprint $table) {
            $table->foreignId('status_envio_id')->constrained('status_envios');
        });

        StatusEnvio::create(['nome_status' => 'pending']);
        StatusEnvio::create(['nome_status' => 'released']);
        StatusEnvio::create(['nome_status' => 'posted']);
        StatusEnvio::create(['nome_status' => 'delivered']);
        StatusEnvio::create(['nome_status' => 'canceled']);
        StatusEnvio::create(['nome_status' => 'undelivered']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('envio_pedidos', function (Blueprint $table) {
            $table->dropForeign(['status_envio_id']);
            $table->dropColumn('status_envio_id');
        });

        Schema::dropIfExists('status_envios');
    }
};
