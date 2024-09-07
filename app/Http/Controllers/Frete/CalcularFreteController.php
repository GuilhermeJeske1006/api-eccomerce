<?php

namespace App\Http\Controllers\Frete;

use App\Http\Controllers\Controller;
use App\Models\{Empresa};
use App\Services\EnvioService;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Redis;

class CalcularFreteController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/calculate-frete",
     *     summary="Calcular Frete",
     *     tags={"Frete"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"cep_destino", "produtos", "empresa_id"},
     *             @OA\Property(property="cep_destino", type="string", example="12345678"),
     *             @OA\Property(
     *                      property="produtos",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="produtoId", type="integer", example="2"),
     *                          @OA\Property(property="quantidade", type="integer", example=1),

     *                      )
     *                  ),
     *             @OA\Property(property="empresa_id", type="integer", example=1)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Frete calculado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="data", type="array", @OA\Items(type="object"))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao calcular frete",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao calcular frete")
     *         )
     *     )
     * )
     */
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'cep_destino' => 'required|string',
                'produtos'    => 'required|array',
                'empresa_id'  => 'required|integer',
            ]);

            $cacheKey = sprintf(
                "frete_%s_%s_%d",
                $request->cep_destino,
                json_encode($request->produtos),
                $request->empresa_id
            );

            $cachedData = Redis::get($cacheKey);

            if ($cachedData) {
                return response()->json(['data' => json_decode($cachedData, true)], 200);
            }
            $response = EnvioService::calcularFrete(
                $request->cep_destino,
                $request->produtos,
                $request->empresa_id
            );

            Redis::setex($cacheKey, 3600, json_encode($response));

            return response()->json(['data' => $response], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

}
