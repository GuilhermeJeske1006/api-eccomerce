<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    
    use HasFactory;

    protected $table = 'produtos';

    protected $fillable = [
        'nome',
        'valor',
        'foto',
        'descricao',
        'descricao_longa',
        'peso',
        'dimensao',
        'largura',
        'altura',
        'comprimento',
        'material',
        'empresa_id',
        'categoria_id',
        'tamanhos',
        'cores'
        
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'valor' => 'float',
        'qtd' => 'integer'
    ];
    protected $appends = ['foto_url'];
    protected $withCount = ['comentarios'];
    protected $perPage = 10;
    protected $orderBy = 'id'; 
    protected $orderDirection = 'desc';
    protected $primaryKey = 'id';



    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function comentarios()
    {
        return $this->hasMany(Comentario::class);
    }

    public function fotos()
    {
        return $this->hasMany(ImagemProduto::class);
    }

    public function tamanhos()
    { 
        return $this->hasMany(TamanhoProduto::class);
    }

    public function cores()
    {
        return $this->hasMany(CorProduto::class);
    }

    public function categoria()
    {
        return $this->belongsTo(Categoria::class);
    }

    public function getFotoUrlAttribute()
    {
        return env('AWS_URL') . $this->foto;
    }


    public function queryBuscaProduto($id, $request)
    {
        $query = self::query();

        if ($id) {
            $query->where('empresa_id', $id);
        }

        if ($request->has('search')) {
            $query->where('nome', 'like', '%' . $request->input('search') . '%');
        }

        if ($request->preco_minimo !== null && $request->preco_maximo !== null) {
            $query->whereBetween('valor', [$request->input('preco_minimo'), $request->input('preco_maximo')]);
        }

        if ($request->has('categoria')) {
            $query->where('categoria_id', $request->input('categoria'));
        }

        return $query;
    }
}
