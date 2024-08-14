<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class EnderecoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [

            'cep'         => $this->faker->postcode(),
            'rua'         => $this->faker->streetName(),
            'numero'      => $this->faker->buildingNumber(),
            'complemento' => $this->faker->secondaryAddress(),
            'cidade'      => $this->faker->city(),
            'estado'      => $this->faker->state(),
            'bairro'      => $this->faker->citySuffix(),
            'pais'        => $this->faker->country(),

        ];
    }
}
