<?php

namespace App\Services;

use App\Models\{Empresa, EnvioPedido, Produto, User};
use Illuminate\Support\Facades\Http;

class EnvioService
{
    /**
     * @param object $request
     * @return array<string, mixed>
     */
    public static function inserirFretesCarrinho(object $request): array
    {
        try {
            $empresa = Empresa::find($request['empresa_id']);
            $usuario = User::find($request['usuario_id']);

            $products = [];
            $volumes  = [];

            foreach ($request['itemPedido'] as $item) {
                $produto = $item['produto'];

                // Adiciona o produto à lista de produtos
                $products[] = [
                    "name"          => $produto['nome'],
                    "quantity"      => $item['quantidade'],
                    "unitary_value" => $produto['valor'],
                ];

                // Adiciona o volume correspondente ao produto
                $volumes[] = [
                    "width"  => $produto['largura'],
                    "height" => $produto['altura'],
                    "length" => $produto['comprimento'],
                    "weight" => $produto['peso'],
                ];
            }

            $body = [
                'service' => $request['envio']['agencia'],
                'agency'  => $request['envio']['agencia'],
                'from'    => [
                    'name'             => $empresa->nome,
                    'phone'            => $empresa->telefone,
                    'email'            => $empresa->email,
                    'document'         => '',
                    'company_document' => $empresa->cnpj,
                    'state_register'   => '',
                    'address'          => $empresa->endereco->rua,
                    'complement'       => $empresa->endereco->complemento,
                    'number'           => $empresa->endereco->numero,
                    'district'         => $empresa->endereco->bairro,
                    'country_id'       => 'BR',
                    'city'             => $empresa->endereco->cidade,
                    'state_abbr'       => $empresa->endereco->estado,
                    'postal_code'      => $empresa->endereco->cep,
                    'note'             => '',
                ],
                'to' => [
                    'postal_code'      => $usuario->endereco->cep,
                    'name'             => $usuario->name,
                    'phone'            => $usuario->telefone,
                    'email'            => $usuario->email,
                    'document'         => $usuario->cpf,
                    'company_document' => '',
                    'state_register'   => '',
                    'address'          => $usuario->endereco->rua,
                    'complement'       => $usuario->endereco->complemento,
                    'number'           => $usuario->endereco->numero,
                    'district'         => $usuario->endereco->bairro,
                    'country_id'       => 'BR',
                    'city'             => $usuario->endereco->cidade,
                    'state_abbr'       => $usuario->endereco->estado,
                    'note'             => '',
                ],
                "products" => $products,
                "volumes"  => $volumes,

            ];

            $endpoint = env('API_MELHOR_ENVIO') . '/' . 'me/cart';

            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'accept'        => 'application/json',
                'User-Agent'    => 'guilhermeieski@gmail.com',
                'Authorization' => 'Bearer ' . env('TOKEN_MELHOR_ENVIO_SANBOX'),
            ])->post($endpoint, $body);

            if ($response->failed()) {
                throw new \Exception($response->body());
            }

            $envio = EnvioPedido::where('pedido_id', $request['id'])->first();

            $envio->update([
                'codigo_rastreio' => $response['id'],
                'status'          => $response['status'],
                'agencia'         => $response['agency_id'],
                'servico'         => $response['service_id'],
                'prazo'           => $response['delivered_at'],
                'valor'           => $response['price'],
                'pedido_id'       => $request['id'],
            ]);

            return $response->json();
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /**
    * @return array<string, mixed>
    */
    public static function gerarEtiqueta(): array
    {
        $body = [
            'orders' => ["9cbfee3c-66da-4eb3-93d4-7ee46b94bb7b"],
        ];

        $endpoint = env('API_MELHOR_ENVIO') . '/' . 'me/shipment/generate';

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'accept'        => 'application/json',
            'User-Agent'    => 'guilhermeieski@gmail.com',
            'Authorization' => 'Bearer ' . env('TOKEN_MELHOR_ENVIO_SANBOX'),
        ])->post($endpoint, $body);

        return $response->json();
    }

    /**
     * @param array<string> $pedidos
     * @return array<string, mixed>
     */
    public static function pegarEnvio(array $pedidos): array
    {
        $body = [
            'orders' => $pedidos,
        ];

        $endpoint = env('API_MELHOR_ENVIO') . '/' . 'me/shipment/checkout';

        $response = Http::withHeaders([
            'Content-Type'  => 'application/json',
            'accept'        => 'application/json',
            'User-Agent'    => 'guilhermeieski@gmail.com',
            'Authorization' => 'Bearer ' . env('TOKEN_MELHOR_ENVIO_SANBOX'),
        ])->post($endpoint, $body);

        return $response->json();
    }

    /**
     * @param array<string> $pedidos
     * @return array<string, mixed>
     */
    public static function imprimirEtiqueta(array $pedidos): array
    {
        try {
            $body = [
                'mode'   => 'public',
                'orders' => $pedidos,
            ];

            $endpoint = env('API_MELHOR_ENVIO') . '/' . 'me/shipment/print';

            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'accept'        => 'application/json',
                'User-Agent'    => 'guilhermeieski@gmail.com',
                'Authorization' => 'Bearer ' . env('TOKEN_MELHOR_ENVIO_SANBOX'),
            ])->post($endpoint, $body);

            return $response->json();
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
    * @param array<string> $pedidos
    * @return array<string, mixed>
    */
    public static function rastrearEnvio(array $pedidos): array
    {
        try {
            $body = [
                'orders' => $pedidos,
            ];

            $endpoint = env('API_MELHOR_ENVIO') . '/' . 'me/shipment/tracking';

            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'accept'        => 'application/json',
                'User-Agent'    => 'guilhermeieski@gmail.com',
                'Authorization' => 'Bearer ' . env('TOKEN_MELHOR_ENVIO_SANBOX'),
            ])->post($endpoint, $body);

            return $response->json();
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    /**
     * @param string $cepDestino
     * @param array<array{produtoId: int, quantidade: int}> $produtos
     * @param int $empresaId
     * @return array<string, mixed>
     */

    public static function calcularFrete(string $cepDestino, array $produtos, int $empresaId): array
    {
        try {

            $produtosDetalhes = Produto::whereIn('id', array_column($produtos, 'produtoId'))->get();

            $produtosFormatados = array_map(function ($produto) use ($produtosDetalhes) {
                $detalhes = $produtosDetalhes->firstWhere('id', $produto['produtoId']);

                return [
                    "width"           => $detalhes->largura,
                    "height"          => $detalhes->altura,
                    "length"          => $detalhes->comprimento,
                    "weight"          => $detalhes->peso,
                    "insurance_value" => 10.1,
                    "quantity"        => $produto['quantidade'],
                ];
            }, $produtos);

            $empresa = Empresa::find($empresaId);

            $body = [
                'from' => [
                    'postal_code' => $empresa->endereco->cep,
                ],
                'to' => [
                    'postal_code' => $cepDestino,
                ],
                'products' => $produtosFormatados,
            ];

            $endpoint = env('API_MELHOR_ENVIO') . '/' . 'me/shipment/calculate';

            $response = Http::withHeaders([
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . env('TOKEN_MELHOR_ENVIO_SANBOX'),
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
