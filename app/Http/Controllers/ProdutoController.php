<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProdutoRequest;
use App\Http\Resources\ProdutoResource;
use App\Models\{Empresa, Produto};
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\{DB, Log, Storage};

class ProdutoController extends Controller
{
    /**
     * Display a listing of the resource.
     */

    /**
     *  @OA\Get(
     *      path="/api/produto/{empresa_id}",
     *      tags={"Produto"},
     *      summary="Retorna uma lista de produtos",
     *      description="Retorna uma lista de produtos",
     *      operationId="index",
     *      @OA\Parameter(
     *          name="empresa_id",
     *          in="path",
     *          description="Id da empresa",
     *          required=true,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="search",
     *          in="query",
     *          description="Nome do produto",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="preco_minimo",
     *          in="query",
     *          description="Preço mínimo",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="preco_maximo",
     *          in="query",
     *          description="Preço máximo",
     *          required=false,
     *          @OA\Schema(
     *              type="string"
     *          )
     *      ),
     *      @OA\Parameter(
     *          name="categoria",
     *          in="query",
     *          description="Id da categoria",
     *          required=false,
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
    public function index(int $empresa_id = null, Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $empresa_id = (int) $empresa_id;

            $request->validate([
                'search'       => 'string',
                'preco_minimo' => 'numeric',
                'preco_maximo' => 'numeric',
                'categoria'    => 'numeric',
            ]);

            if($empresa_id == null) {
                return response()->json(["message" => "Empresa não encontrada"], 404);
            }

            $produto = new Produto();
            $query   = $produto->queryBuscaProduto((string) $empresa_id, $request);

            $produtos = $query->paginate(15);

            foreach ($produtos as $prod) {
                $prod->foto = Storage::disk('s3')->url($prod->foto);

            }

            return response()->json(ProdutoResource::collection($produtos));
        } catch (\Exception $e) {
            return response()->json(["message" => "Empresa não encontrada"], 404);
        }

    }

    /**
     *  @OA\Post(
     *      path="/api/produto/criar",
     *      tags={"Produto"},
     *      summary="Cria um produto",
     *      description="Cria um produto",
     *      operationId="store",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\MediaType(
     *              mediaType="application/json",
     *              @OA\Schema(
     *                  @OA\Property(property="nome", type="string", example="Produto Exemplo"),
     *                  @OA\Property(property="valor", type="number", example=100),
     *                  @OA\Property(property="largura", type="number", example=11),
     *                  @OA\Property(property="altura", type="number", example=17),
     *                  @OA\Property(property="comprimento", type="number", example=11),
     *                  @OA\Property(property="empresa_id", type="integer", example=1),
     *                  @OA\Property(property="categoria_id", type="integer", example=1),
     *                  @OA\Property(property="foto", type="string", example=""),
     *                  @OA\Property(property="descricao", type="string", example="Descrição curta do produto"),
     *                  @OA\Property(property="descricao_longa", type="string", example="Descrição longa do produto"),
     *                  @OA\Property(property="peso", type="number", example="0.3"),
     *                  @OA\Property(property="material", type="string", example="Plástico"),
     *                  @OA\Property(
     *                      property="cores",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="cor", type="string", example="Verde"),
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="tamanhos",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="tamanho", type="string", example="P"),
     *                          @OA\Property(property="qtdTamanho", type="integer", example=2),
     *                          @OA\Property(property="cor_id", type="integer", example=1),
     *                      )
     *                  ),
     *                  @OA\Property(
     *                      property="fotos",
     *                      type="array",
     *                      @OA\Items(
     *                          type="object",
     *                          @OA\Property(property="imagem", type="string", example=""),
     *                      )
     *                  )
     *              )
     *          )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Produto criado com sucesso",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Produto criado com sucesso")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Erro ao criar produto",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Erro ao criar produto")
     *          )
     *      )
     *  )
     */

