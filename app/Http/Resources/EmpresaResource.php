<?php

namespace App\Http\Resources;

use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $nome
 * @property string $email
 * @property string $whatsapp
 * @property string $instagram
 * @property string $facebook
 * @property string $telefone
 * @property string $cor
 * @property string $cnpj
 * @property string $descricao
 * @property string $palavras_chaves
 * @property string $titulo
 * @property int $endereco_id
 * @property string $logo
 */
class EmpresaResource extends JsonResource
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
            'id'              => $this->resource->id,
            'nome'            => $this->resource->nome,
            'email'           => $this->resource->email,
            'whatsapp'        => $this->resource->whatsapp,
            'instagram'       => $this->resource->instagram,
            'facebook'        => $this->resource->facebook,
            'telefone'        => $this->resource->telefone,
            'cor'             => $this->resource->cor,
            'cnpj'            => $this->resource->cnpj,
            'descricao'       => $this->resource->descricao,
            'palavras_chaves' => $this->resource->palavras_chaves,
            'titulo'          => $this->resource->titulo,
            'endereco_id'     => $this->resource->endereco_id,
            'logo'            => $this->resource->logo,
            'endereco'        => Endereco::find($this->resource->endereco_id),
        ];
    }
}
