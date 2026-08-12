<?php

namespace Tests\Feature;

use App\Mail\ContactSubmitted;
use App\Models\Contact;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_contact_page_shows_lead_form(): void
    {
        $this->get(route('contact'))
            ->assertOk()
            ->assertSee('Gửi yêu cầu tư vấn')
            ->assertSee(route('contact.store'));
    }

    public function test_guest_can_submit_contact_form_and_admin_is_emailed(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [
            'name' => 'Nguyen Van Lead',
            'email' => 'lead@example.com',
            'phone' => '0900000000',
            'company' => 'Lead Company',
            'service' => 'business_signature',
            'message' => 'Toi muon tu van chu ky so doanh nghiep.',
        ])
            ->assertRedirect(route('contact'))
            ->assertSessionHas('success');

        $contact = Contact::query()->firstOrFail();

        $this->assertSame('Nguyen Van Lead', $contact->name);
        $this->assertSame('lead@example.com', $contact->email);
        $this->assertSame('new', $contact->status);

        Mail::assertSent(ContactSubmitted::class, function (ContactSubmitted $mail) use ($contact) {
            return $mail->hasTo(config('mail.admin_address'))
                && $mail->contact->is($contact);
        });
    }

    public function test_contact_form_validates_required_fields(): void
    {
        Mail::fake();

        $this->post(route('contact.store'), [])
            ->assertSessionHasErrors(['name', 'email', 'message']);

        $this->assertDatabaseCount('contacts', 0);
        Mail::assertNothingSent();
    }

    public function test_contact_form_is_rate_limited(): void
    {
        Mail::fake();

        for ($attempt = 1; $attempt <= 5; $attempt++) {
            $this->withServerVariables(['REMOTE_ADDR' => '10.10.10.10'])
                ->post(route('contact.store'), [
                    'name' => 'Rate Limited Lead',
                    'email' => 'rate@example.com',
                    'message' => 'Noi dung can tu van.',
                ])
                ->assertRedirect(route('contact'));
        }

        $this->withServerVariables(['REMOTE_ADDR' => '10.10.10.10'])
            ->post(route('contact.store'), [
                'name' => 'Rate Limited Lead',
                'email' => 'rate@example.com',
                'message' => 'Noi dung can tu van.',
            ])
            ->assertTooManyRequests();
    }
}
