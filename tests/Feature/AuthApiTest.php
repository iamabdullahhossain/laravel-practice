<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

test('authenticated user can change password with correct credentials', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old_password_123'),
    ]);

    $response = $this->actingAs($user)->postJson('/api/change-password', [
        'old_password' => 'old_password_123',
        'new_password' => 'new_password_123',
        'confirm_password' => 'new_password_123',
    ]);

    $response->assertStatus(200)
        ->assertJson([
            'message' => 'Password changed successfully.',
        ]);

    $user->refresh();
    expect(Hash::check('new_password_123', $user->password))->toBeTrue();
});

test('user cannot change password with incorrect old password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old_password_123'),
    ]);

    $response = $this->actingAs($user)->postJson('/api/change-password', [
        'old_password' => 'wrong_old_password',
        'new_password' => 'new_password_123',
        'confirm_password' => 'new_password_123',
    ]);

    $response->assertStatus(422)
        ->assertJson([
            'message' => 'The provided old password does not match our records.',
        ]);
});

test('user cannot change password if new password confirmation does not match', function () {
    $user = User::factory()->create([
        'password' => Hash::make('old_password_123'),
    ]);

    $response = $this->actingAs($user)->postJson('/api/change-password', [
        'old_password' => 'old_password_123',
        'new_password' => 'new_password_123',
        'confirm_password' => 'different_password',
    ]);

    $response->assertStatus(422)
        ->assertJsonValidationErrors(['confirm_password']);
});
