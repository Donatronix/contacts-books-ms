<?php

namespace Database\Factories;

use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

class ContactFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Contact::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'user_id' => $this->faker->randomNumber(3, true),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName,
            'suffix_name' => $this->faker->suffix,
            'birthday' => $this->faker->date($format = 'Y-m-d', $max = 'now'),
        ];
    }
}
