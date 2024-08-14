<?php

namespace App\Observers;

use App\Jobs\EnviarEmailPedido;
use App\Models\{ItemPedido, Pedido, User};
use App\Services\EnvioService;
use Illuminate\Support\Facades\{Log};

class PedidoObserver
{
    /**
     * Handle the Pedido "updated" event.
     */
    public function updated(Pedido $pedido): void
    {
        // Log::info('Pedido atualizado', $pedido->toArray());

        if($pedido->status == 'PAID') {

            $usuario = User::find($pedido->usuario_id);

            $queryPedido = ItemPedido::montarItemPedido($pedido);

            Log::info('Pedido atualizado', ['pedido' => $queryPedido]);

            EnvioService::inserirFretesCarrinho($queryPedido);

            if($usuario->email) {
                EnviarEmailPedido::dispatch($usuario, $queryPedido);
            }
        }

    }

}
