<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EnvioPedido extends Model
{
    use HasFactory;

    protected $table = 'envio_pedidos';

    protected $fillable = [
        'codigo_rastreio',
        'status_envio_id',
        'agencia',
        'servico',
        'prazo',
        'valor',
        'pedido_id',
    ];

    protected $casts = [
        'valor' => 'float',
    ];

    public function pedido(): BelongsTo
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    public function statusEnvio(): BelongsTo
    {
        return $this->belongsTo(StatusEnvio::class);
    }
    /**
     * Cria um novo envio de pedido.
     *
     * @param array{
     *     id: int
     * } $pedido Associative array com o ID do pedido.
     * @param array{
     *     agencia: int,
     *     servico: int,
     *     vlrFrete: float
     * } $request Associative array com os dados do envio.
     * @return EnvioPedido
     */
    public static function criarEnvioPedido(array $pedido, array $request): EnvioPedido
    {
        $envio                  = new EnvioPedido();
        $envio->pedido_id       = $pedido['id'];
        $envio->codigo_rastreio = uniqid();
        $envio->status_envio_id = 1;
        $envio->agencia         = $request['agencia'];
        $envio->servico         = $request['servico'];
        $envio->valor           = $request['vlrFrete'];
        $envio->save();

        return $envio;
    }

}
