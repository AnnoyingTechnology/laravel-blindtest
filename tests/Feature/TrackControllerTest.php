<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;


class TrackControllerTest extends TestCase
{
    use RefreshDatabase;

    // public function test_user_can_get_track()
    // {
    //     $track = Track::factory()->create();
    //     $user = User::factory()->create();

    //     $response = $this->actingAs($user)->get($track->getUrl());

    //     $response->assertStatus(200);
    //     $response->assertHeader('Content-Type', $track->mime_type);
    // }
}
