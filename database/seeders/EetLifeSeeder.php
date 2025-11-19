<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\Shoutout;
use App\Models\User;
use Carbon\Carbon;

class EetLifeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample events
        $events = [
            [
                'title' => 'Annual Company Meeting 2025',
                'description' => 'Join us for our annual company meeting where we will discuss achievements, goals, and future plans.',
                'date' => Carbon::now()->addDays(15),
                'location' => 'Main Conference Hall',
                'organizer' => 'HR Department',
                'status' => 'upcoming',
                'is_featured' => true
            ],
            [
                'title' => 'Team Building Workshop',
                'description' => 'A fun and interactive team building workshop to strengthen our team bonds.',
                'date' => Carbon::now()->addDays(7),
                'location' => 'Outdoor Activity Center',
                'organizer' => 'Management Team',
                'status' => 'upcoming',
                'is_featured' => false
            ],
            [
                'title' => 'New Employee Orientation',
                'description' => 'Welcome new employees to our company with a comprehensive orientation program.',
                'date' => Carbon::now()->addDays(3),
                'location' => 'Training Room A',
                'organizer' => 'HR Department',
                'status' => 'upcoming',
                'is_featured' => false
            ],
            [
                'title' => 'Monthly All-Hands Meeting',
                'description' => 'Monthly meeting to discuss company updates and achievements.',
                'date' => Carbon::now()->subDays(5),
                'location' => 'Main Conference Hall',
                'organizer' => 'CEO Office',
                'status' => 'completed',
                'is_featured' => false
            ],
            [
                'title' => 'Technology Innovation Day',
                'description' => 'Showcase of new technologies and innovations in our industry.',
                'date' => Carbon::now()->subDays(10),
                'location' => 'Tech Lab',
                'organizer' => 'IT Department',
                'status' => 'completed',
                'is_featured' => true
            ]
        ];

        foreach ($events as $eventData) {
            Event::create($eventData);
        }

        // Create sample shoutouts
        $users = User::take(5)->get();
        if ($users->count() > 0) {
            $shoutouts = [
                [
                    'user_id' => $users[0]->id,
                    'message' => 'Great job on the project presentation! Your hard work really paid off. 🎉',
                    'recipient_name' => 'Ahmed Hassan',
                    'type' => 'achievement'
                ],
                [
                    'user_id' => $users[1]->id,
                    'message' => 'Happy Birthday! Hope you have an amazing day filled with joy and happiness! 🎂',
                    'recipient_name' => 'Sarah Mohamed',
                    'type' => 'birthday'
                ],
                [
                    'user_id' => $users[2]->id,
                    'message' => 'Thank you for helping me with the technical issue yesterday. Really appreciate it! 🙏',
                    'recipient_name' => 'Omar Ali',
                    'type' => 'thanks'
                ],
                [
                    'user_id' => $users[3]->id,
                    'message' => 'Welcome to the team! Looking forward to working with you. 💬',
                    'recipient_name' => 'Fatima Ahmed',
                    'type' => 'general'
                ],
                [
                    'user_id' => $users[4]->id,
                    'message' => 'Congratulations on completing the certification! Your dedication is inspiring. 🏆',
                    'recipient_name' => 'Youssef Ibrahim',
                    'type' => 'achievement'
                ]
            ];

            foreach ($shoutouts as $shoutoutData) {
                Shoutout::create($shoutoutData);
            }
        }

        // Mark some users as featured employees
        User::take(3)->update(['is_featured' => true]);

        // Add birth dates to some users for birthday section
        $usersWithBirthdays = User::take(5)->get();
        $birthMonths = [1, 3, 6, 9, 12]; // Different months
        
        foreach ($usersWithBirthdays as $index => $user) {
            $user->update([
                'birth_date' => Carbon::create(null, $birthMonths[$index % count($birthMonths)], rand(1, 28))
            ]);
        }
    }
}