    public function store(StoreProdutoRequest $request): \Illuminate\Http\JsonResponse
    {
        try {

            DB::beginTransaction();

            if (!is_null($request->foto)) {
                $data['foto'] = uploadBase64ImageToS3($request['foto'], 'produtos');
            }

            $produto = Produto::create($request->all());

            if ($request->has('cores')) {
                $cores = array_map(function ($cor) use ($produto) {
                    $cor['produto_id'] = $produto->id;

                    return $cor;
                }, $request->input('cores'));

                $produto->cores()->createMany($cores);
            }

            if ($request->has('tamanhos')) {
                foreach ($request->input('tamanhos') as $tamanho) {
                    $tamanho['produto_id'] = $produto->id;
                    $produto->tamanhos()->create($tamanho);
                }
            }

            if (!is_null($request->fotos)) {
                foreach ($request->fotos as $foto) {
                    $path = uploadBase64ImageToS3($foto['imagem'], 'produtos');

                    // Certifique-se de que 'imagem' está presente no array $foto
                    if (isset($foto['imagem'])) {
                        $produto->fotos()->create(['produto_id' => $produto->id, 'imagem' => $path]);
                    } else {

                        Log::error('Campo "imagem" não encontrado na estrutura de dados das fotos.');
                    }
                }
            }

            DB::commit();

            return response()->json(["message" => "Produto criado com sucesso", "produto" => $produto], Response::HTTP_CREATED);
        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Erro ao criar produto: ', ['error' => $th]);

            return response()->json(["message" => "Erro ao criar produto", 'erro' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */

    /**
     * @OA\Get(
       *      path="/api/produto/{empresa_id}/{id}",
       *      tags={"Produto"},
       *      summary="Retorna um produto",
       *      description="Retorna um produto",
       *      operationId="show",
       *      @OA\Parameter(
       *          name="empresa_id",
       *          in="path",
       *          description="Id da empresa",
       *          required=true,
       *          @OA\Schema(
       *              type="string"
       *          )
       *      ),
       *      @OA\Parameter(
       *          name="id",
       *          in="path",
       *          description="Id do produto",
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
       *          description="Produto não encontrado"
       *      )
       *  )

     */

    public function show(string $empresa_id, string $id): ProdutoResource
    {
        $empresa_id = Empresa::findOrFail($empresa_id);

        $produto = Produto::find($id);

        if($produto->foto) {
            $produto->foto = Storage::disk('s3')->url($produto->foto);
        }

        foreach($produto->fotos as $foto) {
            if($foto['foto']) {
                $foto['foto'] = Storage::disk('s3')->url($foto['foto']);
            }
        }

        return ProdutoResource::make($produto);
    }

    /**
     * @OA\Put(
     *     path="/api/produto/{id}",
     *     summary="Atualizar Produto",
     *     tags={"Produto"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID do produto a ser atualizado",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"nome", "valor", "empresa_id", "categoria_id"},
     *             @OA\Property(property="nome", type="string", example="Produto A"),
     *             @OA\Property(property="largura", type="number", example=11),
     *             @OA\Property(property="altura", type="number", example=17),
     *             @OA\Property(property="comprimento", type="number", example=11),
     *             @OA\Property(property="valor", type="number", format="float", example=99.99),
     *             @OA\Property(property="empresa_id", type="integer", example=1),
     *             @OA\Property(property="categoria_id", type="integer", example=2),
     *             @OA\Property(property="foto", type="string", format="base64", example="base64_encoded_image_data"),
     *             @OA\Property(property="descricao", type="string", example="Descrição curta do produto"),
     *             @OA\Property(property="descricao_longa", type="string", example="Descrição longa do produto"),
     *             @OA\Property(property="peso", type="string", example="500g"),
     *             @OA\Property(property="material", type="string", example="Aço inoxidável"),
     *             @OA\Property(
     *                 property="cores",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="nome", type="string", example="Vermelho"),
     *                     @OA\Property(property="codigo_hex", type="string", example="#FF0000")
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="tamanhos",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="nome", type="string", example="Grande"),
     *                     @OA\Property(property="quantidade", type="integer", example=20)
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="fotos",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="imagem", type="string", format="base64", example="base64_encoded_image_data")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Produto atualizado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Produto atualizado com sucesso"),
     *             @OA\Property(property="produto", type="object",
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="nome", type="string", example="Produto A"),
     *                 @OA\Property(property="valor", type="number", format="float", example=99.99),
     *                 @OA\Property(property="empresa_id", type="integer", example=1),
     *                 @OA\Property(property="categoria_id", type="integer", example=2),
     *                 @OA\Property(property="foto", type="string", example="https://s3.example.com/produtos/1234567890.png"),
     *                 @OA\Property(property="descricao", type="string", example="Descrição curta do produto"),
     *                 @OA\Property(property="descricao_longa", type="string", example="Descrição longa do produto"),
     *                 @OA\Property(property="peso", type="string", example="0.3"),
     *                 @OA\Property(property="qtd", type="integer", example=10),
     *                 @OA\Property(property="material", type="string", example="Aço inoxidável"),
     *                 @OA\Property(property="cores", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nome", type="string", example="Vermelho"),
     *                         @OA\Property(property="codigo_hex", type="string", example="#FF0000")
     *                     )
     *                 ),
     *                 @OA\Property(property="tamanhos", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="nome", type="string", example="Grande"),
     *                         @OA\Property(property="quantidade", type="integer", example=20)
     *                     )
     *                 ),
     *                 @OA\Property(property="fotos", type="array",
     *                     @OA\Items(
     *                         @OA\Property(property="id", type="integer", example=1),
     *                         @OA\Property(property="foto", type="string", example="https://s3.example.com/produtos/1234567890.png")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Erro ao atualizar produto",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao atualizar produto"),
     *             @OA\Property(property="erro", type="string", example="Mensagem de erro detalhada")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro interno do servidor",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro interno do servidor"),
     *             @OA\Property(property="erro", type="string", example="Mensagem de erro detalhada")
     *         )
     *     )
     * )
     */
    public function update(StoreProdutoRequest $request, string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $produto = Produto::findOrFail($id);

            DB::beginTransaction();

            if ($request->has('foto')) {
                $request['foto'] = uploadBase64ImageToS3($request['foto'], 'produtos');
            }

            $produto->update($request->all());

            if ($request->has('cores')) {
                $produto->cores()->delete();
                $produto->cores()->createMany($request->input('cores'));
            }

            if ($request->has('tamanhos')) {
                $produto->tamanhos()->delete();
                $produto->tamanhos()->createMany($request->input('tamanhos'));
            }

            if($request->has('fotos')) {
                $produto->fotos()->delete();

                foreach($request->fotos as $foto) {
                    $path = uploadBase64ImageToS3($foto['imagem'], 'produtos');
                    $produto->fotos()->create(['foto' => $path, 'produto_id' => $produto->id]);
                }
            }

            DB::commit();

            return response()->json(["message" => "Produto atualizado com sucesso", "produto" => $produto], Response::HTTP_OK);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Erro ao atualizar produto: ', ['error' => $th]);

            return response()->json(["message" => "Erro ao atualizar produto", 'erro' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $produto = Produto::findOrFail($id);

            $produto->delete();

            return response()->json(["message" => "Produto deletado com sucesso"], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(["message" => "Erro ao deletar produto", 'erro' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
