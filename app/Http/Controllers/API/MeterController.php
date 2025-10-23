<?php

namespace App\Http\Controllers\API;

use App\Enums\MeterType;
use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\Meter;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Enum;

class MeterController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        $meters = Meter::query()
            ->forUser($user)
            ->with(['address.owner'])
            ->withCount('readings')
            ->orderBy('type')
            ->get()
            ->map(fn (Meter $meter) => $this->transform($meter));

        return response()->json(['data' => $meters]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validateMeter($request);

        $address = Address::query()->findOrFail($validated['address_id']);
        $this->ensureAddressAccess($request->user(), $address, ownersOnly: true);

        $meter = $address->meters()->create(
            collect($validated)->except('address_id')->toArray()
        );

        $meter->refresh()->loadCount('readings');

        return response()->json($this->transform($meter), 201);
    }

    public function show(Request $request, Meter $meter): JsonResponse
    {
        $meter = $this->ensureMeterBelongsToUser($request, $meter);
        $meter->load(['address.owner'])->loadCount('readings');

        return response()->json($this->transform($meter));
    }

    public function update(Request $request, Meter $meter): JsonResponse
    {
        $meter = $this->ensureMeterBelongsToUser($request, $meter, ownersOnly: true);

        $validated = $this->validateMeter($request, $meter->id);

        if (isset($validated['address_id'])) {
            $address = Address::findOrFail($validated['address_id']);
            $this->ensureAddressAccess($request->user(), $address, ownersOnly: true);
            $meter->address()->associate($address);
        }

        $meter->fill(collect($validated)->except('address_id')->toArray());
        $meter->save();
        $meter->refresh()->loadCount('readings');

        return response()->json($this->transform($meter));
    }

    public function destroy(Request $request, Meter $meter): JsonResponse
    {
        $meter = $this->ensureMeterBelongsToUser($request, $meter, ownersOnly: true);

        if ($meter->readings()->exists()) {
            return response()->json([
                'message' => 'Cannot delete a meter that has readings.',
            ], 422);
        }

        $meter->delete();

        return response()->json(status: 204);
    }

    private function ensureMeterBelongsToUser(Request $request, Meter $meter, bool $ownersOnly = false): Meter
    {
        $this->ensureAddressAccess($request->user(), $meter->address, $ownersOnly);

        return $meter;
    }

    /**
     * @return array<string, mixed>
     */
    private function validateMeter(Request $request, ?int $meterId = null): array
    {
        $rules = [
            'type' => ['required', new Enum(MeterType::class)],
            'unit' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
            'submission_day' => ['nullable', 'integer', 'min:1', 'max:31'],
        ];

        $addressRule = ['integer', 'exists:addresses,id'];

        if ($meterId) {
            $rules['address_id'] = array_merge(['sometimes'], $addressRule);
        } else {
            $rules['address_id'] = array_merge(['required'], $addressRule);
        }

        return $request->validate($rules);
    }

    /**
     * @return array<string, mixed>
     */
    private function transform(Meter $meter): array
    {
        $latestReading = $meter->readings()->latestFirst()->first();

        return [
            'id' => $meter->id,
            'type' => $meter->type?->value,
            'type_label' => $meter->type instanceof MeterType ? $meter->type->getLabel() : null,
            'unit' => $meter->unit,
            'description' => $meter->description,
            'submission_day' => $meter->submission_day,
            'readings_count' => $meter->readings_count ?? $meter->readings()->count(),
            'address' => $meter->address ? [
                'id' => $meter->address->id,
                'name' => $meter->address->name,
            ] : null,
            'latest_reading' => $latestReading ? [
                'id' => $latestReading->id,
                'value' => $latestReading->value,
                'reading_date' => $latestReading->reading_date->toDateString(),
            ] : null,
            'created_at' => $meter->created_at?->toISOString(),
            'updated_at' => $meter->updated_at?->toISOString(),
        ];
    }

    private function ensureAddressAccess(User $user, Address $address, bool $ownersOnly = false): void
    {
        if ($ownersOnly) {
            abort_unless($address->owner_id === $user->getKey(), 403, 'You do not have permission to manage this resource.');

            return;
        }

        abort_unless($address->userHasAccess($user), 403, 'You do not have permission to access this resource.');
    }
}
