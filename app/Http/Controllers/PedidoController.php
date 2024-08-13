<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\PedidoResource;
use App\Models\ItemPedido;
use App\Models\Pedido;
use Illuminate\Http\Request;

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

    public function index($id)
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
    public function show(string $id)
    {
        $pedido = Pedido::find($id);

        if (!$pedido) {
            return response()->json(['message' => 'Pedido não encontrado'], 404);
        }

        $pedido = ItemPedido::montarItemPedido($pedido);

        return new PedidoResource($pedido);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pedido  = Pedido::find($id);

        $pedido->status = 'PAID';
        $pedido->save();

        return response()->json(['message' => 'Pedido atualizado com sucesso'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}