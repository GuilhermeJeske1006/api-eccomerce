<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEnderecoRequest;
use App\Models\{Endereco, User};

class EnderecoController extends Controller
{
    /**
     *  @OA\POST(
     *      path="/api/endereco/criar",
     *      summary="Store address",
     *      tags={"Endereco"},
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"cep", "rua", "numero", "bairro", "cidade", "estado", "pais"},
     *              @OA\Property(property="cep", type="string", example="string"),
     *              @OA\Property(property="rua", type="string", example="sRua são Pedro"),
     *              @OA\Property(property="numero", type="integer", example=123),
     *              @OA\Property(property="bairro", type="string", example="string"),
     *              @OA\Property(property="cidade", type="string", example="string"),
     *              @OA\Property(property="estado", type="string", example="string"),
     *             @OA\Property(property="pais", type="string", example="Brasil"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      ),
     *  )
     */
    public function store(StoreEnderecoRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            Endereco::create($request->all());

            return response()->json(["message" => "endereco criado com sucesso"], 201);

        } catch (\Throwable $th) {
            return response()->json(["message" => "Erro ao criar endereco", "erro" => $th->getMessage()], 500);
        }

    }

    /**
     *  @OA\PUT(
     *      path="/api/endereco/editar/{id}",
     *      summary="Update address",
     *      tags={"Endereco"},
     *      @OA\Parameter(
     *          name="id",
     *          in="path",
     *          description="ID of address to update",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              required={"cep", "rua", "numero", "bairro", "cidade", "estado", "pais"},
     *              @OA\Property(property="cep", type="string", example="12345678"),
     *              @OA\Property(property="rua", type="string", example="Rua Exemplo"),
     *              @OA\Property(property="numero", type="string", example="123"),
     *              @OA\Property(property="bairro", type="string", example="Bairro Exemplo"),
     *              @OA\Property(property="cidade", type="string", example="Cidade Exemplo"),
     *              @OA\Property(property="estado", type="string", example="Estado Exemplo"),
     *              @OA\Property(property="pais", type="string", example="Brasil"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      ),
     *  )
     */
    public function update(StoreEnderecoRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {

            $endereco = Endereco::find($id);

            if (!$endereco) {
                return response()->json(["message" => "Endereco não encontrado"], 404);
            }

            $endereco->update($request->all());

            if($request->usuario_id){
                User::find($request->usuario_id)->update(['endereco_id' => $endereco->id]);
            }

            return response()->json(["message" => "Endereco atualizado com sucesso"], 200);
        } catch (\Exception $e) {
            return response()->json(["message" => "Erro ao validar dados", 'erro' => $e->getMessage()], 400);
        }
    }

}
