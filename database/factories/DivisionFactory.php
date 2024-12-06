<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use App\Models\Division;

class DivisionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Division::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->name(),
            'address' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'tel' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'license' => $this->faker->regexify('[A-Za-z0-9]{50}'),
            'logo_file_url' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'remark' => $this->faker->regexify('[A-Za-z0-9]{255}'),
            'printer_com_port' => $this->faker->regexify('[A-Za-z0-9]{20}'),
            'group_id' => $this->faker->randomNumber(),
            'group_name' => $this->faker->regexify('[A-Za-z0-9]{128}'),
        ];
    }
}
