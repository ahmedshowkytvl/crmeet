<?php

namespace Database\Factories;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    protected $model = Notification::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['message', 'task', 'asset']);

        return [
            'user_id' => User::factory(),
            'type' => $type,
            'title' => $this->getTitleForType($type),
            'body' => $this->getBodyForType($type),
            'actor_id' => User::factory(),
            'resource_type' => $this->getResourceTypeForType($type),
            'resource_id' => fake()->numberBetween(1, 1000),
            'link' => $this->getLinkForType($type),
            'metadata' => $this->getMetadataForType($type),
            'is_read' => fake()->boolean(30), // 30% مقروءة
            'read_at' => function (array $attributes) {
                return $attributes['is_read'] ? fake()->dateTimeBetween('-1 week', 'now') : null;
            },
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }

    /**
     * إشعار غير مقروء
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => false,
            'read_at' => null,
        ]);
    }

    /**
     * إشعار مقروء
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_read' => true,
            'read_at' => fake()->dateTimeBetween($attributes['created_at'], 'now'),
        ]);
    }

    /**
     * إشعار رسالة
     */
    public function message(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'message',
            'title' => 'رسالة جديدة',
            'body' => 'لديك رسالة جديدة من ' . fake()->name(),
            'resource_type' => 'chat_message',
            'link' => '/chat/' . fake()->numberBetween(1, 100),
        ]);
    }

    /**
     * إشعار مهمة
     */
    public function task(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'task',
            'title' => 'مهمة مسندة',
            'body' => 'تم إسناد مهمة جديدة إليك: ' . fake()->sentence(3),
            'resource_type' => 'task',
            'link' => '/tasks/' . fake()->numberBetween(1, 100),
        ]);
    }

    /**
     * إشعار جهاز
     */
    public function asset(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'asset',
            'title' => 'جهاز مسند',
            'body' => 'تم إسناد جهاز إليك: ' . fake()->word(),
            'resource_type' => 'asset',
            'link' => '/assets/' . fake()->numberBetween(1, 100),
        ]);
    }

    // Helper methods
    protected function getTitleForType(string $type): string
    {
        return match($type) {
            'message' => 'رسالة جديدة',
            'task' => 'مهمة مسندة',
            'asset' => 'جهاز مسند',
        };
    }

    protected function getBodyForType(string $type): string
    {
        return match($type) {
            'message' => 'لديك رسالة جديدة من ' . fake()->name(),
            'task' => 'تم إسناد مهمة جديدة إليك: ' . fake()->sentence(3),
            'asset' => 'تم إسناد جهاز إليك: ' . fake()->word(),
        };
    }

    protected function getResourceTypeForType(string $type): string
    {
        return match($type) {
            'message' => 'chat_message',
            'task' => 'task',
            'asset' => 'asset',
        };
    }

    protected function getLinkForType(string $type): string
    {
        return match($type) {
            'message' => '/chat/' . fake()->numberBetween(1, 100),
            'task' => '/tasks/' . fake()->numberBetween(1, 100),
            'asset' => '/assets/' . fake()->numberBetween(1, 100),
        };
    }

    protected function getMetadataForType(string $type): array
    {
        return match($type) {
            'message' => [
                'chat_room_id' => fake()->numberBetween(1, 100),
                'message_preview' => fake()->sentence(),
            ],
            'task' => [
                'priority' => fake()->randomElement(['high', 'medium', 'low']),
                'due_date' => fake()->date(),
            ],
            'asset' => [
                'asset_code' => 'AST-' . fake()->numberBetween(1000, 9999),
                'serial_number' => fake()->uuid(),
            ],
        };
    }
}

