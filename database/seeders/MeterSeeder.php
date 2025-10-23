<?php

namespace Database\Seeders;

use App\Enums\MeterType;
use App\Models\Address;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

class MeterSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $owner = User::firstOrCreate(
            ['email' => 'demo@komunalka.test'],
            [
                'name' => 'Demo User',
                'password' => Hash::make('password'),
            ],
        );

        $member = User::firstOrCreate(
            ['email' => 'roommate@komunalka.test'],
            [
                'name' => 'Roommate',
                'password' => Hash::make('password'),
            ],
        );

        $addresses = [
            [
                'name' => 'Київ, Лесі Українки 14',
                'address_line' => 'просп. Лесі Українки, 14, кв. 73',
                'city' => 'Київ',
            ],
            [
                'name' => 'Дача у Вишгороді',
                'address_line' => 'вул. Набережна, 5',
                'city' => 'Вишгород',
            ],
        ];

        foreach ($addresses as $addressData) {
            $address = Address::updateOrCreate(
                [
                    'owner_id' => $owner->id,
                    'name' => $addressData['name'],
                ],
                array_merge($addressData, [
                    'state' => null,
                    'postal_code' => null,
                    'notes' => 'Створено сидером для демонстрації.',
                ]),
            );

            $address->members()->syncWithoutDetaching([
                $owner->id => ['role' => 'owner'],
                $member->id => ['role' => 'member'],
            ]);

            $meters = [
                ['type' => MeterType::ColdWater, 'submission_day' => 25],
                ['type' => MeterType::HotWater, 'submission_day' => 25],
                ['type' => MeterType::Electricity, 'submission_day' => 1],
            ];

            foreach ($meters as $config) {
                $type = $config['type'];

                $meter = Meter::updateOrCreate(
                    [
                        'address_id' => $address->id,
                        'type' => $type,
                    ],
                    [
                        'unit' => $type->defaultUnit(),
                        'description' => 'Створено сидером для демонстрації.',
                        'submission_day' => $config['submission_day'],
                    ],
                );

                $meter->readings()->delete();

                $date = Carbon::now()->startOfMonth()->subMonths(5);
                $value = 0;

                for ($i = 0; $i < 6; $i++) {
                    $consumption = match ($type) {
                        MeterType::Electricity => random_int(80, 160),
                        MeterType::Gas => random_int(15, 30),
                        default => random_int(4, 12),
                    };

                    $value += $consumption;

                    MeterReading::updateOrCreate(
                        [
                            'meter_id' => $meter->id,
                            'reading_date' => $date->toDateString(),
                        ],
                        [
                            'user_id' => $owner->id,
                            'value' => $value,
                            'notes' => null,
                        ],
                    );

                    $date = $date->addMonth();
                }
            }
        }
    }
}
