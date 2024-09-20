<?php

namespace App\Http\Controllers\Produto;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProdutoResource;
use App\Models\Produto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DestaqueProdutoController extends Controller
{
    /**
     *  @OA\Get(
     *      path="/api/produto/destaque/{empresa_id}",
     *      tags={"Produto"},
     *      summary="Retorna uma lista de produtos em destaque",
     *      description="Retorna uma lista de produtos em destaque",
     *      operationId="DestaqueProdutoController",
     *      @OA\Parameter(
     *          name="empresa_id",
     *          in="path",
     *          description="Id da empresa",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="OK",
     *          @OA\MediaType(
     *              mediaType="application/json"
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Nenhum produto encontrado"
     *      )
     *  )
     */
    public function __invoke(string $empresa_id = null, Request $request): JsonResponse
    {
        try {
            $produtos = Produto::where('empresa_id', $empresa_id)
                ->where('destaque', true)
                ->where('ativo', true)
                ->where('IrParaSite', true)
                ->limit(10)
                ->orderBy('created_at', 'desc')
                ->get();

            foreach ($produtos as $prod) {
                if ($prod->foto) {
                    $prod->foto = Storage::disk('s3')->url($prod->foto);
                }
            }

            return ProdutoResource::collection($produtos)->response()->setStatusCode(Response::HTTP_OK);

        } catch (\Exception $e) {
            Log::error('Erro ao buscar produtos: ', ['error' => $e]);

            return ProdutoResource::collection([])->response()->setStatusCode(Response::HTTP_NOT_FOUND);
        }
    }
}
