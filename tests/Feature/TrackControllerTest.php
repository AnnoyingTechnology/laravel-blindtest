<?php

namespace Tests\Feature;

use App\Models\Track;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TrackControllerTest extends TestCase
{
    use RefreshDatabase;

    private function makeTrackWithAudio(string $contents = 'dummy-audio-bytes'): Track
    {
        Storage::fake('local');
        Storage::disk('local')->put('music/track.mp3', $contents);

        return Track::factory()->create(['file' => 'track.mp3']);
    }

    public function test_user_can_get_track(): void
    {
        // use the real demo track so fileinfo reports a proper audio MIME type
        Storage::fake('local');
        Storage::disk('local')->put(
            'music/track.mp3',
            file_get_contents(base_path('demo/just_call-sweet_tooth.mp3'))
        );

        $track = Track::factory()->create(['file' => 'track.mp3']);
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get($track->getUrl());

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'audio/mpeg');
    }

    public function test_unsigned_url_is_rejected(): void
    {
        $track = Track::factory()->create();
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get(route('track', ['id' => $track->id]));

        $response->assertForbidden();
    }

    public function test_missing_file_returns_not_found(): void
    {
        $track = $this->makeTrackWithAudio();
        Storage::disk('local')->delete('music/track.mp3');

        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get($track->getUrl());

        $response->assertNotFound();
    }

    public function test_partial_range_returns_206_with_content_range(): void
    {
        $track = $this->makeTrackWithAudio(str_repeat('a', 1000));
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders(['Range' => 'bytes=100-199'])
            ->get($track->getUrl());

        $response->assertStatus(206);
        $response->assertHeader('Content-Length', '100');
        $response->assertHeader('Content-Range', 'bytes 100-199/1000');
    }

    public function test_open_ended_range_streams_to_end_of_file(): void
    {
        $track = $this->makeTrackWithAudio(str_repeat('a', 1000));
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders(['Range' => 'bytes=900-'])
            ->get($track->getUrl());

        $response->assertStatus(206);
        $response->assertHeader('Content-Length', '100');
        $response->assertHeader('Content-Range', 'bytes 900-999/1000');
    }

    public function test_range_beyond_file_size_returns_416(): void
    {
        $track = $this->makeTrackWithAudio(str_repeat('a', 1000));
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders(['Range' => 'bytes=2000-'])
            ->get($track->getUrl());

        $response->assertStatus(416);
        $response->assertHeader('Content-Range', 'bytes */1000');
    }

    public function test_inverted_range_returns_416(): void
    {
        $track = $this->makeTrackWithAudio(str_repeat('a', 1000));
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders(['Range' => 'bytes=5-3'])
            ->get($track->getUrl());

        $response->assertStatus(416);
    }

    public function test_unparsable_range_is_ignored(): void
    {
        $track = $this->makeTrackWithAudio(str_repeat('a', 1000));
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders(['Range' => 'bytes=foo bar'])
            ->get($track->getUrl());

        // not parseable as a byte range at all: serve the full file
        $response->assertStatus(200);
    }

    public function test_unsupported_range_unit_is_ignored(): void
    {
        $track = $this->makeTrackWithAudio(str_repeat('a', 1000));
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders(['Range' => 'items=0-99'])
            ->get($track->getUrl());

        // RFC 9110: an unknown range unit must be ignored, not rejected
        $response->assertStatus(200);
    }

    public function test_multi_range_is_ignored(): void
    {
        $track = $this->makeTrackWithAudio(str_repeat('a', 1000));
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders(['Range' => 'bytes=0-1,5-6'])
            ->get($track->getUrl());

        // RFC 9110: a multi-range specifier may be ignored; we serve the
        // full file rather than rejecting a satisfiable request
        $response->assertStatus(200);
    }

    public function test_suffix_range_returns_last_n_bytes(): void
    {
        $track = $this->makeTrackWithAudio(str_repeat('a', 1000));
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders(['Range' => 'bytes=-200'])
            ->get($track->getUrl());

        $response->assertStatus(206);
        $response->assertHeader('Content-Length', '200');
        $response->assertHeader('Content-Range', 'bytes 800-999/1000');
    }
}
