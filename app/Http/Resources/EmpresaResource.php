<?php

namespace App\Http\Resources;

use App\Models\Endereco;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmpresaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'nome' => $this->nome,
            'email' => $this->email,
            'whatsapp' => $this->whatsapp,
            'instagram' => $this->instagram,
            'facebook' => $this->facebook,
            'telefone' => $this->telefone,
            'cor' => $this->cor,
            'cnpj' => $this->cnpj,
            'descricao' => $this->descricao,
            'palavras_chaves' => $this->palavras_chaves,
            'titulo' => $this->titulo,
            'endereco_id' => $this->endereco_id,
            'logo' => $this->logo,
            'endereco' => Endereco::all()->where('id', $this->endereco_id)->first(),
        ];
    }
}
