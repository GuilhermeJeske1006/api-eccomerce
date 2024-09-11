<?php

namespace App\Http\Controllers;

use App\Http\Resources\PedidoResource;
use App\Models\{Pedido};
use Illuminate\Http\{Request};

class GestaoPedidoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/pedidos/gestao/{empresa_id}",
     *     summary="Obtém uma lista paginada de pedidos de uma empresa",
     *     tags={"Pedidos"},
     *     @OA\Parameter(
     *         name="empresa_id",
     *         in="path",
     *         description="ID da empresa cujos pedidos serão buscados",
     *         required=true,
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista de pedidos retornada com sucesso",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao buscar pedidos",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao buscar pedidos"),
     *             @OA\Property(property="erro", type="string", example="Detalhes do erro...")
     *         )
     *     )
     * )
     * Exibe uma lista paginada de pedidos para a empresa especificada.
     *
     * @param  int  $empresa_id  O ID da empresa cujos pedidos serão buscados.
     * @return \Illuminate\Http\JsonResponse Retorna uma coleção de recursos de pedidos ou uma mensagem de erro em caso de falha.
     */
    public function index(int $empresa_id, Request $request)
    {
        try {
            $pedidos = Pedido::queryBuscaPedido($request->all(), $empresa_id);

            return PedidoResource::collection($pedidos)->response()->setStatusCode(200);

        } catch (\Throwable $th) {
            return PedidoResource::collection(null)->response()->setStatusCode(500, $th->getMessage());
        }
    }

}
