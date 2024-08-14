<?php

namespace App\Http\Controllers\Endereco;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Http;

class ViaCepController extends Controller
{
    /**
     * @OA\GET(
     *     path="/api/endereco/cep/{cep}",
     *     summary="Recebe um cep e retorna informações do CEP",
     *     tags={"Endereco"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cep"},
     *             @OA\Property(property="cep", type="string", example="01001000", description="CEP do endereço")
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Dados do CEP",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="object", description="Dados retornados da API ViaCEP")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="CEP não informado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="CEP não informado")
     *         )
     *     )
     * )
     */
    public function __invoke(string $cep): \Illuminate\Http\JsonResponse
    {
        try {
            if (!$cep) {
                return response()->json([
                    'message' => 'CEP não informado',
                ], HttpResponse::HTTP_BAD_REQUEST);
            }

            $response = Http::get("viacep.com.br/ws/{$cep}/json");

            return response()->json([
                'data' => $response->json(),
            ], HttpResponse::HTTP_OK);
        } catch (\Throwable $th) {

            return response()->json([
                'message' => 'Erro ao buscar CEP',
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
