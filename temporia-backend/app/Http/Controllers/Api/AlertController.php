<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Alert;
use Illuminate\Http\Request;

class AlertController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'date'  => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year'  => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $alerts = $request->user()
            ->alerts()
            ->when($request->date,  fn ($q) => $q->whereDate('alert_date', $request->date))
            ->when($request->month, fn ($q) => $q->whereMonth('alert_date', (int) $request->month))
            ->when($request->year,  fn ($q) => $q->whereYear('alert_date',  (int) $request->year))
            ->orderBy('alert_date')
            ->orderBy('alert_time')
            ->get();

        return response()->json($alerts);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'alert_date'  => ['required', 'date_format:Y-m-d'],
            'title'       => ['required', 'string', 'min:1', 'max:255'],
            'alert_time'  => ['nullable', 'date_format:H:i'],
            'priority'    => ['nullable', 'in:low,medium,high'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $alert = $request->user()->alerts()->create($data);

        return response()->json($alert, 201);
    }

    public function update(Request $request, Alert $alert)
    {
        $this->checkOwnership($request->user(), $alert);

        $data = $request->validate([
            'title'       => ['sometimes', 'string', 'min:1', 'max:255'],
            'alert_time'  => ['nullable', 'date_format:H:i'],
            'priority'    => ['nullable', 'in:low,medium,high'],
            'description' => ['nullable', 'string', 'max:2000'],
        ]);

        $alert->update($data);

        return response()->json($alert);
    }

    public function destroy(Request $request, Alert $alert)
    {
        $this->checkOwnership($request->user(), $alert);
        $alert->delete();

        return response()->json(null, 204);
    }

    private function checkOwnership(object $user, Alert $alert): void
    {
        abort_if($alert->user_id !== $user->id, 403, 'Forbidden.');
    }
}
