<?php

namespace Tests\Feature;

use App\Mail\NewContactEnquiryMail;
use App\Models\JobOpening;
use App\Models\NewsletterSubscriber;
use App\Models\Setting;
use App\Models\SolutionCategory;
use App\Models\SolutionItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PublicApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_settings_endpoint_returns_a_flat_key_value_map(): void
    {
        Setting::create(['key' => 'contact_email', 'value' => 'info@xponent-global.com']);

        $this->getJson('/api/v1/settings')
            ->assertOk()
            ->assertJson(['contact_email' => 'info@xponent-global.com']);
    }

    public function test_solutions_endpoint_returns_categories_with_nested_items(): void
    {
        $category = SolutionCategory::create([
            'title' => 'Exploration',
            'slug' => 'exploration',
            'sort_order' => 1,
        ]);
        SolutionItem::create([
            'solution_category_id' => $category->id,
            'title' => 'Drill Rods',
            'sort_order' => 1,
        ]);

        $response = $this->getJson('/api/v1/solutions');

        $response->assertOk()
            ->assertJsonPath('data.0.title', 'Exploration')
            ->assertJsonPath('data.0.items.0.title', 'Drill Rods');
    }

    public function test_jobs_endpoint_only_returns_open_jobs(): void
    {
        JobOpening::create(['title' => 'Open Role', 'slug' => 'open-role', 'employment_type' => 'full_time', 'status' => 'open']);
        JobOpening::create(['title' => 'Closed Role', 'slug' => 'closed-role', 'employment_type' => 'full_time', 'status' => 'closed']);

        $response = $this->getJson('/api/v1/jobs');

        $response->assertOk()->assertJsonCount(1, 'data')->assertJsonPath('data.0.title', 'Open Role');
    }

    public function test_contact_enquiry_can_be_submitted(): void
    {
        $response = $this->postJson('/api/v1/contact-enquiries', [
            'enquiry_type' => 'Drilling Consumables',
            'region' => 'Asia',
            'country' => 'Philippines',
            'name' => 'Test User',
            'email' => 'test@example.com',
            'message' => 'Hello, I have a question.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('contact_enquiries', ['email' => 'test@example.com']);
    }

    public function test_contact_enquiry_notification_is_queued_not_sent_inline(): void
    {
        // The notification used to be sent inside the request, which made the
        // visitor wait on the mail host (~3.4s of a 3.8s response in production).
        // assertNotSent is the half that matters: it fails if someone drops the
        // ShouldQueue contract and quietly puts the round trip back in the request.
        Mail::fake();

        $this->postJson('/api/v1/contact-enquiries', [
            'enquiry_type' => 'Drilling Consumables',
            'region' => 'Asia',
            'country' => 'Philippines',
            'name' => 'Test User',
            'email' => 'queued@example.com',
            'message' => 'Hello, I have a question.',
        ])->assertCreated();

        Mail::assertQueued(NewContactEnquiryMail::class);
        Mail::assertNotSent(NewContactEnquiryMail::class);
    }

    public function test_contact_enquiry_honeypot_field_rejects_bots(): void
    {
        $response = $this->postJson('/api/v1/contact-enquiries', [
            'enquiry_type' => 'Drilling Consumables',
            'region' => 'Asia',
            'country' => 'Philippines',
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'Spam.',
            'website' => 'http://spam.example.com',
        ]);

        $response->assertStatus(422);
        $this->assertDatabaseMissing('contact_enquiries', ['email' => 'bot@example.com']);
    }

    public function test_newsletter_subscribe_and_unsubscribe(): void
    {
        $this->postJson('/api/v1/newsletter-subscribers', ['email' => 'subscriber@example.com'])
            ->assertCreated();

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'subscriber@example.com',
            'status' => 'subscribed',
        ]);

        $this->postJson('/api/v1/newsletter-subscribers/unsubscribe', ['email' => 'subscriber@example.com'])
            ->assertOk();

        $this->assertDatabaseHas('newsletter_subscribers', [
            'email' => 'subscriber@example.com',
            'status' => 'unsubscribed',
        ]);
    }

    public function test_unsubscribing_an_unknown_email_still_returns_success(): void
    {
        // Prevents this endpoint being used to probe which emails are subscribed.
        $this->postJson('/api/v1/newsletter-subscribers/unsubscribe', ['email' => 'nobody@example.com'])
            ->assertOk();

        $this->assertSame(0, NewsletterSubscriber::count());
    }
}
