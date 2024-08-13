<?php

namespace App\Models;

use Illuminate\Bus\Queueable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

class Pedido extends Model
{
    use HasFactory, Notifiable;

    protected $table = 'pedidos';

    protected $fillable = [
        'id',
        'dataPedido',
        'status',
        'reference',
        'usuario_id',
        'vlr_total',
        'formaPagamento',
        'endereco_id',
        'vlr_frete',
        'empresa_id',
        'envio_id',
        'status_envio',
        'transportadora_id',
    ];


    public function itemPedido()
    {
        return $this->hasMany(itemPedido::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function endereco()
    {
        return $this->belongsTo(Endereco::class);
    }

    public function envio()
    {
        return $this->belongsTo(EnvioPedido::class);
    }

    public static function criarPedido($usuario, $referenceId, $totalVlr, $vlrFrete, $formaPagamento)
    {
        try {
            $pedido = new Pedido();
            $pedido->dataPedido = now();
            $pedido->vlr_total = $totalVlr;
            $pedido->formaPagamento = $formaPagamento;
            $pedido->endereco_id = $usuario['endereco_id'];
            $pedido->vlr_frete = $vlrFrete;
            $pedido->empresa_id = $usuario['empresa_id'];
            $pedido->status = "WAITING_PAYMENT";
            $pedido->usuario_id = $usuario['id'];
            $pedido->reference = $referenceId;
            $pedido->save();            

            return $pedido;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
 
    }
}
