<?php

namespace App\Http\Controllers\Envio;

use App\Http\Controllers\Controller;
use App\Models\EnvioPedido;
use App\Services\EnvioService;
use Illuminate\Http\Request;

class RastrearEnvioController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request): object
    {
        try {
            if(!is_array($request->pedidos)) {
                throw new \Exception('Pedido inválido');
            }

            $pedido = EnvioService::rastrearEnvio($request->pedidos_id);

            foreach ($pedido as $key => $item) {
                $envioPedido = EnvioPedido::where('codigo_rastreio', $key)->first();

                if ($envioPedido) {
                    $envioPedido->update([
                        'status' => $item['status'],
                    ]);
                } else {
                    throw new \Exception("EnvioPedido com o id {$key} não encontrado.");
                }
            }

            return response()->json([
                'data' => $pedido,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao rastrear envio',
                'error'   => $th->getMessage(),
            ], 400);
        }
    }

}
