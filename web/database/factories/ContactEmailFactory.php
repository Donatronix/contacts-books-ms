<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\ContactEmail;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactEmailFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContactEmail::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'email' => $this->faker->email(),
            'email_type' => $this->faker->randomElement(['home', 'work', 'other']),
            'is_default' => false,
            'contact_id' => function () {
                return Contact::factory()->create()->id;
            },
        ];
    }
}
