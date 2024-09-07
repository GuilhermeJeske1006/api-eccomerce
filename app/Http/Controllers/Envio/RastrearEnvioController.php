<?php

namespace App\Http\Controllers\Envio;

use App\Http\Controllers\Controller;
use App\Models\{Empresa, EnvioPedido};
use App\Services\EnvioService;
use Illuminate\Http\Request;

class RastrearEnvioController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/envio/rastrear",
     *     summary="Rastreia e atualiza o status dos envios com base nos IDs fornecidos",
     *     tags={"Envio"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="pedidos",
     *                 type="array",
     *                 @OA\Items(type="string"),
     *                 description="Array de IDs dos pedidos"
     *             ),
     *             example={
     *                 "pedidos": {
     *                     "9cc21d73-c2a8-4c17-b931-96d49fc0b81c"
     *                 }
     *             }
     *         ),
     *         @OA\Property(property="empresa_id", type="integer", example=1)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Status dos envios rastreados e atualizados com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="codigo_rastreio", type="string", example="ABC123"),
     *                     @OA\Property(property="status", type="string", example="Entregue")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao rastrear envios",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao rastrear envio"),
     *             @OA\Property(property="error", type="string", example="Mensagem de erro detalhada")
     *         )
     *     )
     * )
     */
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $pedidos = $request->input('pedidos', []);

            if (!is_array($pedidos) || empty($pedidos)) {
                throw new \Exception('Pedido inválido');
            }

            $empresa = Empresa::findOrFail($request->empresa_id);

            $pedido = EnvioService::rastrearEnvio($pedidos, $empresa);

            $erroPedidos = [];

            foreach ($pedido as $key => $item) {
                $envioPedido = EnvioPedido::where('codigo_rastreio', $key)->first();

                if ($envioPedido) {
                    $envioPedido->update([
                        'status' => $item['status'],
                    ]);
                } else {
                    $erroPedidos[] = "EnvioPedido com o id {$key} não encontrado.";
                }
            }

            if (!empty($erroPedidos)) {
                return response()->json([
                    'message' => 'Erro ao rastrear envio',
                    'errors'  => $erroPedidos,
                ], 400);
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
