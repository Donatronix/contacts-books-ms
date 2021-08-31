<?php

namespace Database\Factories;

use App\Models\Contact;
use App\Models\Phone;
use Illuminate\Database\Eloquent\Factories\Factory;

class PhoneFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Phone::class;

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
            'is_default' => $this->faker->boolean(),
            'contact_id' => function () {
                return Contact::all()->random()->id;
            },
        ];
    }
}
