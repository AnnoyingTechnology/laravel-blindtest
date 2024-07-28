<?php

namespace Tests\Unit;

use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_increase_score_by()
    {
        $user = User::factory()->create(['score' => 0]);

        $user->increaseScoreBy(5.5);

        $this->assertEquals(5.5, $user->score);
    }

    public function test_reset_scores()
    {
        User::factory()->count(3)->create(['score' => 10]);

        User::resetScores();

        $this->assertDatabaseMissing('users', ['score' => 10]);
        $this->assertDatabaseCount('users', 3);
    }

    public function test_username_attribute_casing_applied()
    {
        $user = User::factory()->create(['username' => 'TEST_USER']);

        $this->assertEquals('Test_user', $user->username);
    }
}
