<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        //Fix admin felhasználó
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 5 felhasználó generálása váltakozó szerepkörrel
        User::factory()->count(5)->sequence(
            ['role' => 'admin'],
            ['role' => 'user'],
            ['role' => 'admin'],
            ['role' => 'user'],
            ['role' => 'admin']
        )->create([
            'password' => Hash::make('password'),
        ]);

        $this->call([
            EventSeeder::class,
        ]);
    }
}
