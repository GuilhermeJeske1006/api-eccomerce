<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'titulo',
        'subtitulo',
        'foto',
        'texto',
        'empresa_id',
    ];

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function comentarios()
    {
        return $this->hasMany(BlogComentario::class);
    }

    public function queryBuscaBlog(string $id, object $request)
    {
        $query = self::query();

        if (isset($id)) {
            $query->where('empresa_id', $id);
        }

        if ($request->has('name')) {
            $query->where('titulo', 'like', '%' . $request->input('name') . '%');
        }

        return $query;
    }
}
