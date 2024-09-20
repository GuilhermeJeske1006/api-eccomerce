<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Produto>
 */
class ProdutoFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nome' => $this->faker->word, // Nome de um produto
            'valor' => $this->faker->randomFloat(2, 10, 1000), // Valor com duas casas decimais
            'foto' => 'produtos/1726595005.png', // URL de uma imagem de produto
            'descricao' => $this->faker->sentence, // Descrição curta
            'descricao_longa' => $this->faker->paragraph, // Descrição longa
            'peso' => $this->faker->randomFloat(2, 0.5, 100), // Peso em quilos (2 casas decimais)
            'largura' => $this->faker->randomFloat(2, 5, 200), // Largura em cm
            'altura' => $this->faker->randomFloat(2, 5, 200), // Altura em cm
            'comprimento' => $this->faker->randomFloat(2, 5, 200), // Comprimento em cm
            'material' => $this->faker->randomElement(['Plástico', 'Metal', 'Madeira', 'Vidro']), // Material do produto
            'empresa_id' => 1, // ID aleatório para empresa
            'categoria_id' => $this->faker->numberBetween(1, 3), // ID aleatório para categoria
            'irParaSite' => $this->faker->boolean, // URL para mais informações
            'destaque' => $this->faker->boolean, // Destaque (verdadeiro ou falso)
        ];
        
    }
}
