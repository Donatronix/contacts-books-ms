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
            'address' => $this->faker->address,
            'address_type' => $this->faker->randomElement(['home', 'work', 'another']),
            'postcode' => $this->faker->postcode,
            'post_office_box_number' => $this->faker->postcode,
            'contact_id' => function () {
                return Contact::factory()->create()->id;
            },
        ];
    }
}
