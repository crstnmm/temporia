<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DiaryEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class DiaryEntryController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year'  => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $entries = $request->user()
            ->diaryEntries()
            ->when($request->month, fn ($q) => $q->whereMonth('date', (int) $request->month))
            ->when($request->year,  fn ($q) => $q->whereYear('date',  (int) $request->year))
            ->orderBy('date', 'desc')
            ->get();

        return response()->json($entries);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'date'    => ['required', 'date_format:Y-m-d'],
            'content' => ['required', 'string', 'min:1', 'max:50000'],
            'mood'    => ['nullable', 'in:happy,sad,neutral,excited,anxious'],
        ]);

        if ($data['date'] !== Carbon::today()->toDateString()) {
            return response()->json([
                'message' => 'You can only create a diary entry for today.',
            ], 422);
        }

        $exists = $request->user()
            ->diaryEntries()
            ->whereDate('date', $data['date'])
            ->exists();

        if ($exists) {
            return response()->json([
                'message' => 'A diary entry for today already exists.',
            ], 409);
        }

        $entry = $request->user()->diaryEntries()->create($data);

        return response()->json($entry, 201);
    }

    public function show(Request $request, DiaryEntry $diaryEntry)
    {
        $this->checkOwnership($request->user(), $diaryEntry);

        return response()->json($diaryEntry);
    }

    public function update(Request $request, DiaryEntry $diaryEntry)
    {
        $this->checkOwnership($request->user(), $diaryEntry);

        if ($diaryEntry->is_locked) {
            return response()->json([
                'message' => 'This Temporia entry is locked after the day ends.',
                'locked'  => true,
            ], 403);
        }

        $data = $request->validate([
            'content' => ['required', 'string', 'min:1', 'max:50000'],
            'mood'    => ['nullable', 'in:happy,sad,neutral,excited,anxious'],
        ]);

        $diaryEntry->update($data);

        return response()->json($diaryEntry);
    }

    public function destroy(Request $request, DiaryEntry $diaryEntry)
    {
        $this->checkOwnership($request->user(), $diaryEntry);
        $diaryEntry->delete();

        return response()->json(null, 204);
    }

    private function checkOwnership(object $user, DiaryEntry $entry): void
    {
        abort_if($entry->user_id !== $user->id, 403, 'Forbidden.');
    }
}
