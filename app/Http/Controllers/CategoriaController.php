<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Http\Resources\CategoriaResource;

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
    public function index()
    {
        return CategoriaResource::collection(Categoria::all());
    }

}
