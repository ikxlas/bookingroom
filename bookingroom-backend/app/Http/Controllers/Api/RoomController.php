<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index()
    {
        $today = date('Y-m-d');
        return \App\Models\Room::all()->map(function($r) use ($today) {
            $upcomingBooking = \App\Models\Booking::where('room_id', $r->id)
                ->where('date', '>=', $today)
                ->where('status', 'Disetujui')
                ->orderBy('date', 'asc')
                ->first();

            return [
                'id' => (string) $r->id,
                'name' => $r->name,
                'capacity' => $r->capacity,
                'target' => $r->target,
                'status' => $r->status,
                'is_in_use' => $upcomingBooking ? true : false,
                'used_date' => $upcomingBooking ? $upcomingBooking->date : null
            ];
        });
    }

    public function store(Request $request)
    {
        if ($request->user()->role !== 'admin') abort(403);
        $room = \App\Models\Room::create($request->all());
        return response()->json([
            'id' => (string) $room->id,
            'name' => $room->name,
            'capacity' => $room->capacity,
            'target' => $room->target,
            'status' => $room->status
        ], 201);
    }

    public function update(Request $request, \App\Models\Room $room)
    {
        if ($request->user()->role !== 'admin') abort(403);
        $room->update($request->all());
        return response()->json([
            'id' => (string) $room->id,
            'name' => $room->name,
            'capacity' => $room->capacity,
            'target' => $room->target,
            'status' => $room->status
        ]);
    }

    public function destroy(Request $request, \App\Models\Room $room)
    {
        if ($request->user()->role !== 'admin') abort(403);
        $room->delete();
        return response()->json(['success' => true]);
    }
}
