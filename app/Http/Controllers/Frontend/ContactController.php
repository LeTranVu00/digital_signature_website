<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Mail\ContactSubmitted;
use App\Models\Contact;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    public function store(StoreContactRequest $request): RedirectResponse
    {
        $contact = Contact::query()->create(array_merge(
            $request->validated(),
            ['status' => 'new']
        ));

        Mail::to(config('mail.admin_address', config('mail.from.address')))
            ->send(new ContactSubmitted($contact));

        return redirect()
            ->route('contact')
            ->with('success', 'Cam on ban da lien he. Chung toi se phan hoi som.');
    }
}
