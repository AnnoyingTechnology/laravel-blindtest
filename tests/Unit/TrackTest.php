<?php

namespace Tests\Unit;

use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackTest extends TestCase
{
    use RefreshDatabase;

    public function test_set_as_current()
    {
        $track1 = Track::factory()->create();
        $track2 = Track::factory()->create();

        $track1->setAsCurrent();

        $this->assertTrue((bool)$track1->fresh()->is_current);
        $this->assertFalse((bool)$track2->fresh()->is_current);
    }

    public function test_get_clues()
    {
        $track = Track::factory()->create([
            'name' => 'Test Track',
            'artist' => 'Test Artist',
            'remix' => 'Test Remix'
        ]);

        $clues = $track->getClues();

        $this->assertCount(3, $clues);
        $this->assertStringContainsString('*', $clues[0]);
        $this->assertStringContainsString('*', $clues[1]);
        $this->assertStringContainsString('*', $clues[2]);
    }

    public function test_perfect_match()
    {
        $track = Track::factory()->create([
            'name' => 'Test Track',
            'artist' => 'Test Artist',
            'remix' => 'Test Remix'
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $result = $track->match('Test Track');

        $this->assertEquals('name', $result['found']);
        $this->assertEquals(1, $result['score']);
    }

    public function test_soft_match_correct_position()
    {
        $track = Track::factory()->create([
            'name' => 'Test Track',
            'artist' => 'Test Artist',
            'remix' => 'Test Remix'
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $result = $track->match('Fucked Track');

        $this->assertEquals('name', $result['found']);
        $this->assertEquals(0.5, $result['score']);
    }

    public function test_soft_match_wrong_position()
    {
        $track = Track::factory()->create([
            'name' => 'Test Track 2000',
            'artist' => 'Test Artist 2000',
            'remix' => 'Test Remix 2000'
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $result = $track->match('Track 2000 Fucked');

        $this->assertEquals('name', $result['found']);
        $this->assertEquals(0.3, $result['score']);
    }

    public function test_clue_scoring_factor_applied()
    {
        $track = Track::factory()->create([
            'name' => 'Test Track',
            'artist' => 'Test Artist',
            'remix' => 'Test Remix'
        ]);

        $user = User::factory()->create();
        $this->actingAs($user);

        $track->getClues();
        $result = $track->match('Test Track');

        $this->assertEquals('name', $result['found']);
        $this->assertEquals(0.75, $result['score']);
    }

}
