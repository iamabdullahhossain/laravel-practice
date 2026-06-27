<?php

use App\Models\Category;
use App\Models\User;

beforeEach(function () {
    $this->user = User::factory()->create();
    $this->actingAs($this->user);
});

test('can list all categories', function () {
    // ডাটাবেজে ৩টি ফেক ক্যাটাগরি তৈরি করছি
    Category::factory()->count(3)->create();

    // এপিআই এন্ডপয়েন্টে রিকোয়েস্ট পাঠাচ্ছি
    $response = $this->getJson('/api/categories');

    // রেসপন্স ২০০ ও ৩টি ক্যাটাগরি ফেরত এসেছে কিনা চেক করছি
    $response->assertStatus(200)
        ->assertJsonCount(3, 'data');
});

test('can create a category', function () {
    // নতুন ক্যাটাগরি তৈরি করার জন্য রিকোয়েস্ট পাঠাচ্ছি
    $response = $this->postJson('/api/categories', [
        'name' => 'Urgent Tasks',
    ]);

    // -রেসপন্স ২০১ (Created) এবং সঠিক ডাটা এসেছে কিনা চেক করছি
    $response->assertStatus(201)
        ->assertJsonPath('data.name', 'Urgent Tasks')
        ->assertJsonPath('data.slug', 'urgent-tasks');

    // ডাটাবেজে ডাটাটি সেভ হয়েছে কিনা চেক করছি
    $this->assertDatabaseHas('categories', [
        'name' => 'Urgent Tasks',
        'slug' => 'urgent-tasks',
    ]);
});

test('validation prevents creating category with duplicate name', function () {
    // আগে থেকেই একটি ক্যাটাগরি তৈরি করে রাখছি
    Category::factory()->create(['name' => 'Work']);

    // একই নামে আরেকটি ক্যাটাগরি তৈরি করার চেষ্টা করছি
    $response = $this->postJson('/api/categories', [
        'name' => 'Work',
    ]);

    // রেসপন্স ৪২২ (Unprocessable Entity) এবং ভ্যালিডেশন এরর এসেছে কিনা চেক করছি
    $response->assertStatus(422)
        ->assertJsonValidationErrors(['name']);
});

test('can update a category by id', function () {
    $category = Category::factory()->create(['name' => 'Work', 'slug' => 'work']);

    // ক্যাটাগরি আপডেট করার জন্য রিকোয়েস্ট পাঠাচ্ছি
    $response = $this->putJson("/api/categories/{$category->id}", [
        'name' => 'Updated Work',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated Work')
        ->assertJsonPath('data.slug', 'updated-work');
});

test('can delete a category by id', function () {
    $category = Category::factory()->create();

    // ক্যাটাগরি ডিলিট করার জন্য রিকোয়েস্ট পাঠাচ্ছি
    $response = $this->deleteJson("/api/categories/{$category->id}");

    // রেসপন্স ২০০ এসেছে কিনা চেক করছি
    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);

    // ডাটাবেজ থেকে মুছে গেছে কিনা চেক করছি
    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});

test('can show a category by its slug', function () {
    $category = Category::factory()->create(['name' => 'Work', 'slug' => 'work']);

    $response = $this->getJson('/api/categories/work');

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Work')
        ->assertJsonPath('data.slug', 'work');
});

test('can update a category by its slug', function () {
    $category = Category::factory()->create(['name' => 'Work', 'slug' => 'work']);

    $response = $this->putJson('/api/categories/work', [
        'name' => 'Updated Work',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.name', 'Updated Work')
        ->assertJsonPath('data.slug', 'updated-work');
});

test('can delete a category by its slug', function () {
    $category = Category::factory()->create(['name' => 'Work', 'slug' => 'work']);

    $response = $this->deleteJson('/api/categories/work');

    $response->assertStatus(200)
        ->assertJson([
            'success' => true,
            'message' => 'Category deleted successfully',
        ]);

    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});
