<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\WorkOrderItem;

class WorkOrderItemFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = WorkOrderItem::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'wo_no' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'barcode' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'name' => $this->faker->name(),
            'description' => $this->faker->text(),
            'quantity' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'unit' => $this->faker->regexify('[A-Za-z0-9]{20}'),
            'price' => $this->faker->randomFloat(4, 0, 9999999999999999.9999),
            'total' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'discount' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'tax_rate' => $this->faker->randomFloat(2, 0, 999.99),
            'tax' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'sub_total' => $this->faker->randomFloat(2, 0, 999999999999999999.99),
            'turnover' => $this->faker->randomFloat(2, 0, 999999.99),
            'acc_code' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'acc_name' => $this->faker->regexify('[A-Za-z0-9]{128}'),
            'status' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'remark' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'location' => $this->faker->regexify('[A-Za-z0-9]{100}'),
        ];
    }
}
