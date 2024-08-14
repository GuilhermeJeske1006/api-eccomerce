<?php

namespace App\Http\Controllers\Pedidos;

use App\Http\Controllers\Controller;
use App\Jobs\EnviarEmailPedido;
use App\Models\{CorProduto, EnvioPedido, ItemPedido, Pedido, Produto, User};
use App\Services\PedidoService;
use Illuminate\Http\Client\Response;
use Illuminate\Http\{Request, Response as HttpResponse};
use Illuminate\Support\Facades\{DB, Log};

class CreditoController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/pagamento/credito",
     *     summary="Processa pagamento credito",
     *     description="Processa um pedido e retorna uma mensagem de sucesso ou erro",
     *     operationId="processarPedido",
     *     tags={"Pedidos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="idUsuario", type="integer", example=1),
     *             @OA\Property(property="cartao", type="object",
     *                 @OA\Property(property="cpf", type="string", example="114.413.859-08"),
     *                 @OA\Property(property="exp_month", type="string", example="12"),
     *                @OA\Property(property="exp_year", type="string", example="2026"),
     *                @OA\Property(property="security_code", type="string", example="123"),
     *                 @OA\Property(property="number", type="string", example="4111111111111111"),
     *                 @OA\Property(property="name", type="string", example="John Doe")
     *             ),
     *             @OA\Property(property="carrinho", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="preco", type="number", format="float", example=100.0),
     *                     @OA\Property(property="quantidade", type="integer", example=2),
     *                     @OA\Property(property="nome", type="string", example="Produto 1"),
     *                     @OA\Property(property="cor_id", type="integer", example="1"),
     *                     @OA\Property(property="tamanho_id", type="integer", example="1"),
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
     *             @OA\Property(property="message", type="string", example="Pagamento realizado com sucesso")
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
            $cpf       = formatarCpf($request->cartao['cpf']);
            $totalCart = PedidoService::calcularTotalCarrinho($request->carrinho);
            $totalVlr  = (float) PedidoService::calcularTotalComFrete($totalCart, $request->vlrFrete);
            $body      = $this->montarBodyRequisicao(
                $request->all(),
                $usuario,
                $cpf,
                $endereco,
                $totalVlr,
                (float) $request->vlrFrete
            );

            $response = PedidoService::enviarRequisicaoPagSeguro($body, 'orders');

            $pedido = Pedido::criarPedido(
                $usuario,
                $response['reference_id'],
                (float) $totalVlr,
                (float) $request->vlrFrete,
                'CREDITO'
            );

            PedidoService::inserirItensPedido($request->carrinho, $pedido->id);

            CorProduto::atualizarEstoque($request->carrinho);

            $queryPedido = ItemPedido::montarItemPedido($pedido);

            EnvioPedido::criarEnvioPedido(['id' => $pedido->id], [
                'agencia'  => $request->agencia,
                'servico'  => $request->servico,
                'vlrFrete' => (float) $request->vlrFrete,
            ]);

            DB::commit();

            $usuario = User::find($usuario['id']);

            EnviarEmailPedido::dispatch($usuario, $queryPedido);

            return response()->json(["message" => $response], HttpResponse::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar pedido: ', ['error' => $e]);

            return response()->json(["message" => "Erro ao processar pedido", 'erro' => $e->getMessage()], HttpResponse::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @param array{
     *     idUsuario: int,
     *     cartao: array{
     *         number: string,
     *         exp_month: int,
     *         exp_year: int,
     *         security_code: string,
     *         name: string,
     *     }
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
     * @param float $vlrFrete
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
     *     charges: array{
     *         array{
     *             reference_id: string,
     *             description: string,
     *             amount: array{
     *                 value: float,
     *                 currency: string,
     *             },
     *             payment_method: array{
     *                 type: string,
     *                 installments: int,
     *                 capture: bool,
     *                 card: array{
     *                     number: string,
     *                     exp_month: int,
     *                     exp_year: int,
     *                     security_code: string,
     *                     holder: array{
     *                         name: string,
     *                     },
     *                     store: bool,
     *                 }
     *             },
     *             notification_urls: array{
     *                 string
     *             },
     *         }
     *     }
     * }
     */
    private function montarBodyRequisicao(
        array $request,
        array $usuario,
        string $cpf,
        array $endereco,
        float $totalVlr,
        float $vlrFrete
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
                    "unit_amount" => formatarFrete($vlrFrete),
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
            'charges' => [
                [
                    'reference_id' => "usuarioid_{$request['idUsuario']}_" . uniqid(),
                    'description'  => 'descricao da cobranca',
                    'amount'       => [
                        'value'    => formatarFrete($totalVlr),
                        'currency' => 'BRL',
                    ],
                    'payment_method' => [
                        'type'         => 'CREDIT_CARD',
                        'installments' => 1,
                        'capture'      => true,
                        'card'         => [
                            "number"        => $request['cartao']['number'],
                            "exp_month"     => $request['cartao']['exp_month'],
                            "exp_year"      => $request['cartao']['exp_year'],
                            "security_code" => $request['cartao']['security_code'],
                            'holder'        => [
                                'name' => $request['cartao']['name'],
                            ],
                            'store' => false,
                        ],
                    ],
                    "notification_urls" => [
                        env('APP_URL') . '/api/pagamentos/notificacao',
                    ],
                ],
            ],
        ];

        $body = PedidoService::montaCarrinho($request, $body);

        return $body;
    }
}
