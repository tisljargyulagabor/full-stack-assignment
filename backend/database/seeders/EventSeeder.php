<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Event;
use App\Models\User;

class EventSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) return;

        for ($i = 1; $i <= 10; $i++) {

            $user = $users[($i - 1) % $users->count()];

            Event::create([
                'event_name' => "Esemény #$i - " . ($user->role === 'admin' ? 'Admin hír' : 'User poszt'),
                'event_user_id' => $user->id,
                'event_date' => now()->addDays($i)->format('Y-m-d H:i:s'),
                'event_description' => "Ez a(z) $i. teszt esemény, amit {$user->name} hozott létre.",
                'event_created_at' => now(),
            ]);
        }
    }
}
