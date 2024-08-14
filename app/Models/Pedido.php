<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Illuminate\Notifications\Notifiable;

class Pedido extends Model
{
    use HasFactory;
    use Notifiable;

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

    public function envio(): BelongsTo
    {
        return $this->belongsTo(EnvioPedido::class);
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
            $pedido                 = new Pedido();
            $pedido->dataPedido     = now();
            $pedido->vlr_total      = $totalVlr;
            $pedido->formaPagamento = $formaPagamento;
            $pedido->endereco_id    = $usuario['endereco_id'];
            $pedido->vlr_frete      = $vlrFrete;
            $pedido->empresa_id     = $usuario['empresa_id'];
            $pedido->status         = "WAITING_PAYMENT";
            $pedido->usuario_id     = $usuario['id'];
            $pedido->reference      = $referenceId;
            $pedido->save();

            return $pedido;
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage());
        }
    }

}
