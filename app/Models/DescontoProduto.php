<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DescontoProduto extends Model
{
    use HasFactory;

    protected $fillable = [
        'produto_id',
        'porcentagem',
        'desconto',
        'valor_final',
    ];

    public function produto(): BelongsTo
    {
        return $this->belongsTo(Produto::class);
    }

}
