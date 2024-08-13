<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorProduto extends Model
{
    use HasFactory;

    protected $table = 'cor_produtos';

    protected $fillable = [
        'cor',
        'produto_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'produto_id' => 'integer'
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }


    public function itemPedido()
    {
        return $this->hasMany(ItemPedido::class);
    }

    public static function atualizarEstoque($items)
    {
        try {
            foreach ($items as $item) {
                if (!empty($item['tamanho_id']) && !empty($item['cor_id'])) {
                    CorProduto::query()
                        ->join('tamanho_produtos', 'cor_produtos.id', '=', 'tamanho_produtos.cor_id')
                        ->where('cor_produtos.produto_id', $item['produtoId'])
                        ->where('tamanho_produtos.id', $item['tamanho_id'])
                        ->where('cor_produtos.id', $item['cor_id'])
                        ->decrement('tamanho_produtos.qtdTamanho', $item['quantidade']);
                }
            }
        } catch (\Throwable $th) {
            throw new \Exception("Erro ao atualizar estoque: " . $th->getMessage());
        }
    }
}
