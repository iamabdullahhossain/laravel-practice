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
                    'due_time',
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
        'due_time' => '14:30',
    ]);

    $response->assertStatus(201)
        ->assertJsonPath('data.title', 'Learn Laravel Testing')
        ->assertJsonPath('data.category.id', $category->id)
        ->assertJsonPath('data.status', 'todo')
        ->assertJsonPath('data.due_time', '14:30');

    $this->assertDatabaseHas('tasks', [
        'title' => 'Learn Laravel Testing',
        'category_id' => $category->id,
        'status' => 'todo',
        'due_time' => '14:30',
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
        'due_time' => '18:00',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.title', 'Updated Task Title')
        ->assertJsonPath('data.status', 'completed')
        ->assertJsonPath('data.due_time', '18:00');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Updated Task Title',
        'status' => 'completed',
        'due_time' => '18:00',
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
        'due_time' => '12:00',
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

test('task status is automatically changed to due when due time is in the past today', function () {
    $task = Task::factory()->create([
        'user_id' => $this->user->id,
        'due_date' => now()->toDateString(),
        'due_time' => now()->subHour()->format('H:i'), // 1 hour ago
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

test('task status is not changed to due when due time is in the future today', function () {
    $task = Task::factory()->create([
        'user_id' => $this->user->id,
        'due_date' => now()->toDateString(),
        'due_time' => now()->addHour()->format('H:i'), // 1 hour from now
        'status' => 'todo',
    ]);

    $response = $this->getJson('/api/tasks');

    $response->assertStatus(200)
        ->assertJsonPath('data.0.status', 'todo');

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'status' => 'todo',
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

test('can get task status stats', function () {
    Task::factory()->create(['user_id' => $this->user->id, 'status' => 'todo']);
    Task::factory()->create(['user_id' => $this->user->id, 'status' => 'todo']);
    Task::factory()->create(['user_id' => $this->user->id, 'status' => 'in_progress']);
    Task::factory()->create(['user_id' => $this->user->id, 'status' => 'completed']);

    Task::factory()->create([
        'user_id' => $this->user->id,
        'status' => 'todo',
        'due_date' => now()->subDay()->toDateString(),
    ]);

    $response = $this->getJson('/api/tasks/stats');

    $response->assertStatus(200)
        ->assertJson([
            'todo' => 2,
            'in_progress' => 1,
            'completed' => 1,
            'due' => 1,
            'total' => 5,
        ]);
});
