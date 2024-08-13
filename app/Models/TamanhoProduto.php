<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TamanhoProduto extends Model
{
    use HasFactory;

    protected $table = 'tamanho_produtos';

    protected $fillable = [
        'tamanho', 
        'qtdTamanho', 
        'cor_id', 
        'produto_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'cor_id' => 'integer',
        'produto_id' => 'integer',
        'qtdTamanho' => 'integer'
    ];
    
    public function produto()
    {
        return $this->hasMany(Produto::class);
    }

    public function cor()
    {
        return $this->belongsTo(CorProduto::class);
    }

    public function itemPedido()
    {
        return $this->hasMany(ItemPedido::class);
    }
}
