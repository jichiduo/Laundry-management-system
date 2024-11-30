<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Customer;

class CustomerFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Customer::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'password' => $this->faker->password(),
            'tel' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'email' => $this->faker->safeEmail(),
            'address' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'member_card' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'member_level_id' => $this->faker->randomNumber(),
            'member_level_name' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'member_discount' => $this->faker->randomFloat(2, 0, 99.99),
            'member_expire_date' => $this->faker->dateTime(),
            'last_trans_no' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'balance' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'remark' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'create_by' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'update_by' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'is_active' => $this->faker->boolean(),
            'group_id' => $this->faker->randomNumber(),
        ];
    }
}
