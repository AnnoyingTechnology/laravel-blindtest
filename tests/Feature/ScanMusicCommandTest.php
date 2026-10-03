<?php

namespace Tests\Feature;

use App\Models\Track;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class ScanMusicCommandTest extends TestCase
{
    use RefreshDatabase;

    private string $musicDir;

    protected function setUp(): void
    {
        parent::setUp();

        if (! function_exists('shell_exec') || ! trim(shell_exec('which ffmpeg') ?? '')) {
            $this->markTestSkipped('ffmpeg is required to generate tagged MP3 fixtures.');
        }

        // point the local disk (and therefore music:scan) at a throwaway
        // directory so tests never touch the real music library
        $this->musicDir = storage_path('app/testing_music_'.bin2hex(random_bytes(8)));
        config(['filesystems.disks.local.root' => $this->musicDir]);
        File::ensureDirectoryExists($this->musicDir.'/music');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->musicDir);

        parent::tearDown();
    }

    private function createMp3(string $filename, array $tags): string
    {
        $path = $this->musicDir.'/music/'.$filename;

        // generate a one second silent mp3 with ffmpeg and embed ID3v2 tags
        // note: ffmpeg writes date metadata as a binary-time frame that
        // getID3 exposes under tags.id3v2.year; a literal "year" tag would
        // only produce a TXXX comment that the command does not read
        exec(
            'ffmpeg -loglevel error -f lavfi -i anullsrc=r=44100 -t 1'
            .' -metadata title='.escapeshellarg($tags['title'])
            .' -metadata artist='.escapeshellarg($tags['artist'] ?? 'The Testers')
            .' -metadata date='.escapeshellarg($tags['year'] ?? '2024')
            .' -metadata genre='.escapeshellarg($tags['genre'] ?? 'Synthwave')
            .' -y '.escapeshellarg($path)
        );

        $this->assertFileExists($path, 'ffmpeg failed to generate the test fixture.');

        return $path;
    }

    public function test_scan_music_command_imports_id3_tags(): void
    {
        $this->createMp3('track_one.mp3', [
            'title' => 'Neon Highway',
            'artist' => 'The Testers',
            'year' => '1998',
        ]);

        $this->artisan('music:scan')
            ->expectsOutput('Music scan completed and database updated.')
            ->assertExitCode(0);

        $this->assertDatabaseCount('tracks', 1);
        $this->assertDatabaseHas('tracks', [
            'file' => 'track_one.mp3',
            'name' => 'Neon Highway',
            'artist' => 'The Testers',
            'year' => '1998',
            'genre' => 'Synthwave',
            'remix' => null,
        ]);

        // the command also sets a random current track
        $this->assertSame('track_one.mp3', Track::getCurrent()->file);
    }

    public function test_scan_music_command_extracts_remix_from_title(): void
    {
        $this->createMp3('track_two.mp3', [
            'title' => 'Sweet Tooth (Extended Remix)',
            'artist' => 'Just Call',
        ]);

        Artisan::call('music:scan');

        // the command leaves an empty string rather than null when every
        // token of the parenthesized part is considered banal
        $this->assertDatabaseHas('tracks', [
            'file' => 'track_two.mp3',
            'name' => 'Sweet Tooth',
            'remix' => '',
        ]);
    }

    public function test_scan_music_command_keeps_meaningful_remix(): void
    {
        $this->createMp3('track_three.mp3', [
            'title' => 'Night Drive (Just Call Rework)',
            'artist' => 'Just Call',
        ]);

        Artisan::call('music:scan');

        $this->assertDatabaseHas('tracks', [
            'file' => 'track_three.mp3',
            'name' => 'Night Drive',
            'remix' => 'Just Call Rework',
        ]);
    }

    public function test_scan_music_command_purges_previous_tracks(): void
    {
        $this->createMp3('only_track.mp3', [
            'title' => 'Only Track',
            'artist' => 'Nobody',
        ]);

        Artisan::call('music:scan');
        Artisan::call('music:scan');

        // truncate + single file means one scan produces exactly one row
        $this->assertDatabaseCount('tracks', 1);
    }
}
