<?php

namespace App\Services;

use App\Models\CorProduto;
use App\Models\ItemPedido;
use Illuminate\Support\Facades\Http;

class PedidoService
{

    public static function inserirItensPedido($items, $pedido)
    {
        foreach ($items as $item) {
            
            ItemPedido::insert([
                'pedido_id' => $pedido['id'],
                'produto_id' => $item['produtoId'],
                'quantidade' => $item['quantidade'],
                'valor' => $item['preco'],
                'tamanho_id' => $item['tamanho_id'],
                'cor_id' => $item['cor_id'],
                'dt_item' => now(),
            ]);
        }
    }

    
    public static function calcularTotalCarrinho($carrinho)
    {
        $totalCarrinho = 0;

        foreach ($carrinho as $item) {
            $totalCarrinho += $item['preco'] * $item['quantidade'];
        }
        return $totalCarrinho;
    }

    public static function calcularTotalComFrete($totalCarrinho, $vlrFrete)
    {
        $totalVlr = $totalCarrinho + $vlrFrete;
        return str_replace(",", "", $totalVlr);
    }

    public static function montaCarrinho($request, $body)
    {
        foreach ($request['carrinho'] as $item) {
            $data = [
                "reference_id" => $item['produtoId'],
                "name" => $item['nome'],
                'cor' => $item['cor_id'],
                'tamanho' => $item['tamanho_id'],
                'id' => $item['produtoId'],
                "quantity" => $item['quantidade'],
                "unit_amount" => formatarFrete($item['preco'])
            ];
            $body['items'][] = $data;
        }
        return $body;
    }


    public static function enviarRequisicaoPagSeguro($body, $pathAPI)
    {
        try {
            $endpoint = env('API_PAGSEGURO') . '/' . $pathAPI;
    
            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . env('TOKEN_PAGSEGURO')
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
