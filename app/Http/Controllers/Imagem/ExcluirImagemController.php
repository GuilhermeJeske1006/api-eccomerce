<?php

namespace App\Http\Controllers\Imagem;

use App\Http\Controllers\Controller;
use App\Models\ImagemProduto;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\Redis;

class ExcluirImagemController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        try {
            $img = ImagemProduto::where('imagem', $request->imagem)->first();

            if (!$img) {
                return response()->json(['message' => 'Imagem não encontrada.'], 404);
            }

            $cacheKey = "empresa_{$request->empresa_id}_produto_{$img->produto_id}";

            if (Redis::exists($cacheKey)) {
                Redis::del($cacheKey);
            }

            $img->delete();

            deleteImageFromS3($request->imagem);

            return response()->json(['message' => 'Imagem excluída com sucesso.'], 200);

        } catch (\Throwable $th) {
            return response()->json(['message' => 'Erro ao excluir a imagem.'], 500);
        }

    }
}
