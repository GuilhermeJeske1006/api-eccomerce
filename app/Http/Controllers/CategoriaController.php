<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;
use Illuminate\Support\Facades\Redis;

class CategoriaController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/categorias",
     *     operationId="getCategorias",
     *     tags={"Categoria"},
     *     summary="Get list of categorias",
     *     description="Returns a list of categorias",
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Resource not found"
     *     ),
     * )
     */
    public function index(): \Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        $cacheKey = "todas_categorias";

        $cachedData = Redis::get($cacheKey);

        if ($cachedData) {
            $categorias = json_decode($cachedData, true);

            return CategoriaResource::collection(collect($categorias));
        }
        $categorias = Categoria::all();

        Redis::setex($cacheKey, 3600, $categorias->toJson());

        return CategoriaResource::collection($categorias);
    }

}
