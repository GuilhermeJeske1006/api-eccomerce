<?php

namespace App\Http\Controllers;

use App\Models\BlogComentario;
use Illuminate\Http\Request;

class ComentarioBlogController extends Controller
{
    public function index(int $blog_id)
    {
        $comentarios = BlogComentario::where('blog_id', $blog_id)->get();

        return response()->json([
            'data' => $comentarios,
        ], 200);
    }

    public function store(Request $request)
    {
        try {
            $comentario = $request->all();

            BlogComentario::create($comentario);

            return response()->json([
                'message' => 'Comentário criado com sucesso',
                'data'    => $comentario,
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao criar comentário',
                'erro'    => $th->getMessage(),
            ], 500);
        }
    }

    public function show(int $blog_id, int $id)
    {
        $comentario = BlogComentario::where('blog_id', $blog_id)->where('id', $id)->first();

        return response()->json([
            'data' => $comentario,
        ], 200);
    }

    public function update(Request $request, int $id)
    {
        try {
            $comentario = BlogComentario::find($id);

            $comentario->update($request->all());

            return response()->json([
                'message' => 'Comentário atualizado com sucesso',
                'data'    => $comentario,
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao atualizar comentário',
                'erro'    => $th->getMessage(),
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $comentario = BlogComentario::find($id);

            $comentario->delete();

            return response()->json([
                'message' => 'Comentário deletado com sucesso',
            ], 200);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Erro ao deletar comentário',
                'erro'    => $th->getMessage(),
            ], 500);
        }
    }
}
