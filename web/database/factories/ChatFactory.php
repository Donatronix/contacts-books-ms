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
            'value' => $this->faker->url,
            'type' => $this->faker->randomElement([
                'gtalk',
                'aim',
                'yahoo',
                'skype',
                'qq',
                'msn',
                'isq',
                'jabber'
            ]),
            'is_default' => $this->faker->boolean(),
            'contact_id' => function () {
                return Contact::all()->random()->id;
            },
        ];
    }
}
