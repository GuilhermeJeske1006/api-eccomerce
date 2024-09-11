<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EnvioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'codigo_rastreio' => $this->codigo_rastreio,
            'status_envio'    => $this->statusEnvio->nome_status,
            'agencia'         => $this->agencia,
            'servico'         => $this->servico,
            'prazo'           => $this->prazo,
            'valor'           => $this->valor,
            'pedido'          => $this->pedido->id,
            'status_envio_id' => $this->status_envio_id,
        ];
    }
}
