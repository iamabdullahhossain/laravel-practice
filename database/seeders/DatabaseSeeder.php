<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Category;
use App\Models\Task;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
     $user =   User::factory()->create([
            'name' => 'Test User',
            'username' => 'testuser',
        ]);

        $categories = Category::factory(5)->create();

        foreach ($categories as $category) {
            Task::factory(5)->create([
                'category_id' => $category->id,
                'user_id' => $user->id,
            ]);
        }
    }
}
