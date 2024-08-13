<?php

namespace App\Http\Resources;

use App\Models\Categoria;
use App\Models\Comentario;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Log;

class ProdutoResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        
        return [
            "id"=> $this->id,
            "nome"=> $this->nome,
            "valor"=> $this->valor,
            "foto"=> $this->foto,
            "descricao"=> $this->descricao,
            "descricao_longa"=> $this->descricao_longa,
            "peso"=> $this->peso,
            "dimensao"=> $this->dimensao,
            "material"=> $this->material,
            "empresa_id"=> $this->empresa_id,
            "categoria"=> Categoria::find($this->categoria_id),
            "imagem" => $this->fotos,
            "comentarios" => $this->comentarios->map(function ($comentario) {
                return [
                    "id" => $comentario->id,
                    "descricao" => $comentario->descricao,
                    "estrela" => $comentario->estrela,
                    "usuario" => $comentario->usuario 
                ];
            }),
            "tamanho" => $this->tamanhos,
            "cores" => $this->cores

        ];
    }
}
