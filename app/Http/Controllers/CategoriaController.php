<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoriaResource;
use App\Models\Categoria;

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
        return CategoriaResource::collection(Categoria::all());
    }

}
