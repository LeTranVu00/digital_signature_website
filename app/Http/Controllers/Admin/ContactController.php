<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ContactController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'service' => ['nullable', 'string', Rule::in(array_keys(Contact::SERVICES))],
            'status' => ['nullable', 'string', Rule::in(Contact::STATUSES)],
        ]);

        $contacts = Contact::query()
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query
                        ->where('name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%')
                        ->orWhere('phone', 'like', '%'.$search.'%')
                        ->orWhere('company', 'like', '%'.$search.'%')
                        ->orWhere('message', 'like', '%'.$search.'%');
                });
            })
            ->when($filters['service'] ?? null, function ($query, string $service) {
                $query->where('service', $service);
            })
            ->when($filters['status'] ?? null, function ($query, string $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.contacts.index', compact(
            'contacts',
            'filters'
        ));
    }

    public function updateStatus(Request $request, Contact $contact): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', Rule::in(Contact::STATUSES)],
        ]);

        $contact->update([
            'status' => $validated['status'],
        ]);

        return redirect()
            ->route('admin.contacts.index')
            ->with('success', 'Cap nhat trang thai lien he thanh cong.');
    }
}
