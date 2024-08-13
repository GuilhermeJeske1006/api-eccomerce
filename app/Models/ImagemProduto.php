<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImagemProduto extends Model
{
    use HasFactory;

    protected $table = 'imagem_produtos';

    protected $fillable = [
        'imagem', 
        'produto_id'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'produto_id' => 'integer'
    ];

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

}
