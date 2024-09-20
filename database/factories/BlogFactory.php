<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Blog>
 */
class BlogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'titulo' => $this->faker->sentence, // Título gerado aleatoriamente
            'subtitulo' => $this->faker->sentence, // Subtítulo gerado aleatoriamente
            'foto' => $this->faker->imageUrl(640, 480, 'business', true), // URL de uma imagem relacionada ao negócio
            'texto' => $this->faker->paragraphs(3, true), // Parágrafos de texto, 3 no total
            'empresa_id' => 1, // ID aleatório de uma empresa
        ];
        
    }
}
