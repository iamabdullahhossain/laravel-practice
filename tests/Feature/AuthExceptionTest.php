<?php

test('unauthenticated API request returns custom json response and 401 status code', function () {
    $response = $this->getJson('/api/tasks');

    $response->assertStatus(401)
        ->assertJson([
            'status' => false,
            'message' => 'Please login first.',
        ]);
});

test('unauthenticated API request returns custom json response even if not expecting JSON', function () {
    // When the request does not send Accept: application/json but is under api/*
    // It should still be intercepted by our AuthenticationException handler
    $response = $this->get('/api/tasks');

    $response->assertStatus(401)
        ->assertJson([
            'status' => false,
            'message' => 'Please login first.',
        ]);
});
