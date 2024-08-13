<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPedido extends Model
{
    use HasFactory;

    protected $table = 'item_pedidos';

    protected $fillable = [
        'tamanho_id',
        'valor',
        'cor_id',
        'dt_item',
        'produto_id',
        'pedido_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'valor' => 'float',
        'dt_item' => 'datetime'
    ];


    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public function cor()
    {
        return $this->belongsTo(CorProduto::class);
    }

    public function tamanho()
    {
        return $this->belongsTo(TamanhoProduto::class);
    }
    
    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    public static function montarItemPedido(Pedido $pedido)
    {
        $pedido['itemPedido'] = $pedido->itemPedido()->get();
        $pedido['user'] = User::find($pedido->usuario_id);
        $pedido['envio'] = EnvioPedido::where('pedido_id', $pedido->id)->first();

        $pedido['itemPedido']->map(function ($item) {
            $item['produto'] = $item->produto()->first();
            $item['cor'] = $item->cor()->first();
            $item['tamanho'] = $item->tamanho()->first();
        });

        return $pedido;
    }
}
