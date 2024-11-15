<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\AppGroup;
use App\Models\Customer;
use App\Models\Division;
use App\Models\User;
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
            'division_id' => Division::factory(),
            'division_name' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'customer_id' => Customer::factory(),
            'customer_name' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'customer_tel' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'customer_email' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'customer_address' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'credit_term' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'currency' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'base_currency' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'exchange_rate' => $this->faker->randomFloat(8, 0, 99999999.99999999),
            'explain' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'remark' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'weight' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'total' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'discount' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'tax' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'grand_total' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'submit_by_userid' => User::factory(),
            'submit_by_username' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'submit_date' => $this->faker->dateTime(),
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'pickup_date' => $this->faker->dateTime(),
            'group_id' => AppGroup::factory(),
            'delivery_status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'export_tag' => $this->faker->boolean(),
            'export_date' => $this->faker->dateTime(),
            'user_id' => User::factory(),
            'app_group_id' => AppGroup::factory(),
        ];
    }
}
