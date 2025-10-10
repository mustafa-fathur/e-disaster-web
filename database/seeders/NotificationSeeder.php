<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Notification;
use App\Models\User;
use App\Enums\NotificationTypeEnum;

class NotificationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get all users
        $users = User::all();

        foreach ($users as $user) {
            // Create 3-5 notifications per user
            $notificationCount = rand(3, 5);
            
            for ($i = 0; $i < $notificationCount; $i++) {
                $categories = [
                    NotificationTypeEnum::VOLUNTEER_VERIFICATION,
                    NotificationTypeEnum::NEW_DISASTER,
                    NotificationTypeEnum::NEW_DISASTER_REPORT,
                    NotificationTypeEnum::NEW_DISASTER_VICTIM_REPORT,
                    NotificationTypeEnum::NEW_DISASTER_AID_REPORT,
                    NotificationTypeEnum::DISASTER_STATUS_CHANGED,
                ];

                $category = $categories[array_rand($categories)];
                $isRead = rand(0, 1) === 1;

                $titles = [
                    'New Disaster Alert',
                    'Volunteer Application Update',
                    'Disaster Report Submitted',
                    'Victim Report Added',
                    'Aid Report Created',
                    'Disaster Status Changed',
                    'System Notification',
                    'Emergency Update',
                ];

                $messages = [
                    'A new disaster has been reported in your area. Please check the details.',
                    'Your volunteer application has been reviewed and approved.',
                    'A new disaster report has been submitted for review.',
                    'New victim information has been added to the disaster database.',
                    'Aid resources have been reported and are being processed.',
                    'The disaster status has been updated. Please check for changes.',
                    'System maintenance will be performed tonight from 2-4 AM.',
                    'Emergency protocols have been activated. Please follow safety guidelines.',
                ];

                Notification::create([
                    'user_id' => $user->id,
                    'title' => $titles[array_rand($titles)],
                    'message' => $messages[array_rand($messages)],
                    'category' => $category,
                    'is_read' => $isRead,
                    'sent_at' => now()->subDays(rand(0, 30)),
                ]);
            }
        }
    }
}