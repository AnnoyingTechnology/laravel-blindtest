<?php 

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use getID3;

class ScanMusic extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'music:scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scan storage/music for MP3 files and store their ID3 tags in the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Purge the current tracks table
        DB::table('tracks')->truncate();

        // Create an instance of getID3
        $getID3 = new getID3();

        // Directory to scan
        $directory = storage_path('app/music');

        // Get all .mp3 files in the directory
        $files = glob($directory . '/*.mp3');

        foreach ($files as $filePath) {
            $fileInfo = $getID3->analyze($filePath);
            $getID3->CopyTagsToComments($fileInfo);

            $name   = $fileInfo['tags']['id3v2']['title'][0] ?? null;
            $artist = $fileInfo['tags']['id3v2']['artist'][0] ?? null;
            $year   = $fileInfo['tags']['id3v2']['year'][0] ?? null;
            $genre  = $fileInfo['tags']['id3v2']['genre'][0] ?? null;

            // if the name contains a remix
            if(str_contains($name, '(')) {
                // isolate the name from the fullname
                list($name, $remix) = explode('(', $name);
                // trim the name 
                $name = trim($name);
                // trim the remixer
                $remix = trim($remix, ' )');
            }
            else {
                $remix = null;
            }

            // Add track data to the database
            DB::table('tracks')->insert([
                'file' => basename($filePath),
                'name' => $name,
                'remix' => $remix,
                'artist' => $artist,
                'year' => $year,
                'genre' => $genre,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $this->info("Added: {$filePath}");
        }

        $this->info('Music scan completed and database updated.');

        return 0;
    }
}