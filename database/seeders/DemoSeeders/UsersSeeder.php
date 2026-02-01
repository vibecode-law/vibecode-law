<?php

namespace Database\Seeders\DemoSeeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->count(6)->collaborator()->create();

        User::factory()->count(50)->create();
    }
}
