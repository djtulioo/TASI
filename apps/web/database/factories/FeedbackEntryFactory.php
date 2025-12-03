<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\FeedbackEntry>
 */
class FeedbackEntryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'conversation_id' => \App\Models\Conversation::factory(),
            'channel_id' => \App\Models\Channel::factory(),
            'tipo' => $this->faker->randomElement(['demanda', 'sugestao', 'opiniao']),
            'titulo' => $this->faker->sentence,
            'descricao' => $this->faker->paragraph,
            'sender_identifier' => $this->faker->phoneNumber,
            'status' => $this->faker->randomElement(['pendente', 'em_analise', 'resolvido', 'cancelado']),
        ];
    }
}
