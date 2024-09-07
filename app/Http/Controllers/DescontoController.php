<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreDescontoRequest;
use App\Models\{DescontoProduto, Produto};
use App\Services\ProdutoService;

class DescontoController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/desconto",
     *     summary="Calcula o valor do desconto aplicado a um produto",
     *     tags={"Desconto"},
     *     @OA\Parameter(
     *         name="produto_id",
     *         in="query",
     *         description="ID do produto para o qual o desconto será calculado",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="porcentagem",
     *         in="query",
     *         description="Porcentagem do desconto a ser aplicado ao valor do produto. Se fornecido, o desconto é calculado como porcentagem.",
     *         required=false,
     *         @OA\Schema(
     *             type="number",
     *             format="float"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="desconto",
     *         in="query",
     *         description="Valor fixo do desconto a ser aplicado ao valor do produto. Se fornecido, o desconto é calculado como valor fixo.",
     *         required=false,
     *         @OA\Schema(
     *             type="number",
     *             format="float"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Desconto calculado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="valor", type="number", format="float", example=90.00, description="Valor do produto após aplicar o desconto")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao calcular desconto",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao calcular desconto!"),
     *             @OA\Property(property="error", type="string", example="Detalhes do erro")
     *         )
     *     )
     * )
     */
    public function index(StoreDescontoRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $produto = Produto::find($request->produto_id);

            if (!$produto) {
                return response()->json([
                    'message' => 'Produto não encontrado!',
                ], 404);
            }

            $valores = ProdutoService::calculaDesconto($produto, $request);

            return response()->json([
                'valor' => $valores,
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao calcular desconto!',
                'error'   => $th->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Post(
     *     path="/api/desconto",
     *     summary="Cadastra um novo desconto para um produto",
     *     tags={"Desconto"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"produto_id"},
     *                 @OA\Property(property="produto_id", type="integer", example=1, description="ID do produto ao qual o desconto será aplicado"),
     *                 @OA\Property(property="porcentagem", type="number", format="float", example=10.0, description="Porcentagem do desconto aplicado ao produto. Se fornecido, o desconto é calculado como porcentagem."),
     *                 @OA\Property(property="desconto", type="number", format="float", example=5.0, description="Valor fixo do desconto aplicado ao produto. Se fornecido, o desconto é calculado como valor fixo."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Desconto cadastrado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Desconto cadastrado com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao cadastrar desconto",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao cadastrar desconto!")
     *         )
     *     )
     * )
     */
    public function store(StoreDescontoRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $produto = Produto::find($request->produto_id);

            if (!$produto) {
                return response()->json([
                    'message' => 'Produto não encontrado!',
                ], 404);
            }

            $valorFinal = ProdutoService::calculaDesconto($produto, $request);

            if(DescontoProduto::where('produto_id', $request->produto_id)->exists()) {
                foreach (DescontoProduto::where('produto_id', $request->produto_id)->get() as $desconto) {
                    $desconto->delete();
                }
            }

            DescontoProduto::create([
                'produto_id'  => $request->produto_id,
                'porcentagem' => $request['porcentagem'],
                'desconto'    => $request['desconto'],
                'valor_final' => $valorFinal,
            ]);

            return response()->json([
                'message' => 'Desconto cadastrado com sucesso!',
            ], 201);
        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Erro ao cadastrar desconto!',
                'error'   => $th->getMessage(),
            ], 400);
        }

    }

    /**
     * @OA\Put(
     *     path="/api/desconto/{id}",
     *     summary="Atualiza um desconto existente",
     *     tags={"Desconto"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do desconto a ser atualizado",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"produto_id"},
     *                 @OA\Property(property="produto_id", type="integer", example=1, description="ID do produto ao qual o desconto é aplicado"),
     *                 @OA\Property(property="porcentagem", type="number", format="float", example=10.0, description="Porcentagem do desconto aplicado ao produto. Se fornecido, o desconto é calculado como porcentagem."),
     *                 @OA\Property(property="desconto", type="number", format="float", example=5.0, description="Valor fixo do desconto aplicado ao produto. Se fornecido, o desconto é calculado como valor fixo."),
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Desconto atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Desconto atualizado com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao atualizar desconto",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao atualizar desconto!"),
     *             @OA\Property(property="error", type="string", example="Detalhes do erro")
     *         )
     *     )
     * )
     */
    public function update(StoreDescontoRequest $request, DescontoProduto $descontoProduto): \Illuminate\Http\JsonResponse
    {
        try {
            $descontoProduto->update($request->all());

            return response()->json([
                'message' => 'Desconto atualizado com sucesso!',
            ]);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao atualizar desconto!',
                'error'   => $th->getMessage(),
            ], 400);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/desconto/{id}",
     *     summary="Remove um desconto existente",
     *     tags={"Desconto"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID do desconto a ser removido",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             example=1
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Desconto removido com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Desconto deletado com sucesso!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao remover desconto",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao deletar desconto!")
     *         )
     *     )
     * )
     */
    public function destroy(int $produto): \Illuminate\Http\JsonResponse
    {
        try {

            $descontoProduto = DescontoProduto::where('produto_id', $produto)->first();

            $descontoProduto->delete();

            return response()->json([
                'message' => 'Desconto deletado com sucesso!',
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao deletar desconto!',
            ], 400);
        }
    }
}
