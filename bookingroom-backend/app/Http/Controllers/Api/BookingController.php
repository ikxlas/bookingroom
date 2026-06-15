<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $query = \App\Models\Booking::with(['user', 'room']);
        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }
        return $query->get()->map(function($booking) {
            return [
                'id' => (string) $booking->id,
                'user' => $booking->user->username,
                'roomName' => $booking->room->name ?? 'Unknown',
                'roomId' => (string) $booking->room_id,
                'date' => $booking->date,
                'duration' => $booking->duration,
                'purpose' => $booking->purpose,
                'status' => $booking->status,
                'rejection_reason' => $booking->rejection_reason,
                'timestamp' => $booking->created_at->toISOString()
            ];
        });
    }

    public function store(Request $request)
    {
        $exists = \App\Models\Booking::where('room_id', $request->roomId)
            ->where('date', $request->date)
            ->where('status', 'Disetujui')
            ->exists();
            
        if ($exists) {
            return response()->json([
                'success' => false, 
                'message' => 'Ruangan ini sudah dipesan dan disetujui untuk pengguna lain pada tanggal tersebut.'
            ], 400);
        }

        $booking = \App\Models\Booking::create([
            'user_id' => $request->user()->id,
            'room_id' => $request->roomId,
            'date' => $request->date,
            'duration' => $request->duration,
            'purpose' => $request->purpose,
            'status' => 'Menunggu Konfirmasi'
        ]);
        return response()->json(['success' => true], 201);
    }

    public function updateStatus(Request $request, \App\Models\Booking $booking)
    {
        if ($request->user()->role !== 'admin') abort(403);
        $updateData = ['status' => $request->newStatus];
        if ($request->newStatus === 'Ditolak' && $request->has('rejection_reason')) {
            $updateData['rejection_reason'] = $request->rejection_reason;
        }
        $booking->update($updateData);
        return response()->json(['success' => true]);
    }

    public function getBookedDates($roomId)
    {
        $dates = \App\Models\Booking::where('room_id', $roomId)
            ->where('status', 'Disetujui')
            ->pluck('date')
            ->unique()
            ->values();
            
        return response()->json($dates);
    }
}
