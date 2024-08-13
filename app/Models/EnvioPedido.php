<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnvioPedido extends Model
{
    use HasFactory;

    protected $table = 'envio_pedidos';

    protected $fillable = [
        'codigo_rastreio',
        'status',
        'agencia',
        'servico',
        'prazo',
        'valor',
        'pedido_id',
    ];

    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }

    public static function criarEnvioPedido($pedido, $request)
    {
        $envio = new EnvioPedido();
        $envio->pedido_id = $pedido['id'];
        $envio->codigo_rastreio = uniqid();
        $envio->status = 'Waiting for product payment';
        $envio->agencia = $request['agencia'];
        $envio->servico = $request['servico'];
        $envio->valor = $request['vlrFrete'];
        $envio->save();

        return $envio;
    }

    
}
