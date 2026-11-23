<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $query = Contact::withCount(['bookings', 'bookings as confirmed_bookings_count' => function ($q) {
            $q->where('status', 'CONFIRMED');
        }])->latest();

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $contacts = $query->paginate(20);

        return view('admin.contacts.index', [
            'contacts' => $contacts,
            'search' => $search,
        ]);
    }

    public function show(int $id): View
    {
        $contact = Contact::with(['bookings.items.category', 'bookings.tickets'])->findOrFail($id);

        return view('admin.contacts.show', [
            'contact' => $contact,
        ]);
    }
}
