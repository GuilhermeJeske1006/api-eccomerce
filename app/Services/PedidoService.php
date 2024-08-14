<?php

namespace App\Services;

use App\Models\{ItemPedido};
use Illuminate\Support\Facades\Http;

class PedidoService
{
    /**
      * Insere itens do pedido na tabela item_pedido.
      *
      * @param array<int, array<string, mixed>> $items
      * @param int $pedido
      * @return void
      */
    public static function inserirItensPedido(array $items, int $pedido): void
    {
        foreach ($items as $item) {
            ItemPedido::insert([
                'pedido_id'  => $pedido,
                'produto_id' => $item['produtoId'],
                'quantidade' => $item['quantidade'],
                'valor'      => $item['preco'],
                'tamanho_id' => $item['tamanho_id'],
                'cor_id'     => $item['cor_id'],
                'dt_item'    => now(),
            ]);
        }
    }

    /**
     * Calcula o total do carrinho.
     *
     * @param array<int, array<string, mixed>> $carrinho
     * @return float
     */
    public static function calcularTotalCarrinho(array $carrinho): float
    {
        $totalCarrinho = 0;

        foreach ($carrinho as $item) {
            $totalCarrinho += $item['preco'] * $item['quantidade'];
        }

        return $totalCarrinho;
    }

    /**
     * Calcula o total do carrinho com frete.
     *
     * @param float $totalCarrinho
     * @param float $vlrFrete
     * @return string
     */
    public static function calcularTotalComFrete(float $totalCarrinho, float $vlrFrete): string
    {
        $totalVlr = $totalCarrinho + $vlrFrete;

        return str_replace(",", "", (string)$totalVlr);
    }

    /**
     * Monta o carrinho para ser enviado à API.
     *
     * @param array<string, mixed> $request
     * @param array<string, mixed> $body
     * @return array<string, mixed>
     */
    public static function montaCarrinho(array $request, array $body): array
    {
        foreach ($request['carrinho'] as $item) {
            $data = [
                "reference_id" => $item['produtoId'],
                "name"         => $item['nome'],
                'cor'          => $item['cor_id'],
                'tamanho'      => $item['tamanho_id'],
                'id'           => $item['produtoId'],
                "quantity"     => $item['quantidade'],
                "unit_amount"  => formatarFrete($item['preco']),
            ];
            $body['items'][] = $data;
        }

        return $body;
    }

    /**
     * Envia uma requisição ao PagSeguro.
     *
     * @param array<string, mixed> $body
     * @param string $pathAPI
     * @return array<string, mixed>
     * @throws \Exception
     */
    public static function enviarRequisicaoPagSeguro(array $body, string $pathAPI): array
    {
        try {
            $endpoint = env('API_PAGSEGURO') . '/' . $pathAPI;

            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . env('TOKEN_PAGSEGURO'),
            ])->post($endpoint, $body);

            if ($response->failed()) {
                throw new \Exception($response->body());
            }

            return $response->json();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }
}
