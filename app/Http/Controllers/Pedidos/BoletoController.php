<?php

namespace App\Http\Controllers\Pedidos;

use App\Http\Controllers\Controller;
use App\Models\CorProduto;
use App\Models\EnvioPedido;
use App\Models\Pedido;
use App\Models\User;
use App\Services\PedidoService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BoletoController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/pagamento/boleto",
     *     summary="Processa pagamento boleto",
     *     description="Processa um pedido e retorna uma mensagem de sucesso ou erro",
     *     operationId="processarPedidoBoleto",
     *     tags={"Pedidos"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="idUsuario", type="integer", example=1),
     *             @OA\Property(property="cpf", type="integer", example="114.413.859-08"),
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
    public function __invoke(Request $request)
    {         

        try {
            DB::beginTransaction();

            $usuario = User::findOrFail($request->idUsuario);

            $endereco = User::findOrFail($request->idUsuario)->endereco;
            $cpf = formatarCpf($request['cpf']);
            $totalCart = PedidoService::calcularTotalCarrinho($request->carrinho);

            $totalVlr = PedidoService::calcularTotalComFrete($totalCart, $request->vlrFrete);
            $body = $this->montarBodyRequisicao($request, $usuario, $cpf, $endereco, $totalVlr,  $request->vlrFrete);


            $response = PedidoService::enviarRequisicaoPagSeguro($body, 'orders');
            
            
            $pedido = Pedido::criarPedido($usuario, $response['reference_id'], $totalVlr, $request->vlrFrete, 'BOLETO');


            PedidoService::inserirItensPedido($request->carrinho, $pedido);

            CorProduto::atualizarEstoque($request->carrinho);

            EnvioPedido::criarEnvioPedido($pedido, $request);

            DB::commit();

            Log::info('Pedido processado com sucesso: ', ['pedido' => $pedido]);

            return response()->json(['boleto' => $response['charges'][0]['links'][0]], Response::HTTP_CREATED);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar pedido: ', ['error' => $e]);
            return response()->json(["message" => "Erro ao processar pedido", 'erro' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    

    private function montarBodyRequisicao($request, $usuario, $cpf, $endereco, $totalVlr)
    {
        $body = [
            "reference_id" => uniqid(),
            "customer" => [
                "name" => $usuario['name'],
                "email" => $usuario['email'],
                "tax_id" => $cpf,
                "phones" => [
                    [
                        "country" => "55",
                        "area" => separarDDDTelefone($usuario['telefone'])['ddd'],
                        "number" => separarDDDTelefone($usuario['telefone'])['numero'],
                        "type" => "MOBILE"
                    ]
                ]
            ],
            "items" => [
                [
                    "id" => uniqid(),
                    "name" => "Frete",
                    "quantity" => 1,
                    "unit_amount" => formatarFrete($request->vlrFrete)
                ]
            ],
            "shipping" => [
                "address" => [
                    "street" => $endereco['rua'],
                    "number" => $endereco['numero'],
                    "complement" => $endereco['complemento'],
                    "locality" => $endereco['bairro'],
                    "city" => $endereco['cidade'],
                    "region_code" => $endereco['estado'],
                    "country" => "BRA",
                    "postal_code" => formatarCep($endereco['cep'])
                ]
            ],
            "notification_urls" => [
                env('APP_URL').'/api/pagamentos/notificacao'
            ],
            'charges' => [
                [
                    'reference_id' => uniqid(),
                    'description' => 'descricao da cobranca',
                    'amount' => [
                        'value' => formatarFrete($totalVlr),
                        'currency' => 'BRL'
                    ],
                    'payment_method' => [
                        'type' => 'BOLETO',
                        "boleto" => [
                        "due_date" => Carbon::now()->addDays(3)->format('Y-m-d'),
                        "instruction_lines" => [
                            "line_1" => "Pagamento processado para DESC Fatura",
                            "line_2" => "Via PagSeguro"
                        ],
                        "holder" => [
                            "name" => $usuario['name'],
                            "tax_id" => formatarCpf($request['cpf']),
                            "email" => $usuario['email'],
                            "address" => [
                            "country" => "Brasil",
                            "region" => $endereco['estado'],
                            "region_code" => $endereco['estado'],
                            "city" => $endereco['cidade'],
                            "postal_code" => formatarCep($endereco['cep']),
                            "street" => $endereco['rua'],
                            "number" => $endereco['numero'],
                            "locality" => $endereco['bairro']
                            ]
                        ]
                        ]
                    ],
                ]
            ]
        ];

        $body = PedidoService::montaCarrinho($request, $body);

        return $body;
    }
}
