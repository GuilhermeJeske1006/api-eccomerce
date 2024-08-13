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
            
            
            $pedido = Pedido::criarPedido($usuario, $response['reference_id'], $totalVlr, $request->vlrFrete, 'PIX');

            PedidoService::inserirItensPedido($request->carrinho, $pedido);

            CorProduto::atualizarEstoque($request->carrinho);

            EnvioPedido::criarEnvioPedido($pedido, $request);

            DB::commit();

            Log::info('Pedido processado com sucesso: ', ['pedido' => $pedido]);

            return response()->json([
                'img_qrcode' => $response['qr_codes'][0]['links'][0], 
                'text_qrCode' => $response['qr_codes'][0]['text'],
                'message' => $response
            ], Response::HTTP_CREATED);

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
        ];

        $body = PedidoService::montaCarrinho($request, $body);

        return $body;
    }
}
