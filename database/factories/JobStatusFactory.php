<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\JobStatus;

class JobStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = JobStatus::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'wo_no' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'barcode' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'name' => $this->faker->name(),
            'quantity' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'user_id' => $this->faker->randomNumber(),
        ];
    }
}
