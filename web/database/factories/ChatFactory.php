<?php


namespace Database\Factories;


use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Contact;
use App\Models\Chat;

class ChatFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Chat::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'chat' => $this->faker->url,
            'chat_name' => $this->faker->randomElement(['gtalk', 'aim', 'yahoo', 'skype', 'qq', 'msn', 'isq', 'jabber']),
            'contact_id' => function () {
                return Contact::factory()->create()->id;
            },
        ];
    }
}
