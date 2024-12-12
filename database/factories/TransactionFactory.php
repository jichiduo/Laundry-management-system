<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Transaction;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'trans_no' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'wo_no' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'customer_id' => $this->faker->randomNumber(),
            'customer_name' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'card_no' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'amount' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'payment_type' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'type' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'remark' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'create_by' => $this->faker->regexify('[A-Za-z0-9]{50}'),
        ];
    }
}
