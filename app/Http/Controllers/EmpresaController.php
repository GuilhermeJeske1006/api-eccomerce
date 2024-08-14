<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreEmpresaRequest;
use App\Http\Resources\EmpresaResource;
use App\Models\{Empresa, Endereco};
use Illuminate\Http\{Response};
use Illuminate\Support\Facades\{DB, Storage};

class EmpresaController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/empresa/criar",
     *     summary="Cria uma nova empresa",
     *     tags={"Empresa"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"nome", "email", "cor", "endereco"},
     *                 @OA\Property(property="nome", type="string", example="Minha Empresa"),
     *                 @OA\Property(property="email", type="string", format="email", example="empresa@example.com"),
     *                 @OA\Property(property="cnpj", type="string", example="00.000.000/0000-00"),
     *                 @OA\Property(property="cor", type="string", example="azul"),
     *                 @OA\Property(property="logo", type="", format="binary"),
     *                 @OA\Property(property="whatsapp", type="string", example="+55 11 99999-9999"),
     *                 @OA\Property(property="instagram", type="string", example="minhaempresa_insta"),
     *                 @OA\Property(property="facebook", type="string", example="minhaempresa_face"),
     *                 @OA\Property(property="telefone", type="string", example="+55 11 8888-8888"),
     *                 @OA\Property(property="descricao", type="string", example="Descrição da minha empresa"),
     *                 @OA\Property(property="palavras_chaves", type="string", example="empresa, negócios, produtos"),
     *                 @OA\Property(property="titulo", type="string", example="Título da minha empresa"),
     *                 @OA\Property(
     *                     property="endereco",
     *                     type="object",
     *                     required={"rua", "cidade", "estado", "cep", "pais", "numero", "bairro"},
     *                     @OA\Property(property="rua", type="string", example="Rua da Empresa"),
     *                     @OA\Property(property="cidade", type="string", example="São Paulo"),
     *                     @OA\Property(property="estado", type="string", example="SP"),
     *                     @OA\Property(property="cep", type="string", example="01000-000"),
     *                     @OA\Property(property="pais", type="string", example="Brasil"),
     *                     @OA\Property(property="numero", type="string", example="123"),
     *                     @OA\Property(property="bairro", type="string", example="Centro"),
     *                     @OA\Property(property="complemento", type="string", example="Sala 101")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Empresa cadastrada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Empresa cadastrada com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro de validação nos campos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno no servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao cadastrar empresa")
     *         )
     *     )
     * )
     */
    public function store(StoreEmpresaRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $empresa = $request->validated();

            DB::beginTransaction();

            $endereco = Endereco::create($empresa['endereco']);

            $empresa['endereco_id'] = $endereco->id;

            if (!is_null($empresa['logo'])) {
                $empresa['logo'] = uploadBase64ImageToS3($empresa['logo'], 'empresas');
            }

            Empresa::create($empresa);

            DB::commit();

            return response()->json(["message" => "Empresa cadastrada com sucesso"], 201);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json(["message" => "Erro ao cadastrar empresa", 'erro' => $th->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/empresa/{id}",
     *     summary="Get empresa by id",
     *     tags={"Empresa"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the empresa",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="nome", type="string"),
     *             @OA\Property(property="endereco", type="object",
     *                 @OA\Property(property="logradouro", type="string"),
     *                 @OA\Property(property="cidade", type="string"),
     *                 @OA\Property(property="estado", type="string"),
     *                 @OA\Property(property="cep", type="string"),
     *                 @OA\Property(property="pais", type="string")
     *             ),
     *             @OA\Property(property="logo", type="string"),
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Empresa não encontrada"
     *     )
     * )
     */
    public function show(string $id): \Illuminate\Http\JsonResponse
    {
        $empresa = Empresa::find($id);

        if(!$empresa) {
            return response()->json(["message" => "Empresa não encontrada"], 404);
        }

        if(isset($empresa['logo'])) {
            $empresa['logo'] = Storage::disk('s3')->url($empresa['logo']);
        }

        return response()->json(EmpresaResource::make($empresa));
    }

    /**
     * @OA\Put(
     *     path="/api/empresa/editar/{id}",
     *     summary="Atualiza uma empresa existente",
     *     tags={"Empresa"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID da empresa a ser atualizada",
     *         required=true,
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"nome", "email", "cor", "endereco"},
     *                 @OA\Property(property="nome", type="string", example="Nova Empresa"),
     *                 @OA\Property(property="email", type="string", format="email", example="novaempresa@example.com"),
     *                 @OA\Property(property="cor", type="string", example="vermelho"),
     *                 @OA\Property(property="logo", type="string", example="base64-encoded-image-data"),
     *                 @OA\Property(property="whatsapp", type="string", example="+55 11 99999-9999"),
     *                 @OA\Property(property="instagram", type="string", example="novaempresa_insta"),
     *                 @OA\Property(property="facebook", type="string", example="novaempresa_face"),
     *                 @OA\Property(property="telefone", type="string", example="+55 11 8888-8888"),
     *                 @OA\Property(property="descricao", type="string", example="Nova descrição da empresa"),
     *                 @OA\Property(property="palavras_chaves", type="string", example="novaempresa, negócios, produtos"),
     *                 @OA\Property(property="titulo", type="string", example="Novo título da empresa"),
     *                 @OA\Property(
     *                     property="cnpj",
     *                     type="string",
     *                     example="12345678901234",
     *                     description="Número do CNPJ da empresa"
     *                 ),
     *                 @OA\Property(
     *                     property="endereco",
     *                     type="object",
     *                     required={"id", "rua", "cidade", "estado", "cep", "pais", "numero", "bairro"},
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="rua", type="string", example="Nova Rua da Empresa"),
     *                     @OA\Property(property="cidade", type="string", example="São Paulo"),
     *                     @OA\Property(property="estado", type="string", example="SP"),
     *                     @OA\Property(property="cep", type="string", example="02000-000"),
     *                     @OA\Property(property="pais", type="string", example="Brasil"),
     *                     @OA\Property(property="numero", type="string", example="456"),
     *                     @OA\Property(property="bairro", type="string", example="Centro"),
     *                     @OA\Property(property="complemento", type="string", example="Sala 202")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Empresa atualizada com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Empresa atualizada com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro de validação",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro de validação nos campos")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Empresa não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Empresa não encontrada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno no servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao atualizar empresa")
     *         )
     *     )
     * )
     */
    public function update(StoreEmpresaRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $empresa = Empresa::find($id);

            if (!$empresa) {
                return response()->json(["message" => "Empresa não encontrada"], 404);
            }

            DB::beginTransaction();

            $endereco = Endereco::find($request['endereco']['id']);

            if (!$endereco) {
                DB::rollBack();

                return response()->json(["message" => "Endereço não encontrado"], 404);
            }

            if ($request['logo'] != "") {
                // deleteImageFromS3($empresa->logo); // Uncomment if you handle image deletion
                $request['logo'] = uploadBase64ImageToS3($request['logo'], 'empresas');
            }

            $endereco->update($request['endereco']);

            DB::commit();

            return response()->json(["message" => "Empresa atualizada com sucesso"], 200);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json(["message" => "Erro ao atualizar empresa", 'erro' => $th->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/empresa/delete/{id}",
     *     summary="Delete empresa by id",
     *     tags={"Empresa"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="ID of the empresa",
     *         required=true,
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Empresa excluída com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Empresa excluída com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Empresa não encontrada",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Empresa não encontrada")
     *         )
     *     )
     * )
     */

    public function destroy(int $id): \Illuminate\Http\JsonResponse
    {
        try {
            $empresa = Empresa::find($id);

            if (!$empresa) {
                return response()->json(["message" => "Empresa não encontrada"], 404);
            }

            $empresa->delete();

            return response()->json(["message" => "Empresa excluída com sucesso"], 200);
        } catch (\Throwable $th) {
            return response()->json(["message" => "Erro ao excluir empresa", 'erro' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

    }
}
