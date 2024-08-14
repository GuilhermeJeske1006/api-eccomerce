<?php

namespace App\Http\Controllers\Envio;

use App\Http\Controllers\Controller;
use App\Services\EnvioService;
use Illuminate\Http\Request;

class ImprimirEtiquetaController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/envio/imprimir-etiqueta",
     *     summary="Gera etiquetas para os pedidos",
     *     description="Este endpoint gera etiquetas para os pedidos fornecidos.",
     *     operationId="imprimirEtiqueta",
     *     tags={"Envios"},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         description="Lista de pedidos para os quais as etiquetas serão geradas",
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
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Etiqueta gerada com sucesso",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Etiqueta gerada com sucesso"
     *             ),
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                 description="Dados da etiqueta gerada"
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao gerar etiqueta",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Erro ao gerar etiqueta"
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
            // Verifique se o campo 'pedidos' é um array válido
            if (!is_array($request->pedidos)) {
                throw new \Exception('Pedido inválido');
            }

            // Chama o serviço para imprimir a etiqueta
            $pedido = EnvioService::imprimirEtiqueta($request->pedidos_id);

            // Retorna uma resposta de sucesso com os dados da etiqueta
            return response()->json([
                'message' => 'Etiqueta gerada com sucesso',
                'data'    => $pedido,
            ], 200);
        } catch (\Throwable $th) {
            // Captura qualquer exceção e retorna uma resposta de erro
            return response()->json([
                'message' => 'Erro ao gerar etiqueta',
                'error'   => $th->getMessage(),
            ], 400);
        }
    }
}
