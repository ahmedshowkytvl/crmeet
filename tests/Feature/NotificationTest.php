<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function user_can_fetch_their_notifications()
    {
        Notification::factory()->count(5)->for($this->user)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/notifications');

        $response->assertOk()
            ->assertJsonStructure([
                'success',
                'data' => [
                    'notifications' => [
                        '*' => ['id', 'type', 'title', 'body', 'is_read', 'created_at']
                    ],
                    'pagination'
                ]
            ]);
    }

    /** @test */
    public function user_can_get_unread_count()
    {
        Notification::factory()->count(3)->unread()->for($this->user)->create();
        Notification::factory()->count(2)->read()->for($this->user)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/notifications/count');

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'data' => ['unread_count' => 3]
            ]);
    }

    /** @test */
    public function user_can_create_notification()
    {
        $targetUser = User::factory()->create();

        $data = [
            'user_id' => $targetUser->id,
            'type' => 'message',
            'title' => 'Test Notification',
            'body' => 'This is a test',
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/notifications', $data);

        $response->assertCreated()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('notifications', [
            'user_id' => $targetUser->id,
            'type' => 'message',
            'title' => 'Test Notification',
        ]);
    }

    /** @test */
    public function user_can_mark_notification_as_read()
    {
        $notification = Notification::factory()->unread()->for($this->user)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/notifications/mark-read', [
                'notification_ids' => [$notification->id]
            ]);

        $response->assertOk();

        $this->assertTrue($notification->fresh()->is_read);
    }

    /** @test */
    public function user_can_mark_all_as_read()
    {
        Notification::factory()->count(5)->unread()->for($this->user)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/notifications/mark-read', [
                'mark_all' => true
            ]);

        $response->assertOk();

        $this->assertEquals(0, Notification::where('user_id', $this->user->id)
            ->where('is_read', false)
            ->count());
    }

    /** @test */
    public function user_can_delete_their_notification()
    {
        $notification = Notification::factory()->for($this->user)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/notifications/{$notification->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('notifications', ['id' => $notification->id]);
    }

    /** @test */
    public function user_cannot_delete_other_users_notification()
    {
        $otherUser = User::factory()->create();
        $notification = Notification::factory()->for($otherUser)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/notifications/{$notification->id}");

        $response->assertNotFound();
    }

    /** @test */
    public function user_can_filter_notifications_by_type()
    {
        Notification::factory()->count(3)->message()->for($this->user)->create();
        Notification::factory()->count(2)->task()->for($this->user)->create();

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/notifications?type=message');

        $response->assertOk();
        
        $notifications = $response->json('data.notifications');
        $this->assertCount(3, $notifications);
        $this->assertEquals('message', $notifications[0]['type']);
    }

    /** @test */
    public function user_can_update_notification_preferences()
    {
        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/notifications/preferences', [
                'type' => 'message',
                'enabled' => false,
                'sound_enabled' => true,
            ]);

        $response->assertOk();

        $this->assertDatabaseHas('notification_preferences', [
            'user_id' => $this->user->id,
            'type' => 'message',
            'enabled' => false,
        ]);
    }
}

