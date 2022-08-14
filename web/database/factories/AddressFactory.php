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
            'po_box' => $this->faker->postcode,
            'postcode' => $this->faker->postcode,
            'address_string1' => $this->faker->address,
            'address_string2' => $this->faker->address,
            'city' => $this->faker->city,
            'provinces' => $this->faker->state,
            'country' => $this->faker->country,
            'type' => $this->faker->randomElement([
                'home',
                'work',
                'another'
            ]),
            'contact_id' => function () {
                return Contact::all()->random()->id;
            }
        ];
    }
}
