<?php

namespace App\Http\Resources;

use App\Models\ItemPedido;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $dataPedido
 * @property string $status
 * @property string $reference
 * @property int $usuario_id
 * @property \Illuminate\Database\Eloquent\Collection<int, ItemPedido> $itemPedido
 */
class PedidoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param Request $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id"           => $this->id,
            "dt_item"      => $this->dataPedido,
            "status"       => $this->status,
            "reference"    => $this->reference,
            "usuario_id"   => $this->usuario_id,
            "item_pedidos" => $this->itemPedido->map(function (ItemPedido $item) {
                return [
                    "id"         => $item->id,
                    "quantidade" => $item->quantidade,
                    "tamanho"    => $item->tamanho,
                    "valor"      => $item->valor,
                    "cor"        => $item->cor,
                    "dt_item"    => $item->dt_item,
                    "produto_id" => $item->produto_id,
                    "pedido_id"  => $item->pedido_id,
                    "produtos"   => $item->produto,
                ];
            }),
        ];
    }
}
