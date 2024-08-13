<?php

namespace App\Http\Controllers\Frete;

use App\Http\Controllers\Controller;
use App\Models\Empresa;
use App\Models\Pedido;
use App\Models\Produto;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

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
    public function __invoke(Request $request)
    {
        try {
            $response = $this->calcularFrete(
                $request->cep_destino,
                $request->produtos,
                $request->empresa_id
            );

            return response()->json(['data' => $response], 200);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    private function calcularFrete($cepDestino, $produtos, $empresaId)
    {
        try {

            $produtosDetalhes = Produto::whereIn('id', array_column($produtos, 'produtoId'))->get();

            
            $produtosFormatados = array_map(function ($produto) use ($produtosDetalhes) {
                $detalhes = $produtosDetalhes->firstWhere('id', $produto['produtoId']);
                return [
                    "width" => $detalhes->largura,
                    "height" => $detalhes->altura,
                    "length" => $detalhes->comprimento,
                    "weight" => $detalhes->peso,
                    "insurance_value" => 10.1,
                    "quantity" => $produto['quantidade']
                ];
            }, $produtos);


            $empresa = Empresa::find($empresaId);

            $body = [
                'from' => [
                    'postal_code' => $empresa->endereco->cep
                ],
                'to' => [
                    'postal_code' => $cepDestino
                ],
                'products' => $produtosFormatados
            ];

            $endpoint = env('API_MELHOR_ENVIO') . '/' . 'me/shipment/calculate';

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('TOKEN_MELHOR_ENVIO_SANBOX')
            ])->post($endpoint, $body);

            if ($response->failed()) {
                throw new \Exception($response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
