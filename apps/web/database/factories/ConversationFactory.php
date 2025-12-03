<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Conversation>
 */
class ConversationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'channel_id' => \App\Models\Channel::factory(),
            'sender_identifier' => $this->faker->phoneNumber,
            'message_body' => $this->faker->sentence,
            'direction' => $this->faker->randomElement(['incoming', 'outgoing']),
            'processed_by_ai' => false,
        ];
    }
}
