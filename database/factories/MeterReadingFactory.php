<?php

namespace Database\Factories;

use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<MeterReading>
 */
class MeterReadingFactory extends Factory
{
    protected $model = MeterReading::class;

    public function definition(): array
    {
        $readingDate = Carbon::parse($this->faker->dateTimeBetween('-1 year', 'now'))->toDateString();

        return [
            'meter_id' => Meter::factory(),
            'user_id' => User::factory(),
            'reading_date' => $readingDate,
            'value' => $this->faker->randomFloat(3, 0, 2000),
            'photo_path' => null,
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
