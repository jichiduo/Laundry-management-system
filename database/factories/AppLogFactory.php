<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\AppLog;
use App\Models\User;

class AppLogFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AppLog::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'wo_no' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'trans_no' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'user_id' => User::factory(),
            'user_name' => $this->faker->userName(),
            'action' => $this->faker->regexify('[A-Za-z0-9]{20}'),
            'amount' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'remark' => $this->faker->regexify('[A-Za-z0-9]{255}'),
        ];
    }
}
