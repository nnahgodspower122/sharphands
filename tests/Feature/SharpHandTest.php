<?php

namespace Tests\Feature;

use App\Enums\BookingStatus;
use App\Enums\WorkerStatus;
use App\Models\Service;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SharpHandTest extends TestCase
{
    use RefreshDatabase;

    public function test_spa_pages_render(): void
    {
        $this->get('/')->assertOk()->assertSee('id="app"', false);
        $this->get('/about')->assertOk()->assertSee('id="app"', false);
        $this->get('/contact')->assertOk()->assertSee('id="app"', false);
        $this->get('/get-started')->assertOk()->assertSee('id="app"', false);
        $this->get('/admin')->assertOk()->assertSee('id="app"', false);
    }

    public function test_user_can_register_and_book_the_nearest_worker(): void
    {
        $service = Service::factory()->create(['slug' => 'plumbing-01', 'name' => 'Plumbing', 'base_amount' => 8000]);
        $workerUser = User::factory()->worker()->create();
        $worker = Worker::factory()->create([
            'user_id' => $workerUser->id,
            'name' => $workerUser->name,
            'service_id' => $service->id,
            'latitude' => 6.5244,
            'longitude' => 3.3792,
            'status' => WorkerStatus::Available,
            'verified' => true,
        ]);

        $register = $this->postJson('/api/register', [
            'name' => 'Amina Balogun',
            'email' => 'amina@example.com',
            'phone' => '+2348011111111',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $register->assertCreated();
        $token = $register->json('user.token');

        $this->withToken($token)
            ->postJson('/api/book', [
                'service_id' => $service->id,
                'latitude' => 6.5250,
                'longitude' => 3.3800,
            ])
            ->assertCreated();

        $this->assertDatabaseHas('bookings', [
            'user_id' => $register->json('user.id'),
            'worker_id' => $worker->id,
            'status' => BookingStatus::Pending->value,
        ]);

        $this->assertEquals(WorkerStatus::Busy, $worker->fresh()->status);
    }

    public function test_admin_can_open_the_dashboard(): void
    {
        $admin = User::factory()->admin()->create([
            'email' => 'admin@sharphand.ng',
        ]);
        $token = $admin->issueApiToken();

        $this->withToken($token)
            ->getJson('/api/admin/dashboard')
            ->assertOk()
            ->assertJsonStructure(['stats', 'bookings', 'queue', 'performance']);
    }

    public function test_api_login_and_booking_flow(): void
    {
        $service = Service::factory()->create(['slug' => 'electrical-01', 'base_amount' => 10000]);
        $user = User::factory()->create([
            'email' => 'user@example.com',
            'password' => 'password',
        ]);
        $workerUser = User::factory()->worker()->create();
        Worker::factory()->create([
            'user_id' => $workerUser->id,
            'service_id' => $service->id,
            'latitude' => 6.52,
            'longitude' => 3.37,
            'verified' => true,
            'status' => WorkerStatus::Available,
        ]);

        $login = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password',
        ]);

        $login->assertOk();
        $token = $login->json('user.token');
        $this->assertNotEmpty($token);

        $this->getJson('/api/services')->assertOk()->assertJsonPath('data.0.name', $service->name);

        $this->withToken($token)
            ->postJson('/api/book', [
                'service_id' => $service->id,
                'latitude' => 6.521,
                'longitude' => 3.371,
            ])
            ->assertCreated()
            ->assertJsonPath('booking.user_id', $user->id);

        $this->withToken($token)
            ->getJson('/api/bookings')
            ->assertOk()
            ->assertJsonPath('data.data.0.service_id', $service->id);
    }

    public function test_non_admin_cannot_access_admin_api(): void
    {
        $user = User::factory()->create();
        $token = $user->issueApiToken();

        $this->withToken($token)
            ->getJson('/api/admin/users')
            ->assertForbidden();
    }

    public function test_contact_form_stores_a_message(): void
    {
        $this->postJson('/api/contact', [
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'subject' => 'Partnership',
            'message' => 'I would like to partner with SharpHand.',
        ])->assertCreated();

        $this->assertDatabaseHas('contact_messages', [
            'email' => 'john@example.com',
            'subject' => 'Partnership',
        ]);
    }

    public function test_admin_can_verify_a_worker(): void
    {
        $admin = User::factory()->admin()->create();
        $worker = Worker::factory()->unverified()->create();
        $token = $admin->issueApiToken();

        $this->withToken($token)
            ->postJson('/api/admin/workers/'.$worker->id.'/approve')
            ->assertOk();

        $this->assertTrue($worker->fresh()->verified);
    }

    public function test_guest_can_list_nearby_workers_without_a_token(): void
    {
        $service = Service::factory()->create(['slug' => 'plumbing-guest']);
        $worker = Worker::factory()->create([
            'service_id' => $service->id,
            'phone' => '+2348099990000',
            'latitude' => 6.5244,
            'longitude' => 3.3792,
            'verified' => true,
            'status' => WorkerStatus::Available,
        ]);

        $this->getJson('/api/workers/nearby?service_id='.$service->id.'&lat=6.5244&lng=3.3792')
            ->assertOk()
            ->assertJsonPath('data.0.id', $worker->id)
            ->assertJsonPath('data.0.name', $worker->name)
            ->assertJsonMissingPath('data.0.phone')
            ->assertJsonMissing(['phone' => '+2348099990000']);

        $this->getJson('/api/workers/nearby?lat=6.5244&lng=3.3792')
            ->assertOk()
            ->assertJsonPath('data.0.id', $worker->id)
            ->assertJsonMissingPath('data.0.phone');
    }

    public function test_guest_can_see_nearby_service_counts(): void
    {
        $service = Service::factory()->create(['slug' => 'cleaning-guest']);
        Worker::factory()->create([
            'service_id' => $service->id,
            'latitude' => 6.5244,
            'longitude' => 3.3792,
            'verified' => true,
            'status' => WorkerStatus::Available,
        ]);

        $this->getJson('/api/services/nearby?lat=6.5244&lng=3.3792')
            ->assertOk()
            ->assertJsonFragment([
                'id' => $service->id,
                'nearby_count' => 1,
            ]);
    }

    public function test_guest_can_book_with_name_and_phone(): void
    {
        $service = Service::factory()->create(['slug' => 'electrical-guest', 'base_amount' => 9000]);
        $worker = Worker::factory()->create([
            'service_id' => $service->id,
            'name' => 'Chidi Mensah',
            'phone' => '+2348088881111',
            'latitude' => 6.5244,
            'longitude' => 3.3792,
            'verified' => true,
            'status' => WorkerStatus::Available,
        ]);

        $this->postJson('/api/book', [
            'name' => 'Amina Balogun',
            'phone' => '+2348011111111',
            'service_id' => $service->id,
            'worker_id' => $worker->id,
            'latitude' => 6.5250,
            'longitude' => 3.3800,
        ])
            ->assertCreated()
            ->assertJsonPath('worker.phone', '+2348088881111')
            ->assertJsonPath('booking.user_id', null)
            ->assertJsonPath('booking.guest_name', 'Amina Balogun')
            ->assertJsonPath('booking.guest_phone', '+2348011111111');

        $this->assertDatabaseHas('bookings', [
            'worker_id' => $worker->id,
            'user_id' => null,
            'guest_name' => 'Amina Balogun',
            'guest_phone' => '+2348011111111',
        ]);

        $this->assertEquals(WorkerStatus::Busy, $worker->fresh()->status);
    }

    public function test_guest_booking_requires_name_and_phone(): void
    {
        $service = Service::factory()->create();

        $this->postJson('/api/book', [
            'service_id' => $service->id,
            'latitude' => 6.5244,
            'longitude' => 3.3792,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'phone']);
    }
}
