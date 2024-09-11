<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StatusEnvio extends Model
{
    use HasFactory;

    protected $fillable = [
        'nome_status',
    ];

    protected $hidden = [
        'created_at',
        'updated_at',
    ];

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }

}
