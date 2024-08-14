<?php

namespace App\Http\Resources;

use App\Models\{Categoria, Comentario, Cor, Tamanho};
// Import the Comentario model
// Import the Tamanho model
// Import the Cor model
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @property int $id
 * @property string $nome
 * @property float $valor
 * @property string $foto
 * @property string $descricao
 * @property string $descricao_longa
 * @property float $peso
 * @property string $dimensao
 * @property string $material
 * @property int $empresa_id
 * @property int $categoria_id
 * @property \Illuminate\Support\Collection<int, DescontoProduto> $desconto
 * @property \Illuminate\Database\Eloquent\Collection<int, Foto> $fotos
 * @property \Illuminate\Database\Eloquent\Collection<int, Comentario> $comentarios
 * @property \Illuminate\Database\Eloquent\Collection<int, Tamanho> $tamanhos
 * @property \Illuminate\Database\Eloquent\Collection<int, Cor> $cores
 */
class ProdutoResource extends JsonResource
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
            "id"              => $this->id,
            "nome"            => $this->nome,
            "valor"           => $this->valor,
            "foto"            => $this->foto,
            "descricao"       => $this->descricao,
            "descricao_longa" => $this->descricao_longa,
            "peso"            => $this->peso,
            "dimensao"        => $this->dimensao,
            "material"        => $this->material,
            "empresa_id"      => $this->empresa_id,
            "categoria"       => Categoria::find($this->categoria_id),
            "imagem"          => $this->fotos,
            'desconto'        => $this->desconto,
            "comentarios"     => $this->comentarios->map(function (Comentario $comentario) {
                return [
                    "id"        => $comentario->id,
                    "descricao" => $comentario->descricao,
                    "estrela"   => $comentario->estrela,
                    "usuario"   => $comentario->usuario,
                ];
            }),
            "tamanho" => $this->tamanhos,
            "cores"   => $this->cores,
        ];
    }
}
