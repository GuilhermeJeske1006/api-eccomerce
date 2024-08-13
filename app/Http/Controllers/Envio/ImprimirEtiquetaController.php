<?php

namespace App\Http\Controllers\Envio;

use App\Http\Controllers\Controller;
use App\Models\EnvioPedido;
use App\Services\EnvioService;
use Illuminate\Http\Request;

class ImprimirEtiquetaController extends Controller
{

    public function __invoke(Request $request)
    {
        try {
            // Verifique se o campo 'pedidos' é um array válido
            if (!is_array($request->pedidos)) {
                throw new \Exception('Pedido inválido');
            }

            // Chama o serviço para imprimir a etiqueta
            $pedido = EnvioService::imprimirEtiqueta($request->pedidos_id);

            // Retorna uma resposta de sucesso com os dados da etiqueta
            return response()->json([
                'message' => 'Etiqueta gerada com sucesso',
                'data' => $pedido
            ], 200);
        } catch (\Throwable $th) {
            // Captura qualquer exceção e retorna uma resposta de erro
            return response()->json([
                'message' => 'Erro ao gerar etiqueta',
                'error' => $th->getMessage()
            ], 400);
        }
    }
}
