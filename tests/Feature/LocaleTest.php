<?php

use App\Models\User;

it('sets locale from query parameter', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/me?lang=tr');

    $response->assertOk();
    expect(app()->getLocale())->toBe('tr');
});

it('sets locale from Accept-Language header', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->withHeaders(['Accept-Language' => 'tr'])
        ->getJson('/api/v1/auth/me');

    $response->assertOk();
    expect(app()->getLocale())->toBe('tr');
});

it('ignores unsupported locale', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/v1/auth/me?lang=xx');

    $response->assertOk();
    expect(app()->getLocale())->toBe('en');
});
