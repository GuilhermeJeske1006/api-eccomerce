<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comentario extends Model
{
    use HasFactory;

    protected $fillable = [
        'descricao',
        'estrela',
        'produto_id',
        'usuario_id'
    ];


    public function usuario()
    {
        return $this->belongsTo(User::class);
    }

    public function produto()
    {
        return $this->belongsTo(Produto::class);
    }

    protected $hidden = [
        'updated_at'
    ];

}
