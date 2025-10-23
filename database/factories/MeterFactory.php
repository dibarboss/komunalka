<?php

namespace Database\Factories;

use App\Enums\MeterType;
use App\Models\Address;
use App\Models\Meter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Meter>
 */
class MeterFactory extends Factory
{
    protected $model = Meter::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(MeterType::cases());

        return [
            'address_id' => Address::factory(),
            'type' => $type,
            'unit' => $type->defaultUnit(),
            'description' => $this->faker->optional()->sentence(),
            'submission_day' => $this->faker->optional(0.7)->numberBetween(1, 28),
        ];
    }
}
