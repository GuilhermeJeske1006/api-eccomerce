<?php

namespace App\Http\Controllers\Envio;

use App\Http\Controllers\Controller;
use App\Models\EnvioPedido;
use App\Services\EnvioService;
use Illuminate\Http\Request;

class PagarEnvioController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        try {
            if(!is_array($request->pedidos)){
              throw new \Exception('Pedido inválido');  
            }              

            $pedido = EnvioService::pegarEnvio($request->pedidos);

            $orders = $pedido['purchase']['orders'];

            foreach ($orders as $order) {
                $envioPedido = EnvioPedido::where('codigo_rastreio', $order['id'])->first();

                if ($envioPedido) {
                    $envioPedido->update([
                        'status' => $order['status']
                    ]);
                } else {
                    throw new \Exception("EnvioPedido com o id {$order['id']} não encontrado.");
                }
            }


            return response()->json([
                'message' => 'Envio pago com sucesso',
                'data' => $pedido
            ], 200);
        
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao pagar envio',
                'error' => $th->getMessage()
            ], 400);
        }
    }
}
