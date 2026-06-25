<?php

use App\Models\Category;
use App\Models\Task;

test('can list all tasks with categories', function () {
    // ১টি টাস্ক তৈরি করছি যা ফ্যাক্টরির মাধ্যমে অটোমেটিক ক্যাটাগরি তৈরি করে নেবে
    Task::factory()->create();

    $response = $this->getJson('/api/tasks');

    // রেসপন্স ২০০ এসেছে কিনা এবং এপিআই রেসপন্সের জেআইওন স্ট্রাকচার ঠিক আছে কিনা চেক করছি
    $response->assertStatus(200)
        ->assertJsonCount(1, 'data')
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'category_id',
                    'category' => [
                        'id',
                        'name',
                        'slug',
                    ],
                    'title',
                    'description',
                    'is_completed',
                    'due_date',
                    'created_at',
                ]
            ]
        ]);
});

test('can create a task', function () {
    $category = Category::factory()->create();

    $response = $this->postJson('/api/tasks', [
        'category_id' => $category->id,
        'title' => 'Learn Laravel Testing',
        'description' => 'Write Pest tests for all API endpoints',
        'is_completed' => false,
        'due_date' => '2026-07-01',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.title', 'Learn Laravel Testing')
        ->assertJsonPath('data.category.id', $category->id);

    $this->assertDatabaseHas('tasks', [
        'title' => 'Learn Laravel Testing',
        'category_id' => $category->id,
    ]);
});

test('validation prevents creating task without title', function () {
    $category = Category::factory()->create();

    $response = $this->postJson('/api/tasks', [
        'category_id' => $category->id,
        // Title অনুপস্থিত
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['title']);
});

test('validation prevents creating task with non-existent category', function () {
    $response = $this->postJson('/api/tasks', [
        'category_id' => 999, // এই আইডি ডাটাবেজে নেই
        'title' => 'Invalid Task',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['category_id']);
});

test('can update a task', function () {
    $task = Task::factory()->create(['is_completed' => false]);

    $response = $this->putJson("/api/tasks/{$task->id}", [
        'category_id' => $task->category_id,
        'title' => 'Updated Task Title',
        'description' => $task->description,
        'is_completed' => true, // সম্পন্ন করলাম
        'due_date' => $task->due_date,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.title', 'Updated Task Title')
        ->assertJsonPath('data.is_completed', true);

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Task Title',
        'is_completed' => true,
    ]);
});

test('can delete a task', function () {
    $task = Task::factory()->create();

    $response = $this->deleteJson("/api/tasks/{$task->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
    ]);
});
