<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\CalendarEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CalendarController extends Controller
{
    public function index()
    {
        $events = CalendarEvent::where('user_id', Auth::id())
            ->orderBy('event_date', 'asc')
            ->get()
            ->map(function ($event) {
                return [
                    'id' => $event->id,
                    'title' => $event->title,
                    'date' => $event->event_date->toISOString(),
                    'description' => $event->description,
                    'type' => $event->event_type,
                    'color' => $event->color,
                ];
            });

        return response()->json($events);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'date' => 'required|date',
            'description' => 'nullable|string',
            'event_type' => 'nullable|string|in:appointment,meeting,event',
            'color' => 'nullable|string',
        ]);

        $event = CalendarEvent::create([
            'user_id' => Auth::id(),
            'title' => $request->title,
            'event_date' => Carbon::parse($request->date),
            'description' => $request->description,
            'event_type' => $request->event_type ?? 'appointment',
            'color' => $request->color ?? '#3b82f6',
        ]);

        return response()->json([
            'success' => true,
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'date' => $event->event_date->toISOString(),
                'description' => $event->description,
                'type' => $event->event_type,
                'color' => $event->color,
            ],
        ]);
    }

    public function destroy($id)
    {
        $event = CalendarEvent::where('user_id', Auth::id())->findOrFail($id);
        $event->delete();

        return response()->json(['success' => true]);
    }
}

