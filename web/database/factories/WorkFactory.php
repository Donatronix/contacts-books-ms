<?php


namespace Database\Factories;

use App\Models\Contact;
use App\Models\Work;
use Illuminate\Database\Eloquent\Factories\Factory;

class WorkFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Work::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'company' => $this->faker->word(),
            'department' => $this->faker->word(),
            'post' => $this->faker->word(),
            'is_default' => $this->faker->boolean(),
            'contact_id' => function () {
                return Contact::all()->random()->id;
            },
        ];
    }
}
