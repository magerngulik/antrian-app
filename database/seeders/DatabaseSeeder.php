<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use App\Models\RoleUser;
use App\Models\CodeQueue;
use App\Models\Assignment;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name' => 'admin',
            'email' => 'admin@admin.com',
        ]);

        \App\Models\User::factory()->create([
            'name' => 'user',
            'email' => 'user@user.com',
        ]);

        CodeQueue::create([
            'name' => 'Taler',
            'queue_code' => 'A'
        ]);

        CodeQueue::create([
            'name' => 'Costumer Services',
            'queue_code' => 'B'
        ]);

        $data = CodeQueue::all();
        $roleCounter = 1;
        for ($i = 0; $i < count($data); $i++) {
            for ($j = 1; $j <= 3; $j++) {
                RoleUser::create([
                    'nama_role' => $data[$i]->name . ' ' . $j,
                    'code_id' => $data[$i]->id,
                ]);
                $roleCounter++;
            }
        }

        Assignment::create([
            'user_id' => 1,
            'role_users_id' => 1
        ]);
        
        Assignment::create([
            'user_id' => 10,
            'role_users_id' => 3
        ]);

    }
}
