<?php

namespace App\Http\Controllers\Endereco;

use App\Http\Controllers\Controller;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\{Http, Redis};

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

            $cacheKey   = "cep_{$cep}";
            $cachedData = Redis::get($cacheKey);

            if ($cachedData) {
                return response()->json([
                    'data' => json_decode($cachedData, true),
                ], HttpResponse::HTTP_OK);
            }

            $response = Http::get("https://viacep.com.br/ws/{$cep}/json");

            if ($response->failed()) {
                return response()->json([
                    'message' => 'Erro ao buscar CEP',
                ], HttpResponse::HTTP_BAD_REQUEST);
            }

            $cepData = $response->json();

            Redis::setex($cacheKey, 86400, json_encode($cepData));

            // Retornar a resposta
            return response()->json([
                'data' => $cepData,
            ], HttpResponse::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao buscar CEP',
                'error'   => $th->getMessage(),
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
