<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Meter;
use App\Models\MeterReading;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class MeterReadingController extends Controller
{
    public function index(Request $request, Meter $meter): JsonResponse
    {
        $meter = $this->ensureMeterBelongsToUser($request, $meter);

        $readings = $meter->readings()
            ->orderBy('reading_date')
            ->orderBy('id')
            ->get();

        $response = [];
        $previousValue = null;

        foreach ($readings as $reading) {
            $difference = $previousValue !== null ? round($reading->value - $previousValue, 3) : null;
            $response[] = $this->transform($reading, $difference);
            $previousValue = $reading->value;
        }

        return response()->json([
            'data' => array_reverse($response),
        ]);
    }

    public function store(Request $request, Meter $meter): JsonResponse
    {
        $meter = $this->ensureMeterBelongsToUser($request, $meter);

        $validated = $this->validateRequest($request, $meter);

        if ($request->hasFile('photo')) {
            $validated['photo_path'] = $this->storePhoto($request->file('photo'));
        }

        $reading = $meter->readings()->create($validated + ['user_id' => $request->user()->id]);
        $reading->load('meter');

        return response()->json($this->transform($reading), 201);
    }

    public function update(Request $request, Meter $meter, MeterReading $reading): JsonResponse
    {
        $meter = $this->ensureMeterBelongsToUser($request, $meter);
        $this->ensureReadingBelongsToMeter($meter, $reading);

        if (! $reading->isLatestForMeter()) {
            return response()->json([
                'message' => 'Only the latest reading can be updated.',
            ], 422);
        }

        $validated = $this->validateRequest($request, $meter, $reading);

        if ($request->hasFile('photo')) {
            if ($reading->photo_path) {
                Storage::disk('public')->delete($reading->photo_path);
            }

            $validated['photo_path'] = $this->storePhoto($request->file('photo'));
        } elseif ($request->boolean('remove_photo')) {
            Storage::disk('public')->delete($reading->photo_path);
            $validated['photo_path'] = null;
        }

        $reading->update($validated);
        $reading->load('meter');

        return response()->json($this->transform($reading));
    }

    public function destroy(Request $request, Meter $meter, MeterReading $reading): JsonResponse
    {
        $meter = $this->ensureMeterBelongsToUser($request, $meter);
        $this->ensureReadingBelongsToMeter($meter, $reading);

        if (! $reading->isLatestForMeter()) {
            return response()->json([
                'message' => 'Only the latest reading can be deleted.',
            ], 422);
        }

        if ($reading->photo_path) {
            Storage::disk('public')->delete($reading->photo_path);
        }

        $reading->delete();

        return response()->json(status: 204);
    }

    private function ensureMeterBelongsToUser(Request $request, Meter $meter): Meter
    {
        abort_unless($meter->address && $meter->address->userHasAccess($request->user()), 404);

        return $meter;
    }

    private function ensureReadingBelongsToMeter(Meter $meter, MeterReading $reading): void
    {
        abort_unless($reading->meter_id === $meter->id, 404);
    }

    /**
     * @return array<string, mixed>
     */
    private function validateRequest(Request $request, Meter $meter, ?MeterReading $reading = null): array
    {
        $readingId = $reading?->id;

        $validated = $request->validate([
            'reading_date' => ['required', 'date', Rule::unique('meter_readings')->where(fn ($query) => $query->where('meter_id', $meter->id))->ignore($readingId)],
            'value' => ['required', 'numeric', 'gte:0'],
            'notes' => ['nullable', 'string'],
            'remove_photo' => ['nullable', 'boolean'],
            'photo' => ['nullable', 'image', 'max:4096'],
        ]);

        $previous = $meter->readings()
            ->where('reading_date', '<', $validated['reading_date'])
            ->when($readingId, fn ($query) => $query->where('id', '!=', $readingId))
            ->orderByDesc('reading_date')
            ->orderByDesc('id')
            ->first();

        if ($previous && $validated['value'] < $previous->value) {
            throw ValidationException::withMessages([
                'value' => 'The new reading must be greater than or equal to the previous value.',
            ]);
        }

        return Arr::except($validated, ['remove_photo', 'photo']);
    }

    private function storePhoto(UploadedFile $file): string
    {
        return $file->store('meter-readings', 'public');
    }

    /**
     * @param  float|null  $difference
     * @return array<string, mixed>
     */
    private function transform(MeterReading $reading, ?float $difference = null): array
    {
        return [
            'id' => $reading->id,
            'meter_id' => $reading->meter_id,
            'value' => $reading->value,
            'reading_date' => $reading->reading_date->toDateString(),
            'difference' => $difference ?? $reading->difference(),
            'photo_url' => $reading->photo_path ? Storage::disk('public')->url($reading->photo_path) : null,
            'notes' => $reading->notes,
            'created_at' => $reading->created_at?->toISOString(),
            'updated_at' => $reading->updated_at?->toISOString(),
        ];
    }
}
