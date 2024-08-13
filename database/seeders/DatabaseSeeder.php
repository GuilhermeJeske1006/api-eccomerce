<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\StatusPedido;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        //  \App\Models\User::factory(10)->create();

        //  \App\Models\Produto::factory(20)->create();

        // \App\Models\User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        StatusPedido::insert([
            ['nome_status' => 'Aguardando pagamento'],
            ['nome_status' => 'Em análise'],
            ['nome_status' => 'Paga'],
            ['nome_status' => 'Disponível'],
            ['nome_status' => 'Em disputa'],
            ['nome_status' => 'Devolvida'],
            ['nome_status' => 'Cancelada'],
            ['nome_status' => 'Debitado'],
            ['nome_status' => 'Retenção temporária'],
        ]);
        

    }
}
