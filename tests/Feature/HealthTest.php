<?php

it('returns health check response', function () {
    $response = $this->getJson('/');

    $response->assertOk()
        ->assertJsonStructure(['name', 'version', 'status'])
        ->assertJson(['status' => 'running']);
});
