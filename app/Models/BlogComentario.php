<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BlogComentario extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_usuario',
        'comentario',
        'blog_id',
    ];

    public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

}