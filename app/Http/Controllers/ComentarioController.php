<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreComentarioRequest;
use App\Models\Comentario;

class ComentarioController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/comentario/criar",
     *     operationId="storeComentario",
     *     tags={"Comentario"},
     *     summary="Create a new comentario",
     *     description="Creates a new comentario associated with a produto and usuario",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"descricao", "estrela", "produto_id", "usuario_id"},
     *                 @OA\Property(property="descricao", type="string", example="Ótimo produto! Recomendo."),
     *                 @OA\Property(property="estrela", type="integer", example=5),
     *                 @OA\Property(property="produto_id", type="integer", example=1),
     *                 @OA\Property(property="usuario_id", type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Comentario criado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Comentario criado com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro de validação"),
     *             @OA\Property(property="errors", type="object", example={"descricao": {"A descrição é obrigatória."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao criar comentario",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao criar comentario"),
     *             @OA\Property(property="erro", type="string", example="Mensagem de erro detalhada")
     *         )
     *     ),
     *     security={
     *         {"api_key": {}}
     *     }
     * )
     */
    public function store(StoreComentarioRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            Comentario::create($request->all());

            return response()->json(["message" => "Comentario criado com sucesso"], 201);
        } catch (\Throwable $th) {
            return response()->json(["message" => "Erro ao criar comentario", 'erro' => $th->getMessage()], 500);
        }
    }

}
