<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $query = Booking::with(['contact', 'items.category'])->latest();

        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%{$search}%")
                  ->orWhereHas('contact', function ($cq) use ($search) {
                      $cq->where('full_name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $bookings = $query->paginate(20);

        return view('admin.bookings.index', [
            'bookings' => $bookings,
            'currentStatus' => $status,
            'search' => $search,
        ]);
    }

    public function show(int $id): View
    {
        $booking = Booking::with([
            'contact',
            'items.category',
            'paymentAttempts',
            'tickets.category',
            'tickets.checkin.scannedBy',
            'refunds',
        ])->findOrFail($id);

        return view('admin.bookings.show', [
            'booking' => $booking,
        ]);
    }
}
