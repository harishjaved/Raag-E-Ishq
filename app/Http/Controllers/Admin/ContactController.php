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
        }]);

        $search = trim((string) $request->query('search', ''));
        if ($search !== '') {
            $query->searchTerm($search);
        }

        $contacts = $query->latest()->paginate(20)->withQueryString();

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
