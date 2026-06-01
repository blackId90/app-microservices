<?php

use function Pest\Laravel\getJson;

describe('HealthController', function () {
    describe('GET /api/v1/auth', function () {
        it('returns successful response with correct message', function () {
            $response = getJson('/api/v1/auth');

            $response->assertStatus(200)
                ->assertJsonStructure([
                    'status',
                    'message',
                    'data',
                ])
                ->assertJson([
                    'status' => 200,
                    'message' => 'Success Connect API',
                ]);
        });

        it('returns correct route name check.index', function () {
            // Verify the route is registered with name check.index
            $route = app('router')->getRoutes()->getByName('check.index');

            expect($route)->not->toBeNull();
            expect($route->getName())->toBe('check.index');
        });
    });
});

