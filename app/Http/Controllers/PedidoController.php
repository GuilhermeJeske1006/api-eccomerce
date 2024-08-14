<?php

namespace App\Http\Controllers;

use App\Http\Resources\PedidoResource;
use App\Models\{ItemPedido, Pedido};

class PedidoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/pedidos/{id}",
     *     summary="Lista pedidos por usuário ou empresa",
     *     description="Retorna uma lista de pedidos com base no usuário ou empresa fornecidos.",
     *     operationId="getPedidos",
     *     tags={"Pedidos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do usuário",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),

     *     @OA\Response(
     *         response=200,
     *         description="Lista de pedidos",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pedidos não encontrados"
     *     )
     * )
     */

    public function index(string $id): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $pedidos = Pedido::where('usuario_id', $id)->get();

        return PedidoResource::collection($pedidos);
    }

    /**
     * @OA\Get(
     *     path="/api/pedido/{id}",
     *     summary="Mostra um pedido específico pelo ID",
     *     description="Retorna os detalhes de um pedido específico com base no ID fornecido.",
     *     tags={"Pedidos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do pedido",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do pedido",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pedido não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Pedido não encontrado"
     *             )
     *         )
     *     )
     * )
     */

    /**
     * Display the specified resource.
     *
     * @param string $id
     * @return PedidoResource
     */
    public function show(string $id): PedidoResource
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return new PedidoResource(null);
        }

        $pedido = ItemPedido::montarItemPedido($pedido);

        return new PedidoResource($pedido);
    }

    /**
     * @OA\Patch(
     *     path="/api/pedido/{id}",
     *     summary="Update the status of a pedido",
     *     description="Updates the status of a pedido to 'PAID' by its ID",
     *     operationId="updatePedidoStatus",
     *     tags={"Pedidos"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the pedido to update",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Pedido updated successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Pedido atualizado com sucesso"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Pedido not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Pedido não encontrado"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error updating the pedido",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Erro ao atualizar pedido"
     *             )
     *         )
     *     ),
     *     security={
     *         {"api_key": {}}
     *     }
     * )
     */

    public function update(string $id): \Illuminate\Http\JsonResponse
    {
        $pedido = Pedido::find($id);

        $pedido->status = 'PAID';
        $pedido->save();

        return response()->json(['message' => 'Pedido atualizado com sucesso'], 200);
    }

}
