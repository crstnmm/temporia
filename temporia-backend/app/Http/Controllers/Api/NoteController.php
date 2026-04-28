<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Note;
use Illuminate\Http\Request;

class NoteController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date'  => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year'  => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $notes = $request->user()
            ->notes()
            ->when($request->date,  fn ($q) => $q->whereDate('note_date', $request->date))
            ->when($request->month, fn ($q) => $q->whereMonth('note_date', (int) $request->month))
            ->when($request->year,  fn ($q) => $q->whereYear('note_date',  (int) $request->year))
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($notes);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'note_date' => ['required', 'date_format:Y-m-d'],
            'title'     => ['required', 'string', 'min:1', 'max:255'],
            'body'      => ['required', 'string', 'min:1', 'max:10000'],
            'color'     => ['nullable', 'in:indigo,rose,amber,emerald'],
        ]);

        $note = $request->user()->notes()->create($data);

        return response()->json($note, 201);
    }

    public function show(Request $request, Note $note)
    {
        $this->checkOwnership($request->user(), $note);

        return response()->json($note);
    }

    public function update(Request $request, Note $note)
    {
        $this->checkOwnership($request->user(), $note);

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'min:1', 'max:255'],
            'body'  => ['sometimes', 'string', 'min:1', 'max:10000'],
            'color' => ['nullable', 'in:indigo,rose,amber,emerald'],
        ]);

        $note->update($data);

        return response()->json($note);
    }

    public function destroy(Request $request, Note $note)
    {
        $this->checkOwnership($request->user(), $note);
        $note->delete();

        return response()->json(null, 204);
    }

    private function checkOwnership(object $user, Note $note): void
    {
        abort_if($note->user_id !== $user->id, 403, 'Forbidden.');
    }
}
