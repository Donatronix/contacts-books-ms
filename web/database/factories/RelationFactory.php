<?php


namespace Database\Factories;

use App\Models\Contact;
use App\Models\Relation;
use Illuminate\Database\Eloquent\Factories\Factory;

class RelationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Relation::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'id' => $this->faker->uuid(),
            'relation' => $this->faker->word(),
            'relation_name' => $this->faker->randomElement(['spouse', 'child', 'mother', 'father', 'parent', 'brother', 'sister', 'friend', 'relative', 'manager', 'assistant', 'referred_by', 'partner', 'domestic_partner']),
            'contact_id' => function () {
                return Contact::factory()->create()->id;
            },
        ];
    }
}
