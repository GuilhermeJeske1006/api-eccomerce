<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};

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
        'titulo',
        'email_melhor_envio',
        'token_melhor_envio',
        'token_pagseguro',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
        'email_melhor_envio',
        'token_melhor_envio',
        'token_pagseguro',
    ];

    protected $casts = [
        'endereco_id' => 'integer',
    ];

    public function produtos(): HasMany
    {
        return $this->hasMany(Produto::class);
    }

    public function endereco(): BelongsTo
    {
        return $this->belongsTo(Endereco::class);
    }

}
