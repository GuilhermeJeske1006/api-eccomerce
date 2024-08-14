<?php

namespace App\Http\Resources;

use App\Models\Endereco;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $foto
 * @property bool $is_master
 * @property string $telefone
 * @property string $cpf
 * @property int $empresa_id
 */
class UsuarioResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            "id"        => $this->id,
            "name"      => $this->name,
            "email"     => $this->email,
            'foto'      => $this->foto,
            "is_master" => $this->is_master,
            "telefone"  => $this->telefone,
            "cpf"       => $this->cpf,
            'endereco'  => Endereco::where('id', $this->empresa_id)->first(),
        ];
    }
}
