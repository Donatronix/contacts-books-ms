<?php


namespace Database\Factories;

use App\Models\Contact;
use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Address::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'country' => $this->faker->country,
            'provinces' => $this->faker->state,
            'city' => $this->faker->city,
            'address_string1' => $this->faker->address,
            'address_string1' => $this->faker->address,
            'type' => $this->faker->randomElement(['home', 'work', 'another']),
            'postcode' => $this->faker->postcode,
            'po_box' => $this->faker->postcode,
            'is_default' => $this->faker->boolean(),
            'contact_id' => function () {
                return Contact::all()->random()->id;
            }
        ];
    }
}
