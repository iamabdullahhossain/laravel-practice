<?php

use App\Models\Category;
use App\Models\Task;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can list all tasks with categories', function () {
    // ১টি টাস্ক তৈরি করছি যা ফ্যাক্টরির মাধ্যমে অটোমেটিক ক্যাটাগরি তৈরি করে নেবে
    Task::factory()->create(['user_id' => $this->user->id]);

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
                    'status',
                    'due_date',
                    'created_at',
                ],
            ],
        ]);
});

test('can create a task', function () {
    $category = Category::factory()->create();

    $response = $this->postJson('/api/tasks', [
        'category_id' => $category->id,
        'title' => 'Learn Laravel Testing',
        'description' => 'Write Pest tests for all API endpoints',
        'status' => 'todo',
        'due_date' => '2026-07-01',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.title', 'Learn Laravel Testing')
        ->assertJsonPath('data.category.id', $category->id)
        ->assertJsonPath('data.status', 'todo');

    $this->assertDatabaseHas('tasks', [
        'title' => 'Learn Laravel Testing',
        'category_id' => $category->id,
        'status' => 'todo',
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
    $task = Task::factory()->create(['user_id' => $this->user->id, 'status' => 'todo']);

    $response = $this->putJson("/api/tasks/{$task->id}", [
        'category_id' => $task->category_id,
        'title' => 'Updated Task Title',
        'description' => $task->description,
        'status' => 'completed',
        'due_date' => $task->due_date,
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.title', 'Updated Task Title')
        ->assertJsonPath('data.status', 'completed');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Task Title',
        'status' => 'completed',
    ]);
});

test('validation prevents creating task with invalid status', function () {
    $category = Category::factory()->create();

    $response = $this->postJson('/api/tasks', [
        'category_id' => $category->id,
        'title' => 'Invalid Status Task',
        'status' => 'invalid_status_value',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('validation prevents updating task with invalid status', function () {
    $task = Task::factory()->create(['user_id' => $this->user->id]);

    $response = $this->putJson("/api/tasks/{$task->id}", [
        'category_id' => $task->category_id,
        'title' => 'Invalid Status Update',
        'status' => 'invalid_status_value',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['status']);
});

test('expired task status is automatically changed to due when retrieved', function () {
    $task = Task::factory()->create([
        'user_id' => $this->user->id,
        'due_date' => now()->subDay()->toDateString(),
        'status' => 'todo',
    ]);

    $response = $this->getJson('/api/tasks');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.status', 'due');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'due',
    ]);
});

test('completed task status is not changed to due even if expired', function () {
    $task = Task::factory()->create([
        'user_id' => $this->user->id,
        'due_date' => now()->subDay()->toDateString(),
        'status' => 'completed',
    ]);

    $response = $this->getJson('/api/tasks');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.status', 'completed');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'completed',
    ]);
});

test('can delete a task', function () {
    $task = Task::factory()->create(['user_id' => $this->user->id]);

    $response = $this->deleteJson("/api/tasks/{$task->id}");

    $response->assertStatus(204);

    $this->assertDatabaseMissing('tasks', [
        'id' => $task->id,
    ]);
});
