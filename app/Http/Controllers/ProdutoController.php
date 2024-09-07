<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProdutoRequest;
use App\Http\Resources\ProdutoResource;
use App\Models\{Empresa, Produto, TamanhoProduto};
use Illuminate\Http\{JsonResponse, Request, Response};
use Illuminate\Support\Facades\{DB, Log, Redis, Storage};
use Illuminate\Support\Str;

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
    public function index(string $empresa_id = null, Request $request): JsonResponse
    {
        try {
            $produto  = new Produto();
            $query    = $produto->queryBuscaProduto($empresa_id, $request);
            $produtos = $query->paginate(15);

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

            if (!is_null($request['foto'])) {
                $request['foto'] = uploadBase64ImageToS3($request['foto'], 'produtos');
            } else {
                $request['foto'] = null;
            }

            $data = [
                'nome'            => $request['nome'],
                'valor'           => $request['valor'],
                'largura'         => $request['largura'],
                'altura'          => $request['altura'],
                'comprimento'     => $request['comprimento'],
                'empresa_id'      => $request['empresa_id'],
                'categoria_id'    => $request['categoria_id'],
                'foto'            => $request['foto'],
                'descricao'       => $request['descricao'],
                'descricao_longa' => $request['descricao_longa'],
                'peso'            => $request['peso'],
                'material'        => $request['material'],
                'irParaSite'      => $request['ir_para_site'],
                'destaque'        => $request['produto_destaque'],

            ];

            $produto = Produto::create($data);

            if ($request->has('cores')) {
                foreach ($request->cores as $cor) {
                    $data = [
                        'cor'        => $cor['cor'],
                        'produto_id' => $produto->id,
                    ];

                    $itemCor = $produto->cores()->create($data);

                    if($cor['tamanhos']) {
                        foreach ($cor['tamanhos'] as $tamanho) {
                            $data = [
                                'tamanho'    => $tamanho['tamanho'],
                                'qtdTamanho' => $tamanho['qtdTamanho'],
                                'cor_id'     => $itemCor->id,
                                'produto_id' => $produto->id,
                            ];

                            $produto->tamanhos()->create($data);
                        }
                    }
                }
            }

            if (!is_null($request->fotos)) {
                foreach ($request->fotos as $key => $foto) {

                    $path = uploadBase64ImageToS3($foto, 'produtos');

                    if (isset($foto[$key])) {
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
        $cacheKey = "empresa_{$empresa_id}_produto_{$id}";

        $cachedData = Redis::get($cacheKey);

        if ($cachedData) {
            $produtoArray = json_decode($cachedData, true);
            $empresa      = Produto::hydrate([$produtoArray])->first();

            return ProdutoResource::make($empresa);
        }

        $produto = Produto::findOrFail($id);

        if ($produto->foto) {
            $produto->foto = Storage::disk('s3')->url($produto->foto);
        }

        foreach ($produto->fotos as $foto) {
            if ($foto['imagem']) {
                $foto['imagem'] = Storage::disk('s3')->url($foto['imagem']);
            }
        }

        Redis::setex($cacheKey, 3600, json_encode($produto));

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

            $cacheKey = "empresa_{$request->empresa_id}_produto_{$id}";

            if (Redis::exists($cacheKey)) {
                Redis::del($cacheKey);
            }

            $produto = Produto::findOrFail($id);

            DB::beginTransaction();

            $request['foto'] = uploadUpdateBase64ImageToS3($request['foto'], $produto->foto, 'produtos');

            $data = [
                'nome'            => $request['nome'],
                'valor'           => $request['valor'],
                'largura'         => $request['largura'],
                'altura'          => $request['altura'],
                'comprimento'     => $request['comprimento'],
                'empresa_id'      => $request['empresa_id'],
                'categoria_id'    => $request['categoria_id'],
                'foto'            => $request['foto'],
                'descricao'       => $request['descricao'],
                'descricao_longa' => $request['descricao_longa'],
                'peso'            => $request['peso'],
                'material'        => $request['material'],
                'irParaSite'      => $request['ir_para_site'],
                'destaque'        => $request['produto_destaque'],
            ];

            $produto->update($data);

            if ($request->has('cores')) {
                $coresRequest = collect($request->cores);
                $coresBanco   = $produto->cores()->get();

                $corParaExcluir = [];
                $corParaManter  = [];

                foreach ($coresBanco as $item) {
                    $existsInRequest = $coresRequest->contains('id', $item->id);

                    if ($existsInRequest) {
                        $corParaManter[] = $item;
                    } else {
                        $corParaExcluir[] = $item;
                    }
                }

                foreach ($corParaExcluir as $item) {
                    $item->delete();
                }

                foreach ($request->cores as $cor) {
                    if (isset($cor['id'])) {

                        $produto->cores()->where('id', $cor['id'])->update(['cor' => $cor['cor']]);

                        $itemCor = $produto->cores()->where('id', $cor['id'])->first();
                        $itemCor->update(['cor' => $cor['cor']]);

                    } else {

                        $itemCor = $produto->cores()->create([
                            'cor'        => $cor['cor'],
                            'produto_id' => $produto->id,
                        ]);

                    }

                    if (isset($cor['tamanhos'])) {
                        $tamanhosRequest = collect($cor['tamanhos']);
                        $tamanhosBanco   = TamanhoProduto::where('cor_id', $itemCor->id)->get();

                        $tamanhoParaExcluir = [];
                        $tamanhoParaManter  = [];

                        foreach ($tamanhosBanco as $itemTamanho) {
                            $existsInRequest = $tamanhosRequest->contains('id', $itemTamanho->id);

                            if ($existsInRequest) {
                                $tamanhoParaManter[] = $itemTamanho;
                            } else {
                                $tamanhoParaExcluir[] = $itemTamanho;
                            }
                        }

                        foreach ($tamanhoParaExcluir as $itemTamanho) {
                            $itemTamanho->delete();
                        }

                        foreach ($cor['tamanhos'] as $tamanho) {
                            if (isset($tamanho['id'])) {

                                $produto->tamanhos()->where('id', $tamanho['id'])->update([
                                    'tamanho'    => $tamanho['tamanho'],
                                    'qtdTamanho' => $tamanho['qtdTamanho'],
                                    'produto_id' => $produto->id,
                                    'cor_id'     => $itemCor->id,
                                ]);
                            } else {
                                $produto->tamanhos()->create([
                                    'tamanho'    => $tamanho['tamanho'],
                                    'qtdTamanho' => $tamanho['qtdTamanho'],
                                    'produto_id' => $produto->id,
                                    'cor_id'     => $itemCor->id,
                                ]);
                            }
                        }
                    }
                }
            }

            if (isset($request->imagem)) {

                foreach ($request->imagem as $foto) {
                    if(isset($foto)) {

                        if(Str::contains($foto, 'data:image')) {
                            $path = uploadBase64ImageToS3($foto, 'produtos');
                        } else {
                            $path = $foto;
                        }

                        $produto->fotos()->updateOrCreate(['produto_id' => $produto->id, 'imagem' => $path]);
                    }
                }
            }

            DB::commit();

            return response()->json(["message" => "Produto atualizado com sucesso"], Response::HTTP_OK);

        } catch (\Throwable $th) {
            DB::rollBack();
            Log::error('Erro ao atualizar produto: ', ['error' => $th]);

            return response()->json(["message" => "Erro ao atualizar produto", 'erro' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\Delete(
     *     path="/produtos/{id}",
     *     summary="Delete a product",
     *     description="Deletes a product by its ID",
     *     operationId="deleteProduct",
     *     tags={"Produto"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="ID of the product to delete",
     *         @OA\Schema(
     *             type="string"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Product deleted successfully",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Produto deletado com sucesso"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Error deleting the product",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Erro ao deletar produto"
     *             ),
     *             @OA\Property(
     *                 property="erro",
     *                 type="string",
     *                 example="Detailed error message here"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Product not found",
     *         @OA\JsonContent(
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Produto não encontrado"
     *             )
     *         )
     *     ),
     *     security={
     *         {"api_key": {}}
     *     }
     * )
     */
    public function destroy(string $id): \Illuminate\Http\JsonResponse
    {
        try {
            $produto = Produto::findOrFail($id);

            if(!$produto) {
                return response()->json(["message" => "Produto não encontrado"], Response::HTTP_NOT_FOUND);
            }

            if(isset($produto->itemPedido()->first()->id)) {

                $produto->forceDeleted();

            } else {

                if($produto->foto) {
                    deleteImageFromS3($produto->foto);
                }

                foreach ($produto->fotos as $foto) {
                    if($foto->imagem) {
                        deleteImageFromS3($foto->imagem);
                    }
                }
                $produto->cores()->delete();

                $produto->tamanhos()->delete();

                $produto->fotos()->delete();

                $produto->delete();
            }

            return response()->json(["message" => "Produto deletado com sucesso"], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json(["message" => "Erro ao deletar produto", 'erro' => $th->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
