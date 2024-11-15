<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\ExchangeRate;

class ExchangeRateFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = ExchangeRate::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'from_currency' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'to_currency' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'rate' => $this->faker->randomFloat(8, 0, 99999999.99999999),
        ];
    }
}
