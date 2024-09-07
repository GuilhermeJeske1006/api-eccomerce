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
    public static function calculaDesconto(Produto $produto, Request $request): float
    {
        $valorOriginal = $produto->valor;

        // Inicializa os valores de desconto e porcentagem como zero
        $desconto    = 0;
        $porcentagem = 0;

        // Verifica se há um valor de desconto no request e ajusta conforme necessário
        if ($request->has('desconto')) {
            $desconto = max(0, min($request->desconto, $valorOriginal));
        }

        // Verifica se há uma porcentagem de desconto no request e ajusta conforme necessário
        if ($request->has('porcentagem')) {
            $porcentagem = max(0, min($request->porcentagem, 100));
        }

        // Calcula o valor após aplicar o desconto em reais
        $valorComDesconto = max(0, $valorOriginal - $desconto);

        // Calcula o valor final após aplicar a porcentagem de desconto sobre o valor com desconto
        $valorFinal = $valorComDesconto - ($valorComDesconto * $porcentagem / 100);

        return max(0, $valorFinal); // Garante que o valor final não seja negativo
    }

}
