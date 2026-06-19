<?php

namespace Tests\Feature\Api;

use App\Models\User;
use App\Notifications\AppointmentScheduled;
use Illuminate\Support\Facades\Notification;

class NotificationApiTest extends ApiTestCase
{
    public function test_can_list_unread_notifications(): void
    {
        $user  = $this->adminUser();
        $token = $this->apiToken($user);

        $user->notifications()->create([
            'id'              => \Illuminate\Support\Str::uuid(),
            'type'            => 'App\\Notifications\\AppointmentScheduled',
            'data'            => ['title' => 'Test', 'message' => 'Test notification'],
            'read_at'         => null,
            'notifiable_id'   => $user->id,
            'notifiable_type' => User::class,
        ]);

        $this->getJson('/api/notifications', ['Authorization' => "Bearer {$token}"])
            ->assertOk()
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_can_get_unread_notification_count(): void
    {
        $user  = $this->adminUser();
        $token = $this->apiToken($user);

        $this->getJson('/api/notifications/unread-count', ['Authorization' => "Bearer {$token}"])
            ->assertOk()
            ->assertJsonStructure(['count'])
            ->assertJsonFragment(['count' => 0]);
    }

    public function test_can_mark_individual_notification_as_read(): void
    {
        $user  = $this->adminUser();
        $token = $this->apiToken($user);
        $headers = ['Authorization' => "Bearer {$token}"];

        // Send a real database notification via Notifiable trait
        $user->notifications()->create([
            'id'            => $id = \Illuminate\Support\Str::uuid(),
            'type'          => 'App\\Notifications\\AppointmentScheduled',
            'data'          => ['title' => 'Appointment', 'message' => 'Test'],
            'read_at'       => null,
            'notifiable_id' => $user->id,
            'notifiable_type' => User::class,
        ]);

        $this->patchJson("/api/notifications/{$id}/read", [], $headers)
            ->assertOk()
            ->assertJsonFragment(['message' => 'Notification marked as read.']);

        $this->assertDatabaseHas('notifications', ['id' => $id]);
        $notification = $user->notifications()->find($id);
        $this->assertNotNull($notification->read_at);
    }

    public function test_can_mark_all_notifications_as_read(): void
    {
        $user  = $this->adminUser();
        $token = $this->apiToken($user);

        // Create two unread notifications
        foreach (range(1, 2) as $i) {
            $user->notifications()->create([
                'id'              => \Illuminate\Support\Str::uuid(),
                'type'            => 'App\\Notifications\\AppointmentScheduled',
                'data'            => ['title' => "Notification {$i}"],
                'read_at'         => null,
                'notifiable_id'   => $user->id,
                'notifiable_type' => User::class,
            ]);
        }

        $this->assertEquals(2, $user->unreadNotifications()->count());

        $this->postJson('/api/notifications/mark-all-read', [], ['Authorization' => "Bearer {$token}"])
            ->assertOk()
            ->assertJsonFragment(['message' => 'All notifications marked as read.']);

        $this->assertEquals(0, $user->fresh()->unreadNotifications()->count());
    }

    public function test_notifications_endpoint_requires_authentication(): void
    {
        $this->getJson('/api/notifications')->assertUnauthorized();
    }
}
