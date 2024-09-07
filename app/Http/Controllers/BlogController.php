<?php

namespace App\Http\Controllers;

use App\Http\Requests\storeBlogRequest;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Illuminate\Http\{JsonResponse, Request};
use Illuminate\Support\Facades\{DB, Log, Storage};

class BlogController extends Controller
{
    /**
     * @OA\Get(
     *     path="/api/blogs/{empresa_id}",
     *     summary="Listar blogs por empresa",
     *     description="Retorna uma lista paginada de blogs pertencentes a uma empresa específica, com a opção de aplicar filtros usando query parameters.",
     *     tags={"Blogs"},
     *     @OA\Parameter(
     *         name="empresa_id",
     *         in="path",
     *         required=true,
     *         description="ID da empresa cujos blogs serão listados",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Parameter(
     *         name="search",
     *         in="query",
     *         required=false,
     *         description="Termo de busca para filtrar blogs",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Lista paginada de blogs",
     *         @OA\JsonContent(
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao buscar blogs",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao buscar blogs")
     *         )
     *     )
     * )
     *
     * Exibe uma lista paginada de blogs para a empresa especificada, com suporte a filtros.
     *
     * @param  int  $empresa_id  O ID da empresa cujos blogs serão buscados.
     * @param  Request  $request  A requisição HTTP contendo possíveis filtros.
     * @return JsonResponse Retorna uma coleção de recursos de blogs ou uma mensagem de erro em caso de falha.
     */
    public function index(int $empresa_id, Request $request): JsonResponse
    {
        try {
            $blogs = new Blog();
            $query = $blogs->queryBuscaBlog($empresa_id, $request);
            $blogs = $query->paginate(15);

            foreach ($blogs as $blog) {
                if ($blog->foto) {
                    $blog->foto = Storage::disk('s3')->url($blog->foto);
                }
            }

            return BlogResource::collection($blogs)->response()->setStatusCode(200);

        } catch (\Throwable $th) {
            return BlogResource::collection([])->response()->setStatusCode(500);
        }
    }

    /**
 * @OA\Post(
 *     path="/api/blog/criar",
 *     summary="Criar um novo blog",
 *     description="Cria um novo blog com os dados fornecidos e faz o upload da imagem associada, se presente.",
 *     tags={"Blogs"},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"titulo", "texto"},
 *             @OA\Property(property="titulo", type="string", example="Título do Blog"),
 *             @OA\Property(property="texto", type="string", example="Conteúdo do blog."),
 *             @OA\Property(property="foto", type="string", format="byte", nullable=true, example="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Blog criado com sucesso",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Blog criado com sucesso"),
 *         )
 *     ),
 *     @OA\Response(
 *         response=500,
 *         description="Erro ao criar blog",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Erro ao criar blog"),
 *             @OA\Property(property="erro", type="string", example="Detalhes do erro")
 *         )
 *     )
 * )
 *
 * Cria um novo blog com os dados fornecidos.
 *
 * @param  storeBlogRequest  $request  O objeto de solicitação contendo os dados do blog.
 * @return JsonResponse Retorna uma mensagem de sucesso ou erro em caso de falha.
 */
    public function store(storeBlogRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            if (!is_null($request['foto']) || $request['foto'] != "") {
                $request['foto'] = uploadBase64ImageToS3($request['foto'], 'blogs');
            }

            Blog::create($request->all());

            DB::commit();

            return response()->json(['message' => 'Blog criado com sucesso', ], 201);

        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();

            return response()->json(['message' => 'Erro ao criar blog', 'erro' => $th->getMessage()], 500);
        }
    }

    /**
     * @OA\Get(
     *     path="/api/blog/{id}",
     *     summary="Exibir detalhes de um blog",
     *     description="Retorna os detalhes de um blog específico, incluindo a URL da foto armazenada no S3.",
     *     tags={"Blogs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do blog a ser exibido",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Detalhes do blog",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Blog não encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao buscar blog",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao buscar blog")
     *         )
     *     )
     * )
     *
     * Exibe os detalhes de um blog específico.
     *
     * @param  int  $id  O ID do blog a ser exibido.
     * @return JsonResponse Retorna o recurso do blog ou uma mensagem de erro em caso de falha.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $blog = Blog::findOrFail($id);

            $blog['foto'] = Storage::disk('s3')->url($blog['foto']);

            return BlogResource::make($blog)->response()->setStatusCode(200);

        } catch (\Throwable $th) {
            Log::error($th->getMessage());

            return BlogResource::make([])->response($th)->setStatusCode(500);
        }

    }

    /**
     * @OA\Put(
     *     path="/api/blog/editar/{id}",
     *     summary="Atualizar um blog",
     *     description="Atualiza os dados de um blog existente, incluindo a imagem associada.",
     *     tags={"Blogs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do blog a ser atualizado",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"titulo", "texto"},
     *             @OA\Property(property="titulo", type="string", example="Novo Título do Blog"),
     *             @OA\Property(property="texto", type="string", example="Conteúdo atualizado do blog."),
     *             @OA\Property(property="foto", type="string", format="byte", example="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAA...")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Blog atualizado com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Blog não encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao atualizar blog",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao atualizar blog"),
     *             @OA\Property(property="erro", type="string", example="Detalhes do erro")
     *         )
     *     )
     * )
     *
     * Atualiza um blog específico com os dados fornecidos.
     *
     * @param  storeBlogRequest  $request  O objeto de solicitação contendo os dados do blog.
     * @param  int  $id  O ID do blog a ser atualizado.
     * @return JsonResponse Retorna uma mensagem de sucesso ou erro em caso de falha.
     */
    public function update(storeBlogRequest $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $blog = Blog::findOrFail($id);

            $request['foto'] = uploadUpdateBase64ImageToS3($request['foto'], $blog->foto, 'blogs');

            $blog->update($request->all());

            DB::commit();

            return response()->json(['message' => 'Blog atualizado com sucesso', ], 200);

        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();

            return response()->json(['message' => 'Erro ao atualizar blog', 'erro' => $th->getMessage()], 500);
        }
    }

    /**
     * @OA\Delete(
     *     path="/api/blog/delete/{id}",
     *     summary="Deletar um blog",
     *     description="Remove um blog específico, incluindo a exclusão da imagem associada no S3, caso exista.",
     *     tags={"Blogs"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do blog a ser deletado",
     *         @OA\Schema(
     *             type="integer"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Blog deletado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Blog deletado com sucesso")
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Blog não encontrado",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Blog não encontrado")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao deletar blog",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao deletar blog"),
     *             @OA\Property(property="erro", type="string", example="Detalhes do erro")
     *         )
     *     )
     * )
     *
     * Deleta um blog específico.
     *
     * @param  int  $id  O ID do blog a ser deletado.
     * @return JsonResponse Retorna uma mensagem de sucesso ou erro em caso de falha.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $blog = Blog::findOrFail($id);

            if ($blog->foto) {
                deleteImageFromS3($blog->foto);
            }

            $blog->delete();
            DB::commit();

            return response()->json(['message' => 'Blog deletado com sucesso'], 200);

        } catch (\Throwable $th) {
            Log::error($th->getMessage());
            DB::rollBack();

            return response()->json(['message' => 'Erro ao deletar blog', 'erro' => $th->getMessage()], 500);
        }
    }
}
