<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\AppGroup;

class AppGroupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AppGroup::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'currency' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'tax_rate' => $this->faker->randomFloat(2, 0, 999.99),
            'address' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'description' => $this->faker->text(),
            'is_active' => $this->faker->boolean(),
        ];
    }
}
