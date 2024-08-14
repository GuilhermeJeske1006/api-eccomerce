<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

class TamanhoProduto extends Model
{
    use HasFactory;

    protected $table = 'tamanho_produtos';

    protected $fillable = [
        'tamanho',
        'qtdTamanho',
        'cor_id',
        'produto_id',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'cor_id'     => 'integer',
        'produto_id' => 'integer',
        'qtdTamanho' => 'integer',
    ];

    public function produto(): HasMany
    {
        return $this->hasMany(Produto::class);
    }

    public function cor(): BelongsTo
    {
        return $this->belongsTo(CorProduto::class);
    }

    public function itemPedido(): HasMany
    {
        return $this->hasMany(ItemPedido::class);
    }
}
