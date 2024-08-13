<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\TamanhoResource;
use App\Models\TamanhoProduto;
use Illuminate\Http\Request;

class TamanhoController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     * @OA\Get(
     *      path="/api/tamanhos-para-cor/{cor_id}/{produto_id}",
     *      operationId="getTamanho",
     *      tags={"Tamanho"},
     *      summary="Get list of tamanhos",
     *      description="Returns list of tamanhos",
     *      @OA\Parameter(
     *          name="cor_id",
     *          in="path",
     *          description="ID of the cor",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="produto_id",
     *          in="path",
     *          description="ID of the produto",
     *          required=true,
     *          @OA\Schema(
     *              type="integer"
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent(
     *              type="array",
     *              @OA\Items(
     *                  type="object",
     *                  @OA\Property(property="id", type="integer"),
     *                  @OA\Property(property="tamanho", type="string"),
     *                  @OA\Property(property="qtdTamanho", type="integer"),
     *                  @OA\Property(property="cor_id", type="integer"),
     *                  @OA\Property(property="produto_id", type="integer")
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Resource not found"
     *      ),
     *      security={
     *          {"api_key": {}}
     *      }
     * )
     */
    public function index($cor_id = null, $produto_id = null)
    {
        if ($cor_id && $produto_id) {
            $tamanhos = TamanhoProduto::where('cor_id', $cor_id)
                ->where('produto_id', $produto_id)
                ->get();
        } else {
            $tamanhos = TamanhoProduto::all();
        }

        return TamanhoResource::collection($tamanhos);
    }
}
