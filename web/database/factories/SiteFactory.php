<?php


namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Contact;
use App\Models\Site;

class SiteFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Site::class;

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
                'profile',
                'blog',
                'homepage',
                'work'
            ]),
            'contact_id' => function () {
                return Contact::all()->random()->id;
            },
        ];
    }
}
