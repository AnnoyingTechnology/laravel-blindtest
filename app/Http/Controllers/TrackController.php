<?php

namespace App\Http\Controllers;

use App\Models\Track;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Support\Facades\Storage;

class TrackController extends Controller
{
    public function getTrack($id)
    {

		// Find the track by ID
        $track = Track::findOrFail($id);

        // Get the file path from storage
        $filePath = 'music/' . $track->file;

        // Check if the file exists
        if (!Storage::disk('local')->exists($filePath)) {
            return response()->json(['error' => 'File not found'], 404);
        }

        // Get the full path to the file
        $fullPath = Storage::disk('local')->path($filePath);

        // Get file size
        $fileSize = filesize($fullPath);

        // Get MIME type
        $mimeType = mime_content_type($fullPath);

        // Set headers
        $headers = [
            'Content-Type' => $mimeType,
            'Content-Length' => $fileSize,
            'Accept-Ranges' => 'bytes',
        ];

        // Check if Range header is set
        if ($range = request()->header('Range')) {
            list($start, $end) = explode('-', str_replace('bytes=', '', $range));
            $end = $end ?: $fileSize - 1;
            $start = $start ?: 0;

            if ($start > 0 || $end < ($fileSize - 1)) {
                header('HTTP/1.1 206 Partial Content');
                $headers['Content-Length'] = $end - $start + 1;
                $headers['Content-Range'] = "bytes $start-$end/$fileSize";
            }
        }

        // Return streamed response
        return new StreamedResponse(function () use ($fullPath, $start, $end) {
            $handle = fopen($fullPath, 'rb');
            fseek($handle, $start);
            $buffer = 1024 * 8;
            $currentPosition = $start;

            while (!feof($handle) && $currentPosition <= $end) {
                $length = min($buffer, $end - $currentPosition + 1);
                echo fread($handle, $length);
                $currentPosition += $length;
                flush();
            }

            fclose($handle);
        }, 200, $headers);

    }
}
