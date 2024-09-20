<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

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
        'cores',
        'irParaSite',
        'destaque',

    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'valor'      => 'float',
        'qtd'        => 'integer',
        'irParaSite' => 'boolean',
        'destaque'   => 'boolean',
    ];

    protected $appends = ['foto_url'];

    protected $withCount = ['comentarios'];

    protected $perPage = 10;

    /**
     * @var string
     */
    protected $orderBy = 'id';

    /**
     * @var string
     */
    protected $orderDirection = 'desc';

    protected $primaryKey = 'id';

    public function empresa(): BelongsTo
    {
        return $this->belongsTo(Empresa::class);
    }

    public function comentarios(): HasMany
    {
        return $this->hasMany(Comentario::class);
    }

    public function fotos(): HasMany
    {
        return $this->hasMany(ImagemProduto::class);
    }

    public function tamanhos(): HasMany
    {
        return $this->hasMany(TamanhoProduto::class);
    }

    public function cores(): HasMany
    {
        return $this->hasMany(CorProduto::class);
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    public function desconto(): HasMany
    {
        return $this->hasMany(DescontoProduto::class);
    }

    public function itemPedido(): HasMany
    {
        return $this->hasMany(ItemPedido::class);
    }

    public function getFotoUrlAttribute(): string
    {
        return env('AWS_URL') . $this->foto;
    }

    public function queryBuscaProduto(string $id, object $request)
    {
        $query = self::query();

        if (isset($id)) {
            $query->where('empresa_id', $id);
        }

        if ($request->has('name')) {
            $query->where('nome', 'like', '%' . $request->input('name') . '%');
        }

        if ($request->preco_minimo !== null && $request->preco_maximo !== null) {
            $query->whereBetween('valor', [$request->input('preco_minimo'), $request->input('preco_maximo')]);
        }

        if ($request->has('categoria')) {
            $query->where('categoria_id', $request->input('categoria'));
        }

        return $query;
    }

    public function queryBuscaProdutoDestaque(string $id, object $request)
    {
        $query = self::query();

        if (isset($id)) {
            $query->where('empresa_id', $id);
        }

        // $query->where('destaque', true);
        // $query->where('ativo', true);

        return $query;
    }
}
