<?php

namespace Database\Factories;

use App\Models\Address;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Address>
 */
class AddressFactory extends Factory
{
    protected $model = Address::class;

    public function definition(): array
    {
        return [
            'owner_id' => User::factory(),
            'name' => $this->faker->streetName().' '.$this->faker->buildingNumber(),
            'address_line' => $this->faker->streetAddress(),
            'city' => $this->faker->city(),
            'state' => $this->faker->optional()->state(),
            'postal_code' => $this->faker->postcode(),
            'notes' => $this->faker->optional()->sentence(),
        ];
    }
}
