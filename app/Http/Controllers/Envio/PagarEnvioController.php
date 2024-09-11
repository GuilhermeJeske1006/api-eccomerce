<?php

namespace App\Http\Controllers\Envio;

use App\Http\Controllers\Controller;
use App\Models\{Empresa, EnvioPedido};
use App\Services\EnvioService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redis;

class PagarEnvioController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/envio/pagar",
     *     summary="Processa o pagamento do envio para os pedidos",
     *     description="Este endpoint processa o pagamento do envio para os pedidos fornecidos. Ele atualiza o status do envio com base nas informações retornadas pelo serviço de envio.",
     *     operationId="pagarEnvio",
     *     tags={"Envio"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Lista de pedidos para os quais o envio será pago",
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
     *
     *
     *     @OA\Response(
     *         response=200,
     *         description="Envio pago com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Envio pago com sucesso"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Dados do envio processado"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao pagar envio",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Erro ao pagar envio"
     *             ),
     *             @OA\Property(
     *                 property="error",
     *                 type="string",
     *                 example="Detalhes do erro"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Dados inválidos",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Pedido inválido"
     *             )
     *         )
     *     )
     * )
     */

    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            if(!is_array($request->pedidos)) {
                throw new \Exception('Pedido inválido');
            }

            $cacheKey = "empresa_{$request->empresa_id}";

            $cachedData = Redis::get($cacheKey);

            if ($cachedData) {
                $empresa = json_decode($cachedData, true);

                $empresa = Empresa::hydrate([$empresa])->first();
            } else {
                $empresa = Empresa::where('id', $request->empresa_id)->first();

                if (!$empresa) {
                    return response()->json(['message' => 'Empresa não encontrada'], 404);
                }

                Redis::setex($cacheKey, 3600, $empresa->toJson());
            }

            $pedido = EnvioService::pegarEnvio($request->pedidos, $empresa);

            $orders = $pedido['purchase']['orders'];

            foreach ($orders as $order) {
                $envioPedido = EnvioPedido::where('codigo_rastreio', $order['id'])->first();

                if ($envioPedido) {
                    $status_envio_id = EnvioService::buscaStatusEnvio($order['status']);
                    $envioPedido->update([
                        'status_envio_id' => $status_envio_id,
                    ]);
                } else {
                    throw new \Exception("EnvioPedido com o id {$order['id']} não encontrado.");
                }
            }

            return response()->json([
                'message' => 'Envio pago com sucesso',
                'data'    => $pedido,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao pagar envio',
                'error'   => $th->getMessage(),
            ], 400);
        }
    }
}
