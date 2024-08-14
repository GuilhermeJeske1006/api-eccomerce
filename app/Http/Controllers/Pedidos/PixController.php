<?php

namespace App\Http\Controllers\Pedidos;

use App\Http\Controllers\Controller;
use App\Models\{CorProduto, EnvioPedido, Pedido, User};
use App\Services\PedidoService;
use Carbon\Carbon;
use Illuminate\Http\{Request, Response};
use Illuminate\Support\Facades\{DB, Log};

class PixController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/pagamento/pix",
     *     summary="Processa pagamento pix",
     *     description="Processa um pedido e retorna uma mensagem de sucesso ou erro",
     *     operationId="processarPedidoPix",
     *     tags={"Pedidos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="idUsuario", type="integer", example=1),
     *             @OA\Property(property="cpf", type="string", example="114.413.859-08"),
     *             @OA\Property(property="carrinho", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="preco", type="number", format="float", example=100.0),
     *                     @OA\Property(property="quantidade", type="integer", example=2),
     *                     @OA\Property(property="nome", type="string", example="Produto 1"),
     *                     @OA\Property(property="cor_id", type="integer", example=1),
     *                     @OA\Property(property="tamanho_id", type="integer", example=1),
     *                     @OA\Property(property="produtoId", type="integer", example=1)
     *                 )
     *             ),
     *             @OA\Property(property="vlrFrete", type="number", format="float", example=15.0),
     *             @OA\Property(property="agencia", type="string", example="1"),
     *             @OA\Property(property="servico", type="string", example="1")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Pedido processado com sucesso",
     *         @OA\JsonContent(
     *             @OA\Property(property="img_qrcode", type="string", example="https://example.com/qrcode.png"),
     *             @OA\Property(property="text_qrCode", type="string", example="QR Code Text"),
     *             @OA\Property(property="message", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Erro ao processar pedido",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string", example="Erro ao processar pedido"),
     *             @OA\Property(property="erro", type="string", example="Detalhes do erro")
     *         )
     *     )
     * )
     */
    public function __invoke(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            DB::beginTransaction();

            $usuario   = User::findOrFail($request->idUsuario)->toArray();
            $endereco  = User::findOrFail($request->idUsuario)->endereco->toArray();
            $cpf       = formatarCpf($request->cpf);
            $totalCart = PedidoService::calcularTotalCarrinho($request->carrinho);
            $totalVlr  = (float) PedidoService::calcularTotalComFrete($totalCart, $request->vlrFrete);
            $body      = $this->montarBodyRequisicao($request->all(), $usuario, $cpf, $endereco, $totalVlr);

            $response = PedidoService::enviarRequisicaoPagSeguro($body, 'orders');

            $pedido = Pedido::criarPedido($usuario, $response['reference_id'], $totalVlr, (float) $request->vlrFrete, 'PIX');

            PedidoService::inserirItensPedido($request->carrinho, $pedido->id);

            CorProduto::atualizarEstoque($request->carrinho);

            EnvioPedido::criarEnvioPedido(['id' => $pedido->id], [
                'agencia'  => $request->agencia,
                'servico'  => $request->servico,
                'vlrFrete' => (float) $request->vlrFrete,
            ]);

            DB::commit();

            Log::info('Pedido processado com sucesso: ', ['pedido' => $pedido]);

            return response()->json([
                'img_qrcode'  => $response['qr_codes'][0]['links'][0],
                'text_qrCode' => $response['qr_codes'][0]['text'],
                'message'     => $response,
            ], Response::HTTP_CREATED);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar pedido: ', ['error' => $e]);

            return response()->json([
                "message" => "Erro ao processar pedido",
                'erro'    => $e->getMessage(),
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param array{
     *     vlrFrete: float,
     * } $request
     * @param array{
     *     name: string,
     *     email: string,
     *     telefone: string,
     * } $usuario
     * @param string $cpf
     * @param array{
     *     rua: string,
     *     numero: string,
     *     complemento: string,
     *     bairro: string,
     *     cidade: string,
     *     estado: string,
     *     cep: string,
     * } $endereco
     * @param float $totalVlr
     * @return array{
     *     reference_id: string,
     *     customer: array{
     *         name: string,
     *         email: string,
     *         tax_id: string,
     *         phones: array{
     *             array{
     *                 country: string,
     *                 area: string,
     *                 number: string,
     *                 type: string,
     *             }
     *         }
     *     },
     *     items: array{
     *         array{
     *             id: string,
     *             name: string,
     *             quantity: int,
     *             unit_amount: float,
     *         }
     *     },
     *     qr_codes: array{
     *         array{
     *             amount: array{
     *                 value: float,
     *             },
     *             expiration_date: string,
     *         }
     *     },
     *     shipping: array{
     *         address: array{
     *             street: string,
     *             number: string,
     *             complement: string,
     *             locality: string,
     *             city: string,
     *             region_code: string,
     *             country: string,
     *             postal_code: string,
     *         }
     *     },
     *     notification_urls: array{
     *         string
     *     },
     * }
     */
    private function montarBodyRequisicao(
        array $request,
        array $usuario,
        string $cpf,
        array $endereco,
        float $totalVlr
    ): array {
        $body = [
            "reference_id" => uniqid(),
            "customer"     => [
                "name"   => $usuario['name'],
                "email"  => $usuario['email'],
                "tax_id" => $cpf,
                "phones" => [
                    [
                        "country" => "55",
                        "area"    => separarDDDTelefone($usuario['telefone'])['ddd'],
                        "number"  => separarDDDTelefone($usuario['telefone'])['numero'],
                        "type"    => "MOBILE",
                    ],
                ],
            ],
            "items" => [
                [
                    "id"          => uniqid(),
                    "name"        => "Frete",
                    "quantity"    => 1,
                    "unit_amount" => formatarFrete($request['vlrFrete']),
                ],
            ],
            'qr_codes' => [
                [
                    'amount' => [
                        'value' => $totalVlr,
                    ],
                    'expiration_date' => Carbon::now()->addMinutes(10)->format('Y-m-d\TH:i:sP'),
                ],
            ],
            "shipping" => [
                "address" => [
                    "street"      => $endereco['rua'],
                    "number"      => $endereco['numero'],
                    "complement"  => $endereco['complemento'],
                    "locality"    => $endereco['bairro'],
                    "city"        => $endereco['cidade'],
                    "region_code" => $endereco['estado'],
                    "country"     => "BRA",
                    "postal_code" => formatarCep($endereco['cep']),
                ],
            ],
            "notification_urls" => [
                env('APP_URL') . '/api/pagamentos/notificacao',
            ],
        ];

        $body = PedidoService::montaCarrinho($request, $body);

        return $body;
    }

}
