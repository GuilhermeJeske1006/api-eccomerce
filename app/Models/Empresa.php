<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $table = 'empresas';

    protected $fillable = [
        'nome',
        'email',
        'logo',
        'telefone',
        'cnpj',
        'endereco_id',
        'whatsapp',
        'facebook',
        'instagram',
        'logo',
        'descricao',
        'cor',
        'palavras_chaves',
        'titulo'
    ];

    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'endereco_id' => 'integer'
    ];

    // protected $appends = ['logo'];

    public function produtos()
    {
        return $this->hasMany(Produto::class);
    }

    public function endereco()
    {
        return $this->belongsTo(Endereco::class);
    }


}
