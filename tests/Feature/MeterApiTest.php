<?php

use App\Enums\MeterType;
use App\Models\Address;
use App\Models\Meter;
use App\Models\MeterReading;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

it('authenticates and manages meters through the API', function (): void {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $address = Address::factory()->for($user, 'owner')->create();

    $token = $this->postJson('/api/auth/token', [
        'email' => $user->email,
        'password' => 'password',
        'device_name' => 'test-suite',
    ])->assertCreated()
        ->json('token');

    $headers = ['Authorization' => 'Bearer '.$token];

    $meterResponse = $this->postJson('/api/meters', [
        'address_id' => $address->id,
        'type' => MeterType::ColdWater->value,
        'unit' => 'м³',
        'description' => 'Main kitchen meter',
        'submission_day' => 25,
    ], $headers)
        ->assertCreated()
        ->json();

    expect($meterResponse)->toHaveKeys(['id', 'type', 'address']);
    expect($meterResponse['type'])->toBe(MeterType::ColdWater->value);
    expect($meterResponse['address']['id'])->toBe($address->id);

    $meterId = $meterResponse['id'];

    $firstReading = $this->postJson("/api/meters/{$meterId}/readings", [
        'reading_date' => '2024-01-01',
        'value' => 100.123,
        'notes' => 'Initial reading',
    ], $headers)->assertCreated()->json();

    expect($firstReading['difference'])->toBeNull();

    $secondReading = $this->postJson("/api/meters/{$meterId}/readings", [
        'reading_date' => '2024-02-01',
        'value' => 125.888,
    ], $headers)->assertCreated()->json();

    expect($secondReading['difference'])->toEqualWithDelta(25.765, 0.001);

    $stats = $this->getJson("/api/meters/{$meterId}/statistics", $headers)
        ->assertOk()
        ->json();

    expect($stats['summary']['total_readings'])->toBe(2)
        ->and($stats['summary']['total_consumption'])->toEqualWithDelta(25.765, 0.001)
        ->and($stats['chart'])->not()->toBeEmpty();
});

it('validates readings and persists photos', function (): void {
    Storage::fake('public');

    $owner = User::factory()->create([
        'password' => Hash::make('owner-secret'),
    ]);

    $address = Address::factory()->for($owner, 'owner')->create();

    $user = User::factory()->create([
        'password' => Hash::make('secret'),
    ]);

    $address->members()->syncWithoutDetaching([
        $owner->id => ['role' => 'owner'],
        $user->id => ['role' => 'member'],
    ]);

    $meter = Meter::factory()->for($address)->create([
        'type' => MeterType::Electricity,
        'unit' => 'кВт·год',
    ]);

    $firstReading = MeterReading::factory()
        ->for($meter)
        ->for($owner)
        ->create([
            'reading_date' => '2024-01-01',
            'value' => 200,
        ]);

    $token = $user->createToken('tests')->plainTextToken;

    $response = $this->post('/api/meters/'.$meter->id.'/readings', [
        'reading_date' => '2024-02-01',
        'value' => 190,
        'photo' => UploadedFile::fake()->image('meter.jpg'),
    ], [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors('value');

    $response = $this->post('/api/meters/'.$meter->id.'/readings', [
        'reading_date' => '2024-02-01',
        'value' => 230.5,
        'photo' => UploadedFile::fake()->image('meter2.jpg'),
    ], [
        'Authorization' => 'Bearer '.$token,
        'Accept' => 'application/json',
    ]);

    $response->assertCreated();

    $payload = $response->json();
    expect($payload['photo_url'])->not()->toBeNull();

    $meter->refresh();
    $reading = MeterReading::query()
        ->where('meter_id', $meter->id)
        ->orderByDesc('reading_date')
        ->orderByDesc('id')
        ->first();

    expect($reading?->photo_path)->not()->toBeNull();
    Storage::disk('public')->assertExists($reading->photo_path);
});
