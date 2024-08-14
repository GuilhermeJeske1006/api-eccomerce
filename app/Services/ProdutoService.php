<?php

namespace App\Services;

use App\Models\Produto;
use Illuminate\Http\Request;

class ProdutoService
{
    /**
     * Calcula o desconto para um produto com base no request.
     *
     * @param Produto $produto  O produto que terá o desconto aplicado.
     * @param Request $request  A requisição contendo as informações de porcentagem e desconto.
     *
     * @return array<string, float>  Array contendo os valores de porcentagem, desconto e valor final.
     */
    public static function calculaDesconto(Produto $produto, Request $request): array
    {
        $valor = $produto->valor;

        $valorPorcentagem = 0;
        $valorDesconto    = 0;

        // Verifica se há uma porcentagem no request e calcula o valor de desconto porcentual
        if ($request->has('porcentagem')) {
            $valorPorcentagem -= $valor * ($request->porcentagem / 100);
        }

        // Verifica se há um valor de desconto no request
        if ($request->has('desconto')) {
            $valorDesconto -= $request->desconto;
        }

        // Calcula o valor final
        $valores = [
            'valorPorcentagem' => $valorPorcentagem,
            'valorDesconto'    => $valorDesconto,
            'valorFinal'       => $valor + $valorPorcentagem + $valorDesconto,
        ];

        return $valores;
    }
}
