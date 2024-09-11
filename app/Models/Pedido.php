<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, HasOne};
use Illuminate\Notifications\Notifiable;

class Pedido extends Model
{
    use HasFactory;
    use Notifiable;

    protected $table = 'pedidos';

    protected $fillable = [
        'id',
        'dataPedido',
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
        'status_pedido_id',
    ];

    public function itemPedido(): HasMany
    {
        return $this->hasMany(ItemPedido::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function endereco(): BelongsTo
    {
        return $this->belongsTo(Endereco::class);
    }

    public function envio(): HasOne
    {
        return $this->hasOne(EnvioPedido::class, 'envio_id');
    }

    public function statusEnvio(): BelongsTo
    {
        return $this->belongsTo(StatusEnvio::class);
    }

    public function statusPedido(): BelongsTo
    {
        return $this->belongsTo(StatusPedido::class);
    }

    /**
     * Cria um novo pedido.
     *
     * @param array{
     *     id: int,
     *     endereco_id: int,
     *     empresa_id: int
     * } $usuario Associative array com os dados do usuário.
     * @param string $referenceId
     * @param float $totalVlr
     * @param float $vlrFrete
     * @param string $formaPagamento
     * @return Pedido
     */
    public static function criarPedido(
        array $usuario, // Expecting an associative array with specific keys
        string $referenceId,
        float $totalVlr,
        float $vlrFrete,
        string $formaPagamento
    ): Pedido {
        try {
            $pedido                   = new Pedido();
            $pedido->dataPedido       = now();
            $pedido->vlr_total        = $totalVlr;
            $pedido->formaPagamento   = $formaPagamento;
            $pedido->endereco_id      = $usuario['endereco_id'];
            $pedido->vlr_frete        = $vlrFrete;
            $pedido->empresa_id       = $usuario['empresa_id'];
            $pedido->status_pedido_id = 1;
            $pedido->usuario_id       = $usuario['id'];
            $pedido->reference        = $referenceId;
            $pedido->save();

            return $pedido;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

    public static function queryBuscaPedido(array $filtro, int $empresa_id): object
    {
        try {

            $query = self::query()
                ->select('pedidos.*')
                ->where('pedidos.empresa_id', $empresa_id)
                ->orderBy('pedidos.created_at', 'desc');

            if (isset($filtro['id']) && $filtro['id'] !== null) {
                $query->where('pedidos.id', $filtro['id']);
            }

            if (isset($filtro['status_envio']) && $filtro['status_envio'] !== null) {
                $query->join('envio_pedidos', 'pedidos.id', '=', 'envio_pedidos.pedido_id')
                    ->where('envio_pedidos.status_envio_id', $filtro['status_envio']);
            }

            if (isset($filtro['status_pagamento']) && $filtro['status_pagamento'] !== null) {
                $query->where('pedidos.status_pedido_id', $filtro['status_pagamento']);
            }

            if (isset($filtro['status_metodo']) && $filtro['status_metodo'] !== null) {
                $query->where('pedidos.formaPagamento', $filtro['status_metodo']);
            }

            return $query->paginate();
        } catch (\Throwable $th) {
            return $th;
        }

    }

}
