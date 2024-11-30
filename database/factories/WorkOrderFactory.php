<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\WorkOrder;

class WorkOrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkOrder::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'wo_no' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'customer_id' => $this->faker->randomNumber(),
            'customer_name' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'customer_tel' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'customer_email' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'customer_address' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'currency' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'base_currency' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'exchange_rate' => $this->faker->randomFloat(8, 0, 99999999.99999999),
            'explain' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'piece' => $this->faker->numberBetween(-10000, 10000),
            'total' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'discount' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'tax' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'grand_total' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'pickup_date' => $this->faker->dateTime(),
            'collect_date' => $this->faker->dateTime(),
            'is_express' => $this->faker->boolean(),
            'user_id' => $this->faker->randomNumber(),
            'user_name' => $this->faker->userName(),
            'division_id' => $this->faker->randomNumber(),
            'division_name' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'group_id' => $this->faker->randomNumber(),
            'group_name' => $this->faker->regexify('[A-Za-z0-9]{128}'),
        ];
    }
}
