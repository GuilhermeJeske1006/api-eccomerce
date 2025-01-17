<?php

namespace App\Http\Controllers\Envio;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
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
     *     tags={"Envio"},
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
     *         ),
     *        @OA\Property(property="empresa_id", type="integer", example=1)
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
            $pedidos = $request->input('pedidos', []);

            if (!is_array($pedidos) || empty($pedidos)) {
                throw new \Exception('Pedido inválido');
            }
            $empresa = Empresa::findOrFail($request->empresa_id);

            $pedido = EnvioService::imprimirEtiqueta($pedidos, $empresa);

            return response()->json([
                'message' => 'Etiqueta gerada com sucesso',
                'data'    => $pedido,
            ], 200);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao gerar etiqueta',
                'error'   => $th->getMessage(),
            ], 400);
        }
    }
}
