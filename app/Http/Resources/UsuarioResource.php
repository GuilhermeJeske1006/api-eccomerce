<?php

namespace App\Http\Resources;

use App\Models\Endereco;
use App\Models\Endreco;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class UsuarioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "name" => $this->name,
            "email" => $this->email,
            'foto' => $this->foto,
            "is_master" => $this->is_master,
            "telefone" => $this->telefone,
            "cpf" => $this->cpf,
            'endereco' => Endereco::all()->where('id', $this->empresa_id)->first(),
        ];

    }
}
