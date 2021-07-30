<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\ContactPhone;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactPhoneFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ContactPhone::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'phone' => $this->faker->phoneNumber(),
            'type' => $this->faker->randomElement(['home', 'work', 'cell', 'other', 'main', 'homefax', 'workfax', 'googlevoice', 'pager']),
            'is_default' => false,
            'contact_id' => function () {
                return Contact::factory()->create()->id;
            },
        ];
    }
}
