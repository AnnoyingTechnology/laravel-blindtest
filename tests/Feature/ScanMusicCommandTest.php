<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

use Illuminate\Support\Facades\Storage;


class ScanMusicCommandTest extends TestCase
{
    use RefreshDatabase;

    // public function test_scan_music_command()
    // {
    //     // Create a mock storage with sample MP3 files
    //     Storage::fake('local');
    //     Storage::disk('local')->put('music/test1.mp3', 'fake mp3 content');
    //     Storage::disk('local')->put('music/test2.mp3', 'fake mp3 content');

    //     $this->artisan('music:scan')
    //         ->expectsOutput('Music scan completed and database updated.')
    //         ->assertExitCode(0);

    //     $this->assertDatabaseCount('tracks', 2);
    // }
}