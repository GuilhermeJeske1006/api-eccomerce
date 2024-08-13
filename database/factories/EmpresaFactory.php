<?php

namespace Database\Factories;

use App\Models\Endereco;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Empresa>
 */
class EmpresaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->name,
            'cnpj' => $this->faker->numberBetween(10000000000000, 99999999999999),
            'telefone' => $this->faker->phoneNumber,
            'email' => $this->faker->email,
            'endereco_id' => Endereco::factory(),
            'logo' => 'logo.png',
            'whatsapp' => '99999999999',
            'facebook' => 'facebook.com',
            'instagram' => 'instagram.com',
            'cor' => '#000000',
            'descricao' => 'descricao da empresa',
            'palavras_chaves' => 'palavra1, palavra2, palavra3',
            'titulo' => 'titulo da empresa',



        ];
    }
}
