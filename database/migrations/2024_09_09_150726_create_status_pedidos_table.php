<?php

use App\Models\StatusPedido;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class () extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('status_pedidos', function (Blueprint $table) {
            $table->id();
            $table->string('nome_status', 50);
            $table->timestamps();
        });

        Schema::table('pedidos', function (Blueprint $table) {
            $table->foreignId('status_pedido_id')->constrained('status_pedidos');
        });

        StatusPedido::create(['nome_status' => 'WAITING_PAYMENT']);
        StatusPedido::create(['nome_status' => 'PAID']);
        StatusPedido::create(['nome_status' => 'IN_ANALYSIS']);
        StatusPedido::create(['nome_status' => 'DECLINED']);
        StatusPedido::create(['nome_status' => 'CANCELED']);
        StatusPedido::create(['nome_status' => 'WAITING']);
        StatusPedido::create(['nome_status' => 'EXPIRED']);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pedidos', function (Blueprint $table) {
            $table->dropForeign(['status_pedido_id']);
            $table->dropColumn('status_pedido_id');
        });
        Schema::dropIfExists('status_pedidos');
    }
};
