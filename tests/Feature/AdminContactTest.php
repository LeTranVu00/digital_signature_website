<?php

namespace Tests\Feature;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_regular_user_cannot_access_admin_contacts(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $this->actingAs($user)
            ->get(route('admin.contacts.index'))
            ->assertForbidden();
    }

    public function test_admin_can_see_contacts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        Contact::query()->create([
            'name' => 'Lead One',
            'email' => 'lead-one@example.com',
            'phone' => '0900000000',
            'company' => 'Lead Company',
            'service' => 'e_invoice',
            'message' => 'Can tu van hoa don dien tu.',
            'status' => 'new',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.contacts.index'))
            ->assertOk()
            ->assertSee('Lead One')
            ->assertSee('lead-one@example.com')
            ->assertSee('Lead Company')
            ->assertSee('Hoa don dien tu')
            ->assertSee('Can tu van hoa don dien tu.')
            ->assertSee('Moi');
    }

    public function test_admin_can_search_and_filter_contacts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $matchedContact = Contact::query()->create([
            'name' => 'Filtered Lead',
            'email' => 'filtered@example.com',
            'service' => 'e_contract',
            'message' => 'Can hop dong dien tu.',
            'status' => 'contacted',
        ]);

        Contact::query()->create([
            'name' => 'Other Lead',
            'email' => 'other@example.com',
            'service' => 'personal_signature',
            'message' => 'Can chu ky ca nhan.',
            'status' => 'new',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.contacts.index', [
                'search' => 'filtered',
                'service' => 'e_contract',
                'status' => 'contacted',
            ]))
            ->assertOk()
            ->assertSee($matchedContact->email)
            ->assertDontSee('other@example.com');
    }

    public function test_admin_can_update_contact_status(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $contact = Contact::query()->create([
            'name' => 'Lead Status',
            'email' => 'status@example.com',
            'message' => 'Can cap nhat trang thai.',
            'status' => 'new',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.contacts.status', $contact), [
                'status' => 'completed',
            ])
            ->assertRedirect(route('admin.contacts.index'));

        $this->assertSame('completed', $contact->refresh()->status);
    }

    public function test_invalid_contact_status_is_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $contact = Contact::query()->create([
            'name' => 'Lead Status',
            'email' => 'status@example.com',
            'message' => 'Can cap nhat trang thai.',
            'status' => 'new',
        ]);

        $this->actingAs($admin)
            ->patch(route('admin.contacts.status', $contact), [
                'status' => 'invalid',
            ])
            ->assertSessionHasErrors('status');

        $this->assertSame('new', $contact->refresh()->status);
    }
}
